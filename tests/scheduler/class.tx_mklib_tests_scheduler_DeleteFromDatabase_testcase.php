<?php
/**
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2014 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
	public function testGetDatabaseUtility() {
		$this->assertEquals(
			'tx_mklib_util_DB',
			$this->callInaccessibleMethod(
				tx_rnbase::makeInstance('tx_mklib_scheduler_DeleteFromDatabase'),
				'getDatabaseUtility'
			),
			'falsche Klasse'
		);
	}

	/**
	 * @group unit
	 */
	public function testExecuteCallsDoSelectOnDatabaseUtilityCorrect() {
		$databaseUtility = $this->getDatabaseUtility();
		$scheduler = $this->getSchedulerByDbUtil($databaseUtility);

		$databaseUtility::staticExpects($this->once())
			->method('doSelect')
			->with(
				'uid', $this->options['table'],
				array(
					'where' => $this->options['where'], 'enablefieldsoff' => true,
					'callback'	=> array($scheduler, 'deleteRow')
				)
			);

		$scheduler->execute();
	}

	/**
	 * @group unit
	 */
	public function testExecuteCallsDoSelectOnDatabaseUtilityCorrectIfSelectFieldsConfigured() {
		$databaseUtility = $this->getDatabaseUtility();
		$this->options['selectFields'] = 'otherFields';
		$scheduler = $this->getSchedulerByDbUtil($databaseUtility);

		$databaseUtility::staticExpects($this->once())
			->method('doSelect')
			->with(
				$this->options['selectFields'] . ',uid', $this->options['table'],
				array(
					'where' => $this->options['where'], 'enablefieldsoff' => true,
					'callback'	=> array($scheduler, 'deleteRow')
				)
			);

		$scheduler->execute();
	}

	/**
	 * @group unit
	 */
	public function testDeleteRowCallsDeleteOnDatabaseUtilityCorrect() {
		$databaseUtility = $this->getDatabaseUtility();
		$databaseUtility::staticExpects($this->once())
			->method('delete')
			->with(
				$this->options['table'],
				'uid = \'123\'',
				$this->options['mode']
			);

		$scheduler = $this->getSchedulerByDbUtil($databaseUtility);
		$row = array('uid' => 123);
		$scheduler->deleteRow($row);
	}

	/**
	 * @group unit
	 */
	public function testDeleteRowCallsDeleteOnDatabaseUtilityCorrectWhenSelectFieldsDifferentToUid() {
		$this->options['uidField'] = 'otherField';
		$databaseUtility = $this->getDatabaseUtility();
		$databaseUtility::staticExpects($this->once())
			->method('delete')
			->with(
				$this->options['table'],
				$this->options['uidField'] . ' = \'123\'',
				$this->options['mode']
			);

		$scheduler = $this->getSchedulerByDbUtil($databaseUtility);
		$row = array($this->options['uidField'] => 123);
		$scheduler->deleteRow($row);
	}

	/**
	 * @group unit
	 */
	public function testDeleteRowSetsAffectedRowsPropertyCorrect() {
		$databaseUtility = $this->getDatabaseUtility();
		$scheduler = $this->getSchedulerByDbUtil($databaseUtility);

		$scheduler->deleteRow(array('uid' => 123));
		$scheduler->deleteRow(array('uid' => 456));

		$affectedRows = new ReflectionProperty(
			'tx_mklib_scheduler_DeleteFromDatabase', 'affectedRows'
		);
		$affectedRows->setAccessible(TRUE);

		$this->assertEquals(
			array(array('uid' => 123), array('uid' => 456)),
			$affectedRows->getValue($scheduler),
			'affectedRows falsch gesetzt'
		);
	}

	/**
	 * @group unit
	 */
	public function testExecuteTaskSetsDevLogCorrect() {
		$databaseUtility = $this->getDatabaseUtility();
		$scheduler = $this->getSchedulerByDbUtil($databaseUtility);

		$affectedRows = new ReflectionProperty(
			'tx_mklib_scheduler_DeleteFromDatabase', 'affectedRows'
		);
		$affectedRows->setAccessible(TRUE);
		$affectedRows->setValue($scheduler, array(array('uid' => 1), array('uid' => 2)));

		$devLog = array();
		$method = new ReflectionMethod('tx_mklib_scheduler_DeleteFromDatabase', 'executeTask');
		$method->setAccessible(true);
		$method->invokeArgs($scheduler, array($this->options, &$devLog));

		$expectedDevLog = array(
			tx_rnbase_util_Logger::LOGLEVEL_INFO => array(
				'message' => 	'2 Datensätze wurden in ' .
								'someTable mit der Bedingung ' .
								'someWhereClause und dem Modus 0 entfernt',
				'dataVar' => 	array(
					'betroffene Datensätze' => array(array('uid' => 1), array('uid' => 2))
				)
			)
		);

		$this->assertEquals(
			$expectedDevLog, $devLog, 'falsches devlog'
		);
	}

	/**
	 * @param string $databaseUtility
	 *
	 * @return tx_mklib_scheduler_DeleteFromDatabase
	 */
	private function getSchedulerByDbUtil($databaseUtility) {
		$scheduler = $this->getMock(
			'tx_mklib_scheduler_DeleteFromDatabase',
			array('getDatabaseUtility')
		);

		$scheduler->expects($this->any())
			->method('getDatabaseUtility')
			->will($this->returnValue($databaseUtility));

		$scheduler->setOptions($this->options);

		return $scheduler;
	}

	/**
	 * @return tx_mklib_util_DB
	 */
	private function getDatabaseUtility() {
		return $this->getMockClass(
			'tx_mklib_util_DB',
			array('doSelect', 'delete')
		);
	}
}