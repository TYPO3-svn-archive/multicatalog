<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Sebastian Gebhard <sebastian.gebhard@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   51: class tx_multicatalog_pi1 extends tslib_pibase
 *   66:     function main($content, $conf)
 *   99:     function singleView()
 *  128:     function listView()
 *  203:     function renderRecord()
 *  296:     function pi_wrapInBaseClass($str)
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib . 'class.tslib_pibase.php');


/**
 * Plugin 'Product Catalog' for the 'multicatalog' extension.
 *
 * @author	Sebastian Gebhard <sebastian.gebhard@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_multicatalog
 */
class tx_multicatalog_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_multicatalog_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_multicatalog_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'multicatalog';	// The extension key.
	var $pi_checkCHash = true;

	private $uploadFolder = 'uploads/tx_multicatalog/';

	/**
	 * Main method of your PlugIn
	 *
	 * @param	string		The content of the PlugIn
	 * @param	array		The PlugIn Configuration
	 * @return	string		The content that should be displayed on the website
	 */
	function main($content, $conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;

		$this->pi_initPIflexForm();

		$this->view = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'view', 'sDEF');
		
		$ff_listPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listPid', 'sDEF');
		$this->listPid = $ff_listPid ? $ff_listPid : $this->conf['listPid'];
		
		$ff_singlePid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singlePid', 'sDEF');
		$this->singlePid = $ff_singlePid ? $ff_singlePid : $this->conf['singlePid'];
		
		/**
		 * Storage Pid
		 * Priority:
		 * 1. Pages set in the plugin flexform
		 * 2. Page set via TS
		 * 3. Current FE Pid
		 */
		$this->pids = $this->cObj->data['pages'] ?
			$this->pi_getPidList($this->cObj->data['pages'],$this->cObj->data['recursive']) :
			( $this->conf['storagePid'] ?
				$this->conf['storagePid'] :
				$GLOBALS['TSFE']->id );
			

		$this->template = $this->cObj->fileResource($this->conf['template']);
		$this->articletemplate = $this->cObj->getSubpart($this->template, '###ARTICLE###');

		if($this->view == 'single'){
			$content = $this->singleView();
		}else{
			$this->view = 'list';
			$content = $this->listView();
		}

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Single View
	 * Makes a 301 redirect if no valid record for single view is available.
	 * Uses $this->renderRecord() to get the Content of the single record
	 *
	 * @return	string		The Rendered View, ready for output
	 */
	function singleView(){

		$this->recordtemplate = $this->cObj->getSubpart(
			$this->template,
			'###RECORD_SINGLE###'
		);

		$where =
			'uid = ' . intval($this->piVars['uid']) . ' AND ' .
			'sys_language_uid = ' . intval($GLOBALS['TSFE']->sys_language_content) .
			$this->cObj->enableFields('tx_multicatalog_catalog');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_multicatalog_catalog', $where);
		$record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if(!$record['uid']) {
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . t3lib_div::locationHeaderUrl($this->cObj->getTypoLink_URL($this->listPid)));
			header('Connection: close');
		}
		$content = $this->renderRecord($record, $this->conf['fields.'], $this->recordtemplate);
		return $content;

	}

	/**
	 * List View
	 * Uses $this->renderRecord() to get the Contents of the records
	 *
	 * @return	string		The Rendered View, ready for output
	 */
	function listView() {

		$this->recordtemplate = $this->cObj->getSubpart(
			$this->template,
			'###RECORD_LIST###'
		);

		$markerArray = array();

		$where =
			'sys_language_uid = ' . intval($GLOBALS['TSFE']->sys_language_content) . ' AND ' .
			'pid IN (' . $this->pids . ') ' .
			$this->cObj->enableFields('tx_multicatalog_catalog');

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_multicatalog_catalog', $where, '', 'sorting ASC');
		while($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$markerArray['###RECORDS###'] .= $this->renderRecord($record, $this->conf['fields.'], $this->recordtemplate);
		}

		return $this->cObj->substituteMarkerArray(
			$this->cObj->getSubpart($this->template,'###LISTVIEW###'),
			$markerArray
		);

	}

	/**
	 * Renders record content for single and list view
	 * Offers some special flexibilities:
	 *
	 * All fields are available as markers:
	 * ====================================
	 * All fields in tx_multicatalog_catalog are available as markers for the template.
	 * E.g. the uid is available as ###UID###. But also fields that come from other Extensions are available.
	 * Assume you have a field tx_multicatalogdatasheet_sheet added by another extension, the field will be
	 * available as ###TX_MULTICATALOGDATASHEET_SHEET###. That makes extening this extension very easy! Just
	 * add the field to the database and you can instantly use it in the template.
	 * If you're working with articles, the same rules apply for them.
	 *
	 * stdWrap for all fields/markers:
	 * ===============================
	 * For all fields and additional markers (see below) stdWrap properties are available. Just use the property named
	 * like your field. Example:
	 * plugin.tx_multicatalog_pi1.list{
	 *   description{
	 *     crop = 160 | ... | 1
	 *     stripHtml = 1
	 *   }
	 * }
	 * If you work with articles, their fields are available below plugin.tx_multicatalog_pi1.[single/list].articles
	 *
	 * Other TS properties:
	 * ====================
	 * For all fields and additional markers (see below) a .link property is available which links the content to the
	 * single view. Example: see "Add custom markers"
	 * If you work with articles, their fields are available below plugin.tx_multicatalog_pi1.[single/list].articles
	 *
	 * Add custom markers:
	 * ===================
	 * You can also add markers via TS. Here's an example to add a "more" link to the list view:
	 * plugin.tx_multicatalog_pi1.list{
	 *   additionalMarkers{
	 *     morelink = TEXT
	 *     morelink.value = more
	 *   }
	 *   morelink.link = 1
	 *   morelink.wrap = <span class="morelink">|</span>
	 * }
	 * If you work with articles, you can add fields below plugin.tx_multicatalog_pi1.[single/list].articles.additionalMarkers
	 *
	 * The default TS (EXT:multicatalog/pi1/static/setup.txt) shows some examples of how to work with this extension
	 * and introduces the markers ###BACKLINK###, ###MORELINK### and ###FIRST_PICTURE### and configures ###PICTURES###
	 *
	 * @param	array		The record to render
	 * @param	array		TS Setup of the record fields
	 * @return	string		The rendered record is given back to singleView() or listView()
	 */
	function renderRecord($record, $fieldsConf, $template){
		$markerArray = array();

		foreach($record as $field => $value) {
			$fieldsConf[$field] = $value;
		}
		
		$this->cObj->data = $fieldsConf;
		
		// render TS fields setup
		foreach($fieldsConf as $field => $value) {
			// [property.] => [property] if [property] is not defined
			if($field{strlen($field)-1} == '.' && !$fieldsConf[substr($field, 0, strlen($field)-1)]) {
				$field = substr($field, 0, strlen($field)-1);
			}
			if($field{strlen($field)-1} != '.') {
				
				// ###PRICE###
				if ($field == 'price') {
					$value = number_format($value, 2, ',', '.');
				}
				
				// link if value.link = 1
				if($fieldsConf[$field . '.']['link'] == 1){
					$fieldsConf[$field . '.']['typolink.']['parameter'] = $this->singlePid;
					$fieldsConf[$field . '.']['typolink.']['additionalParams'] = '&' . $this->prefixId . '[uid]=' . $record['uid'];
				}
	
				// backlink if value.backlink = 1
				if($fieldsConf[$field . '.']['backlink'] == 1){
					$fieldsConf[$field . '.']['typolink.']['parameter'] = $this->listPid;
				}
				
				$markerArray['###' . strtoupper($field) . '###'] = $this->cObj->stdWrap(
					$value,
					$fieldsConf[$field . '.']
				);
			}
		}

		// Articles
		if ($record['articles']) {
			
			$markerArray['###ARTICLES###'] = '';
			$articles = array();
			$i = 0;
			$where = 'irre_parentid = ' . $record['uid'] . $this->cObj->enableFields('tx_multicatalog_article');
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_multicatalog_article', $where, '', 'sorting ASC');
			while($article = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				
				// Fill cObj with article fields, product fields (prefixed with "parent_") and the iteration number
				$this->cObj->data = $article;
				foreach($record as $field => $value){
					$this->cObj->data['parent_'.$field] = $value;
				}
				$this->cObj->data['i'] = $i;
				
				$markerArray['###ARTICLES###'] .= $this->renderRecord($article, $this->conf['articlefields.'], $this->articletemplate);
				
			}
		}

		return $this->cObj->substituteMarkerArray($template, $markerArray);
	}

	/**
	 * Custom implementation of tslib_pibase::pi_wrapInBaseClass
	 * Adds the current view as class
	 *
	 * @param	string		Content to Wrap
	 * @return	string		Content wrapped by div with Plugin Classes
	 */
	function pi_wrapInBaseClass($str){
		return '<div class="'.str_replace('_','-',$this->prefixId).' '.str_replace('_','-',$this->prefixId).'-'.$this->view.'">' . $str . '</div>';
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/multicatalog/pi1/class.tx_multicatalog_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/multicatalog/pi1/class.tx_multicatalog_pi1.php']);
}

?>