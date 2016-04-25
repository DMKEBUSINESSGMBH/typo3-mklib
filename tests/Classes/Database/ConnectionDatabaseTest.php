<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Michael Wagner
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
tx_rnbase::load('tx_mklib_tests_Util');
tx_rnbase::load('Tx_Mklib_Database_Connection');
tx_rnbase::load('tx_mklib_tests_DBTestCaseSkeleton');

/**
 * Tx_Mklib_Database_ConnectionDatabaseTest
 *
 * @package 		TYPO3
 * @subpackage	 	mklib
 * @author 			Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class Tx_Mklib_Database_ConnectionDatabaseTest extends tx_mklib_tests_DBTestCaseSkeleton {

	private static $hooks = array();

	private $logBackup;

	private $ignoreTablesBackup;

	/**
	 * Klassenkonstruktor
	 *
	 * @param string $name
	 */
	public function __construct ($name=null) {
		parent::__construct($name);

		// devlog erstmal deaktivieren,
		// da Prozesse auserhalb des Tests auch darauf zugreifen!
		tx_mklib_tests_Util::disableDevlog();
	}

	/**
	 * setUp() = init DB etc.
	 */
	protected function setUp() {
		parent::setUp();

		$ttContentExtension = tx_rnbase_util_TYPO3::isTYPO62OrHigher() ? 'frontend' : 'cms';
		$this->importExtensions(array($ttContentExtension));

		// logging aktivieren
		tx_mklib_tests_Util::storeExtConf();
		tx_mklib_tests_Util::setExtConfVar('logDbHandler', 1);

		// Hooks leer machen da die aus anderen extensions stören könnten
		self::$hooks['rn_base']['util_db_do_insert_post'] =
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_insert_post'];
		self::$hooks['rn_base']['util_db_do_update_post'] =
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_update_post'];
		self::$hooks['rn_base']['util_db_do_delete_pre'] =
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_delete_pre'];
		self::$hooks['core']['devlog'] = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['devLog'];

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_insert_post'] = array();
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_update_post'] = array();
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_delete_pre'] = array();
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['devLog'] = array(
			'Tx_Mklib_Database_ConnectionDatabaseTestDevlog->devLog'
		);

		$property = new ReflectionProperty('Tx_Mklib_Database_Connection', 'log');
		$property->setAccessible(TRUE);
		$this->logBackup = $property->getValue(tx_rnbase::makeInstance('Tx_Mklib_Database_Connection'));

		$property = new ReflectionProperty('Tx_Mklib_Database_Connection', 'ignoreTables');
		$property->setAccessible(TRUE);
		$this->ignoreTablesBackup = $property->getValue(tx_rnbase::makeInstance('Tx_Mklib_Database_Connection'));
	}

	/**
	 * tearDown() = destroy DB etc.
	 */
	protected function tearDown () {
		parent::tearDown();

		tx_mklib_tests_Util::restoreExtConf();

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_insert_post'] =
			self::$hooks['rn_base']['util_db_do_insert_post'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_update_post'] =
			self::$hooks['rn_base']['util_db_do_update_post'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_delete_pre'] =
			self::$hooks['rn_base']['util_db_do_delete_pre'];

		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_div.php']['devLog'] = self::$hooks['core']['devlog'];

		$property = new ReflectionProperty('Tx_Mklib_Database_Connection', 'log');
		$property->setAccessible(TRUE);
		$property->setValue(tx_rnbase::makeInstance('Tx_Mklib_Database_Connection'), $this->logBackup);

		$this->restoreIgnoreTablesProperty();
	}

	/**
	 * @return void
	 */
	protected function restoreIgnoreTablesProperty() {
		$property = new ReflectionProperty('Tx_Mklib_Database_Connection', 'ignoreTables');
		$property->setAccessible(TRUE);
		$property->setValue(tx_rnbase::makeInstance('Tx_Mklib_Database_Connection'), $this->ignoreTablesBackup);
	}

	/**
	 * @group integration
	 */
	public function testInsertTtContent(){
		// logging deaktivieren
		tx_mklib_tests_Util::setExtConfVar('logDbHandler', 0);

		$aValues = array(
				'uid' => 20,
				'pid' => 128,
				'hidden' => 0,
				'deleted' => 0,
				'bodytext' => 'Test!'
			);
		Tx_Mklib_Database_ConnectionDatabaseTestDevlog::devLog(array('before insert ' . __METHOD__));
		tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doInsert('tt_content', $aValues);

		$ttContent = tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doSelect('*', 'tt_content', array('enablefieldsoff' => true));

		self::assertEquals(1, count($ttContent), 'tt_content wurde nicht in die Datenbank eingefügt!');
		self::assertEquals(20, $ttContent[0]['uid'], 'tt_content hat die Falsche UID!');
		self::assertEquals(128, $ttContent[0]['pid'], 'tt_content hat die Falsche PID!');

		self::assertEquals(
			array('before insert ' . __METHOD__), Tx_Mklib_Database_ConnectionDatabaseTestDevlog::$lastLogData,
			'Es wurde ein neuer Eintrag ans devlog gegeben nachdem insert'
		);
	}

	/**
	 * @group integration
	 */
	public function testInsertTtContentWithDevLogAndIgnoreTable(){
		// logging für tt_content deaktivieren
		tx_mklib_tests_Util::setExtConfVar('logDbIgnoreTables', 'tt_content');

		$aValues = array(
				'uid' => 20,
				'pid' => 128,
				'hidden' => 0,
				'deleted' => 0,
				'bodytext' => 'Test!'
			);
		Tx_Mklib_Database_ConnectionDatabaseTestDevlog::devLog(array('before insert ' . __METHOD__));
		tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doInsert('tt_content', $aValues);

		$ttContent = tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doSelect('*', 'tt_content', array('enablefieldsoff' => true));

		self::assertEquals(1, count($ttContent), 'tt_content wurde nicht in die Datenbank eingefügt!');
		self::assertEquals(20, $ttContent[0]['uid'], 'tt_content hat die Falsche UID!');
		self::assertEquals(128, $ttContent[0]['pid'], 'tt_content hat die Falsche PID!');

		self::assertEquals(
			array('before insert ' . __METHOD__), Tx_Mklib_Database_ConnectionDatabaseTestDevlog::$lastLogData,
			'Es wurde ein neuer Eintrag ans devlog gegeben nachdem insert'
		);
	}

	/**
	 * @group integration
	 */
	public function testInsertTtContentWithDevLog(){
		$aValues = array(
				'uid' => 20,
				'pid' => 128,
				'hidden' => 0,
				'deleted' => 0,
				'bodytext' => 'Test!'
			);
		tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doInsert('tt_content', $aValues);

		$ttContent = tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doSelect('*', 'tt_content', array('enablefieldsoff' => true));

		self::assertEquals(1, count($ttContent), 'tt_content wurde nicht in die Datenbank eingefügt!');
		self::assertEquals(20, $ttContent[0]['uid'], 'tt_content hat die Falsche UID!');
		self::assertEquals(128, $ttContent[0]['pid'], 'tt_content hat die Falsche PID!');

		$lastLogData = Tx_Mklib_Database_ConnectionDatabaseTestDevlog::$lastLogData;
		self::assertSame('mklib', $lastLogData['extKey'], 'Falscher extKey in devlog daten');
		self::assertSame('doInsert(tt_content)', $lastLogData['msg'], 'Falsche Nachricht in devlog daten');
		self::assertSame(1, $lastLogData['severity'], 'Falsche severity in devlog daten');
		self::assertSame('tt_content', $lastLogData['dataVar']['tablename'], 'tablename falsch in dataVar der devlog daten');
		self::assertSame(128, $lastLogData['dataVar']['values']['pid'], 'values|pid falsch in dataVar der devlog daten');
	}

	/**
	 * @group integration
	 */
	public function testUpdateTtContentWithDevLog(){
		// Daten eintragen!
		$this->testInsertTtContentWithDevLog();

		$aValues = array(
				'pid' => 256,
				'bodytext' => 'geändert!'
			);
		tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doUpdate('tt_content', 'uid=20', $aValues);

		$ttContent = tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doSelect('*', 'tt_content', array('enablefieldsoff' => true));

		self::assertEquals(1, count($ttContent), 'tt_content wurde nicht in die Datenbank eingefügt!');
		self::assertEquals(20, $ttContent[0]['uid'], 'tt_content hat die Falsche UID!');
		self::assertEquals(256, $ttContent[0]['pid'], 'tt_content hat die Falsche PID!');

		$lastLogData = Tx_Mklib_Database_ConnectionDatabaseTestDevlog::$lastLogData;
		self::assertSame('mklib', $lastLogData['extKey'], 'Falscher extKey in devlog daten');
		self::assertSame('doUpdate(tt_content)', $lastLogData['msg'], 'Falsche Nachricht in devlog daten');
		self::assertSame(1, $lastLogData['severity'], 'Falsche severity in devlog daten');
		self::assertSame('tt_content', $lastLogData['dataVar']['tablename'], 'tablename falsch in dataVar der devlog daten');
		self::assertSame(256, $lastLogData['dataVar']['values']['pid'], 'values|pid falsch in dataVar der devlog daten');
	}

	/**
	 * @group integration
	 */
	public function testUpdateTtContentWithIgnoreTables(){
		// Daten eintragen!
		$this->testInsertTtContentWithDevLog();
		// logging für tt_content deaktivieren
		tx_mklib_tests_Util::setExtConfVar('logDbIgnoreTables', 'tt_content');

		$this->restoreIgnoreTablesProperty();

		$aValues = array(
				'pid' => 256,
				'bodytext' => 'geändert!'
			);
		Tx_Mklib_Database_ConnectionDatabaseTestDevlog::devLog(array('before update ' . __METHOD__));
		tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doUpdate('tt_content', 'uid=20', $aValues);

		$ttContent = tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doSelect('*', 'tt_content', array('enablefieldsoff' => true));

		self::assertEquals(1, count($ttContent), 'tt_content wurde nicht in die Datenbank eingefügt!');
		self::assertEquals(20, $ttContent[0]['uid'], 'tt_content hat die Falsche UID!');
		self::assertEquals(256, $ttContent[0]['pid'], 'tt_content hat die Falsche PID!');

		self::assertEquals(
			array('before update ' . __METHOD__), Tx_Mklib_Database_ConnectionDatabaseTestDevlog::$lastLogData,
			'Es wurde ein neuer Eintrag ans devlog gegeben nachdem update'
		);
	}
}

class Tx_Mklib_Database_ConnectionDatabaseTestDevlog {

	/**
	 * @var array
	 */
	public static $lastLogData = array();

	/**
	 * @param array $logData
	 * @return void
	 */
	public static function devLog(array $logData) {
		self::$lastLogData = $logData;
	}
}