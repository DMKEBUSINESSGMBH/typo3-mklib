<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_validator
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

/**
 * Testfälle für tx_mklib_validator_EasyKonto
 *
 * @author hbochmann
 * @package tx_mklib
 * @subpackage tx_mklib_tests_validator
 */
class tx_mklib_tests_validator_EasyKonto_testcase extends tx_phpunit_testcase {
	protected $easyKontoValidator;
  	/**
   	 * setUp() = init DB etc.
   	 */
  	public function setUp() {
  		$this->markTestSkipped('Tests können im Moment nicht ausgeführt werden, da der Demoaccount nicht mehr gültig ist! Bei Bedarf muss ein neuer Demoaccount erstellt werden!');
	    $this->easyKontoValidator = tx_rnbase::makeInstance('tx_mklib_validator_EasyKonto','dasMedienkombinat','mk2010',0,'www.easykonto.de/services/');
  	}
  	
  	/**
   	 * Prüft das checkBICAndAccountNumber() true zurück gibt
   	 */
  	public function testCheckBICAndAccountNumberReturnsTrue() {
  		$this->assertTrue($this->easyKontoValidator->checkBICAndAccountNumber(99999999,1),'Die Kombination wurde nicht als richtig erkannt!');
  	}
  	
/**
   	 * Prüft das checkBICAndAccountNumber() false zurück gibt
   	 */
  	public function testCheckBICAndAccountNumberReturnsFalse() {
  		$this->assertFalse($this->easyKontoValidator->checkBICAndAccountNumber(99999999,2),'Die Kombination wurde als richtig erkannt!');
  	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/validator/class.tx_mklib_tests_validator_EasyKonto_testcase.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/validator/class.tx_mklib_tests_validator_EasyKonto_testcase.php']);
}

?>