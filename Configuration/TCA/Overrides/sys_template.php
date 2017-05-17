<?php
defined('TYPO3_MODE') || die;

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'pxa_newsletter_subscription',
    'Configuration/TypoScript',
    'Newsletter Subscription'
);
