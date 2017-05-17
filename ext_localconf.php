<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Pixelant.' . $_EXTKEY,
    'Subscription',
    [
        'NewsletterSubscription' => 'form,ajax,confirm',
    ],
    // non-cacheable actions
    [
        'NewsletterSubscription' => 'ajax,confirm'
    ]
);
