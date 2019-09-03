<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Controller;

use Pixelant\PxaNewsletterSubscription\Controller\Traits\TranslateTrait;
use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\Domain\Repository\SubscriptionRepository;
use Pixelant\PxaNewsletterSubscription\Service\FlexFormSettingsService;
use Pixelant\PxaNewsletterSubscription\Service\HashService;
use Pixelant\PxaNewsletterSubscription\Service\Notification\EmailNotification;
use Pixelant\PxaNewsletterSubscription\Service\Notification\EmailNotificationFactory;
use Pixelant\PxaNewsletterSubscription\SignalSlot\EmitSignal;
use Pixelant\PxaNewsletterSubscription\Url\SubscriptionUrlGenerator;
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
    protected function notifySubscriber(Subscription $subscription): void
    {
        if (!empty($this->settings['notifySubscriber'])) {
            $subscriberNotification = $this->getEmailNotification('SubscriberSuccessSubscriptionNotification');

            $subscriberNotification
                ->setSubject($this->translate('mail.subscriber.success_subject'))
                ->setReceivers([$subscription->getEmail()]);

            $subscriberNotification->assignVariables(compact('subscription'));

            $this->emitSignal(__CLASS__, 'beforeSendEmail' . __METHOD__, $subscription);

            $subscriberNotification->send();
        }
    }

    /**
     * Send email notification to admin
     *
     * @param Subscription $subscription
     */
    protected function notifyAdmin(Subscription $subscription): void
    {
        if (!empty($this->settings['notifyAdmin'])) {
            $receivers = array_filter(
                GeneralUtility::trimExplode("\n", $this->settings['notifyAdmin'], true),
                function ($email) {
                    return GeneralUtility::validEmail($email);
                }
            );

            $adminNotification = $this->getEmailNotification('AdminNotification');

            $adminNotification
                ->setSubject($this->translate('mail.admin.subject'))
                ->setReceivers($receivers);

            $adminNotification->assignVariables(compact('subscription'));

            $this->emitSignal(__CLASS__, 'beforeSendEmail' . __METHOD__, $subscription);

            $adminNotification->send();
        }
    }

    /**
     * Send confirmation email
     *
     * @param Subscription $subscription
     */
    protected function sendSubscriberConfirmationEmail(Subscription $subscription): void
    {
        $subscriberNotification = $this->getEmailNotification('SubscriberConfirmationNotification');

        $subscriberNotification
            ->setSubject($this->translate('mail.subscriber.confirmation_subject'))
            ->setReceivers([$subscription->getEmail()]);

        $confirmationLink = $this->generateConfirmationLink(
            $subscription,
            intval($this->settings['confirmationPage']) ?: null
        );

        $subscriberNotification->assignVariables(compact('subscription', 'confirmationLink'));

        $this->emitSignal(__CLASS__, 'beforeSendEmail' . __METHOD__, $subscription);

        $subscriberNotification->send();
    }

    /**
     * Generate confirmation link
     *
     * @param Subscription $subscription
     * @param int|null $pageUid
     * @return string
     */
    protected function generateConfirmationLink(Subscription $subscription, int $pageUid = null): string
    {
        // Use own URL generator. This will make it possible to generate subscriptions related links from outside
        $urlGenerator = GeneralUtility::makeInstance(SubscriptionUrlGenerator::class, $this->hashService);

        return $urlGenerator->generateConfirmationUrl(
            $subscription,
            (int)$this->request->getArgument('ceUid'),
            $pageUid ?? $GLOBALS['TSFE']->id
        );
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
     * Prepare email notification, set sender name and email if set
     *
     * @param string $template
     * @return EmailNotification
     */
    protected function getEmailNotification(string $template): EmailNotification
    {
        $emailNotification = EmailNotificationFactory::getEmailNotification($template);
        $emailNotification
            ->setSenderEmail($this->settings['senderEmail'] ?? '')
            ->setSenderName($this->settings['senderName'] ?? '');

        return $emailNotification;
    }
}
