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
			->expects(self::any())
			->method('getSearcher')
			->will(self::returnValue($searcher))
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
		self::assertEquals(
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
		self::assertInstanceOf(
			'tx_rnbase_model_base',
			$model
		);
		self::assertEquals(
			'pages',
			$model->getTablename()
		);
	}

	/**
	 * Test the getChildren method.
	 *
	 * @return void
	 *
	 * @group unit
	 * @test
	 */
	public function testGetChildren() {

		$repo = $this->getRepository();
		$searcher = $this->callInaccessibleMethod(
			$repo,
			'getSearcher'
		);
		$that = $this; // workaround for php 5.3
		$searcher
			->expects(self::once())
			->method('search')
			->with(
				// check, if only (count:1) the page field is set
				self::callback(
					function($f) use($that) {
						$that::assertTrue(is_array($f));
						$that::assertCount(1, $f);
						$that::assertSame(57, $f['PAGES.pid'][OP_EQ_INT]);
						return TRUE;
					}
				),
				// check, if only (count:1) the searchdef
				self::callback(
					function($o) use($that) {
						$that::assertTrue(is_array($o));
						// if the test rans in be, the enablefieldsbe option will be set
						if (TYPO3_MODE === 'BE') {
							$that::assertCount(2, $o);
							$that::assertArrayHasKey('enablefieldsbe', $o);
						} else {
							$that::assertCount(1, $o);
						}
						$that::assertArrayHasKey('searchdef', $o);

						return TRUE;
					}
				)
			)
			->will(self::returnValue(array()))
		;


		$page = $this->getModel(array('uid'=> 57), 'tx_mklib_model_Page');

		self::assertTrue(is_array($repo->getChildren($page)));
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
		self::assertArrayHasKey('basetable', $searchdef);
		self::assertEquals('pages', $searchdef['basetable']);
		self::assertArrayHasKey('wrapperclass', $searchdef);
		self::assertInstanceOf(
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
		$that = $this; // workaround for php 5.3
		$searcher
			->expects(self::once())
			->method('search')
			->with(
				self::callback(
					function($f) use($that) {
						$that::assertTrue(is_array($f));
						$that::assertArrayHasKey('NEWALIAS.uid', $f);
						$that::assertTrue(is_array($f['NEWALIAS.uid']));
						$that::assertArrayHasKey(OP_EQ, $f['NEWALIAS.uid']);
						$that::assertSame(57, $f['NEWALIAS.uid'][OP_EQ]);
						return TRUE;
					}
				),
				self::callback(
					function($o) use($that) {
						$that::assertTrue(is_array($o));
						$that::assertArrayHasKey('sqlonly', $o);
						$that::assertTrue($o['sqlonly']);
						$that::assertArrayHasKey('searchdef', $o);
						$searchdef = &$o['searchdef'];
						$that::assertSearchDef($searchdef);

						// test the search dev overrule
						$that::assertArrayHasKey('alias', $searchdef);
						$that::assertTrue(is_array($searchdef['alias']));
						$that::assertArrayHasKey('NEWALIAS', $searchdef['alias']);
						$that::assertTrue(is_array($searchdef['alias']['NEWALIAS']));
						$that::assertArrayHasKey('table', $searchdef['alias']['NEWALIAS']);
						$that::assertEquals('tx_new_table', $searchdef['alias']['NEWALIAS']['table']);
						$that::assertArrayHasKey('join', $searchdef['alias']['NEWALIAS']);
						$that::assertEquals('JOIN tx_new_table AS NEWALIAS ON PAGES.new_field = NEWALIAS.uid', $searchdef['alias']['NEWALIAS']['join']);

						return TRUE;
					}
				)
			)
			->will(self::returnValue(array()))
		;

		self::assertTrue(is_array($repo->search($fields, $options)));
	}



	/**
	 * checks the searchdev options.
	 *
	 * @param array $searchdef
	 * @access protected only public for php5.3 and missing $this usage in closures.
	 */
	public static function assertSearchDef($searchdef) {
		self::assertTrue(is_array($searchdef));
		self::assertArrayHasKey('alias', $searchdef);
		self::assertTrue(is_array($searchdef['alias']));
		self::assertArrayHasKey('PAGES', $searchdef['alias']);
		self::assertTrue(is_array($searchdef['alias']['PAGES']));
		self::assertArrayHasKey('table', $searchdef['alias']['PAGES']);
		self::assertEquals('pages', $searchdef['alias']['PAGES']['table']);
	}
}
