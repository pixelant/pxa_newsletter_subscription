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
     * Notify
     *
     * @return bool
     */
    public function notify(): bool;
}
