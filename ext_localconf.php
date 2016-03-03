<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$boot = function($_EXTKEY) {
	// Add the frontend plugin render definition.
	$typoScript = <<<EOT
plugin.tx_{$_EXTKEY}_pi1 = USER
plugin.tx_{$_EXTKEY}_pi1 {
	userFunc = Retiolum\Lumophpinclude\Pi1\Plugin->main
}

tt_content.list.20.lumophpinclude_pi1 =< plugin.tx_lumophpinclude_pi1
EOT;
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
		$_EXTKEY . '_pi1',
		'setup',
		$typoScript,
		'defaultContentRendering'
	);
};
$boot($_EXTKEY);
unset($boot);
