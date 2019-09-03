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
    public function generateConfirmationUrl(Subscription $subscription, int $pluginUid, $targetPage): string
    {
        return $this->generateUrlForActionAndHash(
            'confirm',
            $subscription->getUid(),
            $this->hashService->generateSubscriptionHash($subscription),
            $pluginUid,
            $targetPage
        );
    }

    /**
     * @param Subscription $subscription
     * @param int $pluginUid
     * @param $targetPage
     * @return string
     */
    public function generateUnsubscribeUrl(Subscription $subscription, int $pluginUid, $targetPage): string
    {
        return $this->generateUrlForActionAndHash(
            'unsubscribe',
            $subscription->getUid(),
            $this->hashService->generateUnsubscriptionHash($subscription),
            $pluginUid,
            $targetPage
        );
    }

    /**
     * Generate url for confirm or unsubscribe action
     *
     * @param string $action
     * @param int $subscriptionUid
     * @param string $hash
     * @param int $pluginUid
     * @param $targetPage
     * @return string
     */
    protected function generateUrlForActionAndHash(string $action, int $subscriptionUid, string $hash, int $pluginUid, $targetPage): string
    {
        $arguments = [
            'subscription' => $subscriptionUid,
            'hash' => $hash,
            'ceUid' => $pluginUid,
            'action' => $action,
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
