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
 * Util Methoden für die Date.
 *
 * @author  Hannes Bochmann
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_Date
{
    public static $days = [
                        1 => 'Monday',
                        2 => 'Tuesday',
                        3 => 'Wednesday',
                        4 => 'Thursday',
                        5 => 'Friday',
                        6 => 'Saturday',
                        7 => 'Sunday',
                    ];
    // @TODO: wär das nicht besser in der locallang.xlf aufgehoben!?
    // \Sys25\RnBase\Configuration\Processor::getLL()
    public static $aGermanDays = [
                        1 => 'Montag',
                        2 => 'Dienstag',
                        3 => 'Mittwoch',
                        4 => 'Donnerstag',
                        5 => 'Freitag',
                        6 => 'Samstag',
                        7 => 'Sonntag',
                    ];

    /**
     * Gibt einen Unixtimestamp für einen Wochentag, eine Kalenderwoche und ein Jahr zurück.
     *
     * @param int $day
     * @param int $calendarWeek
     * @param int $year
     *
     * @return int
     */
    public static function getTimestampByCalendarWeekDayAndYear($day, $calendarWeek, $year)
    {
        $weekDay = self::$days[$day];

        $time = strtotime('4 January '.$year);
        if (0 != date('w', $time)) {// wenn der 4.Januar von dem Jahr keine Sonntag ist, holen wir den timestamp vom ersten Sonntag
            $time = strtotime('last Sunday', $time);
        }

        $time = strtotime('next '.$weekDay, $time); // anschliessend holen wir den timestamp für das erste Auftreten des Veröffentlichungstags

        // wenn es nicht die erste Kalenderwoche ist sonder die 2.
        if (2 == date('W', $time)) {
            $time = strtotime('last '.$weekDay, $time);
        } // gehen wir eine Woche zurück
        elseif (52 == date('W', $time) || 53 == date('W', $time)) {// oder die letzte des vorhergehenden Jahres
            $time = strtotime('next '.$weekDay, $time);
        }// gehen wir eine Woche weiter

        // und dann noch das Datum der jeweilgen Woche holen
        // TODO: Zeitzone beachten
        return strtotime('+'.($calendarWeek - 1).' weeks', $time);
    }

    /**
     * Liefert ein Array zurück das folgendes enthält (=key): weekday,week,year,date
     * Wenn kein timestamp gegeben wurde, wird die aktuelle zeit genommen.
     *
     * @param int $timestamp
     *
     * @return array
     */
    public static function getCalendarWeekDayAndYearByTimestamp($timestamp = null)
    {
        if (empty($timestamp)) {
            $timestamp = time();
        }

        // das gesamte datum
        $return['date'] = date('d-m-Y', $timestamp);
        // numerischer Wochentag
        $return['weekday'] = date('w', $timestamp);
        // die Kalenderwoche
        $return['week'] = date('W', $timestamp);
        // das Jahr
        $return['year'] = date('Y', $timestamp);

        return $return;
    }

    /**
     * Gibt ein Array mit alle Zeiten innerhalb der Spanne zurück
     * im gegebenen Format zurück
     * Jahrestage => der 1. februar wäre der 32. tag.
     *
     * @see tx_mklib_tests_util_Date_testcase::testgetTimesInTimeRangeReturnsCorrectDays
     *
     * @param int    $starttime
     * @param int    $endtime
     * @param string $format
     *
     * @return array
     */
    public static function getTimesInTimeRange($starttime, $endtime, $format = 'z')
    {
        for ($index = $starttime; $index < $endtime;) {
            $days[] = date($format, $index);
            $index = strtotime('+1 days', $index);
        }

        return $days;
    }

    /**
     * Prüft, ob es sich bei dem string um ein Datum handelt.
     *
     * @param string $date
     *
     * @return bool
     */
    public static function isMySQLDate($date)
    {
        return !(
            '0000-00-00' === $date
            || strlen($date) < 2
            || 3 !== count(explode('-', $date))
        );
    }

    /**
     * prüft String ob es sich um Datetime handelt
     * Format: YYYY-mm-dd HH:ii:ss.
     *
     * @param string $datetime
     *
     * @return bool
     */
    public static function isDateTime($datetime)
    {
        return !(
            19 !== strlen($datetime)
            || 2 !== count(explode(' ', $datetime))
            || 3 !== count(explode(':', $datetime))
            || 3 !== count(explode('-', $datetime))
        );
    }

    /**
     * DateTimeZone ist wichtig, falls nicht dann:
     *  It is not safe to rely on the system's timezone settings.
     *  Please use the date.timezone setting, the TZ envir onment variable
     *  or the date_default_timezone_set() function. In case you used any
     *  of those methods and you are still getting this warning, you most
     *  likely missp elled the timezone identifier. We selected
     *  'Europe/Paris' for '2.0/DST' instead.
     *
     * @param string|DateTimeZone $timezone
     *
     * @return DateTimeZone
     */
    public static function getDateTimeZone($timezone = null)
    {
        static $europeBerlin = null;
        if (is_null($timezone) && is_null($europeBerlin)) {
            $europeBerlin = new DateTimeZone('Europe/Berlin');
        }

        return is_null($timezone) ? $europeBerlin : new DateTimeZone($timezone);
    }

    /**
     * @param string|DateTimeZone $date
     * @param string              $timezone
     *
     * @return DateTime
     */
    public static function getDateTime($date = null, $timezone = null)
    {
        $timezone = is_object($timezone) ? $timezone : self::getDateTimeZone($timezone);

        return new DateTime($date, $timezone);
    }

    /**
     * Liefert dei Scriptausführungszeit.
     *
     * @param string $format
     * @param bool   $useGMT
     *
     * @return string
     */
    public static function getExecDate($format = 'U', $useGMT = false)
    {
        $tstamp = isset($GLOBALS['EXEC_TIME']) ? $GLOBALS['EXEC_TIME'] : time();

        return $useGMT ? gmdate($format, $tstamp) : date($format, $tstamp);
    }
}
