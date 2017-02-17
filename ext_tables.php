<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Subscription',
	'Subscription'
);

/**
 * Include Flexform
 */
$extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY));
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$extensionName.'_subscription']='pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($extensionName.'_subscription', 'FILE:EXT:'.$_EXTKEY.'/Configuration/FlexForms/flexform_pi1.xml');

	// Add deleted to fe_users so property is accessible in model.
$TCA['fe_users']['columns']['deleted'] = array(
	'exclude' => 1,
    'config' => Array(
		'type' => 'passthrough',
    )
);
/*********************************/

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Newsletter Subscription');
?>