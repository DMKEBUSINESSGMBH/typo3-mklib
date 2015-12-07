<?php
/**
 *  @package tx_mklib
 *  @subpackage tx_mklib_treelib
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_util_Strings');

/**
 * Konfiguration für einen TreeView.
 *
 * @package tx_mklib
 * @subpackage tx_mklib_treelib
 * @author Michael Wagner
 */
class tx_mklib_treelib_Config {
	/**
	 *
	 * @var t3lib_TCEforms
	 */
	private $oTceForm = null;
	/**
	 * @var array
	 */
	private $config = array();

	/**
	 * Liefert eine Instans des Treeviews
	 *
	 * @param 	array 			$PA
	 * @param 	t3lib_TCEforms 	$fObj
	 * @return 	void
	 */
	public function tx_mklib_treelib_Config(&$PA, &$pObj){
		$this->oTceForm = &$PA['pObj'];
		$this->config = &$PA['fieldConf']['config'];
	}

	/**
	 * Liefert eine Instans der Konfiguration
	 *
	 * @param 	array 			$PA
	 * @param 	t3lib_TCEforms 	$fObj
	 * @return 	tx_mklib_treelib_Config
	 */
	public static function &makeInstance(&$PA, &$pObj) {
		return tx_rnbase::makeInstance('tx_mklib_treelib_Config', $PA, $pObj);
	}

	/**
	 * @return 	t3lib_TCEforms 	$fObj
	 */
	public function &getTceForm(){
		return $this->oTceForm;
	}

	public function get($field, $default=null) {
		return array_key_exists($field, $this->config) ? $this->config[$field] : $default;
	}
	/**
	 * Liefert die MM Configuration der Tabelle.
	 * Die steht in der ext_tables.php!
	 * @param 	string 	$field
	 * @param 	string 	$default
	 * @return 	string
	 */
	public function getMM($field='MM', $default=null){
		global $TCA;
		$config = (array) $TCA[$this->getForeignTable()]['ctrl']['treeParentMM'];
		return array_key_exists($field, $config) ? $config[$field] : $default;
	}
	public function addLabelAltFields(array &$fields = array()){
		if($this->getTreeConfig('parseRecordTitle')) {
			global $TCA;
			if($TCA[$this->getForeignTable()]['ctrl']['label_alt_force']) {
				$altFields = tx_rnbase_util_Strings::trimExplode(',', $TCA[$this->getForeignTable()]['ctrl']['label_alt'], true);
				$fields = array_merge($altFields, $fields);
			}
		}
	}
	public function getTreeConfig($field=false, $default=null){
		$treeConfig = $this->get('treeConfig', array());
		return $field ? (array_key_exists($field, $treeConfig) ? $treeConfig[$field] : $default) : $treeConfig;
	}
	public function getForeignTable(){
		$val = $this->get('foreign_table');
		if(!$val){
			throw new Exception(__METHOD__.'(): no foreign table defined');
		}
		return $val;
	}
	public function getParentField(){
		global $TCA;
		$val = $TCA[$this->getForeignTable()]['ctrl']['treeParentField'];
		return $val ? $val : $this->get('field', 'parent');
	}
	public function getTitleField(){
		return $this->get('titleField', 'title');
	}
	public function getMaxDepth(){
		return $this->get('maxDepth', 99);
	}
	public function getExpandAll(){
		return $this->get('expandAll', 0);
	}
	public function getExpandFirst(){
		return $this->get('expandFirst', 0);
	}
	public function getExtIconMode(){
		return $this->get('ext_IconMode', true); // default no context menu on icons
	}

	public function getMinItems(){
		tx_rnbase::load('tx_rnbase_util_Math');
		return tx_rnbase_util_Math::intInRange($this->get('minitems', 0), 0);
	}
	public function getMaxItems(){
		tx_rnbase::load('tx_rnbase_util_Math');
		return tx_rnbase_util_Math::intInRange($this->get('maxitems', 100000), 0);
	}
	public function getAutoSizeMax(){
		tx_rnbase::load('tx_rnbase_util_Math');
		return tx_rnbase_util_Math::intInRange($this->get('autoSizeMax', 1), 0);
	}
	public function getSize(){
		return $this->get('size', 1);
	}

	public function getTreeWrapStyle(){
		$default = 'border:solid 1px; overflow:auto; background:#fff; margin-bottom:5px; padding:1px 0;';
		// Breite und Höhe anfügen!
		$size = $this->get('treeSize', array(320, 120));
		$default .= 'width:'.$size[0].'px;height:'.$size[1].'px;';
		return $this->get('treeWrapStyle', $default);
	}
	public function getSelectedListStyle(){
		return $this->get('selectedListStyle', 'width:250px;');
	}

	public function getWizards(){
		return $this->get('wizards');
	}

	/**
	 * wenn bei einem normalen nutzer kein root record
	 * gefunden wird, dann kommt es zur exception. das
	 * ist ein Bug in TYPO3 bzw. in der Extension,
	 * die keine Mounts unterstützt.
	 * Damit der gleiche dummy record wie
	 * bei einem Admin verwendet wird, kann diese option
	 * gesetzt werden.
	 *
	 * @return boolean
	 */
	public function forceAdminRootRecord() {
		return (boolean)$this->get('forceAdminRootRecord');
	}

	/**
	 * @return boolean
	 */
	public function dontLinkParentRecords() {
		return (boolean) $this->getTreeConfig('dontLinkParentRecords', 0);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/treelib/class.tx_mklib_treelib_Config.php'])	{
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/treelib/class.tx_mklib_treelib_Config.php']);
}