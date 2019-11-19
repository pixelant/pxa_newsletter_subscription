<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Notification\Builder;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\Notification\NotificationInterface;
use Pixelant\PxaNewsletterSubscription\Notification\EmailNotification;
use Pixelant\PxaNewsletterSubscription\Url\SubscriptionUrlGenerator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract builder for notifications. Include common logic for all builders
 *
 * @package Pixelant\PxaNewsletterSubscription\Notification\Builder
 */
abstract class AbstractBuilder implements BuilderInterface
{

    /**
     * @var Subscription
     */
    protected $subscription = null;

    /**
     * Plugin settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * @var EmailNotification
     */
    protected $notification = null;

    /**
     * Initialize builder
     *
     * @param Subscription $subscription
     * @param array $settings
     */
    public function __construct(Subscription $subscription, array $settings)
    {
        $this->subscription = $subscription;
        $this->settings = $settings;
    }

    /**
     * Configure sender of notification
     */
    public function configureSender(): void
    {
        $this->notification->setSenderEmail($this->settings['senderEmail'] ?? '');
        $this->notification->setSenderName($this->settings['senderName'] ?? '');
    }

    /**
     * Create notification
     *
     * @return mixed
     */
    public function createNotification(): void
    {
        $this->notification = GeneralUtility::makeInstance(EmailNotification::class);
    }

    /**
     * Return notification
     *
     * @return NotificationInterface
     */
    public function getNotification(): NotificationInterface
    {
        return $this->notification;
    }

    /**
     * Use custom URL generator. This makes it possible to generate subscriptions related links from outside
     *
     * @return SubscriptionUrlGenerator
     */
    protected function getSubscriptionUrlGenerator(): SubscriptionUrlGenerator
    {
        return GeneralUtility::makeInstance(SubscriptionUrlGenerator::class);
    }

    /**
     * Get admin emails from settings
     *
     * @return array
     */
    protected function getAdminsRecipients(): array
    {
        $recipients = array_filter(
            GeneralUtility::trimExplode("\n", $this->settings['notifyAdmin'], true),
            function ($email) {
                return GeneralUtility::validEmail($email);
            }
        );

        return $recipients;
    }
}
