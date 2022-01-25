<?php
/*
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 * @author Hannes Bochmann
 */
class tx_mklib_tests_filter_SingleItemTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @var string
     */
    private $testParamName = 'johnDoe';

    /**
     * @var string
     */
    private $testSearchAlias = 'JOHNDOE';

    /**
     * @group unit
     */
    public function testFilterSetsOptionsAndFieldsCorrectIfNoParameter()
    {
        self::markTestIncomplete("Error: Class 'TYPO3\CMS\Core\TimeTracker\NullTimeTracker' not found");

        $parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class);
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
    public function testFilterSetsOptionsAndFieldsCorrectIfParameterSet()
    {
        self::markTestIncomplete("Error: Class 'TYPO3\CMS\Core\TimeTracker\NullTimeTracker' not found");

        $parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class);
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
    private function getFilter($parameters)
    {
        $configurations = tx_mklib_util_TS::loadConfig4BE('mklib');

        $confId = 'doesNotMatter.';
        $filter = $this->getMockForAbstractClass(
            'tx_mklib_filter_SingleItem',
            [&$parameters, &$configurations, $confId]
        );
        $filter->expects(self::once())
            ->method('getParameterName')
            ->will(self::returnValue($this->testParamName));
        $filter->expects(self::once())
            ->method('getSearchAlias')
            ->will(self::returnValue($this->testSearchAlias));

        return $filter;
    }
}
