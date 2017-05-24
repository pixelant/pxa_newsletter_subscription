<?php

namespace Pixelant\PxaNewsletterSubscription\Controller;

use Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUser;
use Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUserGroup;
use Pixelant\PxaNewsletterSubscription\Service\EmailNotificationService;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * NewsletterSubscriptionController
 */
class NewsletterSubscriptionController extends ActionController
{
    const STATUS_SUBSCRIBE = 'subscribe';

    const STATUS_UNSUBSCRIBE = 'unsubscribe';

    /**
     * frontendUserRepository
     *
     * @var \Pixelant\PxaNewsletterSubscription\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository;

    /**
     * frontendUserGroupRepository
     *
     * @var \Pixelant\PxaNewsletterSubscription\Domain\Repository\FrontendUserGroupRepository
     * @inject
     */
    protected $frontendUserGroupRepository;

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
     * Renders confirm result as a content element if hash parameter is set
     *
     * @param string $status
     * @param string $hashid
     * @param string $hash
     */
    public function confirmAction($status, $hashid, $hash)
    {
        $id = intval($hashid);

        switch ($status) {
            case self::STATUS_SUBSCRIBE:
                $this->confirmSubscription($hash, $id);
                break;
            case self::STATUS_UNSUBSCRIBE:
                $this->unSubscribe($hash, $id);
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
        $isNewSubscription = $this->request->hasArgument('submitSubscribe');
        $arguments = array_map('trim', $this->request->getArguments());

        $message = $this->validateSubscription($isNewSubscription, $arguments);
        $valid = $message === '';

        if ($valid) {
            // It still could fail ?
            list($valid, $message) = $this->processSubscription($isNewSubscription, $arguments);
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
     * Process subscribe or unsubscribe action
     *
     * @param bool $isNewSubscription
     * @param array $arguments
     * @return array
     */
    protected function processSubscription($isNewSubscription, $arguments)
    {
        // Variables to store message and status
        $pid = intval($this->settings['saveFolder']);

        // Check what action to execute
        if ($isNewSubscription) {
            // Since name is validated and still can be empty if name isn't mandatory, set empty name from email.
            $name = empty($arguments['name']) ? $arguments['email'] : $arguments['name'];

            /** @var FrontendUserGroup $frontendUserGroup */
            $frontendUserGroup = $this->frontendUserGroupRepository->getFrontendUserGroupByUid(
                $this->settings['userGroup']
            );

            // Try to create feuser and store it in repository
            /** @var FrontendUser $frontendUser */
            $frontendUser = $this->objectManager->get(FrontendUser::class);
            $frontendUser->setAsSubscriber(
                $pid,
                $arguments['email'],
                $name,
                $this->settings['enableEmailConfirm'],
                $frontendUserGroup
            );

            // Signal slot for after fe_user creation
            $this->signalSlotDispatcher->dispatch(
                __CLASS__,
                'afterFeUserCreation',
                [$frontendUser, $this]
            );

            $this->frontendUserRepository->add($frontendUser);
            $this->persistenceManager->persistAll();

            // User was created
            if ($this->settings['enableEmailConfirm']) {
                /** @var EmailNotificationService $emailNotificationService */
                $emailNotificationService = GeneralUtility::makeInstance(
                    EmailNotificationService::class,
                    $this->settings
                );

                $emailNotificationService->sendConfirmationEmail(
                    $frontendUser,
                    $this->getFeLink($frontendUser->getUid(), self::STATUS_SUBSCRIBE),
                    false
                );

                $message = $this->translate('success.subscribe.subscribed-confirm');
            } else {
                // Add user
                $message = $this->translate('success.subscribe.subscribed-noconfirm');
            }

            $success = true;
        } else {
            /** @var FrontendUser $frontendUser */
            $frontendUser = $this->frontendUserRepository->getUserByEmailAndPid($arguments['email'], $pid);

            if ($this->settings['enableEmailConfirm']) {
                /** @var EmailNotificationService $emailNotificationService */
                $emailNotificationService = GeneralUtility::makeInstance(
                    EmailNotificationService::class,
                    $this->settings
                );

                $emailNotificationService->sendConfirmationEmail(
                    $frontendUser,
                    $this->getFeLink($frontendUser->getUid(), self::STATUS_UNSUBSCRIBE),
                    true
                );

                $message = $this->translate('success.unsubscribe.unsubscribed-confirm');
            } else {
                // Set user to deleted
                $this->frontendUserRepository->remove($frontendUser);
                $this->persistenceManager->persistAll();

                $message = $this->translate('success.unsubscribe.unsubscribed-noconfirm');
            }
            $success = true;
        }

        return [$success, $message];
    }

    /**
     * Check if data is valid
     *
     * @param bool $isNewSubscription
     * @param array $arguments
     *
     * @return string Empty if no error
     */
    protected function validateSubscription($isNewSubscription, $arguments)
    {
        $alreadyExist = $this->frontendUserRepository->doesEmailExistInPid(
            $arguments['email'],
            intval($this->settings['saveFolder'])
        );

        $message = '';

        if (!GeneralUtility::validEmail($arguments['email'])) {
            $message = $this->translate('error.invalid.email');
        } elseif ($isNewSubscription && $alreadyExist) {
            $message = $this->translate('error.subscribe.already-subscribed');
        } elseif ($isNewSubscription && !$this->isNameValid($arguments['name'])) {
            $message = $this->translate('error.invalid.name');
        } elseif ($isNewSubscription && is_null($this->frontendUserGroupRepository->getFrontendUserGroupByUid($this->settings['userGroup']))) {
            $message = $this->translate('error.subscribe.4101');
        } elseif (!$isNewSubscription && !$alreadyExist) {
            $message = $this->translate('error.unsubscribe.not-subscribed');
        }

        return $message;
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
     * Confirms subscription
     *
     * @param string $hash
     * @param string $id
     * @return void
     */
    protected function confirmSubscription($hash, $id)
    {

        $status = false;

        if ($this->hashService->validateHmac('pxa_newsletter_subscription-subscribe' . $id, $hash)) {
            $frontendUser = $this->frontendUserRepository->findByUid($id);
            $frontendUser->setDisable(0);

            $this->frontendUserRepository->update($frontendUser);
            $this->persistenceManager->persistAll();

            $message = $this->translate('subscribe_ok');
            $status = true;
        }

        if (!isset($message)) {
            $message = $this->translate('subscribe_error');
        }

        $this->view->assignMultiple([
            'message' => $message,
            'status' => $status
        ]);
    }

    /**
     * Unsubscribe
     *
     * @param string $hash
     * @param string $id
     * @return void
     */
    protected function unSubscribe($hash, $id)
    {
        $status = false;

        if ($this->hashService->validateHmac('pxa_newsletter_subscription-unsubscribe' . $id, $hash)) {
            $frontendUser = $this->frontendUserRepository->findByUid($id);

            $this->frontendUserRepository->remove($frontendUser);
            $this->persistenceManager->persistAll();

            $message = $this->translate('unsubscribe_ok');
            $status = true;
        }

        if (!isset($message)) {
            $message = $this->translate('unsubscribe_error');
        }

        $this->view->assignMultiple([
            'message' => $message,
            'status' => $status
        ]);
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

        $linkParams = array(
            'status' => $status,
            'hashid' => $id,
            'hash' => $this->hashService->generateHmac('pxa_newsletter_subscription-' . $status . $id)
        );


        return $this
            ->uriBuilder
            ->reset()
            ->setTargetPageUid($confirmPageId)
            ->setCreateAbsoluteUri(true)
            ->uriFor('confirm', $linkParams);
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
