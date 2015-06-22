<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2015 DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
 * All rights reserved
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
require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_repository_Abstract');
tx_rnbase::load('tx_rnbase_util_Arrays');

/**
 * Page Repository
 *
 * @package tx_mklib
 * @subpackage tx_mklib_repository
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_repository_Pages
	extends tx_mklib_repository_Abstract
{
	/**
	 * Liefert den Namen der Suchklasse
	 *
	 * @return 	string
	 */
	protected function getSearchClass() {
		return 'tx_rnbase_util_SearchGeneric';
	}

	/**
	 * Liefert die Model Klasse.
	 *
	 * @return 	string
	 */
	protected function getWrapperClass() {
		return 'tx_mklib_model_Page';
	}

	/**
	 * Return an instantiated dummy model without any content
	 *
	 * This is used only to access several model info methods like
	 * getTableName(), getColumnNames() etc.
	 *
	 * @return tx_rnbase_model_base
	 */
	protected function getEmptyModel() {
		return parent::getEmptyModel()->setTablename('pages');
	}

	/**
	 * returns all subpages of a page on first level.
	 *
	 * @param tx_mklib_model_Page $page
	 * @return array[tx_mklib_model_Page]
	 */
	public function getChildren(
		tx_mklib_model_Page $page
	) {
		$fields = $options = array();
		$fields['PAGES.pid'][OP_EQ_INT] = $page->getUid();

		return $this->search($fields, $options);
	}

	/**
	 * Search database
	 *
	 * @param array $fields
	 * @param array $options
	 * @return array[tx_rnbase_model_base]
	 */
	public function search(array $fields, array $options) {
		if (empty($options['searchdef']) || !is_array($options['searchdef'])) {
			$options['searchdef'] = array();
		}
		$options['searchdef'] = tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
			// default sercher config
			$this->getSearchdef(),
			// searcher config overrides
			$options['searchdef']
		);
		return parent::search($fields, $options);
	}

	/**
	 *
	 * @return array
	 */
	protected function getSearchdef() {
		return array(
			'usealias' => '1',
			'basetable' => 'pages',
			'basetablealias' => 'PAGES',
			'wrapperclass' => $this->getWrapperClass(),
			'alias' => array(
				'PAGES' => array(
					'table' => 'pages'
				),
				'PAGESPARENT' => array(
					'table' => 'pages',
					'join' => 'JOIN pages AS PAGESPARENT ON PAGES.pid = PAGESPARENT.uid',
				),
			)
		);
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['tx_mklib_repository_Abstract']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['tx_mklib_repository_Abstract']);
}
