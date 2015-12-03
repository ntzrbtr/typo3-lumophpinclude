<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Add the frontend plugin.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
	array(
    	'LLL:EXT:lumophpinclude/Resources/Private/Language/locallang_be.xml:pi1_title',
		$_EXTKEY . '_pi1'
	),
	'list_type'
);

// Use FlexForms for the plugin.
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:lumophpinclude/Configuration/FlexForms/flexform_pi1.xml');

// Define icon for the wizard.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:lumophpinclude/Configuration/TSconfig/PageTS.txt">');
