<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\TranslateTrait;

/**
 * Email notification for subscriber about success subscription
 *
 * @package Pixelant\PxaNewsletterSubscription\Notification\Builder
 */
class UserSuccessSubscriptionNotification extends AbstractBuilder
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
        $this->notification->setSubject($this->translate('mail.subscriber.success_subject'));
    }

    /**
     * Configure template name of notification
     */
    public function configureTemplate(): void
    {
        $this->notification->setNotificationTemplateName('UserSuccessSubscription');
    }

    /**
     * Assign required variables to template
     */
    public function assignTemplateVariables(): void
    {
        $variables = [
            'subscription' => $this->subscription,
            'settings' => $this->settings
        ];
        if (!empty($this->settings['unsubscribePage'])) {
            $variables['unsubscribeUrl'] = $this->getSubscriptionUrlGenerator()->generateUnsubscribePageUrl(
                $this->subscription,
                $this->settings['unsubscribePage']
            );
        }

        $this->notification->assignVariables($variables);
    }
}
