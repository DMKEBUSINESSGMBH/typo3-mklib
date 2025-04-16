<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mklib" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
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
class tx_mklib_tests_repository_TtNewsTest extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp(): void
    {
        if (!TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_news')) {
            self::markTestSkipped('tt_news nicht installiert');
        }
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getRepository()
    {
        $searcher = $this->getMock(
            Sys25\RnBase\Search\SearchGeneric::class
        );
        $repo = $this->getMock(
            'tx_mklib_repository_TtNews',
            ['getSearcher']
        );

        $repo
            ->expects(self::any())
            ->method('getSearcher')
            ->willReturn($searcher);

        return $repo;
    }

    /**
     * Test the getSearchClass method.
     *
     * @group unit
     *
     * @test
     */
    public function testGetSearchClassShouldBeGeneric(): void
    {
        self::assertEquals(
            Sys25\RnBase\Search\SearchGeneric::class,
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
    public function testGetEmptyModelShouldBeBaseModelWithPageTable(): void
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
            'tt_news',
            $model->getTablename()
        );
    }

    /**
     * Test the getSearchdef method.
     *
     * @group unit
     *
     * @test
     */
    public function testGetSearchDefinition(): void
    {
        $searchdef = $this->callInaccessibleMethod(
            $this->getRepository(),
            'getSearchDefinition'
        );
        self::assertArrayHasKey('basetable', $searchdef);
        self::assertEquals('tt_news', $searchdef['basetable']);
        self::assertArrayHasKey('wrapperclass', $searchdef);
        self::assertInstanceOf(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($searchdef['wrapperclass'])
        );
        $this->assertSearchDefiniton($searchdef);
    }

    /**
     * Test the search method.
     *
     * @group unit
     *
     * @test
     */
    public function testSearchWithGivenSearchDefinition(): void
    {
        $fields = [];
        $options = [];
        $fields['NEWALIAS.uid'][OP_EQ] = 57;
        $options['sqlonly'] = true;
        $options['searchdef'] = [
            'alias' => [
                'NEWALIAS' => [
                    'table' => 'tx_new_table',
                    'join' => 'JOIN tx_new_table AS NEWALIAS ON NEWS.new_field = NEWALIAS.uid',
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
                    function ($f) use ($that): bool {
                        $that::assertTrue(is_array($f));
                        $that::assertArrayHasKey('NEWALIAS.uid', $f);
                        $that::assertTrue(is_array($f['NEWALIAS.uid']));
                        $that::assertArrayHasKey(OP_EQ, $f['NEWALIAS.uid']);
                        $that::assertSame(57, $f['NEWALIAS.uid'][OP_EQ]);

                        return true;
                    }
                ),
                self::callback(
                    function ($o) use ($that): bool {
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
            ->willReturn([]);

        self::assertTrue(is_array($repo->search($fields, $options)));
    }

    /**
     * @group unit
     *
     * @test
     */
    public function testSearch(): void
    {
        $fields = [];
        $options = [];
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
                    function ($f) use ($that): bool {
                        $that::assertTrue(is_array($f));
                        $that::assertArrayHasKey('NEWS.uid', $f);
                        $that::assertTrue(is_array($f['NEWS.uid']));
                        $that::assertArrayHasKey(OP_EQ, $f['NEWS.uid']);
                        $that::assertSame(57, $f['NEWS.uid'][OP_EQ]);

                        return true;
                    }
                ),
                self::callback(
                    function ($o) use ($that): bool {
                        $that::assertTrue(is_array($o));
                        $that::assertArrayHasKey('searchdef', $o);
                        $searchdef = &$o['searchdef'];
                        $that::assertSearchDefiniton($searchdef);

                        return true;
                    }
                )
            )
            ->willReturn(['test']);

        self::assertEquals(['test'], $repo->search($fields, $options));
    }

    /**
     * @group unit
     *
     * @test
     */
    public function testSearchSingle(): void
    {
        $fields = [];
        $options = [];
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
                    function ($f) use ($that): bool {
                        $that::assertTrue(is_array($f));
                        $that::assertArrayHasKey('NEWS.uid', $f);
                        $that::assertTrue(is_array($f['NEWS.uid']));
                        $that::assertArrayHasKey(OP_EQ, $f['NEWS.uid']);
                        $that::assertSame(57, $f['NEWS.uid'][OP_EQ]);

                        return true;
                    }
                ),
                self::callback(
                    function ($o) use ($that): bool {
                        $that::assertTrue(is_array($o));
                        $that::assertArrayHasKey('searchdef', $o);
                        $searchdef = &$o['searchdef'];
                        $that::assertSearchDefiniton($searchdef);

                        return true;
                    }
                )
            )
            ->willReturn([0 => 'test']);

        self::assertEquals('test', $repo->searchSingle($fields, $options));
    }

    /**
     * checks the searchdev options.
     */
    public static function assertSearchDefiniton(array|ArrayAccess $searchDefinition): void
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
