<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$boot = function($_EXTKEY) {
	// Add the frontend plugin.
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
		array(
			'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xlf:pi1_title',
			$_EXTKEY . '_pi1'
		),
		'list_type'
	);

	// Use FlexForms for the plugin.
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_pi1.xml');

	// Define icon for the wizard.
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/Configuration/TSconfig/PageTS.txt">');
};
$boot($_EXTKEY);
unset($boot);
