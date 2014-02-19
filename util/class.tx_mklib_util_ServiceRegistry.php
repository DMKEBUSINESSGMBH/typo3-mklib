<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
tx_rnbase::load('tx_rnbase_util_Misc');

/**
 * Class to access services
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_ServiceRegistry {

	/** @var 	string 	Extensionkey*/
	private static $extKey = 'mklib';
	/**
	 * Return wordlist service
	 * @return tx_mklib_srv_Wordlist
	 */
	public static function getWordlistService() {
		return tx_rnbase_util_Misc::getService(self::$extKey, 'wordlist');
	}

	/**
	 * Return wordlist service
	 * @return tx_mklib_srv_Finance
	 */
	public static function getFinanceService() {
		return tx_rnbase_util_Misc::getService(self::$extKey, 'finance');
	}
	
	/**
	 * @return tx_mklib_srv_StaticCountries
	 */
	public static function getStaticCountriesService() {
		return tx_rnbase_util_Misc::getService(self::$extKey, 'staticCountries');
	}
	
	/**
	 * @return tx_mklib_srv_StaticCountryZones
	 */
	public static function getStaticCountryZonesService() {
		return tx_rnbase_util_Misc::getService(self::$extKey, 'staticCountryZones');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_ServiceRegistry.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_ServiceRegistry.php']);
}