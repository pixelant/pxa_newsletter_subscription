<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\TranslateTrait;

/**
 * Email notification for subscriber with unsubscribe confirmation instructions
 *
 * @package Pixelant\PxaNewsletterSubscription\Notification\Builder
 */
class UserUnsubscribeConfirmationNotification extends AbstractBuilder
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
        $this->notification->setSubject($this->translate('mail.unsubscribe.confirmation_subject'));
    }

    /**
     * Configure template name of notification
     */
    public function configureTemplate(): void
    {
        $this->notification->setNotificationTemplateName('UserUnsubscribeConfirmation');
    }

    /**
     * Assign required variables to template
     */
    public function assignTemplateVariables(): void
    {
        $this->notification->assignVariables([
            'subscription' => $this->notification,
            'settings' => $this->settings,
            'confirmationLink' => $this->getSubscriptionUrlGenerator()->generateConfirmationUnsubscribeUrl(
                $this->subscription,
                $GLOBALS['TSFE']->id
            )
        ]);
    }
}
