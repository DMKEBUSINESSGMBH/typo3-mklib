<?php
/**
 *  @package tx_mklib
 *  @subpackage tx_mklib_treelib
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@das-medienkombinat.de>
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
require_once (PATH_t3lib.'class.t3lib_treeview.php');
require_once (t3lib_extMgm::extPath('rn_base').'class.tx_rnbase.php');

/**
 * Basisklasse, um eine Baumstruktur abzubilden.
 *
 * @package tx_mklib
 * @subpackage tx_mklib_treelib
 * @author Michael Wagner
 */
class tx_mklib_treelib_TreeView extends t3lib_treeview {
	/**
	 * @var t3lib_TCEforms
	 */
	private $oTceForm = null;
	/**
	 * @var array
	 */
	private $PA = array();
	/**
	 * @var tx_mklib_treelib_Config
	 */
	private $config = array();
	/**
	 * @var boolean
	 */
	private $useAjax = false;
	/**
	 * @var array
	 */
	var $row = array();
	/**
	 * @var array
	 */
	private $itemArray = array();
	/**
	 * @var string
	 */
	private $hiddenField = '';
	/**
	 * @var int
	 */
	private $iCurrentRow = 0;

	/**
	 * Liefert eine Instans des Treeviews
	 *
	 * @param 	array 					$PA
	 * @param 	t3lib_TCEforms 			$fObj
	 * @return 	tx_mklib_treelib_TreeView
	 */
	public static function makeInstance($PA, &$fObj){
		$oTreeView = tx_rnbase::makeInstance('tx_mklib_treelib_TreeView', $PA, $fObj);
		return $oTreeView;
	}

	/**
	 * Initialisiert den Treeview
	 *
	 * @param 	array 					$PA
	 * @param 	t3lib_TCEforms 			$fObj
	 * @return 	string
	 * @return	void
	 */
	public function tx_mklib_treelib_TreeView($PA, &$fObj)	{
		global $GLOBALS, $LANG, $TCA;

		$this->oTceForm = &$PA['pObj'];
		$this->PA = &$PA;

		tx_rnbase::load('tx_mklib_treelib_Config');
		$oConfig = tx_mklib_treelib_Config::makeInstance($PA, $fObj);
		$this->config = &$oConfig;

		$this->table = $oConfig->getForeignTable(); //$PA['table'];
		$this->parentField = $oConfig->getParentField();
		$this->row = $PA['row'];

		$TCA[$this->table]['ctrl']['treeParentMM'];

		$this->backPath = $this->oTceForm->backPath;

		$clause = ' AND '.$GLOBALS['BE_USER']->getPagePermsClause(1);
		$orderByFields = '';
		parent::init($clause, $orderByFields);

		$this->setTreeName($PA['table'].'_'.$PA['field'].'_tree');

		$this->title = $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
		$this->MOUNTS = $GLOBALS['WEBMOUNTS'];

		$this->maxDepth = $oConfig->getMaxDepth();
		$this->expandAll = $oConfig->getExpandAll();
		$this->expandFirst = $oConfig->getExpandFirst();
		$this->fieldArray = array(
								'uid',
								$oConfig->getTitleField(),
								$this->parentField,
							);
		// label_alt felder zum record hinzufügen.
		$oConfig->addLabelAltFields($this->fieldArray);

		$this->showDefaultTitleAttribute = true;
		$this->ext_IconMode = $oConfig->getExtIconMode();
		$this->title = $LANG->sL( $TCA[$oConfig->getForeignTable()]['ctrl']['title'] );
		$this->thisScript = 'alt_doc.php';

		$this->hiddenField = '<input type="hidden" name="PM" value="">';
		$this->itemArray = t3lib_div::trimExplode (',', $this->PA['itemFormElValue'], 1);
		$this->makeHTML = 1;

		if (t3lib_extMgm::isLoaded('xajax') && $oConfig->get('useAjax', true)) {
			$this->useAjax = 'xajax';

			if (!defined('XAJAX_DEFAULT_CHAR_ENCODING')) {
				if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']) {
					define('XAJAX_DEFAULT_CHAR_ENCODING', $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']);
				} else {
					define('XAJAX_DEFAULT_CHAR_ENCODING', 'iso-8859-15');
				}
			}
		}

	}

	public function useAjax(){
		return $this->useAjax !== FALSE;
	}

	/**
	 * Liefert das Konfigurations Objekt.
	 *
	 * @return 	tx_mklib_treelib_Config
	 */
	public function &getConfig() {
		return $this->config;
	}
	/**
	 * Liefert ein Array mit den bereits selektierten Daten.
	 *
	 * @return 	array
	 */
	public function getItemArray() {
		return $this->itemArray;
	}
	/**
	 * Liefert ein Array mit den UIDs der bereits selektierten Daten.
	 *
	 * @return 	array
	 */
	private function getItemArrayIds(){
		if(!is_array($this->itemArrayIds)) {
			$this->itemArrayIds = array();
			foreach($this->getItemArray() as $tk => $tv ) {
				$tvP = explode('|', $tv, 2);
				$this->itemArrayIds[] = $tvP[0];
			}
		}
		return $this->itemArrayIds;
	}
	/**
	 * Liefert das Hidden Field
	 * @return 	string
	 */
	public function getHiddenField() {
		return $this->hiddenField;
	}

