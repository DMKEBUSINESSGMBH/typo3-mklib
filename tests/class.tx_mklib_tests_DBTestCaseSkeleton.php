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
 * Model eines wordlist eintrages.
 */
abstract class tx_mklib_tests_DBTestCaseSkeleton extends Tx_Phpunit_Database_TestCase
{
    protected $workspaceIdAtStart;
    /**
     * Extensions, welche importiert werden sollen.
     *
     * @see tx_phpunit_database_testcase::importExtensions();
     *
     * @var array
     */
    protected $importExtensions = array();

    /**
     * Sollen Abhängigkeiten in Extension mit importiert werden?
     *
     * @see tx_phpunit_database_testcase::importExtensions();
     *
     * @var array
     */
    protected $importDependencies = false;
    /**
     * Sollen die statischen Daten einer Extension
     * (ext_tables_static.sql) in die Datenbank importiert werden?
     *
     * @var bool
     */
    protected $importStaticTables = false;
    /**
     * Diese FixtureXMLs werden beim setUp in die Datenbank geladen.
     * Es sollte nach folgendem Muster angegeben werden:
     * EXT:mklib/tests/fixtures/test.xml.
     *
     * @var array
     */
    protected $importDataSet = array();

    /**
     * Constructs a test case with the given name.
     *
     *  Klassenkonstruktor - BE-Workspace setzen
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        global $TYPO3_DB, $BE_USER;
        parent::__construct($name, $data, $dataName);
        $TYPO3_DB->debugOutput = true;

        $this->workspaceIdAtStart = $BE_USER->workspace;
        $BE_USER->setWorkspace(0);
    }

    /**
     * Importier SQL-datei einer Extension.
     *
     * @param unknown_type $extKey
     * @param unknown_type $files
     */
    protected static function importStaticTables(
        $extKey = 'mklib',
        $files = array('ext_tables_static+adt.sql')
    ) {
        foreach ($files as $file) {
            // read sql file content
            $sqlFilename = tx_rnbase_util_Files::getFileAbsFileName(tx_rnbase_util_Extensions::extPath($extKey, $file));
            if (@is_file($sqlFilename)) {
                \DMK\Mklib\Utility\Tests::queryDB($sqlFilename, false, true); //alle statements importieren
            }
        }
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * setUp() = init DB etc.
     */
    protected function setUp()
    {
        //Devlog stört beim Testen nur
        \DMK\Mklib\Utility\Tests::disableDevlog();

        try {
            $this->createDatabase();
        } catch (RuntimeException $e) {
            $this->markTestSkipped(
                'This test is skipped because the test database is not available.'
            );
        }
        // assuming that test-database can be created otherwise PHPUnit will skip the test
        $this->useTestDatabase();
        $this->importStdDB();

        // extensions laden
        if (count($this->importExtensions)) {
            foreach ($this->importExtensions as $extension) {
                $this->importExtensions(array($extension), $this->importDependencies);
                // static tables in die db importieren
                if ($this->importStaticTables) {
                    self::importStaticTables($extension);
                }
            }
        }
        // fixtures laden
        if (count($this->importDataSet)) {
            foreach ($this->importDataSet as $fixturePath) {
                $this->importDataSet(tx_rnbase_util_Files::getFileAbsFileName($fixturePath));
            }
        }
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * tearDown() = destroy DB etc.
     */
    protected function tearDown()
    {
        $this->cleanDatabase();
        $this->dropDatabase();
        $this->switchToTypo3Database();
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_DBTestCaseSkeleton.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_DBTestCaseSkeleton.php'];
}
