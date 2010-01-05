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
 * Hint: use extdeveval to insert/update function index above.
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
	private $uploadFolder = 'uploads/tx_multicatalog/';
	var $pi_checkCHash = true;
	
	/**
	 * Main method of your PlugIn
	 *
	 * @param	string		$content: The content of the PlugIn
	 * @param	array		$conf: The PlugIn Configuration
	 * @return	The content that should be displayed on the website
	 */
	function main($content, $conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;       
		 
		$this->pi_initPIflexForm();
		
		$this->view = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'view', 'sDEF');
		$this->listPid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'listPid', 'sDEF');
		$this->singlePid = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singlePid', 'sDEF');		
		
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
	
	function pi_wrapInBaseClass($str){
		return '<div class="'.str_replace('_','-',$this->prefixId).' '.str_replace('_','-',$this->prefixId).'-'.$this->view.'">' . $str . '</div>';
	}
	
	function singleView(){
		
		$this->recordtemplate = $this->cObj->getSubpart(
			$this->template,
			'###RECORD_SINGLE###'
		);
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_multicatalog_catalog', 'uid = '.intval($this->piVars['uid']));
		$this->record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		if(!$this->record['uid']){
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.t3lib_div::locationHeaderUrl($this->cObj->getTypoLink_URL($this->listPid)));
			header("Connection: close");
		}
		$content = $this->renderRecord();
		return $content;
		
	}
	
	function listView() {
		
		$this->recordtemplate = $this->cObj->getSubpart(
			$this->template,
			'###RECORD_LIST###'
		);
		
		$markerArray = array();
		
		$pids = $this->pi_getPidList($this->cObj->data['pages'],$this->cObj->data['recursive']);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_multicatalog_catalog', 'pid IN ('.$pids.')', '', 'sorting ASC');
		while($this->record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$markerArray['###RECORDS###'] .= $this->renderRecord();
		}
		
		return $this->cObj->substituteMarkerArray(
			$this->cObj->getSubpart($this->template,'###LISTVIEW###'),
			$markerArray
		);
		
	}
	
	function renderRecord(){
		$this->cObj->data = $this->record;
		$markerArray = array();
		
		// render additional markers
		foreach($this->conf[$this->view . '.']['additionalMarkers.'] as $marker => $markerCobj){
			if($marker{strlen($marker)-1} != '.'){
				$this->record[$marker] = $this->cObj->cObjGetSingle($markerCobj, $this->conf[$this->view . '.']['additionalMarkers.'][$marker . '.']);
			}
		}
		
		foreach($this->record as $field => $value){
			
			// ###PRICE###
			if ($field == 'price') {
				$value = number_format($value, 2, ',', '.');
			}
			
			// ###PICTURES### AND ###FIRST_PICTURE###
			if($field == 'pictures'){
				$pictures = t3lib_div::trimExplode(',', $value, 1);
				$imgConf = array(
					'file.' => array(
						'width' => $this->conf[$this->view.'.']['pictures.']['width']
					),
					'altText' => $this->record['title']
				);
				$lbxImgConf = array(
					'file.' => array(
						'width' => $this->conf[$this->view.'.']['pictures.']['lightbox_width']
					)
				);
				
				$value = '';
				// ###PICTURES###
				foreach($pictures as $picture){
					$imgConf['file'] = $this->uploadFolder . $picture;
					$lbxImgConf['file'] = $this->uploadFolder . $picture;
					$image = $this->cObj->IMAGE($imgConf);
					if($this->conf[$this->view.'.']['pictures.']['lightbox'] == 1){
						$image =
							'<a href="' .
							$this->cObj->IMG_RESOURCE($lbxImgConf) .
							'" rel="lightbox[' .
							'catalog' .
							$this->record['uid'] .
							']">' .
							$image .
							'</a>';
					}
					$value .= $image;
				}
				
					// link if value.link = 1
				if ($this->conf[$this->view.'.']['firstPicture.']['link'] == 1) {
					$this->conf[$this->view.'.']['firstPictureStdWrap.']['typolink.']['parameter'] = $this->singlePid;
					$this->conf[$this->view.'.']['firstPictureStdWrap.']['typolink.']['additionalParams'] = '&'.$this->prefixId.'[uid]='.$this->record['uid'];
				}
				
				// ###FIRST_PICTURE 
				$imgConf['file'] = $this->uploadFolder . $pictures[0];
				$firstPicture = $this->cObj->IMAGE($imgConf);
				$markerArray['###FIRST_PICTURE###'] = $this->cObj->stdWrap(
					$firstPicture,
					$this->conf[$this->view.'.']['firstPictureStdWrap.']
				);
				
			}
			
			// link if value.link = 1
			if($this->conf[$this->view.'.'][$field.'.']['link'] == 1){
				$this->conf[$this->view.'.'][$field.'StdWrap.']['typolink.']['parameter'] = $this->singlePid;
				$this->conf[$this->view.'.'][$field.'StdWrap.']['typolink.']['additionalParams'] = '&'.$this->prefixId.'[uid]='.$this->record['uid'];
			}
			
			// stdWrap for each value 
			$markerArray['###' . strtoupper($field) . '###'] = $this->cObj->stdWrap(
				$value,
				$this->conf[$this->view.'.'][$field.'StdWrap.']
			);
			
		}
		
		// Articles
		if ($this->record['articles']) {
			$markerArray['###ARTICLES###'] = '';
			$articles = array();
			$i = 0;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_multicatalog_article', 'uid IN ('.$this->record['articles'].')', '', 'sorting ASC');
			while($article = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				
				// Fill cObj with article fields, product fields (prefixed with "product_") and the iteration number
				$this->cObj->data = $article;
				foreach($this->record as $field => $value){
					$this->cObj->data['product_'.$field] = $value;
				}
				$this->cObj->data['i'] = $i;
				
					// render additional markers
				foreach($this->conf[$this->view . '.']['articles.']['additionalMarkers.'] as $marker => $markerCobj){
					if($marker{strlen($marker)-1} != '.'){
						$article[$marker] = $this->cObj->cObjGetSingle($markerCobj, $this->conf[$this->view . '.']['articles.']['additionalMarkers.'][$marker . '.']);
					}
				}
				
				$subMarkerArray = array();
				foreach($article as $field => $value){
					
					// ###PRICE###
					if ($field == 'price') {
						$value = number_format($value, 2, ',', '.');
					}
					
					// stdWrap for each value 
					$subMarkerArray['###' . strtoupper($field) . '###'] = $this->cObj->stdWrap(
						$value,
						$this->conf[$this->view.'.']['articles.'][$field.'StdWrap.']
					);
				}
				$markerArray['###ARTICLES###'] .= $this->cObj->substituteMarkerArrayCached($this->articletemplate, $subMarkerArray);
				$i++;
			}
		}
		
		// Backlink
		$markerArray['###BACKLINK###'] = $this->cObj->typoLink($this->pi_getLL('backlink'), array('parameter'=>$this->listPid));
		
		return $this->cObj->substituteMarkerArray($this->recordtemplate, $markerArray);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/multicatalog/pi1/class.tx_multicatalog_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/multicatalog/pi1/class.tx_multicatalog_pi1.php']);
}

?>