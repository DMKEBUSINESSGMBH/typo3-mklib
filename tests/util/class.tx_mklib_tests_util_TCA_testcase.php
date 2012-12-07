<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
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
tx_rnbase::load('tx_mklib_util_TCA');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_TCA_testcase extends tx_phpunit_testcase {

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		//es kann sein dass die TCA von der wordlist nicht geladen wurde.
		//also stellen wir die TCA hier bereit
		tx_rnbase::load('tx_mklib_srv_Wordlist');
		global $TCA;
		$TCA['tx_mklib_wordlist'] = tx_mklib_srv_Wordlist::getTca();
	}
	
	/**
	 *
	 */
	public function testEleminateNonTcaColumns(){
		$model = tx_rnbase::makeInstance('tx_mklib_model_WordlistEntry', array());
		$data = array(
	  		'blacklisted' => true,
	  		'whitelisted' => 0,
	  		'ich-muss-raus' => true,
	  		'ich-auch' => false,
		);
		$res = tx_mklib_util_TCA::eleminateNonTcaColumns($model,$data);
		$this->assertEquals(2,count($res),'falsche array größe');
		$this->assertTrue($res['blacklisted'],'blacklsited Feld ist nicht korrekt!');
		$this->assertEquals(0,$res['whitelisted'],'whitelisted Feld ist nicht korrekt!');
		$this->assertTrue(empty($res['ich-muss-raus']),'ich-muss-raus Feld wurde nicht entfernt!');
		$this->assertTrue(empty($res['ich-auch']),'ich-auch Feld wurde nicht entfernt!');
	}
	/**
	 *
	 */
	public function testEleminateNonTcaColumnsByTable(){
		$data = array(
	  		'blacklisted' => true,
	  		'whitelisted' => 0,
	  		'ich-muss-raus' => true,
	  		'ich-auch' => false,
		);
		$res = tx_mklib_util_TCA::eleminateNonTcaColumnsByTable('tx_mklib_wordlist',$data);
		$this->assertEquals(2,count($res),'falsche array größe');
		$this->assertTrue($res['blacklisted'],'blacklsited Feld ist nicht korrekt!');
		$this->assertEquals(0,$res['whitelisted'],'whitelisted Feld ist nicht korrekt!');
		$this->assertFalse(isset($res['ich-muss-raus']),'ich-muss-raus Feld wurde nicht entfernt!');
		$this->assertFalse(isset($res['ich-auch']),'ich-auch Feld wurde nicht entfernt!');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_TCA_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_TCA_testcase.php']);
}