<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\TranslateTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Email notification is send to admin about new subscription
 *
 * @package Pixelant\PxaNewsletterSubscription\Notification\Builder
 */
class AdminNewSubscriptionNotification extends AbstractBuilder
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
        $this->notification->setSubject($this->translate('mail.admin.subscribe.subject'));
    }

    /**
     * Configure template name of notification
     */
    public function configureTemplate(): void
    {
        $this->notification->setNotificationTemplateName('AdminNewSubscription');
    }

    /**
     * Assign required variables to template
     */
    public function assignTemplateVariables(): void
    {
        $this->notification->assignVariables([
            'subscription' => $this->subscription,
            'settings' => $this->settings
        ]);
    }
}
