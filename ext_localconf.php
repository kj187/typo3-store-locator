<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Aijko.' . $_EXTKEY,
	'Storelocator',
	array(
		'Store' => 'list, getStores, show',

	),
	// non-cacheable actions
	array(
		'Store' => 'getStores',

	)
);

?>