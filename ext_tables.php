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

if (!defined('TYPO3_MODE')) die ('Access denied.');




/*******************************************************************************************************************
 * Register plugin
 *
 */

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Storelocator',
	'Store Locator'
);




/*******************************************************************************************************************
 * Add plugin to new element wizard
 *
 */

if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_storelocator_wizicon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Utility/Wizicon.php';
}


/*******************************************************************************************************************
 * Add flexform configuration
 *
 */

$TCA['tt_content']['types']['list']['subtypes_addlist']['storelocator_storelocator']='pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('storelocator_storelocator', 'FILE:EXT:'.$_EXTKEY.'/Configuration/Flexforms/Settings.xml');




/*******************************************************************************************************************
 * Add static typoscript files
 *
 */

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Store Locator');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Style', 'Store Locator - Default Style');



/*******************************************************************************************************************
 * Add static tsconf files
 *
 */

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_tsconf')) {
	Aijko\StaticTsconf\Utility\StaticFileUtility::addStaticFile($_EXTKEY, 'Configuration/TSconfig/Page/default.ts', 'page - default.ts');
	Aijko\StaticTsconf\Utility\StaticFileUtility::addStaticFileByDirectory($_EXTKEY, 'Configuration/TSconfig/Page/allowedTables/', 'allowedTables - ');
}




/*******************************************************************************************************************
 * Define TCA
 *
 */

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_storelocator_domain_model_store', 'EXT:store_locator/Resources/Private/Language/locallang_csh_tx_storelocator_domain_model_store.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_storelocator_domain_model_store');
$TCA['tx_storelocator_domain_model_store'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'name,address,city,street,state,zipcode,country,latitude,longitude,url,description,email,phone,fax,logo,ismainstore',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Store.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_storelocator_domain_model_store.png'
	),
);

?>