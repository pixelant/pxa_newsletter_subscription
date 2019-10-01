<?php

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\Notification\NotificationInterface;

/**
 * Interface BuilderInterface
 * @package Pixelant\PxaNewsletterSubscription\Notification
 */
interface BuilderInterface
{
    /**
     * Required for every notification
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
     * Set sender email and name of notification
     */
    public function setSender(): void;

    /**
     * Set receiver email
     */
    public function setReceiver(): void;

    /**
     * Set subject of notification
     */
    public function setSubject(): void;

    /**
     * Sets template name of notification
     */
    public function setTemplate(): void;

    /**
     * Assign required variables to template
     */
    public function addTemplateVariables(): void;

    /**
     * Return notification
     *
     * @return NotificationInterface
     */
    public function getNotification(): NotificationInterface;
}
