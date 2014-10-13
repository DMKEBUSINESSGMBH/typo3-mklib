<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_validator
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
tx_rnbase::load('tx_mklib_validator_WordList');
/**
 * Testfälle für tx_mklib_validator_WordList
 *
 * @author hbochmann
 * @package tx_mklib
 * @subpackage tx_mklib_tests_validator
 *
 * @group integration
 */
class tx_mklib_tests_validator_WordList_testcase extends tx_phpunit_database_testcase {

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
   	 * Prüft das stringContainsNoBlacklistedWords() true zurück gibt wenn kein Wort gegeben wurde
   	 * @group integration
   	 */
  	public function testStringContainsNoBlacklistedWordsRetrunsTrueIfNoWordGiven() {
  		$this->assertTrue(tx_mklib_validator_WordList::stringContainsNoBlacklistedWords(''),'Kein Wort gegeben und es wurde nicht true zurück gegeben!');
  	}

  	/**
   	 * Prüft das stringContainsNoBlacklistedWords() true zurück gibt wenn kein Wort gegeben wurde
   	 * @group integration
   	 */
  	public function testStringContainsNoBlacklistedWordsRetrunsTrueWhenWordNotBlacklsited() {
  		$this->assertTrue(tx_mklib_validator_WordList::stringContainsNoBlacklistedWords('nice',false),'Kein Wort gegeben und es wurde nicht true zurück gegeben!');
  		$this->assertTrue(tx_mklib_validator_WordList::stringContainsNoBlacklistedWords('alles sehr schön',false),'Kein Wort gegeben und es wurde nicht true zurück gegeben!');
  	}

  	/**
   	 * Prüft das stringContainsNoBlacklistedWords() true zurück gibt wenn kein Wort gegeben wurde
   	 * @group integration
   	 */
  	public function testStringContainsNoBlacklistedWordsRetrunsTrueInGreedyModeWhenWordNotBlacklsited() {
  		$ret = tx_mklib_validator_WordList::stringContainsNoBlacklistedWords('nice');
  		$this->assertTrue($ret,'Es wurde ein Treffer zurück gegeben!');

  		$ret = tx_mklib_validator_WordList::stringContainsNoBlacklistedWords('alles sehr schön');
  		$this->assertTrue($ret,'Es wurde ein Treffer zurück gegeben!');
  	}

  	/**
   	 * Prüft das stringContainsNoBlacklistedWords() true zurück gibt wenn kein Wort gegeben wurde
   	 * @group integration
   	 */
  	public function testStringContainsNoBlacklistedWordsRetrunsMatchesIfWordsAreBlacklisted() {
  		$ret = tx_mklib_validator_WordList::stringContainsNoBlacklistedWords('sfuck fuck shit');
  		$this->assertEquals(2,count($ret),'Das Treffer Array hat nicht die korrekte Größe!');
  		$this->assertEquals('fuck',$ret[0],'Das geblacklisted Wort wurde nicht zurück gegeben!');
  		$this->assertEquals('shit',$ret[1],'Das geblacklisted Wort wurde nicht zurück gegeben!');

  		//non greedy
  		$ret = tx_mklib_validator_WordList::stringContainsNoBlacklistedWords('who the fuck is alice? shit',false);
  		$this->assertEquals('fuck',$ret,'Das geblacklisted Wort wurde nicht zurück gegeben!');
  	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/validator/class.tx_mklib_tests_validator_WordList_testcase.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/validator/class.tx_mklib_tests_validator_WordList_testcase.php']);
}

?>