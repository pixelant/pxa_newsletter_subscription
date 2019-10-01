<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Controller;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\Domain\Repository\SubscriptionRepository;
use Pixelant\PxaNewsletterSubscription\Notification\Builder\AdminNewSubscriptionNotification;
use Pixelant\PxaNewsletterSubscription\Notification\Builder\BuilderInterface;
use Pixelant\PxaNewsletterSubscription\Notification\Builder\UserSuccessSubscriptionNotification;
use Pixelant\PxaNewsletterSubscription\Notification\Notificator;
use Pixelant\PxaNewsletterSubscription\Service\FlexFormSettingsService;
use Pixelant\PxaNewsletterSubscription\Service\HashService;
use Pixelant\PxaNewsletterSubscription\SignalSlot\EmitSignal;
use Pixelant\PxaNewsletterSubscription\TranslateTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class AbstractController
 * @package Pixelant\PxaNewsletterSubscription\Controller
 */
abstract class AbstractController extends ActionController
{
    use TranslateTrait, EmitSignal;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository = null;

    /**
     * @var HashService
     */
    protected $hashService = null;

    /**
     * @var FlexFormSettingsService
     */
    protected $flexFormSettingsService = null;

    /**
     * @param FlexFormSettingsService $flexFormSettingsService
     */
    public function injectFlexFormSettingsService(FlexFormSettingsService $flexFormSettingsService)
    {
        $this->flexFormSettingsService = $flexFormSettingsService;
    }

    /**
     * @param SubscriptionRepository $subscriptionRepository
     */
    public function injectSubscriptionRepository(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @param HashService $hashService
     */
    public function injectHashService(HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    /**
     * Send notification to subscriber about successful subscription
     *
     * @param Subscription $subscription
     */
    protected function sendSubscriberSuccessSubscriptionEmail(Subscription $subscription): void
    {
        if (!empty($this->settings['notifySubscriber'])) {
            $this->sendNotification(UserSuccessSubscriptionNotification::class, $subscription);
        }
    }

    /**
     * Send email notification to admin
     *
     * @param Subscription $subscription
     */
    protected function sendAdminNewSubscriptionEmail(Subscription $subscription): void
    {
        if (!empty($this->settings['notifyAdmin'])) {
            $this->sendNotification(AdminNewSubscriptionNotification::class, $subscription);
        }
    }

    /**
     * Get flexform settings by content UID from arguments and merge with settings
     */
    protected function mergeSettingsWithFlexFormSettings(): void
    {
        $flexFormSettings = $this->flexFormSettingsService->getFlexFormArray((int)$this->request->getArgument('ceUid'));

        $this->settings = array_merge(
            $this->settings,
            $flexFormSettings['settings']
        );
    }

    /**
     * Send notification using builder
     *
     * @param string $notificationBuilder
     * @param Subscription $subscription
     * @return bool
     */
    protected function sendNotification(string $notificationBuilder, Subscription $subscription): bool
    {
        /** @var BuilderInterface $builder */
        $builder = GeneralUtility::makeInstance($notificationBuilder, $subscription, $this->settings);
        return GeneralUtility::makeInstance(Notificator::class)->build($builder)->notify();
    }
}
