<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_srv
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
tx_rnbase::load('tx_mklib_util_ServiceRegistry');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_srv
 */
class tx_mklib_tests_srv_Finance_testcase extends tx_phpunit_testcase {
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
		
		$this->assertTrue(is_object($oCurrency));
		$this->assertEquals('tx_mklib_model_Currency', get_class($oCurrency));
	}
	
	public function testGetFormattedCurrency(){
		$oSrv = tx_mklib_util_ServiceRegistry::getFinanceService();
		
		$this->assertEquals('54.587,96 €', $oSrv->getFormattedCurrency('54587.957', false));
		$this->assertEquals('5,78 &euro;', $oSrv->getFormattedCurrency('5.7825', true));
		$this->assertEquals('6,00 €', $oSrv->getFormattedCurrency('6', false));
		$this->assertEquals('-3,60 &euro;', $oSrv->getFormattedCurrency('-3.6'));
	}
	/**
	 * Prüft ob richtig gerundet wird 
	 */
	public function testRoundDouble(){
		$srv = tx_mklib_util_ServiceRegistry::getFinanceService();
		$this->assertEquals(2.54,$this->oFinanceSrv->roundUpDouble(2.5316,2,false),'Die Zahl wurde nicht korrekt gerundet!');
		$this->assertEquals(2.54,$this->oFinanceSrv->roundUpDouble(2.5356,2,false),'Die Zahl wurde nicht korrekt gerundet!');
		$this->assertEquals(2.536,$this->oFinanceSrv->roundUpDouble(2.5356,3,false),'Die Zahl wurde nicht korrekt gerundet!');
		$this->assertEquals('2,20',$this->oFinanceSrv->roundUpDouble('2.2000',2, true, ','),'Die Zahl wurde nicht korrekt gerundet!');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/srv/class.tx_mklib_tests_srv_Finance_testcase.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/srv/class.tx_mklib_tests_srv_Finance_testcase.php']);
}