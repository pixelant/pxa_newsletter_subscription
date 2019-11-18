<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\TranslateTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Email notification for subscriber with instruction how to confirm subscription
 *
 * @package Pixelant\PxaNewsletterSubscription\Notification\Builder
 */
class UserConfirmationNotification extends AbstractBuilder
{
    use TranslateTrait;

    /**
     * Configure receiver of notification
     */
    public function configureRecipient(): void
    {
        $this->notification->setRecipients([$this->subscription->getEmail()]);
    }

    /**
     * Configure subject of notification
     */
    public function configureSubject(): void
    {
        $this->notification->setSubject($this->translate('mail.subscriber.confirmation_subject'));
    }

    /**
     * Configure template name of notification
     */
    public function configureTemplate(): void
    {
        $this->notification->setNotificationTemplateName('SubscribeConfirmation');
    }

    /**
     * Assign required variables to template
     */
    public function assignTemplateVariables(): void
    {
        $confirmationUrl = $this->getSubscriptionUrlGenerator()->generateConfirmationSubscriptionUrl(
            $this->subscription,
            (int)GeneralUtility::_GP('tx_pxanewslettersubscription_subscription')['ceUid'],
            intval($this->settings['confirmationPage']) ?: $GLOBALS['TSFE']->id
        );

        $variables = [
            'subscription' => $this->subscription,
            'settings' => $this->settings,
            'confirmationUrl' => $confirmationUrl
        ];

        $this->notification->assignVariables($variables);
    }
}
