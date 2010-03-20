<?php
/***************************************************************
*  Copyright notice
*
*  (c) 20010 Sebastian Gebhard <sebastian.gebhard@gmail.com>
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
 * The Product Controller for the Multicatalog package
 *
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Tx_Multicatalog_Controller_ProductController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_Multicatalog_Domain_Repository_ProductRepository
	 */
	protected $productRepository;
	
	/**
	 * Initializes the current action
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->productRepository = t3lib_div::makeInstance('Tx_Multicatalog_Domain_Repository_ProductRepository');
	}

	/**
	 * Index action for this controller. Displays a list of blogs.
	 *
	 * @return string The rendered view
	 */
	public function indexAction() {
		$this->view->assign('products', $this->productRepository->findAll());
	}

}

?>