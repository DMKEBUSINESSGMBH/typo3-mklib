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
 * DB util tests.
 */
class Tx_Mklib_Database_ConnectionTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Tx_Mklib_Database_Connection::delete(): Unknown deletion mode (123)
     *
     * @group unit
     */
    public function testDeleteWithUnknownModeThrowsException()
    {
        tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->delete('', '', 123);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Tx_Mklib_Database_Connection::delete(): Cannot hide records in table unknown - no $TCA entry found!
     *
     * @group unit
     */
    public function testDeleteWithModeHiddenThrowsExceptionIfNoDisableColumnInTca()
    {
        tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->delete('unknown', '', Tx_Mklib_Database_Connection::DELETION_MODE_HIDE);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Tx_Mklib_Database_Connection::delete(): Cannot soft-delete records in table unknown - no $TCA entry found!
     *
     * @group unit
     */
    public function testDeleteWithModeSoftDeleteThrowsExceptionIfNoDeleteColumnInTca()
    {
        tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->delete('unknown', '', Tx_Mklib_Database_Connection::DELETION_MODE_SOFTDELETE);
    }

    /**
     * @group unit
     */
    public function testDeleteWithModeHiddenCallsDoUpdateCorrect()
    {
        self::markTestIncomplete('Exception: Tx_Mklib_Database_Connection::delete():'.
        'Cannot hide records in table pages - no $TCA entry found!');

        $util = $this->getUtilMock();
        $util->expects(self::never())
            ->method('doDelete');

        $util->expects(self::once())
            ->method('doUpdate')
            ->with('pages', 'someWhereClause', ['hidden' => 1]);

        $util->delete('pages', 'someWhereClause', Tx_Mklib_Database_Connection::DELETION_MODE_HIDE);
    }

    /**
     * @group unit
     */
    public function testDeleteWithModeSoftDeleteCallsDoUpdateCorrect()
    {
        self::markTestIncomplete('Exception: Tx_Mklib_Database_Connection::delete():'.
        'Cannot soft-delete records in table pages - no $TCA entry found!');

        $util = $this->getUtilMock();
        $util->expects(self::never())
            ->method('doDelete');

        $util->expects(self::once())
            ->method('doUpdate')
            ->with('pages', 'someWhereClause', ['deleted' => 1]);

        $util->delete('pages', 'someWhereClause', Tx_Mklib_Database_Connection::DELETION_MODE_SOFTDELETE);
    }

    /**
     * @group unit
     */
    public function testDeleteWithModeHardDeleteCallsDoDeleteCorrect()
    {
        $util = $this->getUtilMock();
        $util->expects(self::never())
            ->method('doUpdate');

        $util->expects(self::once())
            ->method('doDelete')
            ->with('pages', 'someWhereClause');

        $util->delete('pages', 'someWhereClause', Tx_Mklib_Database_Connection::DELETION_MODE_REALLYDELETE);
    }

    /**
     * @return Tx_Mklib_Database_Connection
     */
    private function getUtilMock()
    {
        return $this->getMock('Tx_Mklib_Database_Connection', ['doUpdate', 'doDelete']);
    }
}
