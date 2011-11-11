<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_srv
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
tx_rnbase::load('tx_mklib_util_ServiceRegistry');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_srv
 */
class tx_mklib_tests_srv_Wordlist_testcase extends tx_phpunit_database_testcase {
	protected $workspaceIdAtStart;
	protected $db;

	/**
	 * Klassenkonstruktor - BE-Workspace setzen
	 *
	 * @param unknown_type $name
	 */
	public function __construct ($name=null) {
		global $TYPO3_DB, $BE_USER;

		parent::__construct ($name);
		$TYPO3_DB->debugOutput = TRUE;

		$this->workspaceIdAtStart = $BE_USER->workspace;
		$BE_USER->setWorkspace(0);

		// devlog deaktivieren
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
		$this->importExtensions(array('cms','static_info_tables','mklib'));
		$fixturePath = tx_mklib_tests_Util::getFixturePath('db/wordlist.xml');
		$this->importDataSet($fixturePath);
	}

	/**
	 * tearDown() = destroy DB etc.
	 */
	public function tearDown () {
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);

		$GLOBALS['BE_USER']->setWorkspace($this->workspaceIdAtStart);
	}
	/**
	 * Testen ob getWordlistEntryByWord null zurück gibt wenn nichts gefunden wurde
	 */
	public function testGetWordlistEntryByWordReturnsEmptyIfNoMatch(){
		$wordlistSrv = tx_mklib_util_ServiceRegistry::getWordlistService();
		$ret = $wordlistSrv->getWordlistEntryByWord('nothing');

		$this->assertTrue(empty($ret),'Es wurden Treffer zurück gegeben!');
	}

	/**
	 * Testen ob getWordlistEntryByWord mehrere Treffer zurück gibt im normale Modus
	 */
	public function testGetWordlistEntryByWordReturnsMatches() {
		$wordlistSrv = tx_mklib_util_ServiceRegistry::getWordlistService();

		$ret = $wordlistSrv->getWordlistEntryByWord('fuck shit sfuck');
		$this->assertEquals(2,count($ret),'Das Treffer Array hat nicht die korrekte Größe!');
		$this->assertEquals('fuck',$ret[0],'Das gefundene Wort ist nicht korrekt!');
		$this->assertEquals('shit',$ret[1],'Das gefundene Wort ist nicht korrekt!');

		$ret = $wordlistSrv->getWordlistEntryByWord('who the fuck is alice? shit haha sfuck');
		$this->assertEquals(2,count($ret),'Das Treffer Array hat nicht die korrekte Größe!');
		$this->assertEquals('fuck',$ret[0],'Das gefundene Wort ist nicht korrekt!');
		$this->assertEquals('shit',$ret[1],'Das gefundene Wort ist nicht korrekt!');
	}

	/**
	 * Testen ob getWordlistEntryByWord einen Treffer zurück gibt im none greedy Modus
	 */
	public function testGetWordlistEntryByWordReturns1MatchIfInNoneGreedyMode() {
		$wordlistSrv = tx_mklib_util_ServiceRegistry::getWordlistService();
		$ret = $wordlistSrv->getWordlistEntryByWord('fuck shit',false);
		$this->assertEquals('fuck',$ret,'Das gefundene Wort ist nicht korrekt!');
		$ret = $wordlistSrv->getWordlistEntryByWord('who the fuck is alice? shit',false);
		$this->assertEquals('fuck',$ret,'Das gefundene Wort ist nicht korrekt!');
	}

	/**
	 * Testen ob getWordlistEntryByWord einen Treffer zurück gibt wenn es einen gibt
	 */
	public function testGetWordlistEntryByWordReturnsMatchWithComplexString() {
		$wordlistSrv = tx_mklib_util_ServiceRegistry::getWordlistService();
		$ret = $wordlistSrv->getWordlistEntryByWord('Einige Worte, blub, da war es!');

		$this->assertEquals(1,count($ret),'Das Treffer Array hat nicht die korrekte Größe!');
		$this->assertEquals('blub',$ret[0],'Das zurückgegebene Wort stimmt nicht!');
		 
	}

	/**
	 * Testen ob getWordlistEntryByWord einen Eintrag zurück liefert
	 */
	public function testGetWordlistEntryByBlacklistedWordReturnsCorrectData(){
		$wordlistSrv = tx_mklib_util_ServiceRegistry::getWordlistService();
		$ret = $wordlistSrv->getBlacklistEntryByWord('fuck shit ass');

		$this->assertEquals(3,count($ret),'Das Treffer Array hat nicht die korrekte Größe!');
		$this->assertEquals('fuck',$ret[0],'Das zurückgegebene Wort stimmt nicht!');
		$this->assertEquals('shit',$ret[1],'Das zurückgegebene Wort stimmt nicht!');
		$this->assertEquals('ass',$ret[2],'Das zurückgegebene Wort stimmt nicht!');
		 
		$ret = $wordlistSrv->getBlacklistEntryByWord('some fuck bad shit words ass');

		$this->assertEquals(3,count($ret),'Das Treffer Array hat nicht die korrekte Größe!');
		$this->assertEquals('fuck',$ret[0],'Das zurückgegebene Wort stimmt nicht!');
		$this->assertEquals('shit',$ret[1],'Das zurückgegebene Wort stimmt nicht!');
		$this->assertEquals('ass',$ret[2],'Das zurückgegebene Wort stimmt nicht!');
	}

	/**
	 * Testen ob getWordlistEntryByWord einen Eintrag zurück liefert
	 */
	public function testGetWordlistEntryByWhitelistedWordReturnsCorrectData(){
		$wordlistSrv = tx_mklib_util_ServiceRegistry::getWordlistService();
		$ret = $wordlistSrv->getWhitelistEntryByWord('nice');

		$this->assertEquals(1,count($ret),'Das Treffer Array hat nicht die korrekte Größe!');
		$this->assertEquals('nice',$ret[0],'Das zurückgegebene Wort stimmt nicht!');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/srv/class.tx_mklib_tests_srv_Wordlist_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/srv/class.tx_mklib_tests_srv_Wordlist_testcase.php']);
}