<?php

namespace Pixelant\PxaNewsletterSubscription\Controller;

use Pixelant\PxaNewsletterSubscription\Service\EmailNotificationService;

use Pixelant\PxaNewsletterSubscription\Utility\FrontendUserStorageUtility;
use Pixelant\PxaNewsletterSubscription\Utility\AddressStorageUtility;
use Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUser;
use Pixelant\PxaNewsletterSubscription\Domain\Model\Address;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * NewsletterSubscriptionController
 */
class NewsletterSubscriptionController extends ActionController
{
    const STATUS_SUBSCRIBE = 'subscribe';

    const STATUS_UNSUBSCRIBE = 'unsubscribe';

    /**
     * RTE fields in settings to process with lib.parseFunc_RTE
     * @var array
     */
    protected static $rteFields = ['confirmMailSubscribeBody', 'confirmMailUnsubscribeBody'];

    /**
     * persistence manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     * @inject
     */
    protected $persistenceManager;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

    /**
     * Hash Service
     *
     * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
     * @inject
     */
    protected $hashService;

    /**
     * Prepare confirmation emails for ajax action
     */
    public function initializeAjaxAction()
    {
        foreach (self::$rteFields as $rteField) {
            $this->settings[$rteField] = $this->configurationManager->getContentObject()->parseFunc(
                $this->settings[$rteField],
                [],
                '< lib.parseFunc_RTE'
            );
        }
    }

    /**
     * Render form action
     *
     * @return void
     */
    public function formAction()
    {
        $this->view->assign(
            'ceuid',
            $this->configurationManager->getContentObject()->getFieldVal('uid')
        );
    }

    /**
     * Render confirm action
     *
     * Renders confirm result as a content element
     *
     * @param string $status
     * @param string $hashid
     * @param string $hash
     */
    public function confirmAction($status, $hashid, $hash)
    {
        $extensionConfArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pxa_newsletter_subscription']);
        $storageTable = $extensionConfArr['table'];

        if ($storageTable === 'fe_user') {
            $storageUtilityClass = $this->objectManager->get(
                FrontendUserStorageUtility::class,
                $this->settings
            );
        } elseif ($storageTable === 'tt_address') {
            $storageUtilityClass = $this->objectManager->get(
                AddressStorageUtility::class,
                $this->settings
            );
        } else {
            return;
        }

        $id = intval($hashid);

        switch ($status) {
            case self::STATUS_SUBSCRIBE:
                list($message, $status) = $storageUtilityClass->confirmSubscription($hash, $id);

                $this->view->assignMultiple([
                    'message' => $message,
                    'status' => $status
                ]);
                break;
            case self::STATUS_UNSUBSCRIBE:
                list($message, $status) = $storageUtilityClass->confirmUnsubscription($hash, $id);

                $this->view->assignMultiple([
                    'message' => $message,
                    'status' => $status
                ]);
                break;
        }
    }

    /**
     * Render ajax action
     *
     * Ajax action:
     * Return result of subscribe/unsubscribe
     *
     * @return void
     */
    public function ajaxAction()
    {
        $extensionConfArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pxa_newsletter_subscription']);
        $storageTable = $extensionConfArr['table'];

        if ($storageTable === 'fe_user') {
            $storageUtilityClass = $this->objectManager->get(
                FrontendUserStorageUtility::class,
                $this->settings
            );
        } elseif ($storageTable === 'tt_address') {
            $storageUtilityClass = $this->objectManager->get(
                AddressStorageUtility::class,
                $this->settings
            );
        } else {
            echo json_encode(
                [
                    'success' => false,
                    'message' => 'ERROR'            // TODO: add error message from translation file
                ]
            );
            exit(0);
        }

        $arguments = $this->request->getArguments();

        if (!GeneralUtility::validEmail($arguments['email'])) {
            $message = $this->translate('error.invalid.email');

            echo json_encode(
                [
                    'success' => false,
                    'message' => $message
                ]
            );
            exit(0);
        }

        $isNewSubscription = $this->request->hasArgument('submitSubscribe');
        foreach (['email', 'name'] as $item) {
            if (array_key_exists($item, $arguments)) {
                $arguments[$item] = trim($arguments[$item]);
            }
        }

        $subscriber = $storageUtilityClass->getSubscriber($arguments['email']);

        $message = $storageUtilityClass->validateSubscription($subscriber, $isNewSubscription, $arguments);
        $valid = $message === '';

        if ($valid) {
            // It still could fail ?
            if ($isNewSubscription) {
                $subscriber = $storageUtilityClass->processSubscription($arguments);

                // User was created
                if ($this->settings['enableEmailConfirm']) {
                    /** @var EmailNotificationService $emailNotificationService */
                    $emailNotificationService = GeneralUtility::makeInstance(
                        EmailNotificationService::class,
                        $this->settings
                    );

                    $emailNotificationService->sendConfirmationEmail(
                        $subscriber,
                        $this->getFeLink($subscriber->getUid(), self::STATUS_SUBSCRIBE),
                        false
                    );

                    $message = $this->translate('success.subscribe.subscribed-confirm');
                } else {
                    // Add user
                    $message = $this->translate('success.subscribe.subscribed-noconfirm');
                }

                $success = true;
            } else {
                // Variables to store message and status

                if ($this->settings['enableEmailConfirm']) {
                    /** @var EmailNotificationService $emailNotificationService */
                    $emailNotificationService = GeneralUtility::makeInstance(
                        EmailNotificationService::class,
                        $this->settings
                    );

                    $emailNotificationService->sendConfirmationEmail(
                        $subscriber,
                        $this->getFeLink($subscriber->getUid(), self::STATUS_UNSUBSCRIBE),
                        true
                    );

                    $message = $this->translate('success.unsubscribe.unsubscribed-confirm');
                } else {
                    $storageUtilityClass->revokeSubscription($subscriber);

                    $message = $this->translate('success.unsubscribe.unsubscribed-noconfirm');
                }

            }
        }

        echo json_encode(
            [
                'success' => $valid,
                'message' => $message
            ]
        );
        exit(0);
    }

    /**
     * Generates a link to frontend either to subscribe or unsubscribe.
     *
     * Also, if flexform setting Confirm Page is set, the link is to a page, otherwise it is a ajax link.
     *
     * @param int $id Frontenduser id
     * @param string $status Subscribe or unsubscribe
     * @return string
     */
    protected function getFeLink($id, $status)
    {
        $confirmPageId = intval($this->settings['confirmPage']) ?
            intval($this->settings['confirmPage']) : $GLOBALS['TSFE']->id;

        $linkParams = [
            'status' => $status,
            'hashid' => $id,
            'hash' => $this->hashService->generateHmac('pxa_newsletter_subscription-' . $status . $id)
        ];


        return $this
            ->uriBuilder
            ->reset()
            ->setTargetPageUid($confirmPageId)
            ->setCreateAbsoluteUri(true)
            ->uriFor('confirm', $linkParams);
    }

    /**
     * Translate label
     *
     * @param string $key
     * @return NULL|string
     */
    protected function translate($key = '')
    {
        return LocalizationUtility::translate($key, 'pxa_newsletter_subscription');
    }

    /**
     * Check if name is valid.
     *
     * @var string $name Name
     * @return bool
     */
    protected function isNameValid($name)
    {
        return !$this->settings['formFieldNameIsMandatory'] || !empty($name);
    }
}
