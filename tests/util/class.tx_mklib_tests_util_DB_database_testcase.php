<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@das-medienkombinat.de>
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
tx_rnbase::load('tx_mklib_tests_Util');
tx_rnbase::load('tx_mklib_util_DB');

/** Kindklasse der eigentlichen UtilDB, um die Variable $log von setzen zu können */
class tx_mklib_util_testDB extends tx_mklib_util_DB {
	public static function clearLogCache(){ self::$log = -1; self::$ignoreTables = -1; }
}

/**
 * DB util tests
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 *
 * @group integration
 */
class tx_mklib_tests_util_DB_database_testcase extends tx_phpunit_database_testcase {
	protected $workspaceIdAtStart;
	protected $db;

	private static $hooks = array();

	/**
	 * Klassenkonstruktor
	 *
	 * @param string $name
	 */
	public function __construct ($name=null) {
		global $TYPO3_DB, $BE_USER;

		parent::__construct ($name);
		$TYPO3_DB->debugOutput = TRUE;

		$this->workspaceIdAtStart = $BE_USER->workspace;
		$BE_USER->setWorkspace(0);

		// devlog erstmal deaktivieren,
		// da Prozesse auserhalb des Tests auch darauf zugreifen!
		tx_mklib_tests_Util::disableDevlog();

	}

	/**
	 * setUp() = init DB etc.
	 */
	public function setUp() {
		$this->createDatabase();
		// assuming that test-database can be created otherwise PHPUnit will skip the test
		$this->db = $this->useTestDatabase();
		$this->importStdDB();
		$ttContentExtension = tx_rnbase_util_TYPO3::isTYPO62OrHigher() ? 'frontend' : 'cms';
		$this->importExtensions(array($ttContentExtension, 'devlog'));

		// devlog wieder aktivieren
		tx_mklib_tests_Util::disableDevlog('devlog', false);

		// logging aktivieren
		tx_mklib_tests_Util::storeExtConf();
		tx_mklib_tests_Util::setExtConfVar('logDbHandler', 1);

		//logging zurücksetzen
		tx_mklib_util_testDB::clearLogCache();

		//wir setzen noch das min Log Level auf -1 damit
		//systemeinstellungen nicht hereinspielen und alles geloggt wird
		tx_mklib_tests_Util::storeExtConf('devlog');
		tx_mklib_tests_Util::setExtConfVar('minLogLevel', -1, 'devlog');

		// Hooks leer machen da die aus anderen extensions stören könnten
		self::$hooks['rn_base']['util_db_do_insert_post'] =
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_insert_post'];
		self::$hooks['rn_base']['util_db_do_update_post'] =
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_update_post'];
		self::$hooks['rn_base']['util_db_do_delete_pre'] =
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_delete_pre'];

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_insert_post'] = array();
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_update_post'] = array();
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_delete_pre'] = array();
	}

	/**
	 * tearDown() = destroy DB etc.
	 */
	public function tearDown () {
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);

		$GLOBALS['BE_USER']->setWorkspace($this->workspaceIdAtStart);

		// devlog wieder deaktivieren
		tx_mklib_tests_Util::disableDevlog();

