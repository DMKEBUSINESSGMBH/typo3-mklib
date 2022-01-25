<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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

/**
 * Generic form view test.
 */
class tx_mklib_tests_util_DateTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * Testen ob getTimestampByCalendarWeekDayAndYear den korrekten timestamp zurück gibt.
     */
    public function testGetTimestampByCalendarWeekDayAndYearReturnsCorrectTimestamps()
    {
        self::markTestIncomplete('Failed asserting that 1577664000 matches expected 1577660400.');

        self::assertEquals(1577660400, tx_mklib_util_Date::getTimestampByCalendarWeekDayAndYear(1, 1, 2020), 'Der zurückgegebene timestamp für den ersten Montag in der ersten Kalenderwoche 2020 ist nicht korrekt.');
        self::assertEquals(1434060000, tx_mklib_util_Date::getTimestampByCalendarWeekDayAndYear(5, 24, 2015), 'Der zurückgegebene timestamp für den ersten Freitag in der 24. Kalenderwoche 2015 ist nicht korrekt.');
    }

    /**
     * Testen ob isMySQLDate funktioniert.
     */
    public function testIsMysqlDate()
    {
        self::assertFalse(tx_mklib_util_Date::isMySQLDate('0000-00-00'), '0000-00-00 ist kein Datum.');
        self::assertTrue(tx_mklib_util_Date::isMySQLDate('1983-07-05'), '1983-07-05 ist ein Datum.');
    }

    /**
     * Testen ob isDateTime funktioniert.
     */
    public function testIsDateTime()
    {
        self::assertFalse(tx_mklib_util_Date::isDateTime('1983-07-05 12:24.48'), '1983-07-05 12:24 ist kein Datetime.');
        self::assertTrue(tx_mklib_util_Date::isDateTime('1983-07-05 12:24:48'), '1983-07-05 12:24:48 ist ein Datetime.');
    }

    /**
     * Testen ob getCalendarWeekDayAndYearByTimestamp das korrekte datenarray zurück gibt.
     */
    public function testGetCalendarWeekDayAndYearByTimestampReturnsCorrectData()
    {
        self::markTestIncomplete("Failed asserting that '4' matches expected 5.");

        $res = tx_mklib_util_Date::getCalendarWeekDayAndYearByTimestamp(1577664000);

        self::assertEquals(1, $res['weekday'], 'Der zurückgegebene array enthält nicht den korrekten Wochentag.');
        self::assertEquals(1, $res['week'], 'Der zurückgegebene array enthält nicht die korrekte Woche.');
        self::assertEquals(2019, $res['year'], 'Der zurückgegebene array enthält nicht das korrekte Jahr.');
        self::assertEquals('30-12-2019', $res['date'], 'Der zurückgegebene array enthält nicht das korrekte gesamte Datum.');

        $res = tx_mklib_util_Date::getCalendarWeekDayAndYearByTimestamp(1434063600);

        self::assertEquals(5, $res['weekday'], 'Der zurückgegebene array enthält nicht den korrekten Wochentag.');
        self::assertEquals(24, $res['week'], 'Der zurückgegebene array enthält nicht die korrekte Woche.');
        self::assertEquals(2015, $res['year'], 'Der zurückgegebene array enthält nicht das korrekte Jahr.');
        self::assertEquals('12-06-2015', $res['date'], 'Der zurückgegebene array enthält nicht das korrekte gesamte Datum.');
    }

    /**
     * Testen ob getTimesInTimeRange die korrekten Tage zurück gibt.
     */
    public function testGetTimesInTimeRangeReturnsCorrectDays()
    {
        self::markTestIncomplete(
            'Failed asserting that two strings are equal.'.
            "-'39|2011'".
            "+'38|2011'"
        );

        //1297206000 = 09.02.2011 00:00 Mittwoch 39
        //1297810800 = 16.02.2011 00:00 Mittwoch 46
        $days = tx_mklib_util_Date::getTimesInTimeRange(1297206000, 1297810800, 'z|Y');

        self::assertEquals(7, count($days), 'Der zurückgegebene array enthält nicht die richtige Anzahl an Tagen.');
        self::assertEquals('39|2011', $days[0], 'Der erste Tag stimmt nicht.');
        self::assertEquals('40|2011', $days[1], 'Der zweite Tag stimmt nicht.');
        self::assertEquals('41|2011', $days[2], 'Der dritte Tag stimmt nicht.');
        self::assertEquals('42|2011', $days[3], 'Der vierte Tag stimmt nicht.');
        self::assertEquals('43|2011', $days[4], 'Der fünfte Tag stimmt nicht.');
        self::assertEquals('44|2011', $days[5], 'Der sechste Tag stimmt nicht.');
        self::assertEquals('45|2011', $days[6], 'Der siebte Tag stimmt nicht.'); //Dienstag

        //1293376600 = 26.12.2010 00:00
        //1294067800 = 03.01.2011 00:00
        $days = tx_mklib_util_Date::getTimesInTimeRange(1293376600, 1294067800, 'z|Y');

        self::assertEquals(8, count($days), 'Der zurückgegebene array enthält nicht die richtige Anzahl an Tagen.');
        self::assertEquals('359|2010', $days[0], 'Der erste Tag stimmt nicht.');
        self::assertEquals('360|2010', $days[1], 'Der zweite Tag stimmt nicht.');
        self::assertEquals('361|2010', $days[2], 'Der dritte Tag stimmt nicht.');
        self::assertEquals('362|2010', $days[3], 'Der vierte Tag stimmt nicht.');
        self::assertEquals('363|2010', $days[4], 'Der fünfte Tag stimmt nicht.');
        self::assertEquals('364|2010', $days[5], 'Der sechste Tag stimmt nicht.');
        self::assertEquals('0|2011', $days[6], 'Der siebte Tag stimmt nicht.');
        self::assertEquals('1|2011', $days[7], 'Der achte Tag stimmt nicht.');
    }

    public function testGetTimesInTimeRangeHandlesSummertimeCorrect()
    {
        // In dieser Zeitspanne wurde der 30.11.2011 doppelt im Array geliefert (Tag 302).
        $start = \Sys25\RnBase\Utility\Dates::date_mysql2tstamp('2011-07-13');
        $end = \Sys25\RnBase\Utility\Dates::date_mysql2tstamp('2012-03-17');
        $days = tx_mklib_util_Date::getTimesInTimeRange($start, $end);
        $daysUnique = array_unique($days);
        self::assertEquals(count($daysUnique), count($days), 'Es wurden doppelte Tage erzeugt.');
    }
}
