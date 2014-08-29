<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Julian Kleinhans <julian.kleinhans@aijko.de>, aijko GmbH
 *
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

class tx_storelocator_wizicon {

	/**
	 * Processing the wizard items array
	 *
	 * @param array $wizardItems The wizard items
	 * @return array Modified array with wizard items
	 */
	function proc($wizardItems)     {
		$wizardItems['plugins_storelocator_storelocator'] = array(
			'icon' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('store_locator') . 'Resources/Public/Icons/Wizard.png',
			'title' => $GLOBALS['LANG']->sL('LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:wizard.title'),
			'description' => $GLOBALS['LANG']->sL('LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:wizard.description'),
			'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=storelocator_storelocator'
		);

		return $wizardItems;
	}
}


?>