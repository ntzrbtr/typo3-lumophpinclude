<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Extend TCA for the plugin.
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,select_key';

// Add the frontend plugin.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array('LLL:EXT:lumophpinclude/Resources/Private/Language/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY . '_pi1'), 'list_type');

// Use FlexForms for the plugin.
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:lumophpinclude/Configuration/FlexForms/flexform_pi1.xml');

// Add wizard icon for the backend.
if (TYPO3_MODE == 'BE') {
    $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_lumophpinclude_pi1_wizicon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'pi1/class.tx_lumophpinclude_pi1_wizicon.php';
}