	/**
	 * Liefert die Anzahl an Kategorien!
	 *
	 * @param	integer		$uid
	 * @return	integer
	 * @access 	private
	 */
	function getCount($uid)	{
		$oConfig = $this->getConfig();
		if (is_array($this->data)) {
			$res = $this->getDataInit($uid);
			return $this->getDataCount($res);
		}
		// wenn eine MM Tabelle gesetzt wurde, müssen wir deren Daten holen.
		elseif(($sMM = $oConfig->getMM('MM', false)) && $uid) {
			global $TYPO3_DB;
			$what = $fromClause = $where = '';
			$this->setMMQueryFelds($uid, $what, $fromClause, $where);
			$what = 'count(BASE.'.$this->fieldArray[0].')';
			$res = $TYPO3_DB->exec_SELECTquery($what, $fromClause, $where);
			$row = $TYPO3_DB->sql_fetch_row($res);
			return $row[0];
		}
		return parent::getCount($uid);
	}

	/**
	 * Getting the tree data: Selecting/Initializing data pointer to items for a certain parent id.
	 * For tables: This will make a database query to select all children to "parent"
	 * For arrays: This will return key to the ->dataLookup array
	 *
	 * @param	integer		$parentId
	 * @param	string		$subCSSclass	(unused)
	 * @return	mixed		data handle (Tables: An sql-resource, arrays: A parentId integer. -1 is returned if there were NO subLevel.)
	 */
	function getDataInit($parentId,$subCSSclass='') {
		$oConfig = $this->getConfig();
		if (is_array($this->data)) {
			if (!is_array($this->dataLookup[$parentId][$this->subLevelID])) {
				$parentId = -1;
			} else {
				reset($this->dataLookup[$parentId][$this->subLevelID]);
			}
			return $parentId;
		}
		// wenn eine MM Tabelle gesetzt wurde, müssen wir deren Daten holen.
		elseif(($sMM = $oConfig->getMM('MM', false)) && $parentId) {
			global $TYPO3_DB;
			$what = $fromClause = $where = '';
			$this->setMMQueryFelds($parentId, $what, $fromClause, $where);
			$res = $TYPO3_DB->exec_SELECTquery($what, $fromClause, $where);
			return $res;
		}

		//@workaround - siehe Wiki von mklib und mkdownloads
		//damit nicht eingeschränkt wird
		if($this->getConfig()->forceAdminRootRecord())
			$this->clause = '';

		return parent::getDataInit($parentId, $subCSSclass);
	}
	/**
	 * Getting the tree data: next entry
	 *
	 * @param	mixed		data handle
	 * @param	string		CSS class for sub elements (workspace related)
	 * @return	array		item data array OR FALSE if end of elements.
	 * @access private
	 * @see getDataInit()
	 */
	function getDataNext(&$res,$subCSSclass='')	{
		$row = parent::getDataNext($res,$subCSSclass);
		// Wir heben jede zweite Zeile hervor
		if($row) {
			$this->iCurrentRow++;
			$bgColorClass = ($this->iCurrentRow)%2 ? 'bgColor' : 'bgColor3';
			// Bereits gewählte werden zusätzlich hervorgehoben
			if(in_array($row['uid'],  $this->getItemArrayIds())) {
				$bgColorClass = 'bgColor4';
			}
			$row['_CSSCLASS'] = ($row['_CSSCLASS'] ? ' ' : '') . $bgColorClass;

			$oConfig = $this->getConfig();
			// den Titel mit label_alt fülen
			if($oConfig->getTreeConfig('parseRecordTitle')) {
				$row[$oConfig->getTitleField().'_alt'] = t3lib_befunc::getRecordTitle($oConfig->getForeignTable(), $row, $prep = TRUE, $forceResult = TRUE);
			}
		}
		return $row;
	}

	/**
	 * Setzt die nötigen SQL werte für die Abfrage einer MM Relation
	 *
	 * @param 	string 		$parentId
	 * @param 	string 		$what
	 * @param 	string 		$fromClause
	 * @param 	string 		$where
	 */
	private function setMMQueryFelds($parentId, &$what, &$fromClause, &$where){
		global $TCA;
		$oConfig = $this->getConfig();
		$sMM = $oConfig->getMM();

		$aWhat = array();
		foreach($this->fieldArray as $field) {
			$aWhat[] = 'BASE.'.$field;
		}
		// doppelte einträge vermeiden
		$what = 'DISTINCT '.implode(',',$aWhat);

		//@FIXME 'MM_opposite_field', 'uid_local' => 'uid_foreign', 'uid_foreign' beachten!?
		$fromClause  = $this->table.' as BASE';
		$fromClause .= ' JOIN '.$sMM.' as MM ON MM.uid_local = BASE.uid';
		$fromClause .= ' JOIN '.$this->table.' AS PARENT ON MM.uid_foreign = PARENT.uid';

		$where = 'PARENT.uid = '.$parentId;

		// match_field anhängen.
		$aMatchFields = array();
		foreach($oConfig->getMM('MM_match_fields', array()) as $field => $value) {
			$aMatchFields[$field] = $field.'=\''.$value.'\'';
		}
		if(count($aMatchFields)) {
			$where .= ' AND '.implode(' AND ', array_values($aMatchFields));
		}
	}

