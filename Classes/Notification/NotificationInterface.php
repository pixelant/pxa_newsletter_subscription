<?php
namespace Pixelant\PxaNewsletterSubscription\Notification;

/**
 * Interface Notification
 *
 * @package Pixelant\PxaNewsletterSubscription\Notification\Notificator
 */
interface NotificationInterface
{
    /**
     * Notify recipient
     *
     * @return bool
     */
    public function notify(): bool;
}
