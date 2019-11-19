<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification;

use Pixelant\PxaNewsletterSubscription\Notification\Builder\BuilderInterface;

/**
 * Notification director
 *
 * @package Pixelant\PxaNewsletterSubscription\Notification
 */
class Notificator
{
    /**
     * Build notification using builder
     *
     * @param BuilderInterface $builder
     * @return NotificationInterface
     */
    public function build(BuilderInterface $builder): NotificationInterface
    {
        $builder->createNotification();

        $builder->configureSubject();
        $builder->configureSender();
        $builder->configureRecipient();
        $builder->configureTemplate();
        $builder->assignTemplateVariables();

        return $builder->getNotification();
    }
}
