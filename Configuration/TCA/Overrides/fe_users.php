<?php
defined('TYPO3_MODE') || die;

// Add deleted to fe_users so property is accessible in model.
$GLOBALS['TCA']['fe_users']['columns']['deleted'] = [
    'exclude' => 1,
    'config' => [
        'type' => 'passthrough'
    ]
];
