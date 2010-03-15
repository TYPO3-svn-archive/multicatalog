<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

t3lib_extMgm::allowTableOnStandardPages('tx_multicatalog_catalog');

$TCA['tx_multicatalog_catalog'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_catalog',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',
		'transOrigPointerField' => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'languageField' => 'sys_language_uid',
		'dividers2tabs' => TRUE,
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'Configuration/TCA/Catalog.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_multicatalog_catalog.gif',
	),
);

$_EXTCONF = unserialize($_EXTCONF);
if($_EXTCONF['use_articles'] || TYPO3_MODE != 'BE'){
	
	$TCA['tx_multicatalog_article'] = array (
		'ctrl' => array (
			'title'     => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_article',		
			'label'     => 'code',
			'label_alt' => 'title',
			'tstamp'    => 'tstamp',
			'crdate'    => 'crdate',
			'cruser_id' => 'cruser_id',
			'sortby' => 'sorting',	
			'delete' => 'deleted',	
			'enablecolumns' => array (		
				'disabled' => 'hidden',	
			),
			'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'Configuration/TCA/Article.php',
			'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_multicatalog_article.gif',
		),
	);
	t3lib_extMgm::allowTableOnStandardPages('tx_multicatalog_article');
}

if($_EXTCONF['category_records'] || TYPO3_MODE != 'BE'){
	$TCA['tx_multicatalog_category'] = array (
		'ctrl' => array (
			'title'     => 'LLL:EXT:multicatalog/locallang_db.xml:tx_multicatalog_category',		
			'label'     => 'name',	
			'tstamp'    => 'tstamp',
			'crdate'    => 'crdate',
			'cruser_id' => 'cruser_id',
			'sortby' => 'sorting',	
			'delete' => 'deleted',
			'transOrigPointerField' => 'l18n_parent',
			'transOrigDiffSourceField' => 'l18n_diffsource',
			'languageField' => 'sys_language_uid',
			'enablecolumns' => array (		
				'disabled' => 'hidden',	
			),
			'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'Configuration/TCA/Category.php',
			'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_multicatalog_category.gif',
		),
	);
	t3lib_extMgm::allowTableOnStandardPages('tx_multicatalog_category');
	
	// Flexform with category records
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] ='pi_flexform';
	t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_category_records.xml');
} else{
	// Flexform without category records
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] ='pi_flexform';
	t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform.xml');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:multicatalog/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','Product Catalog');

if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_multicatalog_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_multicatalog_pi1_wizicon.php';
}
?>