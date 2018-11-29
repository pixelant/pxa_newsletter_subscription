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

$storageTable = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('pxa_newsletter_subscription', 'table');

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
