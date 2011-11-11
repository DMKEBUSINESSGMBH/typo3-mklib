<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

/**
 * Util Methoden für die Date.
 * @author	Hannes Bochmann
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_Date {
	public static $days = array(
						1=>"Monday",
						2=>"Tuesday",
						3=>"Wednesday",
						4=>"Thursday",
						5=>"Friday",
						6=>"Saturday",
						7=>"Sunday"
					);
	// @TODO: wär das nicht besser in der locallang.xml aufgehoben!?
	// tx_rnbase_configurations::getLL()
	public static $aGermanDays = array(
						1=>"Montag",
						2=>"Dienstag",
						3=>"Mittwoch",
						4=>"Donnerstag",
						5=>"Freitag",
						6=>"Samstag",
						7=>"Sonntag"
					);

	/**
	 * Gibt einen Unixtimestamp für einen Wochentag, eine Kalenderwoche und ein Jahr zurück
	 *
	 * @param int $day
     * @param int $calendarWeek
   	 * @param int $year
   	 *
   	 * @return int
	 */
	public static function getTimestampByCalendarWeekDayAndYear($day,$calendarWeek,$year){
		$weekDay = self::$days[$day];

		$time = strtotime("4 January ".$year);
		if(date('w', $time) != 0)//wenn der 4.Januar von dem Jahr keine Sonntag ist, holen wir den timestamp vom ersten Sonntag
			$time = strtotime("last Sunday", $time);

		$time = strtotime("next ".$weekDay, $time);//anschliessend holen wir den timestamp für das erste Auftreten des Veröffentlichungstags

		//wenn es nicht die erste Kalenderwoche ist sonder die 2.
		if(date('W', $time) == 2)
			$time = strtotime("last ".$weekDay, $time);//gehen wir eine Woche zurück
		elseif(date('W', $time) == 52 || date('W', $time) == 53)//oder die letzte des vorhergehenden Jahres
			$time = strtotime("next ".$weekDay, $time);//gehen wir eine Woche weiter

		//und dann noch das Datum der jeweilgen Woche holen
		//TODO: Zeitzone beachten
		return strtotime("+" . ($calendarWeek - 1). " weeks", $time);
	}

	/**
	 * Liefert ein Array zurück das folgendes enthält (=key): weekday,week,year,date
	 * Wenn kein timestamp gegeben wurde, wird die aktuelle zeit genommen
	 *
	 * @param int $timestamp
   	 *
   	 * @return array
	 */
	public static function getCalendarWeekDayAndYearByTimestamp($timestamp = null){
		if(empty($timestamp)) $timestamp = time();

		//das gesamte datum
		$return['date'] = date('d-m-Y',$timestamp);
		//numerischer Wochentag
		$return['weekday'] = date('w',$timestamp);
		//die Kalenderwoche
		$return['week'] = date('W',$timestamp);
		//das Jahr
		$return['year'] = date('Y',$timestamp);

		return $return;

	}
	
	/**
	 * Gibt ein Array mit alle Zeiten innerhalb der Spanne zurück 
	 * im gegebenen Format zurück
	 * Jahrestage => der 1. februar wäre der 32. tag
	 * @see tx_mklib_tests_util_Date_testcase::testgetTimesInTimeRangeReturnsCorrectDays
	 * 
	 * @param 	int 	$starttime
	 * @param 	int 	$endtime
	 * @param 	string 	$format
	 * @return 	array
	 */
	public static function getTimesInTimeRange($starttime, $endtime, $format='z') {
		for ($index = $starttime; $index < $endtime;) {
			$days[] = date($format,$index);
			$index = strtotime('+1 days', $index);
		}
  		return $days;
	}
	
	/**
	 * Prüft, ob es sich bei dem string um ein Datum handelt.
	 * 
	 * @param 	string 	$date
	 * @return 	boolean
	 */
	public static function isMySQLDate($date){
		return !(
			$date === '0000-00-00'
			|| strlen($date) < 2
			|| count(explode('-', $date)) !== 3
		);
	}
	
	/**
	 * prüft String ob es sich um Datetime handelt
	 * Format: YYYY-mm-dd HH:ii:ss
	 * @param string $datetime
	 * @return boolean
	 */
	public static function isDateTime($datetime){
		return !(
			strlen($datetime)!==19
			|| count(explode(' ', $datetime))!==2
			|| count(explode(':', $datetime))!==3
			|| count(explode('-', $datetime))!==3
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Date.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Date.php']);
}
