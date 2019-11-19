<?php

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\Notification\NotificationInterface;

/**
 * Notification builder interface
 *
 * @package Pixelant\PxaNewsletterSubscription\Notification
 */
interface BuilderInterface
{
    /**
     * Initialize builder
     *
     * @param Subscription $subscription
     * @param array $settings
     */
    public function __construct(Subscription $subscription, array $settings);

    /**
     * Create notification
     *
     * @return mixed
     */
    public function createNotification(): void;

    /**
     * Configure sender of notification
     */
    public function configureSender(): void;

    /**
     * Configure recipient of notification
     */
    public function configureRecipient(): void;

    /**
     * Configure subject of notification
     */
    public function configureSubject(): void;

    /**
     * Configure template name of notification
     */
    public function configureTemplate(): void;

    /**
     * Assign required variables to template
     */
    public function assignTemplateVariables(): void;

    /**
     * Return notification
     *
     * @return NotificationInterface
     */
    public function getNotification(): NotificationInterface;
}
