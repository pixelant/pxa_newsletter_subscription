<?php
declare(strict_types=1);

namespace Pixelant\PxaNewsletterSubscription\Controller;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class TranslateTrait
 * @package Pixelant\PxaNewsletterSubscription\Controller
 */
trait TranslateTrait
{
    /**
     * Translate by key
     *
     * @param string $key
     * @param array|null $arguments
     * @return string|null
     */
    protected function translate(string $key, array $arguments = null): ?string
    {
        return LocalizationUtility::translate($key, 'PxaNewsletterSubscription', $arguments);
    }
}
