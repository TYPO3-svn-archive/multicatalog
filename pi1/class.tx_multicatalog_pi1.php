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

	private $markerLLs;
	
	private $local_cObj;
	
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

		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj');
		
		$this->pluginConfiguration();

		$content = $this->dispatchView();
		return $this->pi_wrapInBaseClass($content);
	}
	
	function pluginConfiguration() {
		
		/**
		 * The current view
		 */
		$this->view = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'view', 'sDEF');
		if(!t3lib_div::inList('single,list,catmenu', $this->view)) {
			$this->view = 'list';
		}
		
		/**
		 * List Page Id
		 */
		$ff_listPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listPid', 'sDEF');
		$this->listPid = $ff_listPid ? $ff_listPid : $this->cObj->stdWrap($this->conf['listPid'], $this->conf['listPid.']);
		
		/**
		 * Single Page Id
		 */
		$ff_singlePid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singlePid', 'sDEF');
		$this->singlePid = $ff_singlePid ? $ff_singlePid : $this->cObj->stdWrap($this->conf['singlePid'], $this->conf['singlePid.']);
		
		/**
		 * Storage Pid
		 * Priority:
		 * 1. Pages set in the plugin flexform
		 * 2. Page set via TS
		 * 3. Current FE Pid
		 */
	 	$TS_storagePid = $this->cObj->stdWrap($this->conf['storagePids'], $this->conf['storagePids.']);
	 	$TS_recursive = $this->cObj->stdWrap($this->conf['storagePids.']['recursive'], $this->conf['storagePids.']['recursive.']);
	 	if($this->cObj->data['pages']) {
	 		$this->pids = $this->pi_getPidList($this->cObj->data['pages'], $this->cObj->data['recursive']); 
	 	} elseif($TS_storagePid) {
	 		$this->pids = $this->pi_getPidList($TS_storagePid, $TS_recursive); 
	 	} else {
	 		$this->pids = $GLOBALS['TSFE']->id;
	 	}
				
		/**
		 * Pid the TS .link property links to.
		 * For single and list view it's the singlePid
		 * For catmenu view it's the listPid
		 */
		$this->linkTargetPid = ($this->view=='catmenu') ? $this->listPid : $this->singlePid;
		
		/**
		 * GET Parameter for the record linked to with the TS .link property
		 * For single and list view it's $this->prefixId[uid]
		 * For catmenu view it's $this->prefixId[cat]
		 */
		$this->linkVarName = ($this->view=='catmenu') ? 'cat' : 'uid';
				
		/**
		 * Template File
		 * Flexform overrides TS setting
		 */
		$ff_templateFile = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'templateFile', 'sDEF');
		$templateFile = $ff_templateFile ? $ff_templateFile : $this->conf['template'];
		$this->template = $this->cObj->fileResource($templateFile);
		$this->articletemplate = $this->cObj->getSubpart($this->template, '###ARTICLE###');
		$this->categorytemplate = $this->cObj->getSubpart($this->template, '###CATEGORY_LIST###');
		
		/**
		 * Pagebrowser settings
		 */
		$this->pagebrowser['perPage'] = $this->cObj->stdWrap($this->conf['list.']['pagebrowser.']['perPage'], $this->conf['list.']['pagebrowser.']['perPage.']);
		$this->pagebrowser['pagesAroundAct'] = $this->cObj->stdWrap($this->conf['list.']['pagebrowser.']['pagesAroundAct'], $this->conf['list.']['pagebrowser.']['pagesAroundAct.']);
		$this->pagebrowser['pagesEach'] = $this->cObj->stdWrap($this->conf['list.']['pagebrowser.']['pagesEach'], $this->conf['list.']['pagebrowser.']['pagesEach.']);
		$this->pagebrowser['pagesFirst'] = $this->cObj->stdWrap($this->conf['list.']['pagebrowser.']['pagesFirst'], $this->conf['list.']['pagebrowser.']['pagesFirst.']);
		$this->pagebrowser['pagesLast'] = $this->cObj->stdWrap($this->conf['list.']['pagebrowser.']['pagesLast'], $this->conf['list.']['pagebrowser.']['pagesLast.']);
		
	}
	
	function dispatchView() {
		switch($this->view) {
			case 'list':
				$content = $this->listView();
				break;
			case 'single':
				$content = $this->singleView();
				break;
			case 'catmenu':
				$content = $this->catMenuView();
				break;
		}
		return $content;
	}

	/**
	 * Extends the pi_loadLL function of tslib_pibase.
	 * Fills $this->markerLLs with the labels of the current language and adds labels from other files given in
	 * $this->conf['includeLL.'] array.
	 * All included labels are available as markers in the template
	 */
	function pi_loadLL() {
		parent::pi_loadLL();
		$this->markerLLs = array();
		foreach($this->LOCAL_LANG['default'] as $key => $value) {
			$this->markerLLs[$key] = $value;
		}
		foreach($this->LOCAL_LANG[$this->LLkey] as $key => $value) {
			$this->markerLLs[$key] = $value;
		}
		foreach($this->conf['includeLL.'] as $LLFile) {
			$ll = t3lib_div::readLLfile($LLFile,$this->LLkey,$GLOBALS['TSFE']->renderCharset);
			if(is_array($ll[$this->LLkey])) {
				$this->markerLLs = t3lib_div::array_merge_recursive_overrule($this->markerLLs, $ll['default']);
				$this->markerLLs = t3lib_div::array_merge_recursive_overrule($this->markerLLs, $ll[$this->LLkey]);
			}
		}
	}
	
	function fetchLocalized($returnArray, $fields, $table, $where, $groupBy='', $orderBy='', $limit='') {
		
		$records = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where . ' AND sys_language_uid = 0', $groupBy, $orderBy, $limit);
		while($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if(!$record['uid']) {
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . t3lib_div::locationHeaderUrl($this->cObj->getTypoLink_URL($this->listPid)));
			header('Connection: close');
			}
			if($GLOBALS['TSFE']->sys_language_uid > 0) {
				$record = $GLOBALS['TSFE']->sys_page->getRecordOverlay($table, $record, $GLOBALS['TSFE']->sys_language_uid);
			}
			if(!$returnArray) {
				return $record;
			}
			$records[] = $record;	
		}
		return $records;
	}
	
	/**
	 * Single View
	 * Uses $this->renderRecord() to get the Content of the single record
	 *
	 * @return	string		The Rendered View, ready for output
	 */
	function singleView(){

		$this->recordtemplate = $this->cObj->getSubpart(
			$this->template,
			'###RECORD_SINGLE###'
		);

		$record = $this->fetchLocalized(FALSE, '*', 'tx_multicatalog_product', 'uid = ' . intval($this->piVars['uid']) . $this->cObj->enableFields('tx_multicatalog_product'));
		$content = $this->renderRecord($record, $this->getFieldsConf('product'), $this->recordtemplate);
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

		$markerArray = $this->recordAndFieldsConfToMarkerArray(array(), $this->getFieldsConf());
		
		$where =
			'pid IN (' . $this->pids . ') ' .
			$this->cObj->enableFields('tx_multicatalog_product');
			
		if($this->piVars['cat']) {
			$cat = intval($this->piVars['cat']);
			$where .= 'AND (
				category = ' . $cat . ' OR
				category LIKE "' . $cat . ',%" OR
				category LIKE "%,' . $cat . ',%" OR
				category LIKE "%,' . $cat . '"
			)';
		}
		
		$page = max(0,$this->piVars['page']-1);
		
		$records = $this->fetchLocalized(TRUE, '*', 'tx_multicatalog_product', $where, '', 'sorting ASC');
		
		if(count($records)) {
			$i=0;
			foreach($records as $record) {
				if($i>=($perPage*$page) && $i < ($perPage*($page+1))){
					$markerArray['###RECORDS###'] .= $this->renderRecord($record, $this->getFieldsConf('product'), $this->recordtemplate);
				}
				$i++;
			}
			$markerArray['###PAGEBROWSER###'] = $this->pagebrowser(ceil($i/$perPage));
		}else{
			$markerArray['###RECORDS###'] = $this->pi_getLL('noRecordsFound');
			$markerArray['###PAGEBROWSER###'] = '';
		}
		
		return $this->cObj->substituteMarkerArray(
			$this->cObj->getSubpart($this->template,'###LISTVIEW###'),
			$markerArray
		);

	}
	
	function pagebrowser($pages) {
		
		if($pages < 2) return;
		
	         // Active Page
		$actPage = $this->piVars['page'] ? $this->piVars['page'] : 1;
		
			// Page List
		$pageList = array();
		
		$pageSchemes = array(
		    // First Pages
		    array(
				'min' => 1,
				'max' => min($pages, $this->pagebrowser['pagesFirst'])
		    ),
		    // Last 3
		    array(
				'min' => max(1, ($pages-($this->pagebrowser['pagesLast']))),
				'max' => $pages
		    ),
		    // ActPage +- 2
		    array(
				'min' => max(1,( $actPage-($this->pagebrowser['pagesAroundAct']))),
				'max' => min($pages, ($actPage+($this->pagebrowser['pagesAroundAct'])))
		    ),
		    // Each 10
		    array(
				'min' => $this->pagebrowser['pagesEach'],
				'max' => $pages,
				'iterate' => $this->pagebrowser['pagesEach']
		    )
		);
		
		foreach($pageSchemes as $pageScheme){
		    $iterate = max(1, $pageScheme['iterate']);
		    for($i = $pageScheme['min']; $i <= $pageScheme['max']; $i = $i+$iterate) {
				$pageList[] = $i;
		    }
		}
	
		$pageList = array_unique($pageList);
		sort($pageList);
	
		$typolinkConf = array(
		    'parameter' => $GLOBALS['TSFE']->id,
		    'addQueryString' => 1,
		    'addQueryString.' => array(
				'method' => 'GET,POST',
				'exclude' => $this->prefixId . '|page',
		    ),
		);
		
		$content = '<ul class="pagebrowser clearfix">';
		foreach($pageList as $page) {
		    $content .= '<li>';
	
		    if($page != $actPage) {
				$typolinkConf['additionalParams'] = '&' . $this->prefixId . '[page]=' . $page;
				$content .= $this->cObj->typolink($page, $typolinkConf);
		    }else{
				$content .= '<span class="act">Seite ' . $page . ' von ' . $pages . '</span>';
		    }
	
		    $content .= '</li>';
		}
		$content .= '</ul>';
		return $content;
		
		
	}
	
	/**
	 * Cat Menu View
	 * Lists available Categories
	 *
	 * @return	string		The Rendered View, ready for output
	 */
	function catMenuView(){
		
		$markerArray = $this->recordAndFieldsConfToMarkerArray(array(), $this->getFieldsConf());
		
		$where = 'category = 0 AND ' . 'pid IN (' . $this->pids . ') ' . $this->cObj->enableFields('tx_multicatalog_category');
		$categories = $this->fetchLocalized(TRUE, '*', 'tx_multicatalog_category', $where, '', 'sorting ASC');
		
		foreach($categories as $category) {
			$markerArray['###CATEGORIES###'] .= $this->renderRecord($category, $this->getFieldsConf('category'), $this->categorytemplate);
		}
		
		return $this->cObj->substituteMarkerArray(
			$this->cObj->getSubpart($this->template,'###CATMENUVIEW###'),
			$markerArray
		);
		
	}

	/**
	 * Renders record content for single and list view
	 * Offers some special flexibilities:
	 *
	 * All fields are available as markers:
	 * ====================================
	 * All fields in tx_multicatalog_product are available as markers for the template.
	 * E.g. the uid is available as ###UID###. But also fields that come from other Extensions are available.
	 * Assume you have a field tx_multicatalogdatasheet_sheet added by another extension, the field will be
	 * available as ###TX_MULTICATALOGDATASHEET_SHEET###. That makes extening this extension very easy! Just
	 * add the field to the database and you can instantly use it in the template.
	 * If you're working with articles, the same rules apply for them.
	 *
	 * stdWrap for all fields/markers:
	 * ===============================
	 * For all fields and additional markers (see below) stdWrap properties are available. Inside "fields." just use
	 * the property named like your field. Example:
	 * plugin.tx_multicatalog_pi1.fields{
	 *   description{
	 *     crop = 160 | ... | 1
	 *     stripHtml = 1
	 *   }
	 * }
	 * If you work with articles, their fields are available below plugin.tx_multicatalog_pi1.articlefields
	 *
	 * Other TS properties:
	 * ====================
	 * For all fields and additional markers (see below) a ".link" property is available which links the content to the
	 * single view. And a "backlink" property is available which links the content to the list view.
	 * Example: see "Add custom markers"
	 *
	 * Add custom markers:
	 * ===================
	 * You can also add markers via TS. Here's an example to add a "more" link to the list view:
	 * plugin.tx_multicatalog_pi1.fields{
	 *   morelink = more
	 *   morelink.link = 1
	 *   morelink.wrap = <span class="morelink">|</span>
	 * }
	 * Every field you mention in your TS Setup will be available as Marker.
	 * If you work with articles, you can add fields below plugin.tx_multicatalog_pi1.articlefields
	 *
	 * The default TS (EXT:multicatalog/pi1/static/setup.txt) shows some examples of how to work with this extension
	 * and introduces the markers ###BACKLINK###, ###MORELINK### and ###FIRST_PICTURE### and configures ###PICTURES###
	 *
	 * @param	array		The record to render
	 * @param	array		TS Setup of the record fields
	 * @param	string		Template for this record
	 * @return	string		The rendered record is given back to singleView() or listView()
	 */
	function renderRecord($record, $fieldsConf, $template) {
		
		$markerArray = $this->recordAndFieldsConfToMarkerArray($record, $fieldsConf);
		
		// Articles
		if ($record['articles']) {
			
			$markerArray['###ARTICLES###'] = '';
			$articles = array();
			$i = 0;    
			$where = 'irre_parentid = ' . $record['uid'] . $this->cObj->enableFields('tx_multicatalog_article');
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_multicatalog_article', $where, '', 'sorting ASC');
			while($article = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				
				// Fill cObj with article fields, product fields (prefixed with "parent_") and the iteration number
				$this->fillCObjData($article);
				foreach($record as $field => $value) {
					$this->fillCObjData(array('parent_' . $field => $value));
				}
				$this->fillCObjData(array('i'=>$i));
				
				$markerArray['###ARTICLES###'] .= $this->renderRecord($article, $this->getFieldsConf('article'), $this->articletemplate);
				
			}

		}
		
		
		// Category
		if ($record['category']) {
			
			$catArray = explode(',', $record['category']);
			$category = $this->fetchLocalized(0, '*', 'tx_multicatalog_category', 'uid = ' . $catArray[0]);
			
			foreach($this->recordAndFieldsConfToMarkerArray($category, $this->getFieldsConf('category')) as $marker => $value) {
				$markerArray[str_replace('xXx###', '###CATEGORY_', 'xXx' . $marker)] = $value;
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
		return '<div class="' . str_replace('_','-',$this->prefixId) . ' ' . str_replace('_','-',$this->prefixId) . '-' . $this->view . '">' . $str . '</div>';
	}
	
	function fillCObjData($array) {
		if(is_array($array)) $this->local_cObj->data = t3lib_div::array_merge_recursive_overrule($this->local_cObj->data, $array);
	}
	
	/**
	 * Gets TS fields configuration for a specific model and view
	 * 
	 * plugin.tx_multicatalog_pi1{
	 *   fields{
	 *     foo = bar1
	 *   }
	 *   articlefields{
	 *     foo = bar2
	 *   }
	 *   single{
	 *   	fields{
	 *   		foo = bar3
	 *   	}
	 *   	productfields{
	 *   		foo = bar4
	 *   	}
	 *   }
	 * }
	 * getFieldsConf('list', 'product'): [foo] => bar1
	 * getFieldsConf('list', 'article'): [foo] => bar2
	 * getFieldsConf('single', 'article'): [foo] => bar3
	 * getFieldsConf('single', 'product'): [foo] => bar4
	 * 
	 * @param string The current model (product, article or category)
	 * @param string The current view (list, single or catmenu) (defaults to $this->view)
	 * @return array Fields Configuration
	 */
	function getFieldsConf($model='', $view = '') {
		if(!$view) {
			$view = $this->view;
		}
		$conf = is_array($this->conf['fields.'])?$this->conf['fields.']:array();
		$conf = t3lib_div::array_merge_recursive_overrule($conf, is_array($this->conf[$model . 'fields.'])?$this->conf[$model . 'fields.']:array());
		$conf = t3lib_div::array_merge_recursive_overrule($conf, is_array($this->conf[$view . '.']['fields.'])?$this->conf[$view . '.']['fields.']:array());
		$conf = t3lib_div::array_merge_recursive_overrule($conf, is_array($this->conf[$view . '.'][$model . 'fields.'])?$this->conf[$view . '.'][$model . 'fields.']:array());
		
		return $conf;
	}
	
	/**
	 * Extends Record fields with TS Fields Configuration and fills a marker array with the fields
	 * 
	 * @param array DB Record to render
	 * @param array Fields Configuration
	 * @return array Marker Array
	 */
	function recordAndFieldsConfToMarkerArray($record, $fieldsConf) {
		
		$markerArray = array();
		
		if(!is_array($fieldsConf)) {
			$fieldsConf = array();
		}
		
		// All actual record fields are attached to the TS setup
		foreach($record as $field => $value) {
			$fieldsConf[$field] = $value;
		}
		
		foreach($this->markerLLs as $key => $value) {
			$fieldsConf['ll_' . $key] = $value;
		}
		
		// render TS fields setup
		foreach($fieldsConf as $field => $value) {
			// [property.] => [property] if [property] is not defined
			if($field{strlen($field)-1} == '.' && !$fieldsConf[substr($field, 0, strlen($field)-1)]) {
				$field = substr($field, 0, strlen($field)-1);
			}
			if($field{strlen($field)-1} != '.') {
				
				// Refill in every iteration step because field-level-conf can overwrite the view-level-conf
				$this->fillCObjData($fieldsConf);
				
				// field-level-conf
				$this->fillCObjData($fieldsConf[$field . '.']['fields.']);
				
				// ###PRICE###
				if ($field == 'price') {
					$value = number_format(str_replace(',', '.', $value), 2, ',', '.');
				}
				
				// ###SUBCATEGORIES###
				if ($field == 'subcategories') {
					$value = '';
					$where = 'category = ' . $fieldsConf['uid'] . ' AND ' . 'pid IN (' . $this->pids . ') ' . $this->cObj->enableFields('tx_multicatalog_category');
					$subcategories = $this->fetchLocalized(TRUE, '*', 'tx_multicatalog_category', $where, '', 'sorting ASC');
					foreach($subcategories as $subcategory) {
						$tmpFConf = array(
							'name.' => array(
								'link' => 1
							) 
						);
						$subcategoryMarkers = $this->recordAndFieldsConfToMarkerArray($subcategory, $tmpFConf);
						$value .= '<li>' . $subcategoryMarkers['###NAME###'] . '</li>';
					}
					$value = '<ul class="categories">' . $value . '</ul>';
				}
				
				/**
				 * If this field is defined, the indexDocTitle for indexed_search is filled with its content
				 */
				if($field == 'indexDocTitle') {
					$GLOBALS['TSFE']->indexedDocTitle = $this->local_cObj->stdWrap(
						$value,
						$fieldsConf[$field . '.']
					);
				}
				
				// link if value.link = 1
				if($this->local_cObj->stdWrap($fieldsConf[$field . '.']['link'], $fieldsConf[$field . '.']['link.']) == 1) {
					$fieldsConf[$field . '.']['typolink.']['parameter'] = $this->linkTargetPid;
					$fieldsConf[$field . '.']['typolink.']['additionalParams'] = '&' . $this->prefixId . '[' . $this->linkVarName . ']=' . $record['uid'];
					if($fieldsConf[$field . '.']['link.']['includeCategoryParameter'] == 1 && $this->linkVarName == 'uid') {
						$fieldsConf[$field . '.']['typolink.']['additionalParams'] .= '&' . $this->prefixId . '[cat]=' . $record['category'];
					}
					$fieldsConf[$field . '.']['typolink.']['useCacheHash'] = true;
				}
	
				// backlink if value.backlink = 1
				if($this->local_cObj->stdWrap($fieldsConf[$field . '.']['backlink'], $fieldsConf[$field . '.']['backlink.']) == 1) {
					$fieldsConf[$field . '.']['typolink.']['parameter'] = $this->listPid;
					
					if($record['category']) {
						$fieldsConf[$field . '.']['typolink.']['additionalParams'] = '&' . $this->prefixId . '[cat]=' . $record['category'];
					}
					$fieldsConf[$field . '.']['typolink.']['useCacheHash'] = true;
				}
				
				$markerArray['###' . strtoupper($field) . '###'] = $this->local_cObj->stdWrap(
					$value,
					$fieldsConf[$field . '.']
				);
			}
		}
		
		return $markerArray;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/multicatalog/pi1/class.tx_multicatalog_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/multicatalog/pi1/class.tx_multicatalog_pi1.php']);
}

?>