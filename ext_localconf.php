<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Extend TypoScript from static template uid=43 to set up userdefined tag.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY, 'editorcfg', 'tt_content.CSS_editor.ch.tx_lumophpinclude_pi1 = < plugin.tx_lumophpinclude_pi1.CSS_editor', 43);

// Add the frontend plugin.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi1/class.tx_lumophpinclude_pi1.php', '_pi1', 'list_type', 0);
