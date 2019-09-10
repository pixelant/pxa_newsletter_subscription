<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Controller;

use Pixelant\PxaNewsletterSubscription\Controller\Traits\TranslateTrait;
use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\SignalSlot\EmitSignal;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class NewsletterSubscriptionController
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
        // If no parameters passed means that page was just visited as regular page
        // No action to perform
        if ($subscription === null && $hash === '') {
            $this->view->assign('noAction', true);
            return;
        }

        // Read flexform settings of subscription content element on confirmation action
        $this->mergeSettingsWithFlexFormSettings();

        $success = false;

        if ($subscription !== null) {
            $subscription = $this->subscriptionRepository->findByUidHidden($subscription);
        }

        if (is_object($subscription) && $this->hashService->isValidSubscriptionHash($subscription, $hash)) {
            // Emit signal
            $this->emitSignal(__CLASS__, 'beforeConfirmSubscription' . __METHOD__, $subscription, $hash, $this->settings);

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

        $this->view->assignMultiple(compact('success', 'subscription'));
    }

    /**
     * Unsubscribe form
     *
     * @param string $email
     */
    public function unsubscribeAction(string $email = '')
    {
        if (!empty($email)) {
            $subscription = $this->subscriptionRepository->findByEmailAndPid($email, (int)$this->settings['storagePid']);

            if ($subscription !== null) {
                $this->emitSignal(__CLASS__, 'unsubscribeRequest' . __METHOD__, $subscription);

                $this->sendUnsubscribeConfirmationEmail($subscription);

                $this->redirect('unsubscribeMessage', null, null, compact('subscription'));
            }
        }

        $this->view->assign('email', $email);
    }

    /**
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

        if ($subscription !== null && $this->hashService->isValidUnsubscriptionHash($subscription, $hash)) {
            $this->emitSignal(__CLASS__, 'unsubscribe' . __METHOD__, $subscription);

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
}
