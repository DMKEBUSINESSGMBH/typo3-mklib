<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
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

/**
 * benötigte Klassen einbinden
 */

tx_rnbase::load('tx_mklib_util_File');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_File_testcase extends Tx_Phpunit_TestCase {

	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		parent::__construct($name, $data, $dataName);
		// methoden aufrufen, die einmalig speicherplatz benötigen
		// das ferfälscht nicht die memmory leak und time werte
		tx_mklib_util_File::getFileTool();
		tx_mklib_util_File::getSiteUrl();
		tx_mklib_util_File::getDocumentRoot();
	}

	public function setUp() {
	}
	public function tearDown() {
	}
	private static function createTestfiles($testfolder){
		tx_rnbase_util_Files::mkdir_deep($testfolder);
		$files = array(
			array($testfolder.'/','test.zip'),
			array($testfolder.'/','test.xml'),
			array($testfolder.'/','test.tmp'),
			array($testfolder.'/','test.dat'),
			array($testfolder.'/sub/','test.zip'),
			array($testfolder.'/sub/','test.tmp'),
			array($testfolder.'/sub/sub/','test.xml'),
			array($testfolder.'/sub/sub/','test.dat'),
		);
		foreach ($files as $file) {
			$path = $file[0]; $file = $file[1];
			if (!is_dir($path)) tx_rnbase_util_Files::mkdir_deep($path);
			$iH = fopen($path.$file, "w+");
			fwrite($iH, 'This is an automatic generated testfile and can be removed.');
			fclose($iH);
		}
	}

	public function testCleanupFilesNotInTypo3temp(){
		//@TODO: lifetime testen
		// testverzeichnis anlegen
		$testfolder = tx_rnbase_util_Extensions::extPath('mklib', 'tests/fixtures/toremove');
		self::createTestfiles($testfolder);

		// das aufräumen!
		$count = tx_mklib_util_File::cleanupFiles($testfolder.'/', array(
			// die dateien werden erst nach der $GLOBALS['EXEC_TIME'] generiert.
			'lifetime' => -10800,
			'recursive' => '0',
			'filetypes' => 'zip, xml',
		));
		self::assertEquals(0, $count, 'wrong deleted count.');
		// weider löschen
		tx_rnbase_util_Files::rmdir($testfolder, true);
	}
	public function testCleanupFilesWithZipAndXml(){
		//@TODO: lifetime testen
		// testverzeichnis anlegen
		$testfolder = tx_rnbase_util_Extensions::extPath('mklib', 'tests/fixtures/toremove');
		self::createTestfiles($testfolder);

		// das aufräumen!
		$count = tx_mklib_util_File::cleanupFiles($testfolder.'/', array(
			// die dateien werden erst nach der $GLOBALS['EXEC_TIME'] generiert.
			'lifetime' => -10800,
			'recursive' => '0',
			'filetypes' => 'zip, xml',
			'skiptypo3tempcheck' => '1',
		));
		self::assertEquals(2, $count, 'wrong deleted count.');
		// weider löschen
		tx_rnbase_util_Files::rmdir($testfolder, true);
	}

	public function testCleanupFilesRecursiveWithZipAndXml(){
		//@TODO: lifetime testen
		// testverzeichnis anlegen
		$testfolder = tx_rnbase_util_Extensions::extPath('mklib', 'tests/fixtures/toremove');
		self::createTestfiles($testfolder);

		// das aufräumen!
		$count = tx_mklib_util_File::cleanupFiles($testfolder.'/', array(
			// die dateien werden erst nach der $GLOBALS['EXEC_TIME'] generiert.
			'lifetime' => -10800,
			'recursive' => '1',
			'filetypes' => 'zip, xml',
			'skiptypo3tempcheck' => '1',
		));
		self::assertEquals(4, $count, 'wrong deleted count. testfolder: ' . $testfolder);
		// weider löschen
		tx_rnbase_util_Files::rmdir($testfolder, true);
	}

	/**
	 * getServerPath testen
	 */
	public function testGetServerPath(){
		if(defined('TYPO3_cliMode') && TYPO3_cliMode){
			$this->markTestSkipped('Geht leider nicht unter CLI.');
		}
		$pathSite = tx_mklib_util_File::getDocumentRoot();
		self::assertEquals($pathSite, tx_mklib_util_File::getServerPath());
		self::assertEquals($pathSite.'typo3conf/', tx_mklib_util_File::getServerPath(tx_mklib_util_File::getSiteUrl().'/typo3conf'));
		self::assertEquals($pathSite.'typo3conf/', tx_mklib_util_File::getServerPath('\typo3conf'));
		self::assertEquals($pathSite.'typo3conf/localconf.php', tx_mklib_util_File::getServerPath('/typo3conf\localconf.php'));
	}

	/**
	 * getRelPath testen
	 */
	public function testGetRelPath(){
		if(defined('TYPO3_cliMode') && TYPO3_cliMode){
			$this->markTestSkipped('Geht leider nicht unter CLI.');
		}
		$pathSite = tx_mklib_util_File::getDocumentRoot();
		self::assertEquals('/', tx_mklib_util_File::getRelPath());
		self::assertEquals('/typo3conf/', tx_mklib_util_File::getRelPath(tx_mklib_util_File::getSiteUrl().'\typo3conf'));
		self::assertEquals('/typo3conf/', tx_mklib_util_File::getRelPath($pathSite.'\typo3conf'));
		self::assertEquals('/typo3conf/localconf.php', tx_mklib_util_File::getRelPath($pathSite.'/typo3conf\localconf.php'));
	}

	/**
	 * getRelPath testen
	 */
	public function testGetRelPathWithRemovedStartingSlashSetToTrue(){
		if(defined('TYPO3_cliMode') && TYPO3_cliMode){
			$this->markTestSkipped('Geht leider nicht unter CLI.');
		}
		$pathSite = tx_mklib_util_File::getDocumentRoot();
		self::assertEquals('', tx_mklib_util_File::getRelPath('', TRUE));
		self::assertEquals(
			'typo3conf/',
			tx_mklib_util_File::getRelPath(tx_mklib_util_File::getSiteUrl().'\typo3conf', TRUE)
		);
		self::assertEquals(
			'typo3conf/',
			tx_mklib_util_File::getRelPath($pathSite.'\typo3conf', TRUE)
		);
		self::assertEquals(
			'typo3conf/localconf.php',
			tx_mklib_util_File::getRelPath($pathSite.'/typo3conf\localconf.php', TRUE)
		);
		self::assertEquals(
			'fileadmin/portal/pdf/test.pdf',
			tx_mklib_util_File::getRelPath('fileadmin/portal/pdf/test.pdf', TRUE)
		);
	}

	/**
	 * getWebPath testen
	 */
	public function testGetWebPath(){
		if(defined('TYPO3_cliMode') && TYPO3_cliMode){
			$this->markTestSkipped('Geht leider nicht unter CLI.');
		}
		$pathSite = tx_mklib_util_File::getDocumentRoot();
		$siteUrl = tx_mklib_util_File::getSiteUrl();
		self::assertEquals($siteUrl, tx_mklib_util_File::getWebPath());
		self::assertEquals($siteUrl.'typo3conf/', tx_mklib_util_File::getWebPath($siteUrl.'/typo3conf'));
		self::assertEquals($siteUrl.'typo3conf/', tx_mklib_util_File::getWebPath($pathSite.'\typo3conf'));
		self::assertEquals($siteUrl.'typo3conf/', tx_mklib_util_File::getWebPath('/typo3conf'));
		self::assertEquals($siteUrl.'typo3conf/localconf.php', tx_mklib_util_File::getWebPath($pathSite.'/typo3conf\localconf.php'));
		self::assertEquals($siteUrl.'typo3conf/localconf.php', tx_mklib_util_File::getWebPath('typo3conf\localconf.php'));
	}

	public function testParseUrlFromParts() {
		$url = 'https://kunde:mk17@jenkins.project.dmknet.de:80/jenkins?test=param#anchor';
		$parts = parse_url($url);
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		self::assertEquals($url, $newUrl);

		unset($parts['pass']);
		$url = 'https://kunde@jenkins.project.dmknet.de:80/jenkins?test=param#anchor';
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		self::assertEquals($url, $newUrl);

		unset($parts['user']);
		$url = 'https://jenkins.project.dmknet.de:80/jenkins?test=param#anchor';
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		self::assertEquals($url, $newUrl);

		unset($parts['port']);
		$url = 'https://jenkins.project.dmknet.de/jenkins?test=param#anchor';
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		self::assertEquals($url, $newUrl);

		unset($parts['query']);
		$url = 'https://jenkins.project.dmknet.de/jenkins#anchor';
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		self::assertEquals($url, $newUrl);

		unset($parts['fragment']);
		$url = 'https://jenkins.project.dmknet.de/jenkins';
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		self::assertEquals($url, $newUrl);
	}

	/**
	 * @dataProvider getFiles
	 */
	public function testIsValidFile($filepath, $expectedReturnValue) {
		self::assertEquals($expectedReturnValue, tx_mklib_util_File::isValidFile($filepath));
	}

	/**
	 *
	 * @return multitype:multitype:string  multitype:Ambigous <string, string, unknown>
	 */
	public function getFiles() {
		return array(
			array(tx_mklib_util_File::getServerPath(''), FALSE),
			array(tx_mklib_util_File::getServerPath('EXT:mklib/tests'), FALSE),
			array(tx_mklib_util_File::getServerPath('EXT:mklib/tests/phpunit.xml'), TRUE),
		);
	}

	/**
	 * @group unit
	 */
	public function testGetDocumentRoot() {
		$documentRoot = tx_mklib_util_File::getDocumentRoot();
		// to make sure that not only "/" is returned
		self::assertGreaterThan(3, strlen($documentRoot));
		self::assertEquals(PATH_site, $documentRoot);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_File_testcase.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Var_testcase.php']);
}
