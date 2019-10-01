<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\TranslateTrait;

/**
 * Class UserSuccessSubscriptionNotification
 * @package Pixelant\PxaNewsletterSubscription\Notification\Builder
 */
class UserSuccessSubscriptionNotification extends AbstractBuilder
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
        $this->notification->setSubject($this->translate('mail.subscriber.success_subject'));
    }

    /**
     * Sets template name of notification
     */
    public function setTemplate(): void
    {
        $this->notification->setNotificationTemplateName('UserSuccessSubscription');
    }

    /**
     * Assign required variables to template
     */
    public function addTemplateVariables(): void
    {
        $variables = [
            'subscription' => $this->subscription,
            'settings' => $this->settings
        ];
        if (!empty($this->settings['unsubscribePage'])) {
            $variables['unsubscribeUrl'] = $this->getSubscriptionUrlGenerator()->generateUnsubscribePageUrl($this->subscription, $this->settings['unsubscribePage']);
        }

        $this->notification->assignVariables($variables);
    }
}
