<?php

namespace Pixelant\PxaNewsletterSubscription\Service;

use Pixelant\PxaNewsletterSubscription\Domain\Model\Subscription;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService as HmacHashService;

/**
 * Generate and check newsletter specific validation hashes
 *
 * Class HashService
 * @package Pixelant\PxaNewsletterSubscription\Service
 */
class HashService
{
    const HASH_PREFIX_SUBSCRIBE = 'pxa_newsletter_subscription-subscribe-';
    const HASH_PREFIX_UNSUBSCRIBE = 'pxa_newsletter_subscription-unsubscribe-';

    /**
     * @var HmacHashService
     */
    protected $hmacHashService = null;

    /**
     * HashService constructor
     */
    public function __construct()
    {
        $this->hmacHashService = GeneralUtility::makeInstance(HmacHashService::class);
    }

    /**
     * Validate a subscribe hash
     *
     * @param Subscription $subscription
     * @param string $hash The hash to validate
     * @return bool True if hash is valid
     */
    public function isValidSubscribeHash(Subscription $subscription, string $hash): bool
    {
        return $this->hmacHashService->validateHmac(self::HASH_PREFIX_SUBSCRIBE . $subscription->getUid(), $hash);
    }

    /**
     * Generate a subscribe hash
     *
     * @param Subscription $subscription
     * @return string The generated hash
     */
    public function generateSubscribeHash(Subscription $subscription)
    {
        return $this->hmacHashService->generateHmac(self::HASH_PREFIX_SUBSCRIBE . $subscription->getUid());
    }

    /**
     * Validate a unsubscribe hash
     *
     * @param Subscription $subscription
     * @param string $hash The hash to validate
     * @return bool True if hash is valid
     */
    public function isValidUnsubscribeHash(Subscription $subscription, string $hash): bool
    {
        return $this->hmacHashService->validateHmac(self::HASH_PREFIX_UNSUBSCRIBE . $subscription->getUid(), $hash);
    }

    /**
     * Generate a unsubscribe hash
     *
     * @param Subscription $subscription
     * @return string The generated hash
     */
    public function generateUnsubscribeHash(Subscription $subscription)
    {
        return $this->hmacHashService->generateHmac(self::HASH_PREFIX_UNSUBSCRIBE . $subscription->getUid());
    }
}
