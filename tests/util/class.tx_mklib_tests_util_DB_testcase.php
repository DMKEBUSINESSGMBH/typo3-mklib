<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
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

/**
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_tests_Util');
tx_rnbase::load('tx_mklib_util_DB');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * DB util tests
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_DB_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage tx_mklib_util_DB::delete(): Unknown deletion mode (123)
	 *
	 * @group unit
	 */
	public function testDeleteWithUnknownModeThrowsException(){
		tx_mklib_util_DB::delete('', '', 123);
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage tx_mklib_util_DB::delete(): Cannot hide records in table unknown - no $TCA entry found!
	 *
	 * @group unit
	 */
	public function testDeleteWithModeHiddenThrowsExceptionIfNoDisableColumnInTca(){
		tx_mklib_util_DB::delete('unknown', '', tx_mklib_util_DB::DELETION_MODE_HIDE);
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage tx_mklib_util_DB::delete(): Cannot soft-delete records in table unknown - no $TCA entry found!
	 *
	 * @group unit
	 */
	public function testDeleteWithModeSoftDeleteThrowsExceptionIfNoDeleteColumnInTca(){
		tx_mklib_util_DB::delete('unknown', '', tx_mklib_util_DB::DELETION_MODE_SOFTDELETE);
	}

	/**
	 * @group unit
	 */
	public function testDeleteWithModeHiddenCallsDoUpdateCorrect(){
		$util = $this->getUtilMock();
		$util::staticExpects($this->never())
			->method('doDelete');

		$util::staticExpects($this->once())
			->method('doUpdate')
			->with('pages', 'someWhereClause', array('hidden' => 1));

		$util::delete('pages', 'someWhereClause', tx_mklib_util_DB::DELETION_MODE_HIDE);
	}

	/**
	 * @group unit
	 */
	public function testDeleteWithModeSoftDeleteCallsDoUpdateCorrect(){
		$util = $this->getUtilMock();
		$util::staticExpects($this->never())
			->method('doDelete');

		$util::staticExpects($this->once())
			->method('doUpdate')
			->with('pages', 'someWhereClause', array('deleted' => 1));

		$util::delete('pages', 'someWhereClause', tx_mklib_util_DB::DELETION_MODE_SOFTDELETE);
	}

	/**
	 * @group unit
	 */
	public function testDeleteWithModeHardDeleteCallsDoDeleteCorrect(){
		$util = $this->getUtilMock();
		$util::staticExpects($this->never())
			->method('doUpdate');

		$util::staticExpects($this->once())
			->method('doDelete')
			->with('pages', 'someWhereClause');

		$util::delete('pages', 'someWhereClause', tx_mklib_util_DB::DELETION_MODE_REALLYDELETE);
	}

	/**
	 * @return tx_mklib_util_DB
	 */
	private function getUtilMock() {
		return $this->getMockClass('tx_mklib_util_DB', array('doUpdate', 'doDelete'));
	}
}