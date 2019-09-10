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
    protected function sendSubscriberSuccessSubscriptionEmail(Subscription $subscription): void
    {
        if (!empty($this->settings['notifySubscriber'])) {
            $subscriberNotification = $this->getEmailNotification('SubscriberSuccessSubscription');

            $subscriberNotification
                ->setSubject($this->translate('mail.subscriber.success_subject'))
                ->setReceivers([$subscription->getEmail()]);

            // Unsubscribe link
            $unsubscribeUrl = !empty($this->settings['unsubscribePage'])
                ? $this->getSubscriptionUrlGenerator()->generateUnsubscribePageUrl($subscription, $this->settings['unsubscribePage'])
                : '';

            $subscriberNotification->assignVariables(compact('subscription', 'unsubscribeUrl') + ['settings' => $this->settings]);

            $this->emitSignal(__CLASS__, 'beforeSendEmail' . __METHOD__, $subscription, $subscriberNotification);

            $subscriberNotification->send();
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
            $receivers = array_filter(
                GeneralUtility::trimExplode("\n", $this->settings['notifyAdmin'], true),
                function ($email) {
                    return GeneralUtility::validEmail($email);
                }
            );

            $adminNotification = $this->getEmailNotification('AdminNewSubscription');

            $adminNotification
                ->setSubject($this->translate('mail.admin.subject'))
                ->setReceivers($receivers);

            $adminNotification->assignVariables(compact('subscription') + ['settings' => $this->settings]);

            $this->emitSignal(__CLASS__, 'beforeSendEmail' . __METHOD__, $subscription, $adminNotification);

            $adminNotification->send();
        }
    }

    /**
     * Send confirmation email
     *
     * @param Subscription $subscription
     */
    protected function sendSubscribeConfirmationEmail(Subscription $subscription): void
    {
        $subscriberNotification = $this->getEmailNotification('SubscribeConfirmation');

        $subscriberNotification
            ->setSubject($this->translate('mail.subscriber.confirmation_subject'))
            ->setReceivers([$subscription->getEmail()]);

        // Subscription confirmation email
        $confirmationUrl = $this->getSubscriptionUrlGenerator()->generateConfirmationSubscriptionUrl(
            $subscription,
            (int)$this->request->getArgument('ceUid'),
            intval($this->settings['confirmationPage']) ?: $GLOBALS['TSFE']->id
        );

        $subscriberNotification->assignVariables(compact('subscription', 'confirmationUrl') + ['settings' => $this->settings]);

        $this->emitSignal(__CLASS__, 'beforeSendEmail' . __METHOD__, $subscription, $subscriberNotification);

        $subscriberNotification->send();
    }

    /**
     * Send confirmation email in order to unsubscribe
     *
     * @param Subscription $subscription
     */
    protected function sendUnsubscribeConfirmationEmail(Subscription $subscription): void
    {
        $subscriberNotification = $this->getEmailNotification('UnsubscribeConfirmation');

        $subscriberNotification
            ->setSubject($this->translate('mail.unsubscribe.confirmation_subject'))
            ->setReceivers([$subscription->getEmail()]);

        $subscriptionUrlGenerator = $this->getSubscriptionUrlGenerator();

        // Unsubscription confirmation email
        $confirmationLink = $subscriptionUrlGenerator->generateConfirmationUnsubscribeUrl(
            $subscription,
            $GLOBALS['TSFE']->id
        );

        $subscriberNotification->assignVariables(compact('subscription', 'confirmationLink') + ['settings' => $this->settings]);

        $this->emitSignal(__CLASS__, 'beforeSendEmail' . __METHOD__, $subscription, $subscriberNotification);

        $subscriberNotification->send();
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

    /**
     * Use own URL generator. This will make it possible to generate subscriptions related links from outside
     *
     * @return SubscriptionUrlGenerator
     */
    protected function getSubscriptionUrlGenerator(): SubscriptionUrlGenerator
    {
        return GeneralUtility::makeInstance(SubscriptionUrlGenerator::class, $this->hashService);
    }
}
