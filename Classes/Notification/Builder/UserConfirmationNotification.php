<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\TranslateTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class UserConfirmationNotificationBuilder
 * @package Pixelant\PxaNewsletterSubscription\Notification\Builder
 */
class UserConfirmationNotification extends AbstractBuilder
{
    use TranslateTrait;

    /**
     * Set receiver email
     */
    public function setReceiver(): void
    {
        $this->notification->setReceivers([$this->subscription->getEmail()]);
    }

    /**
     * Set subject of notification
     */
    public function setSubject(): void
    {
        $this->notification->setSubject($this->translate('mail.subscriber.confirmation_subject'));
    }

    /**
     * Sets template name of notification
     */
    public function setTemplate(): void
    {
        $this->notification->setNotificationTemplateName('SubscribeConfirmation');
    }

    /**
     * Assign required variables to template
     */
    public function addTemplateVariables(): void
    {
        $confirmationUrl = $this->getSubscriptionUrlGenerator()->generateConfirmationSubscriptionUrl(
            $this->subscription,
            (int)GeneralUtility::_GP('tx_pxanewslettersubscription_subscription')['ceUid'],
            intval($this->settings['confirmationPage']) ?: $GLOBALS['TSFE']->id
        );

        $variables = [
            'subscription' => $this->subscription,
            'settings' => $this->settings,
            'confirmationUrl' => $confirmationUrl
        ];

        $this->notification->assignVariables($variables);
    }
}
