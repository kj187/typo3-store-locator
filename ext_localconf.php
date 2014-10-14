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
 * Configure plugin
 *
 */

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Aijko.' . $_EXTKEY,
	'Storelocator',
	array(
		'Store' => 'storeSearch, directionsService, getStores, getMainStores',

	),
	// non-cacheable actions
	array(
		'Store' => 'getStores, getMainStores',

	)
);

// Register Hooks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'Aijko\\StoreLocator\\Hooks\\AutoFillFields';

// Scheduler task for store import
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Aijko\\StoreLocator\\Task\\Store\\ImportTask'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'CSV Store Importer',
	'description'      => 'Import store data from CSV file',
	'additionalFields' => 'Aijko\\StoreLocator\\Task\\Store\\ImportTaskAddFields'
);

// Scheduler task for store import
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Aijko\\StoreLocator\\Task\\Store\\GeoTask'] = array(
	'extension'        => $_EXTKEY,
	'title'            => 'Missing GEO Lat/Long',
	'description'      => 'Get all missing Lat/Long coordinates',
	'additionalFields' => 'Aijko\\StoreLocator\\Task\\Store\\GeoTaskAddFields'
);