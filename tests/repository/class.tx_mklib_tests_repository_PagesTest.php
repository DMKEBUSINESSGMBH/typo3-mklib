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
 * Tests for tx_mklib_repository_Pages.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_tests_repository_PagesTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepository()
    {
        $searcher = $this->getMock(
            \Sys25\RnBase\Search\SearchGeneric::class
        );
        $repo = $this->getMock(
            'tx_mklib_repository_Pages',
            ['getSearcher']
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
     * @group unit
     *
     * @test
     */
    public function testGetSearchClassShouldBeGeneric()
    {
        self::assertEquals(
            \Sys25\RnBase\Search\SearchGeneric::class,
            $this->callInaccessibleMethod(
                $this->getRepository(),
                'getSearchClass'
            )
        );
    }

    /**
     * Test the getEmptyModel method.
     *
     * @group unit
     *
     * @test
     */
    public function testGetEmptyModelShouldBeBaseModelWithPageTable()
    {
        $model = $this->callInaccessibleMethod(
            $this->getRepository(),
            'getEmptyModel'
        );
        self::assertInstanceOf(
            Sys25\RnBase\Domain\Model\BaseModel::class,
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
     * @group unit
     *
     * @test
     */
    public function testGetChildren()
    {
        self::markTestIncomplete('Database tests are no longer supported. Please switch to functional tests');

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
                    function ($f) use ($that) {
                        $that::assertTrue(is_array($f));
                        $that::assertCount(1, $f);
                        $that::assertSame(57, $f['PAGES.pid'][OP_EQ_INT]);

                        return true;
                    }
                ),
                // check, if only (count:1) the searchdef
                self::callback(
                    function ($o) use ($that) {
                        $that::assertTrue(is_array($o));
                        $that::assertCount(2, $o);
                        $that::assertArrayHasKey('enablefieldsbe', $o);
                        $that::assertArrayHasKey('searchdef', $o);

                        return true;
                    }
                )
            )
            ->will(self::returnValue([]));

        $page = $this->getModel(['uid' => 57], 'tx_mklib_model_Page');

        self::assertTrue(is_array($repo->getChildren($page)));
    }

    /**
     * Test the getSearchdef method.
     *
     * @group unit
     *
     * @test
     */
    public function testGetSearchdef()
    {
        $searchdef = $this->callInaccessibleMethod(
            $this->getRepository(),
            'getSearchdef'
        );
        self::assertArrayHasKey('basetable', $searchdef);
        self::assertEquals('pages', $searchdef['basetable']);
        self::assertArrayHasKey('wrapperclass', $searchdef);
        self::assertInstanceOf(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($searchdef['wrapperclass'])
        );
        $this->assertSearchDef($searchdef);
    }

    /**
     * Test the search method.
     *
     * @group unit
     *
     * @test
     */
    public function testSearchWithDefSearcher()
    {
        self::markTestIncomplete('Database tests are no longer supported. Please switch to functional tests');

        $fields = $options = [];
        $fields['NEWALIAS.uid'][OP_EQ] = 57;
        $options['sqlonly'] = true;
        $options['searchdef'] = [
            'alias' => [
                'NEWALIAS' => [
                    'table' => 'tx_new_table',
                    'join' => 'JOIN tx_new_table AS NEWALIAS ON PAGES.new_field = NEWALIAS.uid',
                ],
            ],
        ];

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

                        return true;
                    }
                )
            )
            ->will(self::returnValue([]));

        self::assertTrue(is_array($repo->search($fields, $options)));
    }

    /**
     * checks the searchdev options.
     *
     * @param array $searchdef
     */
    public static function assertSearchDef($searchdef)
    {
        self::assertTrue(is_array($searchdef));
        self::assertArrayHasKey('alias', $searchdef);
        self::assertTrue(is_array($searchdef['alias']));
        self::assertArrayHasKey('PAGES', $searchdef['alias']);
        self::assertTrue(is_array($searchdef['alias']['PAGES']));
        self::assertArrayHasKey('table', $searchdef['alias']['PAGES']);
        self::assertEquals('pages', $searchdef['alias']['PAGES']['table']);
    }
}
