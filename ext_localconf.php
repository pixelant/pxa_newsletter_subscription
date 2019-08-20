<?php
defined('TYPO3_MODE') || die('Access denied.');

(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Pixelant.pxa_newsletter_subscription',
        'Subscription',
        [
            'NewsletterSubscription' => 'form,ajax,confirm',
        ],
        // non-cacheable actions
        [
            'NewsletterSubscription' => 'ajax,confirm'
        ]
    );
})();
