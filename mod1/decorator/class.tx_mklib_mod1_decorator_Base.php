<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_mod1
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 */
require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_mod_IDecorator');
tx_rnbase::load('tx_mklib_util_TCA');
tx_rnbase::load('tx_rnbase_mod_Util');

/**
 * Diese Klasse ist für die Darstellung von Elementen im Backend verantwortlich.
 *
 * @package tx_mklib
 * @subpackage tx_mklib_mod1
 */
class tx_mklib_mod1_decorator_Base implements tx_rnbase_mod_IDecorator{

	/**
	 *
	 * @param 	tx_rnbase_mod_IModule 	$mod
	 */
	public function __construct(tx_rnbase_mod_IModule $mod) {
		$this->mod = $mod;
	}

	/**
	 *
	 * @param 	string 					$value
	 * @param 	string 					$colName
	 * @param 	array 					$record
	 * @param 	tx_rnbase_model_base 	$item
	 */
	public function format($value, $colName, $record, tx_rnbase_model_base $item) {
		$ret = $value;
		switch ($colName) {
			case 'uid':
				$ret = $this->getUidColumn($item);
				break;

			case 'label':
				$ret = $this->getLabelColumn($item);
				break;

			case 'crdate':
			case 'tstamp':
				$ret = strftime('%d.%m.%y %H:%M:%S', (int) $ret);
				break;

			case 'sys_language_uid':
				$ret = $this->getSysLanguageColumn($item);
				break;

			case 'actions':
				$ret .= $this->getActions($item, $this->getActionOptions($item));
				break;

			default:
				break;
		}

		return $this->wrapValue($ret, $value, $colName, $record, $item);
	}

	/**
	 * renders the uid column.
	 *
	 * @param tx_rnbase_model_base $item
	 * @return string
	 */
	protected function getUidColumn(tx_rnbase_model_base $item) {
		$wrap = $item->isHidden() ? array('<del>','</del>') : array('','');
		$ret = $wrap[0] . $item->getProperty('uid') . $wrap[1];
		$dates = array();
		$dates['crdate'] = (array_key_exists('crdate', $item->record)) ? strftime('%d.%m.%y %H:%M:%S', intval($item->record['crdate'])) : '-';
		$dates['tstamp'] = (array_key_exists('tstamp', $item->record)) ? strftime('%d.%m.%y %H:%M:%S', intval($item->record['tstamp'])) : '-';
		return "<span title=\"Creation: ".$dates['crdate']." \nLast Change: ".$dates['tstamp']." \">".$ret .'</span>';
	}

	/**
	 * renders the label column.
	 *
	 * @param tx_rnbase_model_base $item
	 * @return string
	 */
	protected function getLabelColumn(tx_rnbase_model_base $item) {
		$lastModifyDateTime = $item->getLastModifyDateTime();
		$creationDateTime = $item->getCreationDateTime();
		return sprintf(
			'<span title="UID: %3$d %1$sLabel: %2$s %1$sCreation: %4$s %1$sLast Change: %5$s">%2$s</span>',
			CRLF,
			$item->getTcaLabel(),
			$item->getProperty('uid'),
			$creationDateTime ? $creationDateTime->format(DateTime::ATOM) : '-',
			$lastModifyDateTime ? $lastModifyDateTime->format(DateTime::ATOM) : '-'
		);
	}

	/**
	 * Renders the language column.
	 * Renders the flag and the title of the sys language record.
	 * Renders some links to create the overlay too.
	 *
	 * @param tx_rnbase_model_base $item
	 * @return string
	 */
	protected function getSysLanguageColumn(tx_rnbase_model_base $item) {
		if ($item->getTableName()) {
			tx_rnbase::load('tx_mklib_mod1_util_Language');
			$ret = tx_mklib_mod1_util_Language::getLangSpriteIcon(
				$item->getSysLanguageUid(),
				array('show_title' => TRUE)
			);
			$new = tx_mklib_mod1_util_Language::getAddLocalizationLinks(
				$item,
				$this->getModule()
			);
			if (!empty($new)) {
				$ret .= ' ('
					. $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:Localize')
					. ' ' . $new
					. ')'
				;
			}
		}

		return empty($ret) ? FALSE : $ret;
	}

	/**
	 * Liefert die möglichen Optionen für die actions
	 * @param tx_rnbase_model_base $item
	 * @return array
	 */
	protected function getActionOptions($item = null) {
		$cols = array(
			'edit' => '',
			'hide' => '',
		);

		$userIsAdmin = is_object($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER']->isAdmin() : 0;
		//admins dürfen auch löschen
		if ($userIsAdmin)
			$cols['remove'] = '';

		return $cols;
	}

	/**
	 * @TODO: weitere links integrieren!
	 * $options = array('hide'=>'ausblenden,'edit'=>'bearbeiten,'remove'=>'löschen','history'='history','info'=>'info','move'=>'verschieben');
	 *
	 * @param 	tx_rnbase_model_base 	$item
	 * @param 	array 					$options
	 * @return 	string
	 */
	protected function getActions(tx_rnbase_model_base $item, array $options) {
		$ret = '';
		$tableName = $item->getTableName();
		// we use the real uid, not the uid of the parent!
		$uid = $item->getProperty('uid');
		foreach($options as $sLinkId => $bTitle){
			switch($sLinkId) {
				case 'edit':
					$ret .= $this->getFormTool()->createEditLink($tableName, $uid, $bTitle);
					break;
				case 'hide':
					$sHiddenColumn = tx_mklib_util_TCA::getEnableColumn($tableName, 'disabled', 'hidden');
					$ret .= $this->getFormTool()->createHideLink($tableName, $uid, $item->record[$sHiddenColumn]);
					break;
				case 'remove':
					//Es wird immer ein Bestätigungsdialog ausgegeben!!! Dieser steht
					//in der BE-Modul locallang.xml der jeweiligen Extension im Schlüssel
					//'confirmation_deletion'. (z.B. mkkvbb/mod1/locallang.xml) Soll kein
					//Bestätigungsdialog ausgegeben werden, dann einfach 'confirmation_deletion' leer lassen
					$ret .= $this->getFormTool()->createDeleteLink($tableName, $uid, $bTitle,array('confirm' => $GLOBALS['LANG']->getLL('confirmation_deletion')));
					break;
				default:
					break;
			}
		}
		return $ret;
	}

	/**
	 *
	 * @param string $output
	 * @return string
	 */
	protected function wrapValue(
		$output,
		$value,
		$colName,
		$record,
		tx_rnbase_model_base $item
	) {
		$stateClass = array();

		if ($item->isHidden()) {
			$stateClass[] = 'ef-hidden';
		}
		if ($item->isDeleted()) {
			$stateClass[] = 'ef-deleted';
		}

		if (!empty($stateClass)) {
			$output = '<div class="' . implode(' ', $stateClass) . '">' . $output . '</div>';
		}

		return $output;
	}



	/**
	 * Returns the module
	 * @return tx_rnbase_mod_IModule
	 */
	protected function getModule() {
		return $this->mod;
	}

	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 *
	 * @return 	tx_rnbase_util_FormTool
	 */
	protected function getFormTool() {
		return $this->mod->getFormTool();
	}
}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/decorator/class.tx_mklib_mod1_decorator_Base.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/decorator/class.tx_mklib_mod1_decorator_Base.php']);
}
