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
    const HASH_PREFIX_SUBSCRIPTION = 'pxa_newsletter_subscription-subscribe-';

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
     * Validate a subscription hash
     *
     * @param Subscription $subscription
     * @param string $hash The hash to validate
     * @return bool True if hash is valid
     */
    public function isValidSubscriptionHash(Subscription $subscription, string $hash): bool
    {
        return $this->hmacHashService->validateHmac(static::HASH_PREFIX_SUBSCRIPTION . $subscription->getUid(), $hash);
    }

    /**
     * Generate a subscription hash
     *
     * @param Subscription $subscription
     * @return string The generated hash
     */
    public function generateSubscriptionHash(Subscription $subscription)
    {
        return $this->hmacHashService->generateHmac(self::HASH_PREFIX_SUBSCRIPTION . $subscription->getUid());
    }
}
