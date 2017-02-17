<?php
if (!defined('TYPO3_MODE')) {
  die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Pixelant.' . $_EXTKEY,
	'Subscription',
	array(
		'NewsletterSubscription' => 'form,ajax,confirm',
	),
	// non-cacheable actions
	array(
		'NewsletterSubscription' => 'ajax,confirm',
	)
);
?>
