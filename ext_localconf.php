<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Add the frontend plugin.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, 'pi1/class.tx_lumophpinclude_pi1.php', '_pi1', 'list_type', 0);
