<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\TranslateTrait;

/**
 * Email notification for admin about unsubscription
 *
 * @package Pixelant\PxaNewsletterSubscription\Notification\Builder
 */
class AdminUnsubscribeNotification extends AbstractBuilder
{
    use TranslateTrait;

    /**
     * Configure receiver of notification
     */
    public function configureRecipient(): void
    {
        $this->notification->setRecipients($this->getAdminsRecipients());
    }

    /**
     * Configure subject of notification
     */
    public function configureSubject(): void
    {
        $this->notification->setSubject($this->translate('mail.admin.unsubscribe.subject'));
    }

    /**
     * Configure template name of notification
     */
    public function configureTemplate(): void
    {
        $this->notification->setNotificationTemplateName('AdminUnsubscribe');
    }

    /**
     * Assign required variables to template
     */
    public function assignTemplateVariables(): void
    {
        $this->notification->assignVariables(['subscription' => $this->subscription]);
    }
}
