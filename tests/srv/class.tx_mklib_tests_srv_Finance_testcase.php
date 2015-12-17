<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_srv
 *  @author Hannes Bochmann
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2010-2015 DMK-EBUSINESS GmbH <dev@dmk-ebusiness.de>
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
tx_rnbase::load('tx_mklib_util_ServiceRegistry');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_srv
 * @author Hannes Bochmann
 * @author Michael Wagner
 */
class tx_mklib_tests_srv_Finance_testcase
	extends tx_rnbase_tests_BaseTestCase {
	protected $oFinanceSrv;

	/**
	 * Service instanzieren
	 */
	public function setUp() {
		/*
		 * ist es sinnvoll hier den setup zu nutzen?
		 * der service wird ja eh vor jedem test neu geholt!
		 */
		$this->oFinanceSrv = tx_mklib_util_ServiceRegistry::getFinanceService();
	}

	public function testGetCurrency(){
		$oSrv = tx_mklib_util_ServiceRegistry::getFinanceService();
		$oCurrency = $oSrv->getCurrency();

		self::assertTrue(is_object($oCurrency));
		self::assertEquals('tx_mklib_model_Currency', get_class($oCurrency));
	}

	public function testGetFormattedCurrency(){
		$oSrv = tx_mklib_util_ServiceRegistry::getFinanceService();

		self::assertEquals('54.587,96 €', $oSrv->getFormattedCurrency('54587.957', false));
		self::assertEquals('5,78 &euro;', $oSrv->getFormattedCurrency('5.7825', true));
		self::assertEquals('6,00 €', $oSrv->getFormattedCurrency('6', false));
		self::assertEquals('-3,60 &euro;', $oSrv->getFormattedCurrency('-3.6'));
	}
	/**
	 * Prüft ob richtig gerundet wird
	 */
	public function testRoundDouble(){
		$srv = tx_mklib_util_ServiceRegistry::getFinanceService();
		self::assertEquals(2.54,$this->oFinanceSrv->roundUpDouble(2.5316,2,false),'Die Zahl wurde nicht korrekt gerundet!');
		self::assertEquals(2.54,$this->oFinanceSrv->roundUpDouble(2.5356,2,false),'Die Zahl wurde nicht korrekt gerundet!');
		self::assertEquals(2.536,$this->oFinanceSrv->roundUpDouble(2.5356,3,false),'Die Zahl wurde nicht korrekt gerundet!');
		self::assertEquals('2,20',$this->oFinanceSrv->roundUpDouble('2.2000',2, true, ','),'Die Zahl wurde nicht korrekt gerundet!');
	}

	/**
	 * test the getJoins method.
	 *
	 * @param string $tableAliases
	 * @param string $expectedJoin
	 * @return void
	 *
	 * @group unit
	 * @test
	 * @dataProvider getValidateVatRegNoData
	 */
	public function testValidateVatRegNo($country, $vatregno, $expected) {
		$srv = tx_mklib_util_ServiceRegistry::getFinanceService();
		self::assertSame(
			$expected,
			$srv->validateVatRegNo($country, $vatregno)
		);
	}

	/**
	 * Liefert die Daten für den testValidateVatRegNo testcase.
	 *
	 * @return array
	 */
	public function getValidateVatRegNoData() {
		return array(
			// test country by uid
			__LINE__ => array('country' => tx_rnbase_util_Extensions::isLoaded('static_info_tables') ? '54' : 'de', 'vatregno' => 'DE123456789', 'expected' => TRUE),
			// test country model
			__LINE__ => array('country' => $this->getModel(array('cn_iso_2' => 'DE')), 'vatregno' => 'DE123456789', 'expected' => TRUE),
			// all the other static tests
			__LINE__ => array('country' => 'de', 'vatregno' => 'DE123456789', 'expected' => TRUE),
			__LINE__ => array('country' => 'de', 'vatregno' => 'DE12345', 'expected' => FALSE),
			__LINE__ => array('country' => 'de', 'vatregno' => 'DE12ABG', 'expected' => FALSE),
			__LINE__ => array('country' => 'PL', 'vatregno' => 'PL1234567890', 'expected' => TRUE),
			__LINE__ => array('country' => 'PL', 'vatregno' => 'PL12345', 'expected' => FALSE),
			__LINE__ => array('country' => 'PL', 'vatregno' => 'PL12ABG', 'expected' => FALSE),
			__LINE__ => array('country' => 'FR', 'vatregno' => 'FR1A 123456789', 'expected' => TRUE),
			__LINE__ => array('country' => 'FR', 'vatregno' => 'FR1A 123', 'expected' => FALSE),
			__LINE__ => array('country' => 'FR', 'vatregno' => 'FR12A 123456789', 'expected' => FALSE),
			__LINE__ => array('country' => 'LU', 'vatregno' => 'LU12345678', 'expected' => TRUE),
			__LINE__ => array('country' => 'LU', 'vatregno' => 'LU123', 'expected' => FALSE),
			__LINE__ => array('country' => 'LU', 'vatregno' => 'LU123456789', 'expected' => FALSE),
			__LINE__ => array('country' => 'LU', 'vatregno' => 'LU123ABG', 'expected' => FALSE),
			__LINE__ => array('country' => 'BE', 'vatregno' => 'BE1234567890', 'expected' => TRUE),
			__LINE__ => array('country' => 'BE', 'vatregno' => 'BE12345', 'expected' => FALSE),
			__LINE__ => array('country' => 'BE', 'vatregno' => 'BE12ABG', 'expected' => FALSE),
			__LINE__ => array('country' => 'NL', 'vatregno' => 'NL123ABG456z', 'expected' => TRUE),
			__LINE__ => array('country' => 'NL', 'vatregno' => 'NL123ABG', 'expected' => FALSE),
			__LINE__ => array('country' => 'NL', 'vatregno' => 'NL123456ASDFGH', 'expected' => FALSE),
			__LINE__ => array('country' => 'DK', 'vatregno' => 'DK12 34 56 78', 'expected' => TRUE),
			__LINE__ => array('country' => 'DK', 'vatregno' => 'DK12 34 56 78 90', 'expected' => False),
			__LINE__ => array('country' => 'DK', 'vatregno' => 'DK12345678', 'expected' => FALSE),
			__LINE__ => array('country' => 'DK', 'vatregno' => 'DKAB CD EF GH', 'expected' => FALSE),
			__LINE__ => array('country' => 'CZ', 'vatregno' => 'CZ12345678', 'expected' => TRUE),
			__LINE__ => array('country' => 'cz', 'vatregno' => 'CZ123456789', 'expected' => TRUE),
			__LINE__ => array('country' => 'CZ', 'vatregno' => 'CZ1234567890', 'expected' => TRUE),
			__LINE__ => array('country' => 'CZ', 'vatregno' => 'CZ12345', 'expected' => FALSE),
			__LINE__ => array('country' => 'CZ', 'vatregno' => 'CZ123456ASDFGH', 'expected' => FALSE),
			__LINE__ => array('country' => 'AT', 'vatregno' => 'ATU123ABG4z', 'expected' => TRUE),
			__LINE__ => array('country' => 'AT', 'vatregno' => 'ATU123ABG', 'expected' => FALSE),
			__LINE__ => array('country' => 'AT', 'vatregno' => 'ATU123456ASDFGH', 'expected' => FALSE),
		);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/srv/class.tx_mklib_tests_srv_Finance_testcase.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/srv/class.tx_mklib_tests_srv_Finance_testcase.php']);
}
