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

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_mklib_repository_Abstract');
tx_rnbase::load('tx_mklib_model_WordlistEntry');
tx_rnbase::load('tx_mklib_search_Wordlist');

/**
 * @package tx_mklib
 * @subpackage tx_mklib_tests
 */
class tx_mklib_tests_repository_Abstract_testcase
	extends tx_rnbase_tests_BaseTestCase {

	protected function setUp() {
		if (empty($GLOBALS['TCA']['tx_mklib_wordlist'])) {
			tx_rnbase::load('tx_mklib_srv_Wordlist');
			tx_mklib_srv_Wordlist::loadTca();
			$GLOBALS['TCA']['tx_mklib_wordlist']['test'] = TRUE;
		}
	}
	protected function tearDown() {
		if (!empty($GLOBALS['TCA']['tx_mklib_wordlist']['test'])) {
			unset($GLOBALS['TCA']['tx_mklib_wordlist']);
		}
	}

	/**
	 * @group unit
	 * @dataProvider getOptions
	 */
	public function testHandleEnableFieldsOptions(
		$options, $expectedOptions
	) {
		$repository = $this->getRepositoryMock();

		$method = new ReflectionMethod('tx_mklib_repository_Abstract', 'handleEnableFieldsOptions');
		$method->setAccessible(true);

		$fields = array();
		$method->invokeArgs($repository, array(&$fields, &$options));

		self::assertEquals($expectedOptions, $options, 'options falsch');
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return array(
			array(array('enablefieldsoff' => true), array('enablefieldsoff' => true)),
			array(array('enablefieldsbe' => true), array('enablefieldsbe' => true)),
			array(array('enablefieldsfe' => true), array('enablefieldsfe' => true)),
			array(array(), array('enablefieldsbe' => true))
		);
	}

	/**
	 * @group unit
	 */
	public function testGetSearcher() {
		$repository = $this->getRepositoryMock();

		$method = new ReflectionMethod('tx_mklib_repository_Abstract', 'getSearcher');
		$method->setAccessible(true);

		self::assertInstanceOf(
				'tx_mklib_search_Wordlist',
				$method->invoke($repository),
				'falsche wrapper Klasse'
		);
	}

	/**
	 * @group unit
	 */
	public function testFindByUidReturnsModelIfModelValid() {
		$repository = $this->getRepositoryMock();

		$expectedModel = tx_rnbase::makeInstance('tx_mklib_model_WordlistEntry', array('uid' => 123));

		self::assertEquals(
			$expectedModel,
			$repository->findByUid(array('uid' => 123)),
			'model nicht zurück gegeben'
		);
	}

	/**
	 * @group unit
	 */
	public function testFindByUidReturnsNullIfModelInvalid() {
		$repository = $this->getRepositoryMock();

		self::assertNull(
			$repository->findByUid(0),
			'NULL nicht zurück gegeben'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetWrapperClass() {
		$repository = $this->getRepositoryMock();

		$method = new ReflectionMethod('tx_mklib_repository_Abstract', 'getWrapperClass');
		$method->setAccessible(true);

		$fields = array();

		self::assertEquals(
			'tx_mklib_model_WordlistEntry',
			$method->invoke($repository),
			'falsche wrapper Klasse'
		);
	}

	/**
	 * @group unit
	 */
	public function testSearchCallsSearcherCorrect() {
		$repository = $this->getRepositoryMock(array('getSearcher'));

		$fields = array('someField' => 1);
		$options = array('enablefieldsbe' => 1);

		$searcher = $this->getMock(
			'tx_mklib_search_Wordlist',
			array('search')
		);

		$searcher->expects(self::once())
			->method('search')
			->with($fields, $options)
			->will(self::returnValue(array('searched')));

		$repository->expects(self::any())
			->method('getSearcher')
			->will(self::returnValue($searcher));

		self::assertEquals(
			array('searched'),
			$repository->search($fields, $options),
			'falsch gesucht'
		);
	}

	/**
	 * @group unit
	 */
	public function testUniqueItemsReducesCorrect() {
		$repository = $this->getRepositoryMock();
		$master = $this->getMock(
			'tx_rnbase_model_base',
			array('getTableName'),
			array(array('uid' => 123))
		);
		$master->expects(self::any())
			->method('getTableName')
			->will(self::returnValue('tt_content'));

		$overlay = $this->getMock(
			'tx_rnbase_model_base',
			array('getTableName'),
			array(array('uid' => 456, 'l18n_parent' => 123, 'sys_language_uid' => 789))
		);
		$overlay->expects(self::any())
			->method('getTableName')
			->will(self::returnValue('tt_content'));

		$items = $this->callInaccessibleMethod($repository, 'uniqueItems', array($master, $overlay), array('distinct' => TRUE));

		self::assertCount(1, $items);
		self::assertArrayHasKey(0, $items);
		self::assertEquals($overlay, $items[0]);
	}

	/**
	 * @group unit
	 */
	public function testUniqueItemsDoesNotReduceCorrect() {
		$repository = $this->getRepositoryMock();
		$master = $this->getMock(
			'tx_rnbase_model_base',
			array('getTableName'),
			array(array('uid' => 123))
		);
		$master->expects(self::any())
			->method('getTableName')
			->will(self::returnValue('tt_content'));

		$overlay = $this->getMock(
			'tx_rnbase_model_base',
			array('getTableName'),
			array(array('uid' => 456, 'l18n_parent' => 123, 'sys_language_uid' => 789))
		);
		$overlay->expects(self::any())
			->method('getTableName')
			->will(self::returnValue('tt_content'));

		$items = $this->callInaccessibleMethod($repository, 'uniqueItems', array($master, $overlay), array());

		self::assertCount(2, $items);
		self::assertArrayHasKey(0, $items);
		self::assertEquals($master, $items[0]);
		self::assertArrayHasKey(1, $items);
		self::assertEquals($overlay, $items[1]);
	}

	/**
	 * @group unit
	 */
	public function testFindAll() {
		$repository = $this->getRepositoryMock(array('search'));

		$repository->expects(self::once())
			->method('search')
			->with(array(), array())
			->will(self::returnValue(array('searched')));

		self::assertEquals(
			array('searched'),
			$repository->findAll(),
			'falsch gesucht'
		);
	}

	/**
	 * @group unit
	 */
	public function testHandleCreation() {
		$repository = $this->getRepositoryMock(array('create'));

		$data = array('field' => 'value');

		$repository->expects(self::once())
			->method('create')
			->with($data)
			->will(self::returnValue(array('created')));

		self::assertEquals(
			array('created'),
			$repository->create($data),
			'not created'
		);
	}

	/**
	 * @group unit
	 */
	public function testSecureFromCrossSiteScriptingReturnsDataIfNoFieldsToBeStrippedAreDefined() {
		$model = $this->getModelMock(array(), array('secureFromCrossSiteScripting'));
		$repository = $this->getRepositoryMock();

		$data = array(
			'field1' => 'value1', 'field2' => 'value2','field3' => 'value3'
		);

		$method = new ReflectionMethod(
			'tx_mklib_repository_Abstract', 'secureFromCrossSiteScripting'
		);
		$method->setAccessible(true);

		$returnArray = $method->invoke($repository, $model, $data);
		$expectedReturnArray = $data;
		self::assertEquals($expectedReturnArray, $returnArray, 'Daten falsch');
	}

	/**
	 * @group unit
	 */
	public function testSecureFromCrossSiteScriptingReturnsStrippedData() {
		$model = $this->getModelMock(array(),
			array('getTagsToBeIgnoredFromStripping', 'getFieldsToBeStripped')
		);
		$repository = $this->getRepositoryMock();

		$data = array(
			'field1' => '<p>value1</p>', 'field2' => '<b>value2</b>','field3' => 'value3'
		);

		$model->expects(self::once())
			->method('getTagsToBeIgnoredFromStripping')
			->will(self::returnValue('<b>'));

		$model->expects(self::once())
			->method('getFieldsToBeStripped')
			->will(self::returnValue(array('field1', 'field2')));


		$method = new ReflectionMethod(
			'tx_mklib_repository_Abstract', 'secureFromCrossSiteScripting'
		);
		$method->setAccessible(true);

		$returnArray = $method->invoke($repository, $model, $data);
		$expectedReturnArray = array('field1' => 'value1', 'field2' => '<b>value2</b>','field3' => 'value3');
		self::assertEquals($expectedReturnArray, $returnArray, 'Daten falsch');
	}

	/**
	 * @group unit
	 */
	public function testGetDatabaseUtility() {
		self::assertEquals(
			'tx_mklib_util_DB',
			$this->callInaccessibleMethod($this->getRepositoryMock(), 'getDatabaseUtility'),
			'falscher Klassenname'
		);
	}

	/**
	 * @group unit
	 */
	public function testHandleUpdateBuildsWhereClauseWhenNoneGiven() {
		$model = $this->getModelMock(
			array('uid' => 123),
			array('getColumnNames', 'getTableName', 'reset')
		);
		$model->expects(self::once())
			->method('getColumnNames')
			->will(self::returnValue(array('column_1')));
		$model->expects(self::any())
			->method('getTableName')
			->will(self::returnValue('unknown'));
		$model->expects(self::once())
			->method('reset');

		$repository = $this->getRepositoryMock(
			array('getSearchClass', 'getDatabaseUtility')
		);

		$databaseUtility = $this->getDatabaseUtilityMock(array('doUpdate'));
		$databaseUtility::staticExpects(self::once())
			->method('doUpdate')
			->with('unknown', '1=1 AND `unknown`.`uid`=\'123\'');

		$repository->expects(self::once())
			->method('getDatabaseUtility')
			->will(self::returnValue($databaseUtility));

		$repository->handleUpdate($model, array());
	}

	/**
	 * @group unit
	 */
	public function testHandleUpdateEliminatesNonTcaColumns() {
		$model = $this->getModelMock(
			array('uid' => 123),
			array('getColumnNames', 'getTableName', 'reset')
		);
		$model->expects(self::once())
			->method('getColumnNames')
			->will(self::returnValue(array('column_1')));
		$model->expects(self::any())
			->method('getTableName')
			->will(self::returnValue('unknown'));
		$model->expects(self::once())
			->method('reset');

		$repository = $this->getRepositoryMock(
			array('getSearchClass', 'getDatabaseUtility')
		);

		$data = array('column_1' => 'new value', 'column_2' => 'new value');
		$databaseUtility = $this->getDatabaseUtilityMock(array('doUpdate'));
		$databaseUtility::staticExpects(self::once())
			->method('doUpdate')
			->with(
				'unknown', '1=1 AND `unknown`.`uid`=\'123\'',
				array('column_1' => 'new value')
			);

		$repository->expects(self::once())
			->method('getDatabaseUtility')
			->will(self::returnValue($databaseUtility));

		$repository->handleUpdate($model, $data);
	}

	/**
	 * @group unit
	 */
	public function testHandleUpdateCallsSecureFromCrossSiteScripting() {
		$model = $this->getModelMock(
			array('uid' => 123),
			array('getColumnNames', 'getTableName', 'reset')
		);
		$model->expects(self::once())
			->method('getColumnNames')
			->will(self::returnValue(array('column_1')));
		$model->expects(self::any())
			->method('getTableName')
			->will(self::returnValue('unknown'));
		$model->expects(self::once())
			->method('reset');

		$repository = $this->getRepositoryMock(
			array('getSearchClass', 'getDatabaseUtility', 'secureFromCrossSiteScripting')
		);

		$data = array('column_1' => 'new value');
		$databaseUtility = $this->getDatabaseUtilityMock(array('doUpdate'));
		$databaseUtility::staticExpects(self::once())
			->method('doUpdate')
			->with('unknown', '1=1 AND `unknown`.`uid`=\'123\'', array('secured'));

		$repository->expects(self::once())
			->method('getDatabaseUtility')
			->will(self::returnValue($databaseUtility));

		$repository->expects(self::once())
			->method('secureFromCrossSiteScripting')
			->with($model, $data)
			->will(self::returnValue(array('secured')));

		$repository->handleUpdate($model, $data);
	}

	/**
	 * @group unit
	 */
	public function testHandleUpdateRemovesUidColumn() {
		$model = $this->getModelMock(
			array('uid' => 123),
			array('getColumnNames', 'getTableName', 'reset')
		);
		$model->expects(self::once())
			->method('getColumnNames')
			->will(self::returnValue(array('column_1')));
		$model->expects(self::any())
			->method('getTableName')
			->will(self::returnValue('unknown'));
		$model->expects(self::once())
			->method('reset');

		$repository = $this->getRepositoryMock(
			array('getSearchClass', 'getDatabaseUtility')
		);

		$data = array('column_1' => 'new value', 'uid' => 456);
		$databaseUtility = $this->getDatabaseUtilityMock(array('doUpdate'));
		$databaseUtility::staticExpects(self::once())
			->method('doUpdate')
			->with(
				'unknown',
				'1=1 AND `unknown`.`uid`=\'123\'',
				array('column_1' => 'new value')
			);

		$repository->expects(self::once())
			->method('getDatabaseUtility')
			->will(self::returnValue($databaseUtility));

		$repository->handleUpdate($model, $data);
	}

	/**
	 * @group unit
	 */
	public function testHandleUpdateUsesGivenWhere() {
		$model = $this->getModelMock(
			array('uid' => 123),
			array('getColumnNames', 'getTableName', 'reset')
		);
		$model->expects(self::once())
			->method('getColumnNames')
			->will(self::returnValue(array('column_1')));
		$model->expects(self::any())
			->method('getTableName')
			->will(self::returnValue('unknown'));
		$model->expects(self::once())
			->method('reset');

		$repository = $this->getRepositoryMock(
			array('getSearchClass', 'getDatabaseUtility')
		);

		$databaseUtility = $this->getDatabaseUtilityMock(array('doUpdate'));
		$databaseUtility::staticExpects(self::once())
			->method('doUpdate')
			->with('unknown', 'test where');

		$repository->expects(self::once())
			->method('getDatabaseUtility')
			->will(self::returnValue($databaseUtility));

		$repository->handleUpdate($model, array(), 'test where');
	}

	/**
	 * @group unit
	 */
	public function testHandleUpdateWhenDebugAndNoQuoteFieldsParametersGiven() {
		$model = $this->getModelMock(
			array('uid' => 123),
			array('getColumnNames', 'getTableName', 'reset')
		);
		$model->expects(self::once())
			->method('getColumnNames')
			->will(self::returnValue(array('column_1')));
		$model->expects(self::any())
			->method('getTableName')
			->will(self::returnValue('unknown'));
		$model->expects(self::once())
			->method('reset');

		$repository = $this->getRepositoryMock(
			array('getSearchClass', 'getDatabaseUtility')
		);

		$databaseUtility = $this->getDatabaseUtilityMock(array('doUpdate'));
		$databaseUtility::staticExpects(self::once())
			->method('doUpdate')
			->with('unknown', 'test where', array(), 987, 'commaSeparatedFields');

		$repository->expects(self::once())
			->method('getDatabaseUtility')
			->will(self::returnValue($databaseUtility));

		$repository->handleUpdate(
			$model, array(), 'test where', 987, 'commaSeparatedFields'
		);
	}

	/**
	 * @param array $mockedMethods
	 * @return tx_mklib_repository_Abstract
	 */
	private function getRepositoryMock($mockedMethods = array('getSearchClass')) {
		$repository = $this->getMockForAbstractClass(
			'tx_mklib_repository_Abstract',
			array(),
			'',
			FALSE,
			FALSE,
			FALSE,
			$mockedMethods
		);

		$repository->expects(self::any())
			->method('getSearchClass')
			->will(self::returnValue('tx_mklib_search_Wordlist'));

		return $repository;
	}

	/**
	 * @param array $mockedMethods
	 * @return tx_mklib_util_DB
	 */
	private function getDatabaseUtilityMock(array $mockedMethods) {
		return $this->getMockClass('tx_mklib_util_DB', $mockedMethods);
	}

	/**
	 * @param array $mockedMethods
	 * @return tx_mklib_repository_Abstract
	 */
	private function getModelMock($rowOrUid = array(), $mockedMethods = array()) {
		$model = $this->getMock(
			'tx_rnbase_model_base',
			$mockedMethods,
			array($rowOrUid)
		);

		return $model;
	}

	/**
	 * @group unit
	 */
	public function testSearchSingleIfItemsFound() {
		$repository = $this->getRepositoryMock(
			array('search')
		);

		$expectedFields = array('fields');
		$expectedOptions = array('orderby' => array(), 'limit' => 1);

		$repository->expects(self::once())
			->method('search')
			->with($expectedFields, $expectedOptions)
			->will(self::returnValue(array(0 => 'test')));

		self::assertEquals(
			'test',
			$repository->searchSingle($expectedFields, array('orderby' => array()))
		);
	}

	/**
	 * @group unit
	 */
	public function testSearchSingleIfNoItemsFound() {
		$repository = $this->getRepositoryMock(
			array('search')
		);

		$expectedFields = array('fields');
		$expectedOptions = array('orderby' => array(), 'limit' => 1);

		$repository->expects(self::once())
			->method('search')
			->with($expectedFields, $expectedOptions)
			->will(self::returnValue(array()));

		self::assertNull(
			$repository->searchSingle($expectedFields, array('orderby' => array()))
		);
	}
}