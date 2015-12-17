<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_validator
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

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_util_Files');

/**
 * Testfälle für tx_mklib_validator_ZipCode
 *
 * @author	 Michael Wagner <michael.wagner@dmk-ebusiness.de>
 * @package tx_mklib
 * @subpackage tx_mklib_tests_validator
 *
 * @group integration
 */
class tx_mklib_tests_validator_ZipCode_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp(){
		if (!tx_rnbase_util_Extensions::isLoaded('static_info_tables')) {
			$this->markTestSkipped('static_info_tables nicht installiert');
		}

		// zur Sicherheit die Zip Code Rules einfügen
		$sqlFilename = tx_rnbase_util_Files::getFileAbsFileName(tx_rnbase_util_Extensions::extPath('mklib', 'ext_tables_static_update.sql'));
		if(@is_file($sqlFilename)) {
			tx_mklib_tests_Util::queryDB($sqlFilename, false, true);//alle statements importieren
		}
	}

	/**
	 * @group integration
	 */
	public function testValidateGermanZips(){
		self::checkStaticCountries();

		$country = self::getStaticCountryModel(54 /*DE*/);

		self::assertTrue(is_object($country), 'No model given.');
		self::assertTrue($country->isValid(), 'No valid model given.');
		self::assertEquals('DE', $country->getISO2(), 'No or wrong iso 2 given.');
		self::assertEquals(5, $country->getZipLength(), 'No or wrong  zip length given.');
		self::assertEquals(4, $country->getZipRule(), 'No or wrong  zip rule given.');

		$validator = tx_mklib_validator_ZipCode::getInstance();

		$zips = array('09113', '14482');
		foreach($zips as $zip) {
			self::assertTrue(
				$validator->validate($country, $zip),
				$zip.' -> '.$validator->getFormatInfo($country)
			);
		}
		$zips = array('9120', 'O9113');
		foreach($zips as $zip) {
			self::assertFalse(
				$validator->validate($country, $zip),
				$zip.' -> '.$validator->getFormatInfo($country)
			);
		}
	}

	/**
	 * @dataProvider providerValidatorRules
	 * @param 	string		$zip
	 * @param 	int 		$countryUid
	 * @param 	boolean		$result
	 *
	 * @group integration
	 */
	public function testValidatorRules($zip, $countryUid, $result){
		self::checkStaticCountries();
		$country = self::getStaticCountryModel($countryUid);
		$validator = tx_mklib_validator_ZipCode::getInstance();
		self::assertEquals(
			$result, $validator->validate($country, $zip),
			$zip.' -> '.$validator->getFormatInfo($country)
		);
	}

	/**
	 * @return multitype:multitype:string number boolean
	 */
	public function providerValidatorRules(){
		$return = array();
		foreach(array(
//				array($iZip, $country, $result),
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

	/**
	 * Wurden die ZipRules geladen?
	 */
	private static function checkStaticCountries(){
		$cnt = tx_rnbase_util_DB::doSelect('COUNT(uid) as cnt','static_countries', array('enablefieldsoff'=>1,/*'debug'=>1,*/ 'where'=>'zipcode_rule > 0'));
		$loaded = intval($cnt[0]['cnt']) > 0;

		if (!$loaded) {
			// zur Sicherheit die Zip Code Rules einfügen
			$sqlFilename = tx_rnbase_util_Files::getFileAbsFileName(tx_rnbase_util_Extensions::extPath('mklib', 'ext_tables_static_update.sql'));
			if(@is_file($sqlFilename)) {
				tx_mklib_tests_Util::queryDB($sqlFilename, false, true);//alle statements importieren
			}
		}
	}

	/**
	 * @param 	mixed $rowOrUid
	 * @return tx_mklib_model_StaticCountry
	 */
	private static function getStaticCountryModel($rowOrUid){
		return tx_rnbase::makeInstance('tx_mklib_model_StaticCountry', $rowOrUid);
	}
}