<?php
/**
 * Copyright notice.
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

/**
 * tx_mklib_tests_repository_TtNewsTest.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_tests_repository_TtNewsTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * {@inheritdoc}
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        if (!tx_rnbase_util_Extensions::isLoaded('tt_news')) {
            self::markTestSkipped('tt_news nicht installiert');
        }
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepository()
    {
        $searcher = $this->getMock(
            'tx_rnbase_util_SearchGeneric'
        );
        $repo = $this->getMock(
            'tx_mklib_repository_TtNews',
            array('getSearcher')
        );

        $repo
            ->expects(self::any())
            ->method('getSearcher')
            ->will(self::returnValue($searcher));

        return $repo;
    }

    /**
     * Test the getSearchClass method.
     *
     *
     * @group unit
     * @test
     */
    public function testGetSearchClassShouldBeGeneric()
    {
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
     *
     * @group unit
     * @test
     */
    public function testGetEmptyModelShouldBeBaseModelWithPageTable()
    {
        $model = $this->callInaccessibleMethod(
            $this->getRepository(),
            'getEmptyModel'
        );
        self::assertInstanceOf(
            'tx_rnbase_model_base',
            $model
        );
        self::assertEquals(
            'tt_news',
            $model->getTablename()
        );
    }

    /**
     * Test the getSearchdef method.
     *
     *
     * @group unit
     * @test
     */
    public function testGetSearchDefinition()
    {
        $searchdef = $this->callInaccessibleMethod(
            $this->getRepository(),
            'getSearchDefinition'
        );
        self::assertArrayHasKey('basetable', $searchdef);
        self::assertEquals('tt_news', $searchdef['basetable']);
        self::assertArrayHasKey('wrapperclass', $searchdef);
        self::assertInstanceOf(
            'tx_rnbase_model_base',
            tx_rnbase::makeInstance($searchdef['wrapperclass'])
        );
        $this->assertSearchDefiniton($searchdef);
    }

    /**
     * Test the search method.
     *
     *
     * @group unit
     * @test
     */
    public function testSearchWithGivenSearchDefinition()
    {
        $fields = $options = array();
        $fields['NEWALIAS.uid'][OP_EQ] = 57;
        $options['sqlonly'] = true;
        $options['searchdef'] = array(
            'alias' => array(
                'NEWALIAS' => array(
                    'table' => 'tx_new_table',
                    'join' => 'JOIN tx_new_table AS NEWALIAS ON NEWS.new_field = NEWALIAS.uid',
                ),
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
                    function ($f) use ($that) {
                        $that::assertTrue(is_array($f));
                        $that::assertArrayHasKey('NEWALIAS.uid', $f);
                        $that::assertTrue(is_array($f['NEWALIAS.uid']));
                        $that::assertArrayHasKey(OP_EQ, $f['NEWALIAS.uid']);
                        $that::assertSame(57, $f['NEWALIAS.uid'][OP_EQ]);

                        return true;
                    }
                ),
                self::callback(
                    function ($o) use ($that) {
                        $that::assertTrue(is_array($o));
                        $that::assertArrayHasKey('sqlonly', $o);
                        $that::assertTrue($o['sqlonly']);
                        $that::assertArrayHasKey('searchdef', $o);
                        $searchdef = &$o['searchdef'];
                        $that::assertSearchDefiniton($searchdef);

                        // test the search dev overrule
                        $that::assertArrayHasKey('alias', $searchdef);
                        $that::assertTrue(is_array($searchdef['alias']));
                        $that::assertArrayHasKey('NEWALIAS', $searchdef['alias']);
                        $that::assertTrue(is_array($searchdef['alias']['NEWALIAS']));
                        $that::assertArrayHasKey('table', $searchdef['alias']['NEWALIAS']);
                        $that::assertEquals('tx_new_table', $searchdef['alias']['NEWALIAS']['table']);
                        $that::assertArrayHasKey('join', $searchdef['alias']['NEWALIAS']);
                        $that::assertEquals('JOIN tx_new_table AS NEWALIAS ON NEWS.new_field = NEWALIAS.uid', $searchdef['alias']['NEWALIAS']['join']);

                        return true;
                    }
                )
            )
            ->will(self::returnValue(array()));

        self::assertTrue(is_array($repo->search($fields, $options)));
    }

    /**
     * @group unit
     * @test
     */
    public function testSearch()
    {
        $fields = $options = array();
        $fields['NEWS.uid'][OP_EQ] = 57;

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
                    function ($f) use ($that) {
                        $that::assertTrue(is_array($f));
                        $that::assertArrayHasKey('NEWS.uid', $f);
                        $that::assertTrue(is_array($f['NEWS.uid']));
                        $that::assertArrayHasKey(OP_EQ, $f['NEWS.uid']);
                        $that::assertSame(57, $f['NEWS.uid'][OP_EQ]);

                        return true;
                    }
                ),
                self::callback(
                    function ($o) use ($that) {
                        $that::assertTrue(is_array($o));
                        $that::assertArrayHasKey('searchdef', $o);
                        $searchdef = &$o['searchdef'];
                        $that::assertSearchDefiniton($searchdef);

                        return true;
                    }
                )
            )
            ->will(self::returnValue(array('test')));

        self::assertEquals(array('test'), $repo->search($fields, $options));
    }

    /**
     * @group unit
     * @test
     */
    public function testSearchSingle()
    {
        $fields = $options = array();
        $fields['NEWS.uid'][OP_EQ] = 57;

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
                    function ($f) use ($that) {
                        $that::assertTrue(is_array($f));
                        $that::assertArrayHasKey('NEWS.uid', $f);
                        $that::assertTrue(is_array($f['NEWS.uid']));
                        $that::assertArrayHasKey(OP_EQ, $f['NEWS.uid']);
                        $that::assertSame(57, $f['NEWS.uid'][OP_EQ]);

                        return true;
                    }
                ),
                self::callback(
                    function ($o) use ($that) {
                        $that::assertTrue(is_array($o));
                        $that::assertArrayHasKey('searchdef', $o);
                        $searchdef = &$o['searchdef'];
                        $that::assertSearchDefiniton($searchdef);

                        return true;
                    }
                )
            )
            ->will(self::returnValue(array(0 => 'test')));

        self::assertEquals('test', $repo->searchSingle($fields, $options));
    }

    /**
     * checks the searchdev options.
     *
     * @param array $searchdef
     */
    public static function assertSearchDefiniton($searchDefinition)
    {
        self::assertTrue(is_array($searchDefinition));
        self::assertArrayHasKey('alias', $searchDefinition);
        self::assertTrue(is_array($searchDefinition['alias']));
        self::assertArrayHasKey('NEWS', $searchDefinition['alias']);
        self::assertTrue(is_array($searchDefinition['alias']['NEWS']));
        self::assertArrayHasKey('table', $searchDefinition['alias']['NEWS']);
        self::assertEquals('tt_news', $searchDefinition['alias']['NEWS']['table']);
    }
}
