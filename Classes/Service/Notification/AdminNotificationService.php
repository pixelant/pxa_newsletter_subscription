<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Service\Notification;

/**
 * Class AdminNotificationService
 * @package Pixelant\PxaNewsletterSubscription\Service\Notification
 */
class AdminNotificationService extends AbstractEmailNotification
{
    /**
     * Notification template name in EXT:pxa_newsletter_subscription/Resources/Private/Templates/Notification/
     *
     * @return string
     */
    public function getNotificationTemplateName(): string
    {
        return 'AdminNotification';
    }
}
