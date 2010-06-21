<?php

########################################################################
# Extension Manager/Repository config file for ext "multicatalog".
#
# Auto generated 22-01-2010 12:35
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Product Catalog',
	'description' => 'Extensible Product Catalog',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.6.0-dev',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'uploadfolder' => 1,
	'createDirs' => 'uploads/tx_multicatalog/rte/',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Sebastian Michaelsen',
	'author_email' => 'sebastian.gebhard@gmail.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'perfectlightbox' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:19:{s:21:"ext_conf_template.txt";s:4:"2bb0";s:12:"ext_icon.gif";s:4:"96e1";s:17:"ext_localconf.php";s:4:"3709";s:14:"ext_tables.php";s:4:"512f";s:14:"ext_tables.sql";s:4:"e108";s:32:"icon_tx_multicatalog_article.gif";s:4:"475a";s:32:"icon_tx_multicatalog_catalog.gif";s:4:"475a";s:13:"locallang.xml";s:4:"4f0c";s:16:"locallang_db.xml";s:4:"61eb";s:29:"Configuration/TCA/Article.php";s:4:"56e8";s:29:"Configuration/TCA/Catalog.php";s:4:"4b83";s:14:"pi1/ce_wiz.gif";s:4:"870e";s:33:"pi1/class.tx_multicatalog_pi1.php";s:4:"6ea6";s:41:"pi1/class.tx_multicatalog_pi1_wizicon.php";s:4:"4303";s:16:"pi1/flexform.xml";s:4:"23f1";s:17:"pi1/locallang.xml";s:4:"940d";s:21:"pi1/res/template.html";s:4:"c687";s:24:"pi1/static/constants.txt";s:4:"f970";s:20:"pi1/static/setup.txt";s:4:"829a";}',
	'suggests' => array(
	),
);

?>