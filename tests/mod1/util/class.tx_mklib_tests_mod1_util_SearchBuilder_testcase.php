<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_mod1_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 DMK E-BUSINESS GmbH  <dev@dmk-ebusiness.de>
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

require_once(tx_rnbase_util_Extensions::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_mklib_mod1_util_SearchBuilder');

/**
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests_mod1_util
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_tests_mod1_util_SearchBuilder_testcase extends tx_phpunit_testcase {

	/**
	 * @dataProvider providerMakeWildcardTerm
	 */
	public function testMakeWildcardTerm($term, $field, $leadingWC, $expected) {
		$result = tx_mklib_mod1_util_SearchBuilder::makeWildcardTerm($term, $field, $leadingWC);
		$this->assertEquals($expected, $result);
	}

	public function providerMakeWildcardTerm() {
		return array(
			'ds 01' => array('test', '', false, '+"test"*'),
			'ds 02' => array('test', '', true, '+*"test"*'),
			'ds 03' => array('test', 'myfield', false, '+myfield:"test"*'),
			'ds 04' => array('Test', 'myfield', false, '+myfield:"test"*'),
			'ds 05' => array('test', 'myfield', true, '+myfield:*"test"*'),
			'ds 06' => array('Bad Ar', 'fieldname', false, '+fieldname:"bad"* +fieldname:"ar"*'),
			'ds 07' => array('Bad Ar', 'fieldname', true, '+fieldname:*"bad"* +fieldname:*"ar"*'),
			'ds 08' => array('Dussmann AG & Co.KGaA', 'sfr_companyname', true, '+sfr_companyname:*"dussmann"* +sfr_companyname:*"ag"* +sfr_companyname:*"co"* +sfr_companyname:*"kgaa"*'),
			'ds 09' => array('\'test\'+-&|!(){}\[]^"~+*?:', 'sfr_companyname', true, '+sfr_companyname:*"test"*'),
			'ds 10' => array('c # sharp', 'testfield', false, '+testfield:"c"* +testfield:"sharp"*'),
		);
	}

	public function testBuildFreeTextWithSearchWordAndNoCols() {
		$fields = array();
		$result = tx_mklib_mod1_util_SearchBuilder::buildFreeText($fields, 'test');

		$this->assertTrue($result,'es wurde trotz Suchbegriff nicht true zurück gegeben.');
		$this->assertEquals('test', $fields['JOINED'][0]['value'], 'fields[JOINED][0][value] ist nicht korrekt');
		$this->assertEmpty($fields['JOINED'][0]['cols'], 'fields[JOINED][0][cols] ist nicht korrekt');
		$this->assertEquals('LIKE', $fields['JOINED'][0]['operator'], 'fields[JOINED][0][operator] ist nicht korrekt');
	}

	public function testBuildFreeTextWithSearchWordAndCols() {
		$fields = array();
		$result = tx_mklib_mod1_util_SearchBuilder::buildFreeText($fields, 'test', array('TEST1.col1','TEST1.col2','TEST2.col1'));

		$this->assertTrue($result,'es wurde trotz Suchbegriff nicht true zurück gegeben.');
		$this->assertEquals('test', $fields['JOINED'][0]['value'], 'fields[JOINED][0][value] ist nicht korrekt');
		$aExpectedCols = array('TEST1.col1','TEST1.col2','TEST2.col1');
		$this->assertEquals($aExpectedCols,$fields['JOINED'][0]['cols'], 'fields[JOINED][0][cols] ist nicht korrekt');
		$this->assertEquals('LIKE', $fields['JOINED'][0]['operator'], 'fields[JOINED][0][operator] ist nicht korrekt');
	}

	public function testBuildFreeTextWithoutSearchWord() {
		$fields = array();
		$result = tx_mklib_mod1_util_SearchBuilder::buildFreeText($fields, '');

		$this->assertFalse($result,'es wurde trotz Suchbegriff nicht true zurück gegeben.');
		$this->assertEmpty($fields, 'fields ist nicht korrekt');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']);
}