<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addPageTSConfig('

	# ***************************************************************************************
	# CONFIGURATION of RTE in table "tx_multicatalog_product", field "description"
	# ***************************************************************************************
RTE.config.tx_multicatalog_product.description {
  hidePStyleItems = H1, H4, H5, H6
  proc.exitHTMLparser_db=1
  proc.exitHTMLparser_db {
    keepNonMatchedTags=1
    tags.font.allowedAttribs= color
    tags.font.rmTagIfNoAttrib = 1
    tags.font.nesting = global
  }
}
');

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,																		// The extension name (in UpperCamelCase) or the extension key (in lower_underscore)
	'Pi2',																			// A unique name of the plugin in UpperCamelCase
	array(																			// An array holding the controller-action-combinations that are accessible 
		'Product' => 'index,show',	// The first controller and its first action will be the default 
		'Category' => 'index'
		),
	array()
);

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_multicatalog_pi1.php', '_pi1', 'list_type', 1);


t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.shortcut.20.0.conf.tx_multicatalog_product = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi1
	tt_content.shortcut.20.0.conf.tx_multicatalog_product.CMD = singleView
',43);
?>