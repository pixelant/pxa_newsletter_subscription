<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Service\Notification;

/**
 * Class SubscriberNotification
 * @package Pixelant\PxaNewsletterSubscription\Service\Notification
 */
class SubscriberNotification extends AbstractEmailNotification
{
    /**
     * Notification template name in EXT:pxa_newsletter_subscription/Resources/Private/Templates/Notification/
     *
     * @return string
     */
    public function getNotificationTemplateName(): string
    {
        return 'SubscriberNotification';
    }
}
