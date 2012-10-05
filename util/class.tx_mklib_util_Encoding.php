<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));

/**
 * Class for encodings
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mklib_util_Encoding {


	/**
	 * Liefert die Zeichencodierung der Umgebung
	 *
	 * @return string
	 */
	public static function getTypo3Encoding() {
		if (TYPO3_MODE == 'FE') {
			$charset = $GLOBALS['TSFE']->renderCharset;
		} elseif (is_object($GLOBALS['LANG'])) { // BE assumed:
			$charset = $GLOBALS['LANG']->charSet;
		} else { // best guess
			$charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
		}
		return $charset;
	}

	/**
	 * Encodes a value using mb_convert_encoding
	 * @param string|array $var
	 * 		The string or array being encoded.
	 * @param string $toEncoding
	 * 		The type of encoding that str is being converted to.
	 * 		If toEncoding is not specified, the Typo3 encoding will be used.
	 * @param string $fromEncoding
	 * 		Is specified by character code names before conversion.
	 * 		It is either an array, or a comma separated enumerated list.
	 * 		If fromEncoding is not specified, the internal encoding will be used.
	 * 		@see Supported Encodings http://www.php.net/manual/en/mbstring.supported-encodings.php
	 */
	public static function convertEncoding(
			$var, $toEncoding = null, $fromEncoding = null
		) {
		if (is_null($toEncoding)){
			$toEncoding = self::getTypo3Encoding();
		}
		if (is_array($var)) {
			foreach ($var as &$value) {
				$value = self::convertEncoding(
					$value, $toEncoding, $fromEncoding);
			}
		}
		else {
			$var = mb_convert_encoding(
				strval($var), $toEncoding, $fromEncoding);
		}
		return $var;
	}
}

if (
	defined('TYPO3_MODE')
	&& $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mklib/util/class.tx_mklib_util_Encoding.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
  		['ext/mklib/util/class.tx_mklib_util_Encoding.php']);
}