		// ext conf zurückspielen aktivieren
		tx_mklib_tests_Util::restoreExtConf();
		tx_mklib_tests_Util::restoreExtConf('devlog');

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_insert_post'] =
			self::$hooks['rn_base']['util_db_do_insert_post'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_update_post'] =
			self::$hooks['rn_base']['util_db_do_update_post'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['util_db_do_delete_pre'] =
			self::$hooks['rn_base']['util_db_do_delete_pre'];
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
		tx_mklib_util_testDB::doInsert('tt_content', $aValues);

		$aDevLog = tx_mklib_util_testDB::doSelect('*', 'tx_devlog', array('enablefieldsoff' => true));
//		$aTtContent = tx_mklib_util_testDB::doSelect('*', 'tt_content', array('where' => 'uid=\'' . $aValues['uid'] . '\'','enablefieldsoff' => true));
		$aTtContent = tx_mklib_util_testDB::doSelect('*', 'tt_content', array('enablefieldsoff' => true));

		$this->assertEquals(1, count($aTtContent), 'tt_content wurde nicht in die Datenbank eingefügt!');
		$this->assertEquals(20, $aTtContent[0]['uid'], 'tt_content hat die Falsche UID!');
		$this->assertEquals(128, $aTtContent[0]['pid'], 'tt_content hat die Falsche PID!');

		$this->assertEquals(0, count($aDevLog), 'tx_devlog wurde in die Datenbank geschrieben!');
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
		tx_mklib_util_testDB::doInsert('tt_content', $aValues);

		$aDevLog = tx_mklib_util_testDB::doSelect('*', 'tx_devlog', array('enablefieldsoff' => true));
//		$aTtContent = tx_mklib_util_testDB::doSelect('*', 'tt_content', array('where' => 'uid=\'' . $aValues['uid'] . '\'','enablefieldsoff' => true));
		$aTtContent = tx_mklib_util_testDB::doSelect('*', 'tt_content', array('enablefieldsoff' => true));

		$this->assertEquals(1, count($aTtContent), 'tt_content wurde nicht in die Datenbank eingefügt!');
		$this->assertEquals(20, $aTtContent[0]['uid'], 'tt_content hat die Falsche UID!');
		$this->assertEquals(128, $aTtContent[0]['pid'], 'tt_content hat die Falsche PID!');

		$this->assertEquals(0, count($aDevLog), 'tx_devlog wurde in die Datenbank geschrieben!');
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
		tx_mklib_util_testDB::doInsert('tt_content', $aValues);

		$aDevLog = tx_mklib_util_testDB::doSelect('*', 'tx_devlog', array('enablefieldsoff' => true));
//		$aTtContent = tx_mklib_util_testDB::doSelect('*', 'tt_content', array('where' => 'uid=\'' . $aValues['uid'] . '\'','enablefieldsoff' => true));
		$aTtContent = tx_mklib_util_testDB::doSelect('*', 'tt_content', array('enablefieldsoff' => true));

		$this->assertEquals(1, count($aTtContent), 'tt_content wurde nicht in die Datenbank eingefügt!');
		$this->assertEquals(20, $aTtContent[0]['uid'], 'tt_content hat die Falsche UID!');
		$this->assertEquals(128, $aTtContent[0]['pid'], 'tt_content hat die Falsche PID!');

		$this->assertEquals(1, count($aDevLog), 'tx_devlog wurde nicht in die Datenbank eingefügt!');
		$this->assertEquals('mklib', $aDevLog[0]['extkey'], 'Falscher extkey in tx_devlog!');
		$this->assertEquals('doInsert(tt_content)', $aDevLog[0]['msg'], 'Falsche msg in tx_devlog!');
		$this->assertEquals(true, !empty($aDevLog[0]['data_var']), 'data_var in tx_devlog nicht gesetzt!');

		$aDevLogData = unserialize($aDevLog[0]['data_var']);
		$this->assertEquals('tt_content', $aDevLogData['tablename'], 'data_var: tablename falsch!');
		$this->assertEquals(128, $aDevLogData['values']['pid'], 'data_var: values|pid falsch!');
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
		tx_mklib_util_testDB::doUpdate('tt_content', 'uid=20', $aValues);

		$aDevLog = tx_mklib_util_testDB::doSelect('*', 'tx_devlog', array('enablefieldsoff' => true));
		$aTtContent = tx_mklib_util_testDB::doSelect('*', 'tt_content', array('enablefieldsoff' => true));

		$this->assertEquals(1, count($aTtContent), 'tt_content wurde nicht in die Datenbank eingefügt!');
		$this->assertEquals(20, $aTtContent[0]['uid'], 'tt_content hat die Falsche UID!');
		$this->assertEquals(256, $aTtContent[0]['pid'], 'tt_content hat die Falsche PID!');

		$this->assertEquals(2, count($aDevLog), 'tx_devlog wurde nicht in die Datenbank eingefügt!');
		$this->assertEquals('mklib', $aDevLog[1]['extkey'], 'Falscher extkey in tx_devlog!');
		$this->assertEquals('doUpdate(tt_content)', $aDevLog[1]['msg'], 'Falsche msg in tx_devlog!');
		$this->assertEquals(true, !empty($aDevLog[1]['data_var']), 'data_var in tx_devlog nicht gesetzt!');

		$aDevLogData = unserialize($aDevLog[1]['data_var']);
		$this->assertEquals('tt_content', $aDevLogData['tablename'], 'data_var: tablename falsch!');
		$this->assertEquals(256, $aDevLogData['values']['pid'], 'data_var: values|pid falsch!');
	}

	/**
	 * @group integration
	 */
	public function testUpdateTtContentWithIgnoreTables(){
		// Daten eintragen!
		$this->testInsertTtContentWithDevLog();
		// logging für tt_content deaktivieren
		tx_mklib_tests_Util::setExtConfVar('logDbIgnoreTables', 'tt_content');
		// db cache löschen
		tx_mklib_util_testDB::clearLogCache();

		$aValues = array(
				'pid' => 256,
				'bodytext' => 'geändert!'
			);
		tx_mklib_util_testDB::doUpdate('tt_content', 'uid=20', $aValues);

		$aDevLog = tx_mklib_util_testDB::doSelect('*', 'tx_devlog', array('enablefieldsoff' => true));
		$aTtContent = tx_mklib_util_testDB::doSelect('*', 'tt_content', array('enablefieldsoff' => true));

		$this->assertEquals(1, count($aTtContent), 'tt_content wurde nicht in die Datenbank eingefügt!');
		$this->assertEquals(20, $aTtContent[0]['uid'], 'tt_content hat die Falsche UID!');
		$this->assertEquals(256, $aTtContent[0]['pid'], 'tt_content hat die Falsche PID!');

		$this->assertEquals(1, count($aDevLog), 'tx_devlog wurde in die Datenbank eingefügt!');
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_DB_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_DB_testcase.php']);
}