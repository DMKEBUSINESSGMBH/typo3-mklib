<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests
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
require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * Model eines wordlist eintrages
 * @package tx_mklib
 * @subpackage tx_mklib_tests
 */
class tx_mklib_tests_DBTestCaseSkeleton extends tx_phpunit_database_testcase {

	protected $workspaceIdAtStart;
	/**
	 * Extensions, welche importiert werden sollen
	 * @see tx_phpunit_database_testcase::importExtensions();
	 * @var array
	 */
	protected $importExtensions = array();

	/**
	 * Sollen Abhängigkeiten in Extension mit importiert werden?
	 * @see tx_phpunit_database_testcase::importExtensions();
	 * @var array
	 */
	protected $importDependencies = false;
	/**
	 * Sollen die statischen Daten einer Extension
	 * (ext_tables_static.sql) in die Datenbank importiert werden?
	 * @var boolean
	 */
	protected $importStaticTables = false;
	/**
	 * Diese FixtureXMLs werden beim setUp in die Datenbank geladen.
	 * Es sollte nach folgendem Muster angegeben werden:
	 * EXT:mklib/tests/fixtures/test.xml
	 * @var array
	 */
	protected $importDataSet = array();

	/**
	 * Constructs a test case with the given name.
	 *
	 *  Klassenkonstruktor - BE-Workspace setzen
	 *
	 * @param string $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct ($name = NULL, array $data = array(), $dataName = '') {
		global $TYPO3_DB, $BE_USER;
		parent::__construct ($name, $data, $dataName);
		$TYPO3_DB->debugOutput = TRUE;

		$this->workspaceIdAtStart = $BE_USER->workspace;
		$BE_USER->setWorkspace(0);
	}

	/**
	 * Importier SQL-datei einer Extension.
	 * @param unknown_type $extKey
	 * @param unknown_type $files
	 */
	protected static function importStaticTables(
			$extKey='mklib',
			$files = array('ext_tables_static+adt.sql')
		){

		foreach($files as $file) {
			// read sql file content
			$sqlFilename = t3lib_div::getFileAbsFileName(t3lib_extMgm::extPath($extKey, $file));
			if(@is_file($sqlFilename)) {
				tx_mklib_tests_Util::queryDB($sqlFilename, false, true);//alle statements importieren
			}
		}

	}

	/**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
	 * setUp() = init DB etc.
	 */
	protected function setUp(){

		//Devlog stört beim Testen nur
		tx_mklib_tests_Util::disableDevlog();

		$this->createDatabase();
		// assuming that test-database can be created otherwise PHPUnit will skip the test
		$this->useTestDatabase();
		$this->importStdDB();

		// extensions laden
		if(count($this->importExtensions)) {
			foreach($this->importExtensions as $extension){
				$this->importExtensions(array($extension), $this->importDependencies);
				// static tables in die db importieren
				if($this->importStaticTables) {
					self::importStaticTables($extension);
				}
			}
//	   		$this->importExtensions($this->importExtensions);
		}
		// fixtures laden
		if(count($this->importDataSet)) {
			foreach($this->importDataSet as $fixturePath) {
	   			$this->importDataSet(t3lib_div::getFileAbsFileName($fixturePath));
			}
		}
	}

	/**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
	 * tearDown() = destroy DB etc.
	 */
	protected function tearDown () {
		$this->cleanDatabase();
		$this->dropDatabase();
		$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);
		$GLOBALS['BE_USER']->setWorkspace($this->workspaceIdAtStart);
	}

	/**
	 * Auf der CLI wird die Klasse als Test betrachtet. Da sie
	 * eigentlich keine Tests hat, kommt ein Fehler zu Stande.
	 *
	 * Da wir einen Datenbank testcase bereitstellen,
	 * können wir wenigstens die Verbindung zur DB prüfen.
	 *
	 * @group dummytest
	 */
	public function testCheckConnection() {
		$dbs = $GLOBALS['TYPO3_DB']->admin_get_dbs();
		$this->assertTrue(in_array($this->testDatabase, $dbs));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_DBTestCaseSkeleton.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_DBTestCaseSkeleton.php']);
}
