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
class tx_mklib_tests_repository_AbstractTest extends Sys25\RnBase\Testing\BaseTestCase
{
    #[PHPUnit\Framework\Attributes\DataProvider('getOptions')]
    public function testHandleEnableFieldsOptionsWhenInBackend(
        array $options,
        array $expectedOptions,
    ): void {
        $GLOBALS['TYPO3_REQUEST'] = new TYPO3\CMS\Core\Http\ServerRequest();
        $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST']->withAttribute(
            'applicationType',
            TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_BE
        );
        $repository = $this->getRepositoryMock();

        $method = new ReflectionMethod('tx_mklib_repository_Abstract', 'handleEnableFieldsOptions');
        $method->setAccessible(true);

        $fields = [];
        $method->invokeArgs($repository, [&$fields, &$options]);

        self::assertEquals($expectedOptions, $options, 'options falsch');
    }

    public static function getOptions(): array
    {
        return [
            [['enablefieldsoff' => true], ['enablefieldsoff' => true]],
            [['enablefieldsbe' => true], ['enablefieldsbe' => true]],
            [['enablefieldsfe' => true], ['enablefieldsfe' => true]],
            [[], ['enablefieldsbe' => true]],
        ];
    }

    /**
     * @group unit
     */
    public function testGetSearcher(): void
    {
        $repository = $this->getRepositoryMock();

        $method = new ReflectionMethod('tx_mklib_repository_Abstract', 'getSearcher');
        $method->setAccessible(true);

        self::assertInstanceOf(
            'tx_mklib_search_StaticCountries',
            $method->invoke($repository),
            'falsche wrapper Klasse'
        );
    }

    /**
     * @group unit
     */
    public function testFindByUidReturnsModelIfModelValid(): void
    {
        $repository = $this->getRepositoryMock();

        $expectedModel = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_model_StaticCountry',
            ['uid' => 123, 'title' => 'dummy']
        );

        self::assertEquals(
            $expectedModel,
            $repository->findByUid(['uid' => 123, 'title' => 'dummy']),
            'model nicht zurück gegeben'
        );
    }

    /**
     * @group unit
     */
    public function testFindByUidReturnsNullIfModelInvalid(): void
    {
        $repository = $this->getRepositoryMock();

        self::assertNull(
            $repository->findByUid(0),
            'NULL nicht zurück gegeben'
        );
    }

    /**
     * @group unit
     */
    public function testGetWrapperClass(): void
    {
        $repository = $this->getRepositoryMock();

        $method = new ReflectionMethod('tx_mklib_repository_Abstract', 'getWrapperClass');
        $method->setAccessible(true);

        self::assertEquals(
            'tx_mklib_model_StaticCountry',
            $method->invoke($repository),
            'falsche wrapper Klasse'
        );
    }

    /**
     * @group unit
     */
    public function testSearchCallsSearcherCorrect(): void
    {
        $GLOBALS['TCA']['static_countries'] = [];
        $repository = $this->getRepositoryMock(['getSearcher']);

        $fields = ['someField' => 1];
        $options = ['enablefieldsbe' => 1];

        $searcher = $this->getMock(
            'tx_mklib_search_StaticCountries',
            ['search']
        );

        $searcher->expects(self::once())
            ->method('search')
            ->with($fields, $options)
            ->willReturn(['searched']);

        $repository->expects(self::any())
            ->method('getSearcher')
            ->willReturn($searcher);

        self::assertEquals(
            ['searched'],
            $repository->search($fields, $options),
            'falsch gesucht'
        );
    }

    /**
     * @group unit
     */
    public function testUniqueItemsReducesCorrect(): void
    {
        $GLOBALS['TCA']['tt_content']['ctrl']['languageField'] = 'sys_language_uid';
        $GLOBALS['TCA']['tt_content']['ctrl']['transOrigPointerField'] = 'l18n_parent';

        $repository = $this->getRepositoryMock();
        $master = $this->getMock(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            ['getTableName'],
            [['uid' => 123]]
        );
        $master->expects(self::any())
            ->method('getTableName')
            ->willReturn('tt_content');

        $overlay = $this->getMock(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            ['getTableName'],
            [['uid' => 456, 'l18n_parent' => 123, 'sys_language_uid' => 789]]
        );
        $overlay->expects(self::any())
            ->method('getTableName')
            ->willReturn('tt_content');

        $items = $this->callInaccessibleMethod($repository, 'uniqueItems', [$master, $overlay], ['distinct' => true]);

        self::assertCount(1, $items);
        self::assertArrayHasKey(0, $items);
        self::assertEquals($overlay, $items[0]);
    }

    /**
     * @group unit
     */
    public function testUniqueItemsDoesNotReduceCorrect(): void
    {
        $repository = $this->getRepositoryMock();
        $master = $this->getMock(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            ['getTableName'],
            [['uid' => 123]]
        );
        $master->expects(self::any())
            ->method('getTableName')
            ->willReturn('tt_content');

        $overlay = $this->getMock(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            ['getTableName'],
            [['uid' => 456, 'l18n_parent' => 123, 'sys_language_uid' => 789]]
        );
        $overlay->expects(self::any())
            ->method('getTableName')
            ->willReturn('tt_content');

        $items = $this->callInaccessibleMethod($repository, 'uniqueItems', [$master, $overlay], []);

        self::assertCount(2, $items);
        self::assertArrayHasKey(0, $items);
        self::assertEquals($master, $items[0]);
        self::assertArrayHasKey(1, $items);
        self::assertEquals($overlay, $items[1]);
    }

    /**
     * @group unit
     */
    public function testFindAll(): void
    {
        $repository = $this->getRepositoryMock(['search']);

        $repository->expects(self::once())
            ->method('search')
            ->with([], [])
            ->willReturn(['searched']);

        self::assertEquals(
            ['searched'],
            $repository->findAll(),
            'falsch gesucht'
        );
    }

    /**
     * @group unit
     */
    public function testHandleCreation(): void
    {
        $repository = $this->getRepositoryMock(['create']);

        $data = ['field' => 'value'];

        $repository->expects(self::once())
            ->method('create')
            ->with($data)
            ->willReturn(['created']);

        self::assertEquals(
            ['created'],
            $repository->create($data),
            'not created'
        );
    }

    /**
     * @group unit
     */
    public function testSecureFromCrossSiteScriptingReturnsDataIfNoFieldsToBeStrippedAreDefined(): void
    {
        $model = $this->getModelMock();
        $repository = $this->getRepositoryMock();

        $data = [
            'field1' => 'value1', 'field2' => 'value2', 'field3' => 'value3',
        ];

        $method = new ReflectionMethod(
            'tx_mklib_repository_Abstract',
            'secureFromCrossSiteScripting'
        );
        $method->setAccessible(true);

        $returnArray = $method->invoke($repository, $model, $data);
        $expectedReturnArray = $data;
        self::assertEquals($expectedReturnArray, $returnArray, 'Daten falsch');
    }

    /**
     * @group unit
     */
    public function testSecureFromCrossSiteScriptingReturnsStrippedData(): void
    {
        $model = new tx_mklib_tests_fixtures_classes_Model();
        $repository = $this->getRepositoryMock();

        $data = [
            'field1' => '<p>value1</p>', 'field2' => '<b>value2</b>', 'field3' => 'value3',
        ];

        $method = new ReflectionMethod(
            'tx_mklib_repository_Abstract',
            'secureFromCrossSiteScripting'
        );
        $method->setAccessible(true);

        $returnArray = $method->invoke($repository, $model, $data);
        $expectedReturnArray = ['field1' => 'value1', 'field2' => '<b>value2</b>', 'field3' => 'value3'];
        self::assertEquals($expectedReturnArray, $returnArray, 'Daten falsch');
    }

    /**
     * @group unit
     */
    public function testGetDatabaseUtility(): void
    {
        self::assertInstanceOf(
            'Tx_Mklib_Database_Connection',
            $this->callInaccessibleMethod($this->getRepositoryMock(), 'getDatabaseUtility'),
            'falscher Klassenname'
        );
    }

    /**
     * @group unit
     */
    public function testHandleUpdateBuildsWhereClauseWhenNoneGiven(): void
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->willReturn(['column_1']);
        $model->expects(self::any())
            ->method('getTableName')
            ->willReturn('unknown');
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getDatabaseUtility']
        );

        $databaseConnection = $this->getDatabaseConnectionMock(['fullQuoteStr', 'doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('fullQuoteStr')
            ->with('123')
            ->willReturn('\'quoted123\'');
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with('unknown', '1=1 AND `unknown`.`uid`=\'quoted123\'');

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->willReturn($databaseConnection);

        $repository->handleUpdate($model, []);
    }

    /**
     * @group unit
     */
    public function testHandleUpdateEliminatesNonTcaColumns(): void
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->willReturn(['column_1']);
        $model->expects(self::any())
            ->method('getTableName')
            ->willReturn('unknown');
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getDatabaseUtility']
        );

        $data = ['column_1' => 'new value', 'column_2' => 'new value'];
        $databaseConnection = $this->getDatabaseConnectionMock(['fullQuoteStr', 'doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('fullQuoteStr')
            ->with('123')
            ->willReturn('\'quoted123\'');
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with(
                'unknown',
                '1=1 AND `unknown`.`uid`=\'quoted123\'',
                ['column_1' => 'new value']
            );

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->willReturn($databaseConnection);

        $repository->handleUpdate($model, $data);
    }

    /**
     * @group unit
     */
    public function testHandleUpdateCallsSecureFromCrossSiteScripting(): void
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->willReturn(['column_1']);
        $model->expects(self::any())
            ->method('getTableName')
            ->willReturn('unknown');
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getDatabaseUtility', 'secureFromCrossSiteScripting']
        );

        $data = ['column_1' => 'new value'];
        $databaseConnection = $this->getDatabaseConnectionMock(['fullQuoteStr', 'doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('fullQuoteStr')
            ->with('123')
            ->willReturn('\'quoted123\'');
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with('unknown', '1=1 AND `unknown`.`uid`=\'quoted123\'', ['secured']);

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->willReturn($databaseConnection);

        $repository->expects(self::once())
            ->method('secureFromCrossSiteScripting')
            ->with($model, $data)
            ->willReturn(['secured']);

        $repository->handleUpdate($model, $data);
    }

    /**
     * @group unit
     */
    public function testHandleUpdateRemovesUidColumn(): void
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->willReturn(['column_1']);
        $model->expects(self::any())
            ->method('getTableName')
            ->willReturn('unknown');
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getDatabaseUtility']
        );

        $data = ['column_1' => 'new value', 'uid' => 456];
        $databaseConnection = $this->getDatabaseConnectionMock(['fullQuoteStr', 'doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('fullQuoteStr')
            ->with('123')
            ->willReturn('\'quoted123\'');
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with(
                'unknown',
                '1=1 AND `unknown`.`uid`=\'quoted123\'',
                ['column_1' => 'new value']
            );

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->willReturn($databaseConnection);

        $repository->handleUpdate($model, $data);
    }

    /**
     * @group unit
     */
    public function testHandleUpdateUsesGivenWhere(): void
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->willReturn(['column_1']);
        $model->expects(self::any())
            ->method('getTableName')
            ->willReturn('unknown');
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getDatabaseUtility']
        );

        $databaseConnection = $this->getDatabaseConnectionMock(['doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with('unknown', 'test where');

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->willReturn($databaseConnection);

        $repository->handleUpdate($model, [], 'test where');
    }

    /**
     * @group unit
     */
    public function testHandleUpdateWhenDebugAndNoQuoteFieldsParametersGiven(): void
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->willReturn(['column_1']);
        $model->expects(self::any())
            ->method('getTableName')
            ->willReturn('unknown');
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getDatabaseUtility']
        );

        $databaseConnection = $this->getDatabaseConnectionMock(['doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with('unknown', 'test where', [], 987, 'commaSeparatedFields');

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->willReturn($databaseConnection);

        $repository->handleUpdate(
            $model,
            [],
            'test where',
            987,
            'commaSeparatedFields'
        );
    }

    /**
     * @return tx_mklib_repository_Abstract
     */
    private function getRepositoryMock(array $mockedMethods = []): PHPUnit\Framework\MockObject\MockObject
    {
        $repository = $this->getMockBuilder('tx_mklib_repository_Abstract')
            ->disableOriginalConstructor()
            ->onlyMethods(array_merge($mockedMethods, ['getSearchClass']))
            ->getMock();

        $repository->expects(self::any())
            ->method('getSearchClass')
            ->willReturn('tx_mklib_search_StaticCountries');

        return $repository;
    }

    /**
     * @return Tx_Mklib_Database_Connection
     */
    private function getDatabaseConnectionMock(array $mockedMethods)
    {
        return $this->getMock('Tx_Mklib_Database_Connection', $mockedMethods);
    }

    /**
     * @return tx_mklib_repository_Abstract
     */
    private function getModelMock(array $rowOrUid = [], array $mockedMethods = [])
    {
        return $this->getMock(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            $mockedMethods,
            [$rowOrUid]
        );
    }

    /**
     * @group unit
     */
    public function testSearchSingleIfItemsFound(): void
    {
        $repository = $this->getRepositoryMock(
            ['search']
        );

        $expectedFields = ['fields'];
        $expectedOptions = ['orderby' => [], 'limit' => 1];

        $repository->expects(self::once())
            ->method('search')
            ->with($expectedFields, $expectedOptions)
            ->willReturn([0 => 'test']);

        self::assertEquals(
            'test',
            $repository->searchSingle($expectedFields, ['orderby' => []])
        );
    }

    /**
     * @group unit
     */
    public function testSearchSingleIfNoItemsFound(): void
    {
        $repository = $this->getRepositoryMock(
            ['search']
        );

        $expectedFields = ['fields'];
        $expectedOptions = ['orderby' => [], 'limit' => 1];

        $repository->expects(self::once())
            ->method('search')
            ->with($expectedFields, $expectedOptions)
            ->willReturn([]);

        self::assertNull(
            $repository->searchSingle($expectedFields, ['orderby' => []])
        );
    }
}
