<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification;

use Pixelant\PxaNewsletterSubscription\Notification\Builder\BuilderInterface;

/**
 * Class Notificator
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

        $builder->setSubject();
        $builder->setSender();
        $builder->setReceiver();
        $builder->setTemplate();
        $builder->addTemplateVariables();

        return $builder->getNotification();
    }
}
