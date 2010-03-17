<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_multicatalog_category'] = array (
	'ctrl' => $TCA['tx_multicatalog_category']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,name'
	),
	'feInterface' => $TCA['tx_multicatalog_category']['feInterface'],
	'columns' => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'name' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:multicatalog/Resources/Private/Language/locallang_db.xml:tx_multicatalog_category.name',		
			'config' => array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			)
		),
		'sys_language_uid' => Array (
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
            'config' => Array (
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => Array(
                    Array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
                    Array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
                )
            )
        ),
        'l18n_parent' => Array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => Array (
				'type' => 'select',
				'items' => Array (
				    Array('', 0),
				),
				'foreign_table' => 'tx_multicatalog_category',
				'foreign_table_where' => 'AND tx_multicatalog_category.uid=###REC_FIELD_l18n_parent### AND tx_multicatalog_category.sys_language_uid IN (-1,0)',
			)
        ),
        'l18n_diffsource' => Array(
            'config'=>array(
                'type'=>'passthrough'
       		)
        ),
        't3ver_label' => Array (
            'displayCond' => 'FIELD:t3ver_label:REQ:true',
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
            'config' => Array (
                'type'=>'none',
                'cols' => 27
            )
        ),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;1,name')
	),
	'palettes' => array (
		'1' => array('showitem' => 't3ver_label,l18n_parent')
	)
);

global $TYPO3_CONF_VARS;
$_EXTCONF = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['multicatalog']);
if($_EXTCONF['hierachical_categories']) {
	
	$TCA['tx_multicatalog_category']['ctrl']['useColumnsForDefaultValues'] = 'category';
	
	$tempColumns = array (
		'category' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:multicatalog/Resources/Private/Language/locallang_db.xml:tx_multicatalog_category.category',		
			'config' => array (
				'type' => 'select',
				'items' => array(
					array('', '')
				),
				// TODO: Implement user func to display oiginal language id's but current language labels
				'foreign_table' => 'tx_multicatalog_category',    
                'foreign_table_where' => 'AND tx_multicatalog_category.pid=###CURRENT_PID### AND tx_multicatalog_category.category=0 AND sys_language_uid = 0 ORDER BY tx_multicatalog_category.name',    
                'size' => 1,    
                'minitems' => 0,
                'maxitems' => 1,
			)
		),
	);
	t3lib_div::loadTCA('tx_multicatalog_category');
	t3lib_extMgm::addTCAcolumns('tx_multicatalog_category',$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes('tx_multicatalog_category', 'category', '', 'before:name');
}

?>