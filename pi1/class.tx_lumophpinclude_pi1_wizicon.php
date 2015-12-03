<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Thomas Off <retiolum@googlemail.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class that adds the wizard icon.
 *
 * @package lumonetphpinclude
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class tx_lumophpinclude_pi1_wizicon {

	/**
	 * Function to add item in the wizard for creating new content elements.
	 *
	 * @param array Array with wizard items added so far
	 * @return array Array of wizard items with entry for this extension added
	 */
	function proc($wizardItems) {
		global $LANG;

		$LL = $this->includeLocalLang();

		$wizardItems['plugins_tx_lumophpinclude_pi1'] = array(
			'icon' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('lumophpinclude') . 'Resources/Public/Icons/pi1.gif',
			'title' => $LANG->getLLL('pi1_title', $LL),
			'description' => $LANG->getLLL('pi1_description', $LL),
			'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=lumophpinclude_pi1',
		);

		return $wizardItems;
	}

	/**
	 * Function to include language files.
	 *
	 * @return array Array containing language labels
	 */
	function includeLocalLang() {
		$llFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('lumophpinclude') . 'Resources/Private/Language/locallang_be.xml';
		$LOCAL_LANG = \TYPO3\CMS\Core\Utility\GeneralUtility::readLLXMLfile($llFile, $GLOBALS['LANG']->lang);

		return $LOCAL_LANG;
	}

}
