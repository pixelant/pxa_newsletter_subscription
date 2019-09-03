<?php
defined('TYPO3_MODE') || die('Access denied.');

(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Pixelant.pxa_newsletter_subscription',
        'Subscription',
        [
            'NewsletterSubscription' => 'form, confirm, unsubscribe, unsubscribeConfirm',
            'Ajax' => 'subscribe',
        ],
        // non-cacheable actions
        [
            'NewsletterSubscription' => 'ajax, confirm, unsubscribe, unsubscribeConfirm',
            'Ajax' => 'subscribe',
        ]
    );

    // Register BE page layout hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['pxanewslettersubscription_subscription'][] =
        \Pixelant\PxaNewsletterSubscription\Hooks\BackendLayoutView::class . '->getExtensionSummary';
})();
