<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
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
tx_rnbase::load('tx_mklib_util_Date');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_Date_testcase extends tx_phpunit_testcase {

  /**
   * Testen ob getTimestampByCalendarWeekDayAndYear den korrekten timestamp zurück gibt
   */
  public function testGetTimestampByCalendarWeekDayAndYearReturnsCorrectTimestamps(){
    $this->assertEquals(1577660400,tx_mklib_util_Date::getTimestampByCalendarWeekDayAndYear(1,1,2020),'Der zurückgegebene timestamp für den ersten Montag in der ersten Kalenderwoche 2020 ist nicht korrekt.');
    $this->assertEquals(1434060000,tx_mklib_util_Date::getTimestampByCalendarWeekDayAndYear(5,24,2015),'Der zurückgegebene timestamp für den ersten Freitag in der 24. Kalenderwoche 2015 ist nicht korrekt.');
  }
  /**
   * Testen ob isMySQLDate funktioniert
   */
  public function testIsMysqlDate(){
    $this->assertFalse(tx_mklib_util_Date::isMySQLDate('0000-00-00'), '0000-00-00 ist kein Datum.');
    $this->assertTrue(tx_mklib_util_Date::isMySQLDate('1983-07-05'), '1983-07-05 ist ein Datum.');
  }
  /**
   * Testen ob isDateTime funktioniert
   */
  public function testIsDateTime(){
    $this->assertFalse(tx_mklib_util_Date::isDateTime('1983-07-05 12:24.48'), '1983-07-05 12:24 ist kein Datetime.');
    $this->assertTrue(tx_mklib_util_Date::isDateTime('1983-07-05 12:24:48'), '1983-07-05 12:24:48 ist ein Datetime.');
  }
  
  /**
   * Testen ob getCalendarWeekDayAndYearByTimestamp das korrekte datenarray zurück gibt
   */
  public function testGetCalendarWeekDayAndYearByTimestampReturnsCorrectData(){
  	$res = tx_mklib_util_Date::getCalendarWeekDayAndYearByTimestamp(1577664000);

    $this->assertEquals(1,$res['weekday'],'Der zurückgegebene array enthält nicht den korrekten Wochentag.');
    $this->assertEquals(1,$res['week'],'Der zurückgegebene array enthält nicht die korrekte Woche.');
    $this->assertEquals(2019,$res['year'],'Der zurückgegebene array enthält nicht das korrekte Jahr.');
    $this->assertEquals('30-12-2019',$res['date'],'Der zurückgegebene array enthält nicht das korrekte gesamte Datum.');

    $res = tx_mklib_util_Date::getCalendarWeekDayAndYearByTimestamp(1434063600);

    $this->assertEquals(5,$res['weekday'],'Der zurückgegebene array enthält nicht den korrekten Wochentag.');
    $this->assertEquals(24,$res['week'],'Der zurückgegebene array enthält nicht die korrekte Woche.');
    $this->assertEquals(2015,$res['year'],'Der zurückgegebene array enthält nicht das korrekte Jahr.');
    $this->assertEquals('12-06-2015',$res['date'],'Der zurückgegebene array enthält nicht das korrekte gesamte Datum.');
  }
  
	/**
   	 * Testen ob getTimesInTimeRange die korrekten Tage zurück gibt
   	 */
  	public function testGetTimesInTimeRangeReturnsCorrectDays(){
  		//1297206000 = 09.02.2011 00:00 Mittwoch 39
 		//1297810800 = 16.02.2011 00:00 Mittwoch 46
	  	$days = tx_mklib_util_Date::getTimesInTimeRange(1297206000,1297810800,'z|Y');
	  	
	  	$this->assertEquals(7,count($days),'Der zurückgegebene array enthält nicht die richtige Anzahl an Tagen.');
	  	$this->assertEquals('39|2011',$days[0],'Der erste Tag stimmt nicht.');
	  	$this->assertEquals('40|2011',$days[1],'Der zweite Tag stimmt nicht.');
	  	$this->assertEquals('41|2011',$days[2],'Der dritte Tag stimmt nicht.');
	  	$this->assertEquals('42|2011',$days[3],'Der vierte Tag stimmt nicht.');
	  	$this->assertEquals('43|2011',$days[4],'Der fünfte Tag stimmt nicht.');
	  	$this->assertEquals('44|2011',$days[5],'Der sechste Tag stimmt nicht.');
	  	$this->assertEquals('45|2011',$days[6],'Der siebte Tag stimmt nicht.');//Dienstag
	  	
	  	//1293376600 = 26.12.2010 00:00
 		//1294067800 = 03.01.2011 00:00
	  	$days = tx_mklib_util_Date::getTimesInTimeRange(1293376600,1294067800,'z|Y');
	  	
	  	$this->assertEquals(8,count($days),'Der zurückgegebene array enthält nicht die richtige Anzahl an Tagen.');
	  	$this->assertEquals('359|2010',$days[0],'Der erste Tag stimmt nicht.');
	  	$this->assertEquals('360|2010',$days[1],'Der zweite Tag stimmt nicht.');
	  	$this->assertEquals('361|2010',$days[2],'Der dritte Tag stimmt nicht.');
	  	$this->assertEquals('362|2010',$days[3],'Der vierte Tag stimmt nicht.');
	  	$this->assertEquals('363|2010',$days[4],'Der fünfte Tag stimmt nicht.');
	  	$this->assertEquals('364|2010',$days[5],'Der sechste Tag stimmt nicht.');
	  	$this->assertEquals('0|2011',$days[6],'Der siebte Tag stimmt nicht.');
	  	$this->assertEquals('1|2011',$days[7],'Der achte Tag stimmt nicht.');
  	}
  	public function testGetTimesInTimeRangeHandlesSummertimeCorrect(){
  		tx_rnbase::load('tx_rnbase_util_Dates');
  		// In dieser Zeitspanne wurde der 30.11.2011 doppelt im Array geliefert (Tag 302).
  		$start = tx_rnbase_util_Dates::date_mysql2tstamp('2011-07-13');
  		$end = tx_rnbase_util_Dates::date_mysql2tstamp('2012-03-17');
  		$days = tx_mklib_util_Date::getTimesInTimeRange($start,$end);
  		$daysUnique = array_unique($days);
  		$this->assertEquals(count($daysUnique), count($days), 'Es wurden doppelte Tage erzeugt.');
  	}
  	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Date_testcase.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Date_testcase.php']);
}