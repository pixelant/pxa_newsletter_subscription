<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\TranslateTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AdminNewSubscriptionNotification
 * @package Pixelant\PxaNewsletterSubscription\Notification\Builder
 */
class AdminNewSubscriptionNotification extends AbstractBuilder
{
    use TranslateTrait;

    /**
     * Set receiver email
     */
    public function setReceiver(): void
    {
        $receivers = $this->getAdminsReceivers();
        $this->notification->setReceivers($receivers);
    }

    /**
     * Set subject of notification
     */
    public function setSubject(): void
    {
        $this->notification->setSubject($this->translate('mail.admin.subscribe.subject'));
    }

    /**
     * Sets template name of notification
     */
    public function setTemplate(): void
    {
        $this->notification->setNotificationTemplateName('AdminNewSubscription');
    }

    /**
     * Assign required variables to template
     */
    public function addTemplateVariables(): void
    {
        $this->notification->assignVariables([
            'subscription' => $this->subscription,
            'settings' => $this->settings
        ]);
    }
}
