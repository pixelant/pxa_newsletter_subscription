<?php
defined('TYPO3_MODE') || die;

// Register FE plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'pxa_newsletter_subscription',
    'Subscription',
    'Subscription'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['pxanewslettersubscription_subscription'] =
    'pages,recursive,layout,select_key';

// Add flexform
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['pxanewslettersubscription_subscription'] =
    'pi_flexform';

$extensionConfArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pxa_newsletter_subscription']);
$storageTable = $extensionConfArr['table'];

$extensionname = strtolower($extensionName);

if ($storageTable == 'fe_user') {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        'pxanewslettersubscription_subscription',
        'FILE:EXT:pxa_newsletter_subscription/Configuration/FlexForms/FrontendUserSubscription.xml'
    );
} else {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        'pxanewslettersubscription_subscription',
        'FILE:EXT:pxa_newsletter_subscription/Configuration/FlexForms/AddressSubscription.xml'
    );
}
