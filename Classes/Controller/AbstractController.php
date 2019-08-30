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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class AbstractController
 * @package Pixelant\PxaNewsletterSubscription\Controller
 */
abstract class AbstractController extends ActionController
{
    use TranslateTrait;

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

            $adminNotification = $this->getAdminNotification();

            $adminNotification
                ->setSubject($this->translate('confirm_mail_admin_subject'))
                ->setReceivers($receivers)
                ->setSenderEmail($this->settings['senderEmail'] ?? '')
                ->setSenderName($this->settings['senderName'] ?? '');

            $adminNotification->assignVariables(compact('subscription'));

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
        $subscriberNotification = $this->getSubscriberConfirmationNotification();

        $subscriberNotification
            ->setSubject($this->translate('confirm_mail_subject'))
            ->setSenderEmail($this->settings['senderEmail'] ?? '')
            ->setSenderName($this->settings['senderName'] ?? '')
            ->setReceivers([$subscription->getEmail()]);

        $confirmationLink = $this->generateConfirmationLink(
            $subscription,
            intval($this->settings['confirmationPage']) ?: null
        );

        $subscriberNotification->assignVariables(compact('subscription', 'confirmationLink'));

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
        $uriBuilder = $this->uriBuilder->reset();

        $arguments = [
            'subscription' => $subscription->getUid(),
            'hash' => $this->hashService->generateSubscriptionHash($subscription),
            'ceUid' => $this->request->getArgument('ceUid')
        ];

        $url = $uriBuilder
            ->setTargetPageUid($pageUid ?? $GLOBALS['TSFE']->id)
            ->setCreateAbsoluteUri(true)
            ->uriFor('confirm', $arguments, 'NewsletterSubscription');

        return $url;
    }

    /**
     * Get flexform settings by content UID from arguments and merge with settings
     */
    protected function mergeSettingsWithFlexFormSettings(): void
    {
        $flexFormSettings = $this->flexFormSettingsService->getFlexFormArray((int)$this->request->getArgument('ceUid'));

        if ($flexFormSettings !== null) {
            $this->settings = array_merge(
                $this->settings,
                $flexFormSettings['settings']
            );
        }
    }

    /**
     * @return EmailNotification
     */
    protected function getAdminNotification(): EmailNotification
    {
        return EmailNotificationFactory::getEmailNotification('AdminNotification');
    }

    /**
     * @return EmailNotification
     */
    protected function getSubscriberConfirmationNotification(): EmailNotification
    {
        return EmailNotificationFactory::getEmailNotification('SubscriberConfirmationNotification');
    }
}
