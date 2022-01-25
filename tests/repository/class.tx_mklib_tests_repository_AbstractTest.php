<?php
/**
 * @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
 * benötigte Klassen einbinden.
 */
class tx_mklib_tests_repository_AbstractTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @group unit
     * @dataProvider getOptions
     */
    public function testHandleEnableFieldsOptions(
        $options,
        $expectedOptions
    ) {
        $repository = $this->getRepositoryMock();

        $method = new ReflectionMethod('tx_mklib_repository_Abstract', 'handleEnableFieldsOptions');
        $method->setAccessible(true);

        $fields = [];
        $method->invokeArgs($repository, [&$fields, &$options]);

        self::assertEquals($expectedOptions, $options, 'options falsch');
    }

    /**
     * @return array
     */
    public function getOptions()
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
    public function testGetSearcher()
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
    public function testFindByUidReturnsModelIfModelValid()
    {
        $repository = $this->getRepositoryMock();

        $expectedModel = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
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
    public function testFindByUidReturnsNullIfModelInvalid()
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
    public function testGetWrapperClass()
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
    public function testSearchCallsSearcherCorrect()
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
            ->will(self::returnValue(['searched']));

        $repository->expects(self::any())
            ->method('getSearcher')
            ->will(self::returnValue($searcher));

        self::assertEquals(
            ['searched'],
            $repository->search($fields, $options),
            'falsch gesucht'
        );
    }

    /**
     * @group unit
     */
    public function testUniqueItemsReducesCorrect()
    {
        self::markTestIncomplete('Failed asserting that actual size 2 matches expected size 1.');

        $repository = $this->getRepositoryMock();
        $master = $this->getMock(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            ['getTableName'],
            [['uid' => 123]]
        );
        $master->expects(self::any())
            ->method('getTableName')
            ->will(self::returnValue('tt_content'));

        $overlay = $this->getMock(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            ['getTableName'],
            [['uid' => 456, 'l18n_parent' => 123, 'sys_language_uid' => 789]]
        );
        $overlay->expects(self::any())
            ->method('getTableName')
            ->will(self::returnValue('tt_content'));

        $items = $this->callInaccessibleMethod($repository, 'uniqueItems', [$master, $overlay], ['distinct' => true]);

        self::assertCount(1, $items);
        self::assertArrayHasKey(0, $items);
        self::assertEquals($overlay, $items[0]);
    }

    /**
     * @group unit
     */
    public function testUniqueItemsDoesNotReduceCorrect()
    {
        $repository = $this->getRepositoryMock();
        $master = $this->getMock(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            ['getTableName'],
            [['uid' => 123]]
        );
        $master->expects(self::any())
            ->method('getTableName')
            ->will(self::returnValue('tt_content'));

        $overlay = $this->getMock(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            ['getTableName'],
            [['uid' => 456, 'l18n_parent' => 123, 'sys_language_uid' => 789]]
        );
        $overlay->expects(self::any())
            ->method('getTableName')
            ->will(self::returnValue('tt_content'));

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
    public function testFindAll()
    {
        $repository = $this->getRepositoryMock(['search']);

        $repository->expects(self::once())
            ->method('search')
            ->with([], [])
            ->will(self::returnValue(['searched']));

        self::assertEquals(
            ['searched'],
            $repository->findAll(),
            'falsch gesucht'
        );
    }

    /**
     * @group unit
     */
    public function testHandleCreation()
    {
        $repository = $this->getRepositoryMock(['create']);

        $data = ['field' => 'value'];

        $repository->expects(self::once())
            ->method('create')
            ->with($data)
            ->will(self::returnValue(['created']));

        self::assertEquals(
            ['created'],
            $repository->create($data),
            'not created'
        );
    }

    /**
     * @group unit
     */
    public function testSecureFromCrossSiteScriptingReturnsDataIfNoFieldsToBeStrippedAreDefined()
    {
        $model = $this->getModelMock([], ['secureFromCrossSiteScripting']);
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
    public function testSecureFromCrossSiteScriptingReturnsStrippedData()
    {
        $model = $this->getModelMock(
            [],
            ['getTagsToBeIgnoredFromStripping', 'getFieldsToBeStripped']
        );
        $repository = $this->getRepositoryMock();

        $data = [
            'field1' => '<p>value1</p>', 'field2' => '<b>value2</b>', 'field3' => 'value3',
        ];

        $model->expects(self::once())
            ->method('getTagsToBeIgnoredFromStripping')
            ->will(self::returnValue('<b>'));

        $model->expects(self::once())
            ->method('getFieldsToBeStripped')
            ->will(self::returnValue(['field1', 'field2']));

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
    public function testGetDatabaseUtility()
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
    public function testHandleUpdateBuildsWhereClauseWhenNoneGiven()
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->will(self::returnValue(['column_1']));
        $model->expects(self::any())
            ->method('getTableName')
            ->will(self::returnValue('unknown'));
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getSearchClass', 'getDatabaseUtility']
        );

        $databaseConnection = $this->getDatabaseConnectionMock(['fullQuoteStr', 'doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('fullQuoteStr')
            ->with('123')
            ->will(self::returnValue('\'quoted123\''));
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with('unknown', '1=1 AND `unknown`.`uid`=\'quoted123\'');

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->will(self::returnValue($databaseConnection));

        $repository->handleUpdate($model, []);
    }

    /**
     * @group unit
     */
    public function testHandleUpdateEliminatesNonTcaColumns()
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->will(self::returnValue(['column_1']));
        $model->expects(self::any())
            ->method('getTableName')
            ->will(self::returnValue('unknown'));
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getSearchClass', 'getDatabaseUtility']
        );

        $data = ['column_1' => 'new value', 'column_2' => 'new value'];
        $databaseConnection = $this->getDatabaseConnectionMock(['fullQuoteStr', 'doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('fullQuoteStr')
            ->with('123')
            ->will(self::returnValue('\'quoted123\''));
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with(
                'unknown',
                '1=1 AND `unknown`.`uid`=\'quoted123\'',
                ['column_1' => 'new value']
            );

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->will(self::returnValue($databaseConnection));

        $repository->handleUpdate($model, $data);
    }

    /**
     * @group unit
     */
    public function testHandleUpdateCallsSecureFromCrossSiteScripting()
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->will(self::returnValue(['column_1']));
        $model->expects(self::any())
            ->method('getTableName')
            ->will(self::returnValue('unknown'));
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getSearchClass', 'getDatabaseUtility', 'secureFromCrossSiteScripting']
        );

        $data = ['column_1' => 'new value'];
        $databaseConnection = $this->getDatabaseConnectionMock(['fullQuoteStr', 'doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('fullQuoteStr')
            ->with('123')
            ->will(self::returnValue('\'quoted123\''));
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with('unknown', '1=1 AND `unknown`.`uid`=\'quoted123\'', ['secured']);

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->will(self::returnValue($databaseConnection));

        $repository->expects(self::once())
            ->method('secureFromCrossSiteScripting')
            ->with($model, $data)
            ->will(self::returnValue(['secured']));

        $repository->handleUpdate($model, $data);
    }

    /**
     * @group unit
     */
    public function testHandleUpdateRemovesUidColumn()
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->will(self::returnValue(['column_1']));
        $model->expects(self::any())
            ->method('getTableName')
            ->will(self::returnValue('unknown'));
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getSearchClass', 'getDatabaseUtility']
        );

        $data = ['column_1' => 'new value', 'uid' => 456];
        $databaseConnection = $this->getDatabaseConnectionMock(['fullQuoteStr', 'doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('fullQuoteStr')
            ->with('123')
            ->will(self::returnValue('\'quoted123\''));
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with(
                'unknown',
                '1=1 AND `unknown`.`uid`=\'quoted123\'',
                ['column_1' => 'new value']
            );

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->will(self::returnValue($databaseConnection));

        $repository->handleUpdate($model, $data);
    }

    /**
     * @group unit
     */
    public function testHandleUpdateUsesGivenWhere()
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->will(self::returnValue(['column_1']));
        $model->expects(self::any())
            ->method('getTableName')
            ->will(self::returnValue('unknown'));
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getSearchClass', 'getDatabaseUtility']
        );

        $databaseConnection = $this->getDatabaseConnectionMock(['doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with('unknown', 'test where');

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->will(self::returnValue($databaseConnection));

        $repository->handleUpdate($model, [], 'test where');
    }

    /**
     * @group unit
     */
    public function testHandleUpdateWhenDebugAndNoQuoteFieldsParametersGiven()
    {
        $model = $this->getModelMock(
            ['uid' => 123],
            ['getColumnNames', 'getTableName', 'reset']
        );
        $model->expects(self::once())
            ->method('getColumnNames')
            ->will(self::returnValue(['column_1']));
        $model->expects(self::any())
            ->method('getTableName')
            ->will(self::returnValue('unknown'));
        $model->expects(self::once())
            ->method('reset');

        $repository = $this->getRepositoryMock(
            ['getSearchClass', 'getDatabaseUtility']
        );

        $databaseConnection = $this->getDatabaseConnectionMock(['doUpdate']);
        $databaseConnection->expects(self::once())
            ->method('doUpdate')
            ->with('unknown', 'test where', [], 987, 'commaSeparatedFields');

        $repository->expects(self::once())
            ->method('getDatabaseUtility')
            ->will(self::returnValue($databaseConnection));

        $repository->handleUpdate(
            $model,
            [],
            'test where',
            987,
            'commaSeparatedFields'
        );
    }

    /**
     * @param array $mockedMethods
     *
     * @return tx_mklib_repository_Abstract
     */
    private function getRepositoryMock($mockedMethods = ['getSearchClass'])
    {
        $repository = $this->getMockForAbstractClass(
            'tx_mklib_repository_Abstract',
            [],
            '',
            false,
            false,
            false,
            $mockedMethods
        );

        $repository->expects(self::any())
            ->method('getSearchClass')
            ->will(self::returnValue('tx_mklib_search_StaticCountries'));

        return $repository;
    }

    /**
     * @param array $mockedMethods
     *
     * @return Tx_Mklib_Database_Connection
     */
    private function getDatabaseConnectionMock(array $mockedMethods)
    {
        return $this->getMock('Tx_Mklib_Database_Connection', $mockedMethods);
    }

    /**
     * @param array $mockedMethods
     *
     * @return tx_mklib_repository_Abstract
     */
    private function getModelMock($rowOrUid = [], $mockedMethods = [])
    {
        $model = $this->getMock(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            $mockedMethods,
            [$rowOrUid]
        );

        return $model;
    }

    /**
     * @group unit
     */
    public function testSearchSingleIfItemsFound()
    {
        $repository = $this->getRepositoryMock(
            ['search']
        );

        $expectedFields = ['fields'];
        $expectedOptions = ['orderby' => [], 'limit' => 1];

        $repository->expects(self::once())
            ->method('search')
            ->with($expectedFields, $expectedOptions)
            ->will(self::returnValue([0 => 'test']));

        self::assertEquals(
            'test',
            $repository->searchSingle($expectedFields, ['orderby' => []])
        );
    }

    /**
     * @group unit
     */
    public function testSearchSingleIfNoItemsFound()
    {
        $repository = $this->getRepositoryMock(
            ['search']
        );

        $expectedFields = ['fields'];
        $expectedOptions = ['orderby' => [], 'limit' => 1];

        $repository->expects(self::once())
            ->method('search')
            ->with($expectedFields, $expectedOptions)
            ->will(self::returnValue([]));

        self::assertNull(
            $repository->searchSingle($expectedFields, ['orderby' => []])
        );
    }
}
