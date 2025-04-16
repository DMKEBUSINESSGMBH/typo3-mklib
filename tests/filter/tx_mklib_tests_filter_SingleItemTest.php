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
 * @author Hannes Bochmann
 */
class tx_mklib_tests_filter_SingleItemTest extends Sys25\RnBase\Testing\BaseTestCase
{
    private string $testParamName = 'johnDoe';

    private string $testSearchAlias = 'JOHNDOE';

    /**
     * @group unit
     */
    public function testFilterSetsOptionsAndFieldsCorrectIfNoParameter(): void
    {
        self::markTestIncomplete("Error: Class 'TYPO3\CMS\Core\TimeTracker\NullTimeTracker' not found");

        $parameters = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Frontend\Request\Parameters::class);
        $filter = $this->getFilter($parameters);

        $fields = [];
        $options = [];
        $filterReturn = $filter->init($fields, $options);
        self::assertTrue($filterReturn, 'filter gibt nicht true zurück.');

        $expectedFields = [
            $this->testSearchAlias.'.uid' => [OP_EQ_INT => 0],
        ];
        self::assertEquals($expectedFields, $fields, 'fields nicht leer');

        $expectedOptions = [
            'limit' => 1,
        ];
        self::assertEquals($expectedOptions, $options, 'options nicht richtig gesetzt.');
    }

    /**
     * @group unit
     */
    public function testFilterSetsOptionsAndFieldsCorrectIfParameterSet(): void
    {
        self::markTestIncomplete("Error: Class 'TYPO3\CMS\Core\TimeTracker\NullTimeTracker' not found");

        $parameters = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Frontend\Request\Parameters::class);
        $itemUid = 123;
        $parameters->offsetSet($this->testParamName, $itemUid);
        $filter = $this->getFilter($parameters);

        $fields = [];
        $options = [];
        $filterReturn = $filter->init($fields, $options);
        self::assertTrue($filterReturn, 'filter gibt nicht true zurück.');

        $expectedFields = [
            $this->testSearchAlias.'.uid' => [OP_EQ_INT => $itemUid],
        ];
        self::assertEquals($expectedFields, $fields, 'fields nicht leer');

        $expectedOptions = [
            'limit' => 1,
        ];
        self::assertEquals($expectedOptions, $options, 'options nicht richtig gesetzt.');
    }

    /**
     * @return tx_mklib_filter_SingleItem
     */
    private function getFilter(object $parameters): PHPUnit\Framework\MockObject\MockObject
    {
        $configurations = tx_mklib_util_TS::loadConfig4BE('mklib');

        $confId = 'doesNotMatter.';
        $filter = $this->getMockForAbstractClass(
            'tx_mklib_filter_SingleItem',
            [&$parameters, &$configurations, $confId]
        );
        $filter->expects(self::once())
            ->method('getParameterName')
            ->willReturn($this->testParamName);
        $filter->expects(self::once())
            ->method('getSearchAlias')
            ->willReturn($this->testSearchAlias);

        return $filter;
    }
}
