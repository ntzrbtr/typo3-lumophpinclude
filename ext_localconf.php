<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Add the frontend plugin render definition.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
	$_EXTKEY . '_pi1',
	'setup',
	'
plugin.tx_lumophpinclude_pi1 = USER
plugin.tx_lumophpinclude_pi1 {
	userFunc = Retiolum\Lumophpinclude\Pi1\Plugin->main
}

tt_content.list.20.lumophpinclude_pi1 =< plugin.tx_lumophpinclude_pi1
	',
	'defaultContentRendering');
