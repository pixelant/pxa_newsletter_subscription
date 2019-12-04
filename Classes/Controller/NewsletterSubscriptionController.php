<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Controller;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\Notification\Builder\AdminUnsubscribeNotification;
use Pixelant\PxaNewsletterSubscription\Notification\Builder\UserUnsubscribeConfirmationNotification;
use Pixelant\PxaNewsletterSubscription\SignalSlot\EmitSignal;
use Pixelant\PxaNewsletterSubscription\TranslateTrait;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Controller handling plugin actions and in-page rendering.
 *
 * @package Pixelant\PxaNewsletterSubscription\Controller
 */
class NewsletterSubscriptionController extends AbstractController
{
    use TranslateTrait;
    use EmitSignal;

    /**
     * Show form
     */
    public function formAction()
    {
        $this->checkPageTypeSettings();
        $this->checkSenderEmail();

        $this->view->assign('ceUid', $this->configurationManager->getContentObject()->getFieldVal('uid'));
    }

    /**
     * Confirm user subscription
     *
     * @param int $subscription
     * @param string $hash
     */
    public function confirmAction(int $subscription = null, string $hash = '')
    {
        // If no parameters are passed, this is just a regular page visit
        // No action to perform
        if ($subscription === null && $hash === '') {
            $this->view->assign('noAction', true);
            return;
        }

        // Read flexform settings of subscription content element on confirmation action
        $this->mergeSettingsWithFlexFormSettings();

        /*
         * In case when subscription form is in footer or header of every site page
         * and in form settings custom confirmation page was set, avoid executing confirm action two times
         */
        $this->forwardToFormIfCustomConfirmationPage();

        $success = false;

        if ($subscription !== null) {
            $subscription = $this->subscriptionRepository->findByUidHidden($subscription);
        }

        if (is_object($subscription) && $this->hashService->isValidSubscribeHash($subscription, $hash)) {
            // Emit signal
            $this->emitSignal(
                __CLASS__,
                'beforeConfirmSubscription',
                $subscription,
                $hash,
                $this->settings
            );

            if ($subscription->isHidden()) {
                $subscription->setHidden(false);
                $this->subscriptionRepository->update($subscription);

                $success = true;

                // Send notifications
                $this->sendAdminNewSubscriptionEmail($subscription);
                $this->sendSubscriberSuccessSubscriptionEmail($subscription);
            } else {
                $this->view->assign('errorReason', 'already_confirmed');
            }
        }

        $this->view->assignMultiple([
            'success' => $success,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Unsubscribe form
     *
     * @param string $email
     */
    public function unsubscribeAction(string $email = '')
    {
        if (!empty($email)) {
            $subscription = $this->subscriptionRepository->findByEmailAndPid(
                $email,
                (int)$this->settings['storagePid']
            );

            if ($subscription !== null) {
                $this->emitSignal(__CLASS__, 'unsubscribeRequest', $subscription);

                $this->sendNotification(UserUnsubscribeConfirmationNotification::class, $subscription);

                $this->redirect('unsubscribeMessage', null, null, compact('subscription'));
            }
        }

        $this->view->assign('email', $email);
    }

    /**
     * Show unsubscribe instructions message after user submitted unsubscribe form
     *
     * @param Subscription $subscription
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("subscription")
     */
    public function unsubscribeMessageAction(Subscription $subscription)
    {
        $this->view->assign('subscription', $subscription);
    }

    /**
     * @param Subscription $subscription
     * @param string $hash
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("subscription")
     */
    public function unsubscribeConfirmAction(Subscription $subscription = null, string $hash = '')
    {
        $success = false;

        if ($subscription !== null && $this->hashService->isValidUnsubscribeHash($subscription, $hash)) {
            $this->emitSignal(__CLASS__, 'unsubscribe', $subscription);

            if (!empty($this->settings['notifyAdmin'])) {
                $this->sendNotification(AdminUnsubscribeNotification::class, $subscription);
            }

            $this->subscriptionRepository->remove($subscription);
            $success = true;
        }

        $this->view->assign('success', $success);
    }

    /**
     * Check if ajax page type is set in settings
     * Add flash message if setting is missing
     */
    protected function checkPageTypeSettings(): void
    {
        if (empty($this->settings['ajaxPageType'])) {
            $this->addFlashMessage(
                $this->translate('error.missing_page_type'),
                '',
                FlashMessage::ERROR
            );
        }
    }

    /**
     * Check if sender name is valid in case emails enabled
     */
    protected function checkSenderEmail()
    {
        if ((!empty($this->settings['notifyAdmin'])
                || !empty($this->settings['notifySubscriber'])
                || !empty($this->settings['enableEmailConfirmation'])
            )
            && !GeneralUtility::validEmail($this->settings['senderEmail'])
        ) {
            $this->addFlashMessage(
                $this->translate('error.sender_email_invalid'),
                '',
                FlashMessage::ERROR
            );
        }
    }

    /**
     * Forward action to form if it's form plugin,
     * but this is custom confirmation page with own confirmation plugin
     */
    protected function forwardToFormIfCustomConfirmationPage(): void
    {
        $contentData = $this->configurationManager->getContentObject()->data;

        if ((int)$this->request->getArgument('ceUid') === $contentData['uid']
            && $contentData['pid'] !== $GLOBALS['TSFE']->id
        ) {
            $this->forward('form');
        }
    }
}
