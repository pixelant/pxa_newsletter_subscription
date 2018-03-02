<?php

namespace Pixelant\PxaNewsletterSubscription\Service;

use Pixelant\PxaNewsletterSubscription\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Generate and check newsletter specific validation hashes
 *
 * Class HashService
 * @package Pixelant\PxaNewsletterSubscription\Service
 */
class HashService extends \TYPO3\CMS\Extbase\Security\Cryptography\HashService
{
	const HASH_PREFIX_SUBSCRIPTION = 'pxa_newsletter_subscription-subscribe-';
	const HASH_PREFIX_REDIRECT = 'pxa_newsletter_subscription-redirect-';

	/**
	 * Validate a subscription hash
	 *
	 * @param int $id The record uid
	 * @param string $hash The hash to validate
	 * @return bool True if hash is valid
	 */
	public function validateSubscriptionHash($id, $hash) {
		return $this->validateHmac(self::HASH_PREFIX_SUBSCRIPTION . $id, $hash);
	}

	/**
	 * Generate a subscription hash
	 *
	 * @param int $id The record uid
	 * @return string The generated hash
	 * @throws \TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException
	 */
	public function generateSubscriptionHash($id) {
		return $this->generateHmac(self::HASH_PREFIX_SUBSCRIPTION . $id);
	}

	/**
	 * Validate a redirect hash
	 *
	 * @param int $id The record uid
	 * @param string $hash The hash to validate
	 * @return bool True if the hash is valid
	 */
	public function validateRedirectHash($id, $hash) {
		return $this->validateHmac(self::HASH_PREFIX_REDIRECT . $id, $hash);
	}

	/**
	 * Generate a redirect validation hash
	 *
	 * @param $id The record uid
	 * @return string The generated hash
	 * @throws \TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException
	 */
	public function generateRedirectHash($id) {
		return $this->generateHmac(self::HASH_PREFIX_REDIRECT . $id);
	}

	/**
	 * Picks validation data from redirect url GET parameters and performs a validation
	 *
	 * @return bool True if hash is valid
	 */
	public function validateRedirectHashInGetParameters() {
		$parameters = GeneralUtility::_GET('tx_pxanewslettersubscription_subscription');
		return $this->validateRedirectHash($parameters['uid'], $parameters['hash']);
	}
}
