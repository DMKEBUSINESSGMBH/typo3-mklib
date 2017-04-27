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
		'mode'  => Tx_Mklib_Database_Connection::DELETION_MODE_HIDE
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
	public function testGetDatabaseConnection() {
		self::assertInstanceOf(
			'Tx_Mklib_Database_Connection',
			$this->callInaccessibleMethod(
				tx_rnbase::makeInstance('tx_mklib_scheduler_DeleteFromDatabase'),
				'getDatabaseConnection'
			),
			'falsche Klasse'
		);
	}

	/**
	 * @group unit
	 */
	public function testExecuteCallsDoSelectOnDatabaseUtilityCorrect() {
		$databaseConnection = $this->getDatabaseConnectionMock();
		$scheduler = $this->getSchedulerByDbUtil($databaseConnection);

		$databaseConnection->expects(self::once())
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
		$databaseConnection = $this->getDatabaseConnectionMock();
		$this->options['selectFields'] = 'otherFields';
		$scheduler = $this->getSchedulerByDbUtil($databaseConnection);

		$databaseConnection->expects(self::once())
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
		$databaseConnection = $this->getDatabaseConnectionMock(array('fullQuoteStr'));
		$databaseConnection->expects(self::once())
			->method('fullQuoteStr')
			->with('123')
			->will(self::returnValue('\'quoted123\''));

		$databaseConnection->expects(self::once())
			->method('delete')
			->with(
				$this->options['table'],
				'uid = \'quoted123\'',
				$this->options['mode']
			);

		$scheduler = $this->getSchedulerByDbUtil($databaseConnection);
		$row = array('uid' => 123);
		$scheduler->deleteRow($row);
	}

	/**
	 * @group unit
	 */
	public function testDeleteRowCallsDeleteOnDatabaseUtilityCorrectWhenSelectFieldsDifferentToUid() {
		$this->options['uidField'] = 'otherField';
		$databaseConnection = $this->getDatabaseConnectionMock(array('fullQuoteStr'));
		$databaseConnection->expects(self::once())
			->method('fullQuoteStr')
			->with('123')
			->will(self::returnValue('\'quoted123\''));
		$databaseConnection->expects(self::once())
			->method('delete')
			->with(
				$this->options['table'],
				$this->options['uidField'] . ' = \'quoted123\'',
				$this->options['mode']
			);

		$scheduler = $this->getSchedulerByDbUtil($databaseConnection);
		$row = array($this->options['uidField'] => 123);
		$scheduler->deleteRow($row);
	}

	/**
	 * @group unit
	 */
	public function testDeleteRowSetsAffectedRowsPropertyCorrect() {
		$databaseConnection = $this->getDatabaseConnectionMock(array('fullQuoteStr'));
		$databaseConnection->expects(self::exactly(2))
			->method('fullQuoteStr')
			->with(self::logicalOr('123', '456'))
			->will(self::returnArgument(0));
		$scheduler = $this->getSchedulerByDbUtil($databaseConnection);

		$scheduler->deleteRow(array('uid' => 123));
		$scheduler->deleteRow(array('uid' => 456));

		$affectedRows = new ReflectionProperty(
			'tx_mklib_scheduler_DeleteFromDatabase', 'affectedRows'
		);
		$affectedRows->setAccessible(TRUE);

		self::assertEquals(
			array(array('uid' => 123), array('uid' => 456)),
			$affectedRows->getValue($scheduler),
			'affectedRows falsch gesetzt'
		);
	}

	/**
	 * @group unit
	 */
	public function testExecuteTaskSetsDevLogCorrect() {
		$databaseConnection = $this->getDatabaseConnectionMock();
		$scheduler = $this->getSchedulerByDbUtil($databaseConnection);

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

		self::assertEquals(
			$expectedDevLog, $devLog, 'falsches devlog'
		);
	}

	/**
	 * @param string $databaseConnection
	 *
	 * @return tx_mklib_scheduler_DeleteFromDatabase
	 */
	private function getSchedulerByDbUtil($databaseConnection) {
		$scheduler = $this->getMock(
			'tx_mklib_scheduler_DeleteFromDatabase',
			array('getDatabaseConnection')
		);

		$scheduler->expects(self::any())
			->method('getDatabaseConnection')
			->will(self::returnValue($databaseConnection));

		$scheduler->setOptions($this->options);

		return $scheduler;
	}

	/**
	 * @return Tx_Mklib_Database_Connection
	 */
	private function getDatabaseConnectionMock(
		array $methods = array()
	) {
		return $this->getMock(
			'Tx_Mklib_Database_Connection',
			array_merge(
				$methods,
				array('doSelect', 'delete')
			)
		);
	}
}
