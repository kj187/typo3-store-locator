<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_storelocator_domain_model_store'] = array(
	'ctrl' => $TCA['tx_storelocator_domain_model_store']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, ismainstore, address, street, city, state, zipcode, country, latitude, longitude, url, description, email, phone, fax, logo',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, name, ismainstore, address, latitude, longitude, street, city, state, zipcode, country, url, email, phone, fax, logo, description,--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_storelocator_domain_model_store',
				'foreign_table_where' => 'AND tx_storelocator_domain_model_store.pid=###CURRENT_PID### AND tx_storelocator_domain_model_store.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'name' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'ismainstore' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.ismainstore',
			'config' => array(
				'type' => 'check',
			),
		),
		'address' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.address',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'latitude' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.latitude',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'longitude' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.longitude',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),

		'street' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.street',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'city' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.city',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'state' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.state',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'zipcode' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.zipcode',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'country' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.country',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'url' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.url',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'description' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.description',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim',
				'wizards' => array(
					'RTE' => array(
						'icon' => 'wizard_rte2.gif',
						'notNewRecords'=> 1,
						'RTEonly' => 1,
						'script' => 'wizard_rte.php',
						'title' => 'LLL:EXT:cms/locallang_ttc.xlf:bodytext.W.RTE',
						'type' => 'script'
					)
				)
			),
			'defaultExtras' => 'richtext:rte_transform[flag=rte_enabled|mode=ts]',
		),
		'email' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.email',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'phone' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.phone',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'fax' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.fax',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'logo' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:store_locator/Resources/Private/Language/locallang_db.xlf:tx_storelocator_domain_model_store.logo',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'file_reference',
				'uploadfolder' => 'uploads/tx_storelocator',
				'allowed' => '*',
				'disallowed' => 'php',
				'size' => 5,
			),
		),
	),
);

?>