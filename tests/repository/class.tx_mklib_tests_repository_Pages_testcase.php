<?php
/**
 * Copyright notice
 *
 * (c) 2015 DMK E-Business GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_rnbase_util_SearchGeneric');
tx_rnbase::load('tx_mklib_repository_Pages');
tx_rnbase::load('tx_rnbase_model_base');

/**
 * Tests for tx_mklib_repository_Pages.
 *
 * @package TYPO3
 * @subpackage tx_mklib
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_tests_repository_Pages_testcase
	extends tx_rnbase_tests_BaseTestCase {


	/**
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getRepository() {

		$searcher = $this->getMock(
			'tx_rnbase_util_SearchGeneric'
		);
		$repo = $this->getMock(
			'tx_mklib_repository_Pages',
			array('getSearcher')
		);

		$repo
			->expects($this->any())
			->method('getSearcher')
			->will($this->returnValue($searcher))
		;

		return $repo;
	}

	/**
	 * Test the getSearchClass method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testGetSearchClassShouldBeGeneric() {
		$this->assertEquals(
			'tx_rnbase_util_SearchGeneric',
			$this->callInaccessibleMethod(
				$this->getRepository(),
				'getSearchClass'
			)
		);
	}

	/**
	 * Test the getEmptyModel method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testGetEmptyModelShouldBeBaseModelWithPageTable() {
		$model = $this->callInaccessibleMethod(
			$this->getRepository(),
			'getEmptyModel'
		);
		$this->assertInstanceOf(
			'tx_rnbase_model_base',
			$model
		);
		$this->assertEquals(
			'pages',
			$model->getTablename()
		);
	}

	/**
	 * Test the getSearchdef method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testGetSearchdef() {
		$searchdef = $this->callInaccessibleMethod(
			$this->getRepository(),
			'getSearchdef'
		);
		$this->assertArrayHasKey('basetable', $searchdef);
		$this->assertEquals('pages', $searchdef['basetable']);
		$this->assertArrayHasKey('wrapperclass', $searchdef);
		$this->assertInstanceOf(
			'tx_rnbase_model_base',
			tx_rnbase::makeInstance($searchdef['wrapperclass'])
		);
		$this->assertSearchDef($searchdef);
	}

	/**
	 * Test the search method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testSearchWithDefSearcher() {
		$fields = $options = array();
		$fields['NEWALIAS.uid'][OP_EQ] = 57;
		$options['sqlonly'] = TRUE;
		$options['searchdef'] = array(
			'alias' => array(
				'NEWALIAS' => array(
					'table' => 'tx_new_table',
					'join' => 'JOIN tx_new_table AS NEWALIAS ON PAGES.new_field = NEWALIAS.uid',
				)
			),
		);

		$repo = $this->getRepository();
		$searcher = $this->callInaccessibleMethod(
			$repo,
			'getSearcher'
		);
		$searcher
			->expects($this->once())
			->method('search')
			->with(
				$this->callback(
					function($f) {
						$this->assertTrue(is_array($f));
						$this->assertArrayHasKey('NEWALIAS.uid', $f);
						$this->assertTrue(is_array($f['NEWALIAS.uid']));
						$this->assertArrayHasKey(OP_EQ, $f['NEWALIAS.uid']);
						$this->assertSame(57, $f['NEWALIAS.uid'][OP_EQ]);
						return TRUE;
					}
				),
				$this->callback(
					function($o) {
						$this->assertTrue(is_array($o));
						$this->assertArrayHasKey('sqlonly', $o);
						$this->assertTrue($o['sqlonly']);
						$this->assertArrayHasKey('searchdef', $o);
						$searchdef = &$o['searchdef'];
						$this->assertSearchDef($searchdef);

						// test the search dev overrule
						$this->assertArrayHasKey('alias', $searchdef);
						$this->assertTrue(is_array($searchdef['alias']));
						$this->assertArrayHasKey('NEWALIAS', $searchdef['alias']);
						$this->assertTrue(is_array($searchdef['alias']['NEWALIAS']));
						$this->assertArrayHasKey('table', $searchdef['alias']['NEWALIAS']);
						$this->assertEquals('tx_new_table', $searchdef['alias']['NEWALIAS']['table']);
						$this->assertArrayHasKey('join', $searchdef['alias']['NEWALIAS']);
						$this->assertEquals('JOIN tx_new_table AS NEWALIAS ON PAGES.new_field = NEWALIAS.uid', $searchdef['alias']['NEWALIAS']['join']);

						return TRUE;
					}
				)
			)
			->will($this->returnValue(array()))
		;

		$this->assertTrue(is_array($repo->search($fields, $options)));
	}



	/**
	 * checks the searchdev options.
	 *
	 * @param array $searchdef
	 */
	protected function assertSearchDef($searchdef) {
		$this->assertTrue(is_array($searchdef));
		$this->assertArrayHasKey('alias', $searchdef);
		$this->assertTrue(is_array($searchdef['alias']));
		$this->assertArrayHasKey('PAGES', $searchdef['alias']);
		$this->assertTrue(is_array($searchdef['alias']['PAGES']));
		$this->assertArrayHasKey('table', $searchdef['alias']['PAGES']);
		$this->assertEquals('pages', $searchdef['alias']['PAGES']['table']);
	}
}