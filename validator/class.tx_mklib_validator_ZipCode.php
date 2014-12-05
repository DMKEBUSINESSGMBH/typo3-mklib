<?php
/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

/**
 * Die Klasse stellt Funktionen für die Validierung von Postleitzahlen zur Verfügung.
 * 
 * @author René Nitzsche
 * @package tx_mklib
 * @subpackage tx_mklib_validator
 */
class tx_mklib_validator_ZipCode {
	static $instance = null;
	/**
	 * Liefert eine instanz des Validators
	 * 
	 * @return tx_mklib_validator_ZipCode
	 */
	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = tx_rnbase::makeInstance('tx_mklib_validator_ZipCode');
		}
		return self::$instance;
	}
	/**
	 * Liefert für ein Land einen Hinweistext für das PLZ-Format.
	 * 
	 * @param tx_mklib_interface_IZipCountry $country
	 * @return string
	 */
	public static function getFormatInfo(tx_mklib_interface_IZipCountry $country) {
		$rule = $country->getZipRule() == 9 ? $country->getZipRule().'_'.$country->getISO2() : $country->getZipRule();
		$labelKey = 'LLL:EXT:mklib/locallang.xml:label_ziperror_r'.$rule;
		$llObj = (TYPO3_MODE == 'BE') ? $GLOBALS['LANG'] : $GLOBALS['TSFE'];
		$label = sprintf($llObj->sL($labelKey), $country->getZipLength());
		return $label;
	}
	
	/**
	 * Validiert einen PLZ-String für ein Land.
	 * @param tx_mklib_interface_IZipCountry $land
	 * @param string $zip
	 * @return boolean
	 */
	public static function validate(tx_mklib_interface_IZipCountry $country, $zip) {
		$result = true;
		switch($country->getZipRule()) {
			case 0: // no rule set
				$result = true;
				tx_rnbase::load('tx_rnbase_util_Logger');
				if(tx_rnbase_util_Logger::isNoticeEnabled())
					tx_rnbase_util_Logger::notice('No zip rule for country defined.', 'mklib', array('zip'=>$zip,'getISO2'=>$country->getISO2(),'getZipLength'=>$country->getZipLength(),'getZipRule'=>$country->getZipRule()));
				break;
			case 1: // maximum length without gaps
				$result = self::validateMaxLengthWG($country, $zip);
				break;
			case 2: // maximum length numerical without gaps
				$result = self::validateMaxLengthNumWG($country, $zip);
				break;
			case 3: // exact length without gaps
				$result = self::validateLengthWG($country, $zip);
				break;
			case 4: // exact length numerical without gaps
				$result = self::validateLengthNumWG($country, $zip);
				break;
			case 5: // maximum length with gaps
				$result = self::validateMaxLength($country, $zip);
				break;
			case 6: // maximum length numerical with gaps
				$result = self::validateMaxLengthNum($country, $zip);
				break;
			case 7: // exact length with gaps
				$result = self::validateLength($country, $zip);
				break;
			case 8: // exact length numerical with gaps
				$result = self::validateLengthNum($country, $zip);
				break;
			case 9: // special rules
				$result = self::validateSpecial($country, $zip);
				break;
			default:
				$result = false;
				break;
		}
		
		return $result;
	}
	/**
	 * http://help.sap.com/saphelp_nw2004s/helpdata/en/0d/40bb3acf19c731e10000000a114084/content.htm
	 * 
	 * @param tx_mklib_interface_IZipCountry $country
	 * @param string $zip
	 * @return boolean
	 */
	private static function validateSpecial($country, $zip) {
		$result = true;
		switch($country->getISO2()) {
			case 'CA':
				$result = preg_match('/^[A-Za-z]\d[A-Za-z] \d[A-Za-z]\d$/', $zip) > 0;
				break;
			case 'SW':
			case 'GR': // TODO: für Griechenland fehlt das Template.
			case 'SK':
			case 'CZ':
				$result = preg_match('/^\d\d\d \d\d$/', $zip) > 0;
				break;
			case 'PT':
				$result = preg_match('/^\d\d\d\d-\d\d\d$/', $zip) > 0 || preg_match('/^\d\d\d\d$/', $zip) > 0;
				break;
			case 'NL':
				$result = preg_match('/^\d\d\d\d [A-Za-z][A-Za-z]$/', $zip) > 0;
				break;
			case 'PL':
				$result = preg_match('/^\d\d-\d\d\d$/', $zip) > 0;
				break;
			case 'KR': // Südkorea
				$result = preg_match('/^\d\d\d-\d\d\d$/', $zip) > 0;
				break;
			default:
				$result = false;
		}
		
		return $result;
	}
	/**
	 * 
	 * @param tx_mklib_interface_IZipCountry $country
	 * @param string $zip
	 * @return boolean
	 */
	private static function validateMaxLengthWG($country, $zip) {
		return preg_match('/^[A-Za-z0-9]{1,'.$country->getZipLength().'}$/', $zip) > 0;
	}
	/**
	 * 
	 * @param tx_mklib_interface_IZipCountry $country
	 * @param string $zip
	 * @return boolean
	 */
	private static function validateMaxLengthNumWG($country, $zip) {
		return preg_match('/^[0-9]{1,'.$country->getZipLength().'}$/', $zip) > 0;
	}

	/**
	 * 
	 * @param tx_mklib_interface_IZipCountry $country
	 * @param string $zip
	 * @return boolean
	 */
	private static function validateLengthWG($country, $zip) {
		return preg_match('/^[A-Za-z0-9]{'.$country->getZipLength().'}$/', $zip) > 0;
	}
	/**
	 * 
	 * @param tx_mklib_interface_IZipCountry $country
	 * @param string $zip
	 * @return boolean
	 */
	private static function validateLengthNumWG($country, $zip) {
		return preg_match('/^[0-9]{'.$country->getZipLength().'}$/', $zip) > 0;
	}
	/**
	 * 
	 * @param tx_mklib_interface_IZipCountry $country
	 * @param string $zip
	 * @return boolean
	 */
	private static function validateMaxLength($country, $zip) {
		return preg_match('/^[ A-Za-z0-9]{1,'.$country->getZipLength().'}$/', $zip) > 0;
	}
	/**
	 * 
	 * @param tx_mklib_interface_IZipCountry $country
	 * @param string $zip
	 * @return boolean
	 */
	private static function validateMaxLengthNum($country, $zip) {
		return preg_match('/^[ 0-9]{1,'.$country->getZipLength().'}$/', $zip) > 0;
	}
	/**
	 * 
	 * @param tx_mklib_interface_IZipCountry $country
	 * @param string $zip
	 * @return boolean
	 */
	private static function validateLength($country, $zip) {
		return preg_match('/^[ A-Za-z0-9]{'.$country->getZipLength().'}$/', $zip) > 0;
	}
	/**
	 * 
	 * @param tx_mklib_interface_IZipCountry $country
	 * @param string $zip
	 * @return boolean
	 */
	private static function validateLengthNum($country, $zip) {
		return preg_match('/^[ 0-9]{'.$country->getZipLength().'}$/', $zip) > 0;
	}
}
if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/validator/class.tx_mklib_validator_ZipCode.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/validator/class.tx_mklib_validator_ZipCode.php']);
}