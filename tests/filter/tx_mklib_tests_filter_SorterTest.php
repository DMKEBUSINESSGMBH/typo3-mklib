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
 * benötigte Klassen einbinden.
 */

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_tests_filter_SorterTest extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp(): void
    {
        self::markTestIncomplete("Error: Class 'TYPO3\CMS\Core\TimeTracker\NullTimeTracker' not found");
        Sys25\RnBase\Utility\Misc::prepareTSFE(['force' => true]);

        // tq_seo extension hat einen hook der auf das folgende feld zugreift.
        // wenn dieses nicht da ist bricht der test mit einer php warnung ab, was
        // wir verhindern wollen!
        if (!is_array($GLOBALS['TSFE']->rootLine)) {
            $GLOBALS['TSFE']->rootLine = [];
        }

        if (!is_array($GLOBALS['TSFE']->rootLine[0])) {
            $GLOBALS['TSFE']->rootLine[0] = [];
        }

        $GLOBALS['TSFE']->rootLine[0]['uid'] = 1;
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        unset($GLOBALS['TSFE']->rootLine[0]['uid']);
    }

    #[PHPUnit\Framework\Attributes\DataProvider('getExpectedParsedLinks')]
    public function testParseTemplateParsesLinksCorrect(
        string $template,
        string $expectedParsedTemplate,
        string $sortBy,
        string $sortOrder,
    ): void {
        $parameters = $this->getParameters();
        $configurations = $this->getConfigurations(true);

        if ('' !== $sortBy && '0' !== $sortBy) {
            $parameters->offsetSet('sortBy', $sortBy);
        }

        if ('' !== $sortOrder && '0' !== $sortOrder) {
            $parameters->offsetSet('sortOrder', $sortOrder);
        }

        $confId = 'myConfId.filter.';
        $filter = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_filter_Sorter',
            $parameters,
            $configurations,
            $confId
        );

        $fields = [];
        $options = [];
        $filter->init($fields, $options);

        $formatter = $configurations->getFormatter();
        $parsedTemplate = $filter->parseTemplate($template, $formatter, $confId);
        $expectedParsedTemplate = str_replace('" >', '">', $expectedParsedTemplate);

        self::assertRegExp($expectedParsedTemplate, $parsedTemplate, 'link falsch');
    }

    public static function getExpectedParsedLinks(): array
    {
        return [
            // auf grund der default config sollte das orderBy nicht auf asc sondern auf desc stehen
            [
                '###SORT_FIRSTFIELD_LINK###link###SORT_FIRSTFIELD_LINK###',
                '/(\<a href="\?id=)([a-z0-9]+)(&amp;mklib%5BsortBy%5D=firstField&amp;mklib%5BsortOrder%5D=desc"\>link\<\/a\>)/',
                '',
                '',
            ],
            // da nach dem feld asc sortiert wurde, sollte sich die sortOrder auf desc ändern
            [
                '###SORT_FIRSTFIELD_LINK###link###SORT_FIRSTFIELD_LINK###',
                '/(\<a href="\?id=)([a-z0-9]+)(&amp;mklib%5BsortBy%5D=firstField&amp;mklib%5BsortOrder%5D=desc"\>link\<\/a\>)/',
                'firstField',
                'asc',
            ],
            // normaler Link mit asc wenn anderes sortBy gewählt
            [
                '###SORT_FIRSTFIELD_LINK###link###SORT_FIRSTFIELD_LINK###',
                '/(\<a href="\?id=)([a-z0-9]+)(&amp;mklib%5BsortBy%5D=firstField&amp;mklib%5BsortOrder%5D=asc"\>link\<\/a\>)/',
                'unknownField',
                'asc',
            ],
            // Links werden ohne default config immer asc sortiert
            [
                '###SORT_SECONDFIELD_LINK###link###SORT_SECONDFIELD_LINK###',
                '/(\<a href="\?id=)([a-z0-9]+)(&amp;mklib%5BsortBy%5D=secondField&amp;mklib%5BsortOrder%5D=asc"\>link\<\/a\>)/',
                '',
                '',
            ],
            // da nach dem feld asc sortiert wurde, sollte sich die sortOrder auf desc ändern
            [
                '###SORT_SECONDFIELD_LINK###link###SORT_SECONDFIELD_LINK###',
                '/(\<a href="\?id=)([a-z0-9]+)(&amp;mklib%5BsortBy%5D=secondField&amp;mklib%5BsortOrder%5D=desc"\>link\<\/a\>)/',
                'secondField',
                'asc',
            ],
            // unbekannte Felder werden nicht geparsed
            [
                '###SORT_UNKNOWN_LINK###link###SORT_UNKNOWN_LINK###',
                '/(###SORT_UNKNOWN_LINK###link###SORT_UNKNOWN_LINK###)/',
                '',
                '',
            ],
        ];
    }

    /**
     * @return Sys25\RnBase\Frontend\Request\Parameters
     */
    private function getParameters()
    {
        $parameters = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Frontend\Request\Parameters::class);
        $parameters->setQualifier('mklib');

        return $parameters;
    }

    /**
     * @return Sys25\RnBase\Configuration\Processor
     */
    private function getConfigurations(bool $defaultConfig = false)
    {
        Sys25\RnBase\Utility\Misc::prepareTSFE();

        $configurations = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Configuration\Processor::class);
        $cObj = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Utility\Typo3Classes::getContentObjectRendererClass());
        $config = [
            'myConfId.' => [
                'filter.' => [
                    'sort.' => [
                        'fields' => 'firstField,secondField',
                        'link.' => [
                            'noHash' => 1,
                        ],
                    ],
                ],
            ],
        ];

        if ($defaultConfig) {
            $config['myConfId.']['filter.']['sort.']['default.'] = [
                'field' => 'firstField',
                'sortOrder' => 'asc',
            ];
        }

        $configurations->init($config, $cObj, 'mklib', 'mklib');

        return $configurations;
    }

    /**
     * @group unit
     */
    public function testFilterSetsOrderByEmptyIfNoDefaultConfigurationAndNoSortingViaParameter(): void
    {
        $parameters = $this->getParameters();
        $configurations = $this->getConfigurations();

        $confId = 'myConfId.filter.';
        $filter = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_tests_fixtures_classes_SorterFilter',
            $parameters,
            $configurations,
            $confId
        );

        $fields = [];
        $options = [];
        $filterReturn = $filter->init($fields, $options);
        self::assertEquals('0  ', $filterReturn, 'orderby und sortby nicht korrekt gesetzt.');
    }

    /**
     * @group unit
     */
    public function testFilterSetsOrderByCorrectFromParametersIfNoDefaultConfigurationButSortingViaParameter(): void
    {
        $parameters = $this->getParameters();
        $configurations = $this->getConfigurations();

        $parameters->offsetSet('sortBy', 'firstField');
        $parameters->offsetSet('sortOrder', 'desc');

        $confId = 'myConfId.filter.';
        $filter = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_tests_fixtures_classes_SorterFilter',
            $parameters,
            $configurations,
            $confId
        );

        $fields = [];
        $options = [];
        $filterReturn = $filter->init($fields, $options);
        self::assertEquals('1 firstField desc', $filterReturn, 'orderby und sortby nicht korrekt gesetzt.');
    }

    /**
     * @group unit
     */
    public function testFilterSetsOrderByCorrectFromDefaultConfigIfDefaultConfigurationAndNoSortingViaParameter(): void
    {
        $parameters = $this->getParameters();
        $configurations = $this->getConfigurations(true);

        $confId = 'myConfId.filter.';
        $filter = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_tests_fixtures_classes_SorterFilter',
            $parameters,
            $configurations,
            $confId
        );

        $fields = [];
        $options = [];
        $filterReturn = $filter->init($fields, $options);
        self::assertEquals('1 firstField asc', $filterReturn, 'orderby und sortby nicht korrekt gesetzt.');
    }

    /**
     * @group unit
     */
    public function testFilterSetsOrderByCorrectDromParametersIfDefaultConfigurationAndSortingViaParameter(): void
    {
        $parameters = $this->getParameters();
        $configurations = $this->getConfigurations(true);

        $parameters->offsetSet('sortBy', 'firstField');
        $parameters->offsetSet('sortOrder', 'desc');

        $confId = 'myConfId.filter.';
        $filter = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_tests_fixtures_classes_SorterFilter',
            $parameters,
            $configurations,
            $confId
        );

        $fields = [];
        $options = [];
        $filterReturn = $filter->init($fields, $options);
        self::assertEquals('1 firstField desc', $filterReturn, 'orderby und sortby nicht korrekt gesetzt.');
    }
}
