<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 michael Wagner <michael.wagner@dmk-ebusiness.de>
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

/**
 * Miscellaneous common methods
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_MiscTools {

	/**
	 * Liefert einen Wert aus der Extension-Konfiguration.
	 * Gibt es für die angegebene Extension keine Konfiguration,
	 * wird als Fallback der Wert von mklib zurückgegeben.
	 *
	 * @param string 	$sValueKey
	 * @param string 	$sExtKey
	 * @param boolean 	$bFallback
	 * @return mixed
	 */
	public static function getExtensionValue($sValueKey, $sExtKey='mklib', $bFallback=false){
		if(!$sExtKey) {
			$sExtKey = 'mklib';
		}
		tx_rnbase::load('tx_rnbase_configurations');
		$mValue = tx_rnbase_configurations::getExtensionCfgValue($sExtKey, $sValueKey);
		if($bFallback && $mValue === false && $sExtKey != 'mklib')
			$mValue = tx_rnbase_configurations::getExtensionCfgValue('mklib', $sValueKey);
		return $mValue;
	}

	/**
	 * Liefert eine BE-Account.
	 * Dieser Nutzer wird für TCE Operationen verwendet. Er sollte Admin-Rechte haben.
	 *
	 * @param string 	$sExtKey
	 * @param boolean 	$bFallback
	 * @return int
	 */
	public static function getProxyBeUserId($sExtKey='mklib', $bFallback=true){
		return intval( self::getExtensionValue('proxyBeUserId', $sExtKey, $bFallback) );
	}

	/**
	 * Liefert den Pfad zu den Bildern.
	 *
	 * @param string 	$sExtKey
	 * @param boolean 	$bFallback
	 * @return int
	 */
	public static function getPicturesUploadPath($sExtKey='mklib', $bFallback=true){
		return self::getExtensionValue('picturesUploadPath', $sExtKey, $bFallback);
	}

	/**
	 * Liefert die Page-ID, wo alle Portaldaten gespeichert sind.
	 *
	 * @param string 	$sExtKey
	 * @param boolean 	$bFallback
	 * @return int
	 */
	public static function getPortalPageId($sExtKey='mklib', $bFallback=true){
		return intval( self::getExtensionValue('portalPageId', $sExtKey, $bFallback) );
	}

	/**
	 * Gibt die Extension Konfiguration für den Sonderzeichen Marker zurück
	 * Diese wird aber lediglich angegeben. Die Mehrwertsteuer wird durch die Extension
	 * Konfiguration NICHT angelegt!
	 *
	 * @param string 	$sExtKey
	 * @param boolean 	$bFallback
	 * @return int
	 */
	public static function getSpecialCharMarker($sExtKey='mklib', $bFallback=true){
		return self::getExtensionValue('specialCharMarker', $sExtKey, $bFallback);
	}

	/**
	 * IP-based Access restrictions
	 * @TODO: in util_dev auslagern!?
	 *
	 * @param 	string 		$remoteAddress
	 * @param 	string 		$devIPmask
	 * @return 	boolean
	 */
	public static function isDevIpMask($remoteAddress='',$devIPmask=''){
		$devIPmask = trim(strcmp($devIPmask, '') ? $devIPmask : $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask']);
		$remoteAddress = trim(strcmp($remoteAddress, '') ? $remoteAddress : t3lib_div::getIndpEnv('REMOTE_ADDR'));
		return t3lib_div::cmpIP($remoteAddress, $devIPmask);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mkmarketplace/util/class.tx_mklib_util_MiscTools.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mkmarketplace/util/class.tx_mklib_util_MiscTools.php']);
}