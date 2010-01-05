<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA['tx_multicatalog_catalog'] = array (
	'ctrl' => $TCA['tx_multicatalog_catalog']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,code,title,description,pictures,price'
	),
	'feInterface' => $TCA['tx_multicatalog_catalog']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(3, 14, 7, 1, 19, 2038),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'code' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_catalog.code',		
			'config' => array (
				'type' => 'input',	
				'size' => '10',
			)
		),
		'title' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_catalog.title',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_catalog.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '7',
				'wizards' => array(
					'_PADDING' => 2,
					'RTE' => array(
						'notNewRecords' => 1,
						'RTEonly'       => 1,
						'type'          => 'script',
						'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
						'icon'          => 'wizard_rte2.gif',
						'script'        => 'wizard_rte.php',
					),
				),
			)
		),
		'teaser' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_catalog.teaser',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '4',
			),
		),
		'pictures' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_catalog.pictures',		
			'config' => array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => 'gif,png,jpeg,jpg',	
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],	
				'uploadfolder' => 'uploads/tx_multicatalog',
				'show_thumbs' => 1,	
				'size' => 10,	
				'minitems' => 0,
				'maxitems' => 10,
			)
		),
		'price' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_catalog.price',		
			'config' => array (
				'type' => 'input',	
				'size' => '5',
				'default'  => '0.00',
				'eval' => 'double2',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => '--div--;LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_catalog.tabs.general,hidden;;1;;1-1-1, code, title;;;;2-2-2, description;;;richtext[]:rte_transform[imgpath=uploads/tx_multicatalog/rte/];3-3-3, pictures, price')
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime, endtime, fe_group')
	)
);

global $TYPO3_CONF_VARS;
$_EXTCONF = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['multicatalog']);
// Add Articles
if($_EXTCONF['use_articles']){
	$tempColumns = array (
		'articles' => array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_catalog.articles',
			'config' => array (
				'type' => 'inline',
				'foreign_table' => 'tx_multicatalog_article',
				'maxitems' => 100,
				'appearance' => array(
					'showSynchronizationLink' => 0,
					'showAllLocalizationLink' => 0,
					'showPossibleLocalizationRecords' => 0,
					'showRemovedLocalizationRecords' => 0,
					'expandSingle' => 0,
					'useSortable' => 1
				),
				'behaviour' => array(
				),
			)
		),
	);
	t3lib_div::loadTCA('tx_multicatalog_catalog');
	t3lib_extMgm::addTCAcolumns('tx_multicatalog_catalog',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('tx_multicatalog_catalog','--div--;LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_catalog.tabs.articles,articles', '', 'after:price');
}
// Add teaser (RTE or plain)
$teaser = 'teaser';
if($_EXTCONF['teaser_rte']){
	$teaser .= ';;;richtext[]:rte_transform[mode=ts_css|imgpath=uploads/tx_multicatalog/rte/];3-3-3';
}
t3lib_extMgm::addToAllTCAtypes('tx_multicatalog_catalog', $teaser, '', 'before:description');
?>