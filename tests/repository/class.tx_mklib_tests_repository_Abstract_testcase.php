<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@das-medienkombinat.de>
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
require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
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
			$GLOBALS['TCA']['tx_mklib_wordlist'] = tx_mklib_srv_Wordlist::getTca();
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

		$this->assertEquals($expectedOptions, $options, 'options falsch');
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

		$this->assertInstanceOf(
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

		$this->assertEquals(
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

		$this->assertNull(
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

		$this->assertEquals(
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

		$searcher->expects($this->once())
			->method('search')
			->with($fields, $options)
			->will($this->returnValue(array('searched')));

		$repository->expects($this->any())
			->method('getSearcher')
			->will($this->returnValue($searcher));

		$this->assertEquals(
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
		$master->expects($this->any())
			->method('getTableName')
			->will($this->returnValue('tt_content'));

		$overlay = $this->getMock(
			'tx_rnbase_model_base',
			array('getTableName'),
			array(array('uid' => 456, 'l18n_parent' => 123, 'sys_language_uid' => 789))
		);
		$overlay->expects($this->any())
			->method('getTableName')
			->will($this->returnValue('tt_content'));

		$items = $this->callInaccessibleMethod($repository, 'uniqueItems', array($master, $overlay), array('distinct' => TRUE));

		$this->assertCount(1, $items);
		$this->assertArrayHasKey(0, $items);
		$this->assertEquals($overlay, $items[0]);
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
		$master->expects($this->any())
			->method('getTableName')
			->will($this->returnValue('tt_content'));

		$overlay = $this->getMock(
			'tx_rnbase_model_base',
			array('getTableName'),
			array(array('uid' => 456, 'l18n_parent' => 123, 'sys_language_uid' => 789))
		);
		$overlay->expects($this->any())
			->method('getTableName')
			->will($this->returnValue('tt_content'));

		$items = $this->callInaccessibleMethod($repository, 'uniqueItems', array($master, $overlay), array());

		$this->assertCount(2, $items);
		$this->assertArrayHasKey(0, $items);
		$this->assertEquals($master, $items[0]);
		$this->assertArrayHasKey(1, $items);
		$this->assertEquals($overlay, $items[1]);
	}

	/**
	 * @group unit
	 */
	public function testFindAll() {
		$repository = $this->getRepositoryMock(array('search'));

		$repository->expects($this->once())
			->method('search')
			->with(array(), array())
			->will($this->returnValue(array('searched')));

		$this->assertEquals(
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

		$repository->expects($this->once())
			->method('create')
			->with($data)
			->will($this->returnValue(array('created')));

		$this->assertEquals(
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
		$this->assertEquals($expectedReturnArray, $returnArray, 'Daten falsch');
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

		$model->expects($this->once())
			->method('getTagsToBeIgnoredFromStripping')
			->will($this->returnValue('<b>'));

		$model->expects($this->once())
			->method('getFieldsToBeStripped')
			->will($this->returnValue(array('field1', 'field2')));


		$method = new ReflectionMethod(
			'tx_mklib_repository_Abstract', 'secureFromCrossSiteScripting'
		);
		$method->setAccessible(true);

		$returnArray = $method->invoke($repository, $model, $data);
		$expectedReturnArray = array('field1' => 'value1', 'field2' => '<b>value2</b>','field3' => 'value3');
		$this->assertEquals($expectedReturnArray, $returnArray, 'Daten falsch');
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

		$repository->expects($this->any())
			->method('getSearchClass')
			->will($this->returnValue('tx_mklib_search_Wordlist'));

		return $repository;
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
}