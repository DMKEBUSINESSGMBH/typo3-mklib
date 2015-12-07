<?php
/**
 * Copyright notice
 *
 * (c) 2015 DMK E-Business GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */
require_once tx_rnbase_util_Extensions::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_mod_Util');

/**
 *
 * @package TYPO3
 * @subpackage Tx_Mkhogaimport
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_mod1_util_Language {


	/**
	 * cached language records
	 *
	 * @var array
	 */
	private static $sysLanguageRecords = array();
	private static $sysLanguageRecordAll = FALSE;

	/**
	 *
	 * @param integer $uid
	 * @return array
	*/
	public static function getLangRecord($uid) {
		$uid = (int) $uid;
		if (empty(static::$sysLanguageRecords[$uid])) {
			tx_rnbase::load('tx_rnbase_util_DB');
			static::$sysLanguageRecords[$uid] = tx_rnbase_util_DB::getRecord(
				'sys_language', $uid
			);
		}

		return static::$sysLanguageRecords[$uid];
	}
	/**
	 *
	 * @param integer $pageId
	 * @return array
	 */
	public static function getLangRecords($pageId) {
		if (!static::$sysLanguageRecordAll) {
			static::$sysLanguageRecordAll = TRUE;
			tx_rnbase::load('tx_rnbase_util_DB');
			$records = tx_rnbase_util_DB::doSelect('*', 'sys_language', array());
			foreach ($records as $record) {
				static::$sysLanguageRecords[(int) $record['uid']] = $record;
			}
		}
		$records = static::$sysLanguageRecords;

		if ($pageId) {
			// check all page overlays to get all available languages for the page
			$available = tx_rnbase_util_DB::doSelect(
				'sys_language.uid',
				array('sys_language,pages_language_overlay', 'sys_language'),
				array(
					'where' => 'pages_language_overlay.sys_language_uid=sys_language.uid'
					. ' AND pages_language_overlay.pid=' . (int) $pageId
					. t3lib_BEfunc::deleteClause('pages_language_overlay'),
					'orderby' => 'sys_language.title ASC',
				)
			);
			$records = array();
			foreach ($available as $langRow) {
				$langUid = (int) $langRow['uid'];
				$records[$langUid] = self::getLangRecord($langUid);
			}
		}

		return $records;
	}

	/**
	 * returns the sprite icon for the given sys language record.
	 *
	 * @param array|int $recordOrUid
	 * @return Ambigous <string, multitype:>
	 */
	public static function getLangSpriteIcon($recordOrUid, $options = NULL) {
		tx_rnbase::load('tx_rnbase_model_data');
		$options = tx_rnbase_model_data::getInstance($options);

		if (!is_array($recordOrUid)) {
			$langUid = (int) $recordOrUid;
			$record = self::getLangRecord($recordOrUid);
		} else {
			$langUid = (int) $recordOrUid['uid'];
			$record = $recordOrUid;
		}
		$spriteIconName = 'flags-multiple';
		if (!empty($record)) {
			$spriteIconName = t3lib_iconWorks::mapRecordTypeToSpriteIconName(
				'sys_language',
				$record
			);
		}
		$out = tx_rnbase_mod_Util::getSpriteIcon(
			$spriteIconName
		);
		// add title per default (typo3 equivalent)!
		if ($options->getShowTitle() !== FALSE) {
			$langTitle = 'N/A';
			if ($langUid === -1) {
				$langTitle = 'LLL:EXT:lang/locallang_general.xml:LGL.allLanguages';
			}
			elseif ($langUid === 0) {
				$langTitle = 'LLL:EXT:lang/locallang_general.xml:LGL.default_value';
			}
			elseif (!empty($record['title'])) {
				$langTitle = $record['title'];
			}

			$out .= '&nbsp;' . htmlspecialchars($GLOBALS['LANG']->sL($langTitle));
		}
		return $out;
	}

	public static function getAddLocalizationLinks(
		tx_rnbase_model_base $item,
		tx_rnbase_mod_BaseModule $mod = NULL
	) {
		if (
			// the item already are an translated item!
			$item->getUid() != $item->getProperty('uid')
			|| $item->getSysLanguageUid() !== 0
		) {
			return '';
		}

		$out = '';
		foreach (self::getLangRecords($item->getPid()) as $lang) {
			// skip, if the be user hase no access to for the language!
			if (!$GLOBALS['BE_USER']->checkLanguageAccess($lang['uid'])) {
				continue;
			}

			// skip, if a overlay for this language allready exists
			tx_rnbase::load('tx_rnbase_util_TCA');
			$parentField = tx_rnbase_util_TCA::getTransOrigPointerFieldForTable($item->getTableName());
			$sysLanguageUidField = tx_rnbase_util_TCA::getLanguageFieldForTable($item->getTableName());
			$overlays = tx_rnbase_util_DB::doSelect(
				'uid',
				$item->getTableName(),
				array(
					'where' => implode(
						' AND ',
						array(
							$parentField . '=' . $item->getUid(),
							$sysLanguageUidField . '=' . (int) $lang['uid'],
						)
					),
					'limit' => 1,
				)
			);
			if (!empty($overlays)) {
				continue;
			}

			/* @var $mod tx_rnbase_mod_BaseModule */
			if (!$mod instanceof tx_rnbase_mod_BaseModule) {
				$mod = $GLOBALS['SOBE'];
			}

			$onclick = $mod->getDoc()->issueCommand(
				'&cmd[' . $item->getTableName() . '][' . $item->getUid() . '][localize]=' . $lang['uid']
			);
			$onclick = 'window.location.href=\'' . $onclick . '\'; return false;';

			$out .= sprintf(
				'<a href="#" onclick="%1$s">%2$s</a>',
				htmlspecialchars(
					$onclick
				),
				self::getLangSpriteIcon(
					$lang,
					array('show_title' => FALSE)
				)
			);
		}

		return $out;
	}
}
