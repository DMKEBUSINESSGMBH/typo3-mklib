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
tx_rnbase::load('tx_mklib_repository_Abstract');
tx_rnbase::load('tx_mklib_model_WordlistEntry');
tx_rnbase::load('tx_mklib_search_Wordlist');

/**
 * @package tx_mklib
 * @subpackage tx_mklib_tests
 */
class tx_mklib_tests_repository_Abstract_testcase extends Tx_Phpunit_TestCase {

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
		$options = array('enablefieldsfe' => 1);

		$searcher = $this->getMock(
			'tx_mklib_search_Wordlist',
			array('search')
		);

		$searcher->expects($this->once())
			->method('search')
			->with($fields, $options)
			->will($this->returnValue('searched'));

		$repository->expects($this->any())
			->method('getSearcher')
			->will($this->returnValue($searcher));

		$this->assertEquals(
			'searched',
			$repository->search($fields, $options),
			'falsch gesucht'
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
			false,
			false,
			false,
			$mockedMethods
		);

		$repository->expects($this->any())
			->method('getSearchClass')
			->will($this->returnValue('tx_mklib_search_Wordlist'));

		return $repository;
	}
}