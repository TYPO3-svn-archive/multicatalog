<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_multicatalog_article'] = array (
	'ctrl' => $TCA['tx_multicatalog_article']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,code,title,description,pictures,price'
	),
	'feInterface' => $TCA['tx_multicatalog_article']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'code' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_article.code',		
			'config' => array (
				'type' => 'input',	
				'size' => '10',
				'eval' => 'trim',
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_article.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'price' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_article.price',		
			'config' => array (
				'type' => 'input',	
				'size' => '5',
				'default'  => '0.00',
				'eval' => 'double2',
			)
		),
		'parentid' => array (
			'config' => array (
				'type' => 'passthrough',
			)
		),
		'parenttable' => array (
			'config' => array (
				'type' => 'passthrough',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'code;;1')
	),
	'palettes' => array (
		'1' => array('showitem' => 'title,price')
	)
);
?>