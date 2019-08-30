<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Service\Notification;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EmailNotificationFactory
 * @package Pixelant\PxaNewsletterSubscription\Service\Notification
 */
class EmailNotificationFactory
{
    /**
     * Factory method
     *
     * @param string $notificationTemplateName
     * @return EmailNotification
     */
    public static function getEmailNotification(string $notificationTemplateName): EmailNotification
    {
        $emailNotification = GeneralUtility::makeInstance(EmailNotification::class);
        $emailNotification->setNotificationTemplateName($notificationTemplateName);

        return $emailNotification;
    }
}
