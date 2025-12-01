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
 * @author Hannes Bochmann
 */
class tx_mklib_tests_scheduler_DeleteFromDatabaseTest extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @var array
     */
    private $options = [
        'table' => 'someTable',
        'where' => 'someWhereClause',
        'mode' => Tx_Mklib_Database_Connection::DELETION_MODE_HIDE,
    ];

    /**
     * @group unit
     */
    public function testGetDatabaseConnection(): void
    {
        self::assertInstanceOf(
            'Tx_Mklib_Database_Connection',
            $this->getAccessibleMock('tx_mklib_scheduler_DeleteFromDatabase', [], [], '', false)
                ->_call('getDatabaseConnection'),
            'falsche Klasse'
        );
    }

    /**
     * @group unit
     */
    public function testExecuteCallsDoSelectOnDatabaseUtilityCorrect(): void
    {
        $databaseConnection = $this->getDatabaseConnectionMock();
        $scheduler = $this->getSchedulerByDbUtil($databaseConnection);

        $databaseConnection->expects(self::once())
            ->method('doSelect')
            ->with(
                'uid',
                $this->options['table'],
                [
                    'where' => $this->options['where'], 'enablefieldsoff' => true,
                    'callback' => $scheduler->deleteRow(...),
                ]
            );

        $scheduler->execute();
    }

    /**
     * @group unit
     */
    public function testExecuteCallsDoSelectOnDatabaseUtilityCorrectIfSelectFieldsConfigured(): void
    {
        $databaseConnection = $this->getDatabaseConnectionMock();
        $this->options['selectFields'] = 'otherFields';
        $scheduler = $this->getSchedulerByDbUtil($databaseConnection);

        $databaseConnection->expects(self::once())
            ->method('doSelect')
            ->with(
                $this->options['selectFields'].',uid',
                $this->options['table'],
                [
                    'where' => $this->options['where'], 'enablefieldsoff' => true,
                    'callback' => $scheduler->deleteRow(...),
                ]
            );

        $scheduler->execute();
    }

    /**
     * @group unit
     */
    public function testDeleteRowCallsDeleteOnDatabaseUtilityCorrect(): void
    {
        $databaseConnection = $this->getDatabaseConnectionMock(['fullQuoteStr']);
        $databaseConnection->expects(self::once())
            ->method('fullQuoteStr')
            ->with('123')
            ->willReturn("'quoted123'");

        $databaseConnection->expects(self::once())
            ->method('delete')
            ->with(
                $this->options['table'],
                "uid = 'quoted123'",
                $this->options['mode']
            );

        $scheduler = $this->getSchedulerByDbUtil($databaseConnection);
        $row = ['uid' => 123];
        $scheduler->deleteRow($row);
    }

    /**
     * @group unit
     */
    public function testDeleteRowCallsDeleteOnDatabaseUtilityCorrectWhenSelectFieldsDifferentToUid(): void
    {
        $this->options['uidField'] = 'otherField';
        $databaseConnection = $this->getDatabaseConnectionMock(['fullQuoteStr']);
        $databaseConnection->expects(self::once())
            ->method('fullQuoteStr')
            ->with('123')
            ->willReturn("'quoted123'");
        $databaseConnection->expects(self::once())
            ->method('delete')
            ->with(
                $this->options['table'],
                $this->options['uidField']." = 'quoted123'",
                $this->options['mode']
            );

        $scheduler = $this->getSchedulerByDbUtil($databaseConnection);
        $row = [$this->options['uidField'] => 123];
        $scheduler->deleteRow($row);
    }

    /**
     * @group unit
     */
    public function testDeleteRowSetsAffectedRowsPropertyCorrect(): void
    {
        $databaseConnection = $this->getDatabaseConnectionMock(['fullQuoteStr']);
        $databaseConnection->expects(self::exactly(2))
            ->method('fullQuoteStr')
            ->with(self::logicalOr('123', '456'))
            ->willReturn(0);
        $scheduler = $this->getSchedulerByDbUtil($databaseConnection);

        $scheduler->deleteRow(['uid' => 123]);
        $scheduler->deleteRow(['uid' => 456]);

        $affectedRows = new ReflectionProperty(
            'tx_mklib_scheduler_DeleteFromDatabase',
            'affectedRows'
        );

        self::assertEquals(
            [['uid' => 123], ['uid' => 456]],
            $affectedRows->getValue($scheduler),
            'affectedRows falsch gesetzt'
        );
    }

    /**
     * @param string $databaseConnection
     *
     * @return tx_mklib_scheduler_DeleteFromDatabase
     */
    private function getSchedulerByDbUtil($databaseConnection)
    {
        $scheduler = $this->getMock(
            'tx_mklib_scheduler_DeleteFromDatabase',
            ['getDatabaseConnection'],
            [],
            '',
            false
        );

        $scheduler->expects(self::any())
            ->method('getDatabaseConnection')
            ->willReturn($databaseConnection);

        $scheduler->setOptions($this->options);

        return $scheduler;
    }

    /**
     * @return Tx_Mklib_Database_Connection
     */
    private function getDatabaseConnectionMock(
        array $methods = [],
    ) {
        return $this->getMock(
            'Tx_Mklib_Database_Connection',
            array_merge(
                $methods,
                ['doSelect', 'delete']
            )
        );
    }
}
