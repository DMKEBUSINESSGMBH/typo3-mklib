<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_validator
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_tests_DBTestCaseSkeleton');

/**
 * Testfälle für tx_mklib_validator_ZipCode
 *
 * @author	 Michael Wagner <michael.wagner@das-medienkombinat.de>
 * @package tx_mklib
 * @subpackage tx_mklib_tests_validator
 */
class tx_mklib_tests_validator_ZipCode_testcase extends tx_mklib_tests_DBTestCaseSkeleton {

	protected $importExtensions = array('static_info_tables', 'mklib');
	protected $importStaticTables = true;

	/**
	 * Wurden die ZipRules geladen?
	 *
	 * @return boolean
	 */
	private static function checkStaticCountries($skip=false){
		$cnt = tx_rnbase_util_DB::doSelect('COUNT(uid) as cnt','static_countries', array('enablefieldsoff'=>1,/*'debug'=>1,*/ 'where'=>'zipcode_rule > 0'));
		$loaded = intval($cnt[0]['cnt']) > 0;
		if(!$loaded && $skip) {
			self::markTestSkipped('Zip code rules not found in database');
		}
		return $loaded;
	}

	/**
	 * @param 	mixed $rowOrUid
	 * @return tx_mklib_model_StaticCountry
	 */
	private static function getStaticCountryModel($rowOrUid){
//		tx_rnbase::load('tx_mklib_model_StaticCountry');
//		return tx_mklib_model_StaticCountry::getInstance($rowOrUid);
		return tx_rnbase::makeInstance('tx_mklib_model_StaticCountry', $rowOrUid);
	}

	protected function setUp(){
		parent::setUp();
		// ziprules in die db schreiben
		self::importStaticTables('mklib', array('ext_tables_static_update.sql'), 'UPDATE');
	}

	public function testValidateDE(){
		self::checkStaticCountries(true);

		$oCountry = self::getStaticCountryModel(54 /*DE*/);

		$this->assertTrue(is_object($oCountry), 'No model given.');
		$this->assertTrue($oCountry->isValid(), 'No valid model given.');
		$this->assertEquals('DE', $oCountry->getISO2(), 'No or wrong iso 2 given.');
		$this->assertEquals(5, $oCountry->getZipLength(), 'No or wrong  zip length given.');
		$this->assertEquals(4, $oCountry->getZipRule(), 'No or wrong  zip rule given.');

		$validator = tx_mklib_validator_ZipCode::getInstance();

		$zips = array('09113', '14482');
		foreach($zips as $zip) {
			$this->assertTrue($validator->validate($oCountry, $zip), $zip.' -> '.$validator->getFormatInfo($oCountry));
		}
		$zips = array('9120', 'O9113');
		foreach($zips as $zip) {
			$this->assertFalse($validator->validate($oCountry, $zip), $zip.' -> '.$validator->getFormatInfo($oCountry));
		}
	}

	/**
	 * @dataProvider providerValidatorRules
	 * @param 	string		$sZip
	 * @param 	int 		$iCountry
	 * @param 	boolean		$bResult
	 */
	public function testValidatorRules($sZip, $iCountry, $bResult){
		self::checkStaticCountries(true);
		$oCountry = self::getStaticCountryModel($iCountry);
		$oValidator = tx_mklib_validator_ZipCode::getInstance();
		$this->assertEquals($bResult, $oValidator->validate($oCountry, $sZip), $sZip.' -> '.$oValidator->getFormatInfo($oCountry));
	}
	public function providerValidatorRules(){
		$return = array();
		foreach(array(
//				array($iZip, $oCountry, $bResult),
				__LINE__ => array('09113', (54 /*DE*/), true),
				__LINE__ => array('9120', (54 /*DE*/), false),
				__LINE__ => array('föllig egal, keine rules!', (130 /*MA*/), true),
				__LINE__ => array('70014', (85 /*GR*/), false),
				__LINE__ => array('700 14', (85 /*GR*/), true),
				__LINE__ => array('irgendwasabermax9', (220 /*US*/), false),
				__LINE__ => array('99825', (220 /*US*/), true),
				__LINE__ => array('4526', (13 /*AT*/), true),
				__LINE__ => array('45267', (13 /*AT*/), false),
				__LINE__ => array('452', (13 /*AT*/), false),
				__LINE__ => array('A0A 0A0', (36 /*CA*/), true),
				__LINE__ => array('9120', (36 /*CA*/), false),
				__LINE__ => array('irgendwas', (36 /*CA*/), false),

				__LINE__ => array('333', (103 /*IS*/), true),
				__LINE__ => array('33', (103 /*IS*/), false),
				__LINE__ => array('3333', (103 /*IS*/), false),
				__LINE__ => array('4444', (20 /*BE*/), true),
				__LINE__ => array('444', (20 /*BE*/), false),
				__LINE__ => array('44444', (20 /*BE*/), false),
				__LINE__ => array('55555', (72 /*FR*/), true),
				__LINE__ => array('5555', (72 /*FR*/), false),
				__LINE__ => array('555555', (72 /*FR*/), false),
				__LINE__ => array('666666', (46 /*CN*/), true),
				__LINE__ => array('66666', (46 /*CN*/), false),
				__LINE__ => array('6666666', (46 /*CN*/), false),

			) as $key => $row) {
			$key = 'Line:'.strtolower($key).' Zip:'.$row[0].' Country:'.intval($row[1]).' Return:'.intval($row[2]);
			$return[$key] = $row;
		}
		return $return;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/validator/class.tx_mklib_tests_validator_ZipCode_testcase.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/validator/class.tx_mklib_tests_validator_ZipCode_testcase.php']);
}

?>