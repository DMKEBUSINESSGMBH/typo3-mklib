<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_mklib_util_FlexForm');

/**
 * testcase for the flexform util
 *
 * @package TYPO3
 * @subpackage tx_mklib
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 * 		  GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_tests_util_FlexForm_testcase
	extends tx_rnbase_tests_BaseTestCase {

	/**
	 * Testet die getByRefererCallsSearchCorrect Methode.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testFlexForm() {
		$util = tx_mklib_util_FlexForm::getInstance($this->getFlexFormFixture());

		$this->assertEquals(
			'tx_mklib_action_AbstractList',
			$util->get('action')
		);
		$this->assertContains(
			'notfound = 404 Not Found',
			$util->get('flexformTS', 's_tssetup')
		);
		$this->assertEquals(
			'tx_mklib_filter_SingleItem',
			$util->get('abstractlist.filter', 's_abstractlist')
		);
	}

	/**
	 * Liefert das Fixture XML
	 *
	 * @return string
	 */
	protected function getFlexFormFixture() {
		return <<<FF
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
	<data>
		<sheet index="sDEF">
			<language index="lDEF">
				<field index="action">
					<value index="vDEF">tx_mklib_action_AbstractList</value>
				</field>
			</language>
		</sheet>
		<sheet index="s_tssetup">
			<language index="lDEF">
				<field index="flexformTS">
					<value index="vDEF">abstractlist.notfound = 404 Not Found</value>
				</field>
			</language>
		</sheet>
		<sheet index="s_abstractlist">
			<language index="lDEF">
				<field index="abstractlist.filter">
					<value index="vDEF">tx_mklib_filter_SingleItem</value>
				</field>
			</language>
		</sheet>
	</data>
</T3FlexForms>
FF;
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_FlexForm_testcase.php']) {
	require_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_FlexForm_testcase.php'];
}