	/**
	 * Das Plus/Minus Symbol in einen Link zum auf-/zu-klappen Wrappen.
	 *
	 * @param	string		$icon
	 * @param	string		$cmd
	 * @param	boolean		$bMark 	Enthält einen Ankerpunkt, wenn gesetzt
	 * @return	string
	 * @access 	private
	 */
	function PM_ATagWrap($icon, $cmd, $bMark = '')	{
//		echo '<pre>';
//		tx_rnbase::load('tx_rnbase_util_Strings');
//		print_r(array(
//				$icon, $cmd, $bMark ,
//				tx_rnbase_util_Strings::isUtf8String($icon),
//				tx_rnbase_util_Strings::isUtf8String($icon[79]),
//				tx_rnbase_util_Strings::isUtf8String($icon[80]),
//				utf8_decode($icon),
//				'DEBUG: '.__METHOD__.' Line: '.__LINE__
//			)); // @TODO: remove me
//		echo '</pre>';
//exit;

		if ($this->thisScript) {

//			$cmdParts = explode ( '_', $cmd );
//			if(count($cmdParts) < 5) { $cmd .= '_'.$this->PA['row']['uid']; }

			$additionalParams = array();

			if ($this->useAjax()) {
				$title = $cmdParts[1] == '1' ? 'expand' : 'collapse';
				// Die Funktion $this->treeName.'_sendXajaxResponse wird von xajax angelegt
				$additionalParams[] = 'onclick="'.$this->treeName.'_sendXajaxResponse(\'' . $cmd . '\');return false;"';
				$additionalParams[] = 'title="'.$title.'"';
			}

			if ($bMark)	{
				$anchor = '#'.$bMark;
				$name=' name="'.$bMark.'"';
			}

			// Den Query-String bis auf den PM Parameter übernehmen.
			$queryString = t3lib_div::getIndpEnv('QUERY_STRING');
			// pm vom query string abschneiden!
			if($pm = t3lib_div::_GET('PM')) {
				$queryString = str_replace('PM='.$pm,'',$queryString);
			}
			// Erstes & Anfügen, wenn noch nicht vorhanden.
			if($queryString{0} !== '&') {
				$queryString = '&'.$queryString;
			}

			$aUrl = $this->thisScript.'?PM='.$cmd.$queryString.$anchor;
			return '<a href="'.htmlspecialchars($aUrl).'"'.$name.(count($additionalParams) ? ' '.implode(' ', $additionalParams) : '').'>'.$icon.'</a>';
		} else {
			return $icon;
		}
	}

	/**
	 * Wrappt den Titel in einen Link, welcher den Eintrag zur Liste hinzufügt.
	 *
	 * @param	string 		$title
	 * @param	array 		$v
	 * @return	string
	 */
	function wrapTitle($title, $v, $bank) {
		$title_alt = $v[$this->getConfig()->getTitleField().'_alt'];
		$title = ($title_alt ? $title_alt : $title);
		//nicht wählbar
		if(
			$this->isRootRecord($v) ||
			($this->getConfig()->dontLinkParentRecords() && $v[$this->getConfig()->getParentField()] == 0)
		) {
			$link = $title;
		} else {
			$link = $this->getRecordOnClickLink($title, $v);
		}
		return $link;
	}

	/**
	 * @param	string 		$title
	 * @param	array 		$v
	 *
	 * @return string
	 */
	private function getRecordOnClickLink($title, $v) {
		$aOnClick =  'setFormValueFromBrowseWin(\'' . $this->PA['itemFormElName'] . '\',' . $v['uid'] . ',\'' . $title . '\');';
		$title_alt = $v[$this->getConfig()->getTitleField().'_alt'];
		$link = '<a href="#" onclick="' . htmlspecialchars($aOnClick) . '" title="' . htmlentities($v['description']) . '">' .
				$title
				. '</a>';

		return $link;
	}

	/**
	 * @param array $record
	 * @return boolean
	 */
	private function isRootRecord(array $record) {
		return $record['uid'] == 0;
	}

	/**
	 * @workaround - siehe Wiki von mklib und mkdownloads
	 * (non-PHPdoc)
	 * @see t3lib_treeView::getBrowsableTree()
	 */
	function getBrowsableTree() {
		if($this->getConfig()->forceAdminRootRecord())
			$this->MOUNTS = array(0 => 0); // dummy
		return parent::getBrowsableTree();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/treelib/class.tx_mklib_treelib_TreeView.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/treelib/class.tx_mklib_treelib_TreeView.php']);
}