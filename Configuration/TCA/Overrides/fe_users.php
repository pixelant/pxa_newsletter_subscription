<?php
defined('TYPO3_MODE') || die('Access denied.');

(function () {
    // Use email as alternative label
    $labelAlt = $GLOBALS['TCA']['fe_users']['ctrl']['label_alt'] ? 'email' : 'email';

    $GLOBALS['TCA']['fe_users']['ctrl']['label_alt'] = $labelAlt;
})();
