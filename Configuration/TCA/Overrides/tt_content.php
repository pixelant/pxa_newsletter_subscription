<?php
defined('TYPO3_MODE') || die;

(function () {
    // Register FE plugin
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'pxa_newsletter_subscription',
        'Subscription',
        'Pxa Subscription'
    );

    $pluginKey = 'pxanewslettersubscription_subscription';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginKey] = 'pages,recursive,layout,select_key';

    // Add flexform
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginKey] = 'pi_flexform';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        $pluginKey,
        'FILE:EXT:pxa_newsletter_subscription/Configuration/FlexForms/FlexFormSubscription.xml'
    );
})();
