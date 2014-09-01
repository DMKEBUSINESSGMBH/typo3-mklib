<?php
/**
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2014 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
tx_rnbase::load('tx_mklib_scheduler_DeleteFromDatabase');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * @author Hannes Bochmann
 * @package TYPO3
 * @subpackage tx_mklib
 */
class tx_mklib_tests_scheduler_DeleteFromDatabase_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * @var array
	 */
	private $options = array(
		'table' => 'someTable',
		'where' => 'someWhereClause',
		'mode'  => tx_mklib_util_DB::DELETION_MODE_HIDE
	);

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		tx_mklib_tests_Util::disableDevlog();
	}

	/**
	 * @group unit
	 */
	public function testDbUtil() {
		$this->assertEquals(
			'tx_mklib_util_DB',
			$this->callInaccessibleMethod(
				tx_rnbase::makeInstance('tx_mklib_scheduler_DeleteFromDatabase'),
				'getDbUtil'
			),
			'falsche Klasse'
		);
	}

	/**
	 * @group unit
	 */
	public function testExecuteCallsDoSelectCorrect() {
		$dbUtil = $this->getDbUtil();
		$dbUtil::staticExpects($this->once())
			->method('doSelect')
			->with(
				'uid', $this->options['table'],
				array('where' => $this->options['where'], 'enablefieldsoff' => true)
			);

		$scheduler = $this->getSchedulerByDbUtil($dbUtil);
		$scheduler->execute();
	}

	/**
	 * @group unit
	 */
	public function testExecuteCallsDoSelectCorrectIfSelectFieldsConfigured() {
		$dbUtil = $this->getDbUtil();
		$this->options['selectFields'] = 'otherFields';
		$dbUtil::staticExpects($this->once())
			->method('doSelect')
			->with(
				$this->options['selectFields'], $this->options['table'],
				array('where' => $this->options['where'], 'enablefieldsoff' => true)
			);

		$scheduler = $this->getSchedulerByDbUtil($dbUtil);
		$scheduler->execute();
	}

	/**
	 * @group unit
	 */
	public function testExecuteCallsDeleteCorrect() {
		$dbUtil = $this->getDbUtil();
		$dbUtil::staticExpects($this->once())
			->method('delete')
			->with(
				$this->options['table'],
				$this->options['where'],
				$this->options['mode']
			);

		$scheduler = $this->getSchedulerByDbUtil($dbUtil);
		$scheduler->execute();
	}

	/**
	 * @group unit
	 */
	public function testExecuteTaskSetsDevLogCorrect() {
		$dbUtil = $this->getDbUtil();

		$dbUtil::staticExpects($this->once())
			->method('doSelect')
			->will($this->returnValue(array('uid' => 1, 'uid' => 2)));

		$dbUtil::staticExpects($this->once())
			->method('delete')
			->will($this->returnValue(2));

		$scheduler = $this->getSchedulerByDbUtil($dbUtil);
		$devLog = array();
		$method = new ReflectionMethod('tx_mklib_scheduler_DeleteFromDatabase', 'executeTask');
		$method->setAccessible(true);
		$method->invokeArgs($scheduler, array($this->options, &$devLog));

		$expectedDevLog = array(
			tx_rnbase_util_Logger::LOGLEVEL_INFO => array(
				'message' => 	'2 Datensätze wurden in ' .
								'someTable mit der Bedingung ' .
								'someWhereClause und dem Modus 0 entfernt',
				'dataVar' => 	array('betroffene Datensätze' => array('uid' => 1, 'uid' => 2))
			)
		);
		$this->assertEquals(
			$expectedDevLog, $devLog, 'falsches devlog'
		);
	}

	/**
	 * @param string $dbUtil
	 *
	 * @return Tx_Mkdifu_Scheduler_MoveNewsletterRecipientsFromFeUsersToTtAddress
	 */
	private function getSchedulerByDbUtil($dbUtil) {
		$scheduler = $this->getMock(
			'tx_mklib_scheduler_DeleteFromDatabase',
			array('getDbUtil')
		);

		$scheduler->expects($this->any())
			->method('getDbUtil')
			->will($this->returnValue($dbUtil));

		$scheduler->setOptions($this->options);

		return $scheduler;
	}

	/**
	 * @param string $dbUtil
	 *
	 * @return tx_mklib_util_DB
	 */
	private function getDbUtil() {
		return $this->getMockClass(
			'tx_mklib_util_DB',
			array('doSelect', 'delete')
		);
	}
}