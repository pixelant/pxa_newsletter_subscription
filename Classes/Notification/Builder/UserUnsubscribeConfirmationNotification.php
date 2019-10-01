<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\TranslateTrait;

/**
 * Class UserUnsubscribeConfirmationNotification
 * @package Pixelant\PxaNewsletterSubscription\Notification\Builder
 */
class UserUnsubscribeConfirmationNotification extends AbstractBuilder
{
    use TranslateTrait;

    /**
     * Set receiver email
     */
    public function setReceiver(): void
    {
        $this->notification->setReceivers([$this->subscription->getEmail()]);
    }

    /**
     * Set subject of notification
     */
    public function setSubject(): void
    {
        $this->notification->setSubject($this->translate('mail.unsubscribe.confirmation_subject'));
    }

    /**
     * Sets template name of notification
     */
    public function setTemplate(): void
    {
        $this->notification->setNotificationTemplateName('UserUnsubscribeConfirmation');
    }

    /**
     * Assign required variables to template
     */
    public function addTemplateVariables(): void
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
