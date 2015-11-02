<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_validator
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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

/**
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_util_ServiceRegistry');

/**
 * Validatoren für den WordList Service
 *
 * @author hbochmann
 * @package tx_mklib
 * @subpackage tx_mklib_validator
 */
class tx_mklib_validator_WordList {

	/**
	 * @var tx_mklib_srv_Wordlist
	 */
	protected static $wordlistService = NULL;

	/**
	 * Prüft ob in einem String kein Wort, das blacklisted ist, vorkommt
	 *
	 * @param string $word
	 * @param $greedy | alle oder nur ein Treffer?
	 * @param $sanitizeWord | alle Sonderzeichen vor der Prüfung entfernen
	 * @return bool
	 */
	public static function stringContainsNoBlacklistedWords ($word,$greedy = true, $sanitizeWord = true) {
		if (empty($word)) {
			return true;//leer bedeutet kein Treffer
		}

		$entry = static::getWordlistService()->getBlacklistEntryByWord($word,$greedy,$sanitizeWord);

		//wenn etwas gefunden wurde, geben wir den Treffer zurück
		return (empty($entry)) ? true : $entry;
	}

	/**
	 * @return tx_mklib_srv_Wordlist
	 */
	protected static function getWordlistService() {
		if (is_null(static::$wordlistService)) {
			static::$wordlistService = tx_mklib_util_ServiceRegistry::getWordlistService();
		}

		return static::$wordlistService;
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/validator/class.tx_mklib_validator_WordList.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/validator/class.tx_mklib_validator_WordList.php']);
}
