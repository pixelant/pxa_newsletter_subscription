<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Controller;

use Pixelant\PxaNewsletterSubscription\Controller\Traits\TranslateTrait;
use Pixelant\PxaNewsletterSubscription\SignalSlot\EmitSignal;
use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * Class NewsletterSubscriptionController
 * @package Pixelant\PxaNewsletterSubscription\Controller
 */
class NewsletterSubscriptionController extends AbstractController
{
    use TranslateTrait;
    use EmitSignal;

    /**
     * Read flexform settings of subsription content element on confirmation action
     */
    protected function initializeConfirmAction()
    {
        $this->mergeSettingsWithFlexFormSettings();
    }

    /**
     * Show form
     */
    public function formAction()
    {
        $this->checkPageTypeSettings();

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
        $success = false;
        if ($subscription !== null) {
            $subscription = $this->subscriptionRepository->findByUidHidden($subscription);
        }

        if (is_object($subscription) && $this->hashService->isValidSubscriptionHash($subscription, $hash)) {
            // Emit signal
            $this->emitSignal('beforeConfirmSubscription' . __METHOD__, $subscription, $hash, $this->settings);

            if ($subscription->isHidden()) {
                $subscription->setHidden(false);
                $this->subscriptionRepository->update($subscription);

                // Send notifications
                $this->notifyAdmin($subscription);
                $this->notifySubscriber($subscription);
            }

            $success = true;
        }

        $this->view->assignMultiple(compact('success', 'subscription'));
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
}
