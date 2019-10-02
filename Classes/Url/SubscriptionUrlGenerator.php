<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Url;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use Pixelant\PxaNewsletterSubscription\Service\HashService;
use Pixelant\PxaNewsletterSubscription\SignalSlot\EmitSignal;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class SubscriptionUrlGenerator
 * @package Pixelant\PxaNewsletterSubscription\Url
 */
class SubscriptionUrlGenerator
{
    use EmitSignal;

    /**
     * Arguments namespace
     *
     * @var string
     */
    protected $namespace = 'tx_pxanewslettersubscription_subscription';

    /**
     * @var HashService
     */
    protected $hashService = null;

    /**
     * SubscriptionUrlGenerator constructor
     *
     * @param HashService|null $hashService
     */
    public function __construct(HashService $hashService = null)
    {
        $this->hashService = $hashService ?? GeneralUtility::makeInstance(HashService::class);
    }

    /**
     * Create confirmation URL for subscription
     *
     * @param Subscription $subscription
     * @param int $pluginUid
     * @param $targetPage
     * @return string
     */
    public function generateConfirmationSubscriptionUrl(Subscription $subscription, int $pluginUid, $targetPage): string
    {
        return $this->generateUrlForActionAndHash(
            'confirm',
            $subscription->getUid(),
            $this->hashService->generateSubscriptionHash($subscription),
            $targetPage,
            $pluginUid
        );
    }

    /**
     * @param Subscription $subscription
     * @param $targetPage
     * @return string
     */
    public function generateConfirmationUnsubscribeUrl(Subscription $subscription, $targetPage): string
    {
        return $this->generateUrlForActionAndHash(
            'unsubscribeConfirm',
            $subscription->getUid(),
            $this->hashService->generateUnsubscriptionHash($subscription),
            $targetPage
        );
    }

    /**
     * URL to unsubscribe page
     *
     * @param Subscription $subscription
     * @param $targetPage
     * @return string
     */
    public function generateUnsubscribePageUrl(Subscription $subscription, $targetPage): string
    {
        $arguments = [
            'email' => $subscription->getEmail(),
            'action' => 'unsubscribe',
            'controller' => 'NewsletterSubscription',
        ];

        $conf = [
            'parameter' => $targetPage,
            'useCacheHash' => true,
            'additionalParams' => GeneralUtility::implodeArrayForUrl($this->namespace, $arguments),
            'forceAbsoluteUrl' => true
        ];

        $signalArguments = [
            'conf' => &$conf
        ];
        $this->emitSignal(__CLASS__, 'beforeBuildUrlUnsubscribe', $signalArguments);

        return $this->getContentObjectRenderer()->typolink_URL($conf);
    }

    /**
     * Generate url for confirm or unsubscribe action
     *
     * @param string $action
     * @param int $subscriptionUid
     * @param string $hash
     * @param $targetPage
     * @param int|null $pluginUid
     * @return string
     */
    protected function generateUrlForActionAndHash(
        string $action,
        int $subscriptionUid,
        string $hash,
        $targetPage,
        int $pluginUid = null
    ): string {
        $arguments = [
            'subscription' => $subscriptionUid,
            'hash' => $hash,
            'action' => $action,
            'controller' => 'NewsletterSubscription',
        ];
        if ($pluginUid !== null) {
            $arguments['ceUid'] = $pluginUid;
        }

        $conf = [
            'parameter' => $targetPage,
            'useCacheHash' => true,
            'additionalParams' => GeneralUtility::implodeArrayForUrl($this->namespace, $arguments),
            'forceAbsoluteUrl' => true
        ];

        $signalArguments = [
            'conf' => &$conf
        ];
        $this->emitSignal(__CLASS__, 'beforeBuildUrl' . $action, $signalArguments);

        return $this->getContentObjectRenderer()->typolink_URL($conf);
    }

    /**
     * @return ContentObjectRenderer
     */
    protected function getContentObjectRenderer(): ContentObjectRenderer
    {
        return GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }
}
