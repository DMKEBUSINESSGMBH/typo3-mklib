<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
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
tx_rnbase::load('tx_mklib_util_File');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_File_testcase extends tx_phpunit_testcase {

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
		t3lib_div::mkdir($testfolder);
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
			if (!is_dir($path)) t3lib_div::mkdir($path);
			$iH = fopen($path.$file, "w+");
			fwrite($iH, 'This is an automatic generated testfile and can be removed.');
			fclose($iH);
		}
	}

	public function testCleanupFilesNotInTypo3temp(){
		//@TODO: lifetime testen
		// testverzeichnis anlegen
		$testfolder = t3lib_extMgm::extPath('mklib', 'tests/fixtures/toremove');
		self::createTestfiles($testfolder);

		// das aufräumen!
		$count = tx_mklib_util_File::cleanupFiles($testfolder.'/', array(
			// die dateien werden erst nach der $GLOBALS['EXEC_TIME'] generiert.
			'lifetime' => -3600,
			'recursive' => '0',
			'filetypes' => 'zip, xml',
		));
		$this->assertEquals(0, $count, 'wrong deleted count.');
		// weider löschen
		t3lib_div::rmdir($testfolder, true);
	}
	public function testCleanupFilesWithZipAndXml(){
		//@TODO: lifetime testen
		// testverzeichnis anlegen
		$testfolder = t3lib_extMgm::extPath('mklib', 'tests/fixtures/toremove');
		self::createTestfiles($testfolder);

		// das aufräumen!
		$count = tx_mklib_util_File::cleanupFiles($testfolder.'/', array(
			// die dateien werden erst nach der $GLOBALS['EXEC_TIME'] generiert.
			'lifetime' => -3600,
			'recursive' => '0',
			'filetypes' => 'zip, xml',
			'skiptypo3tempcheck' => '1',
		));
		$this->assertEquals(2, $count, 'wrong deleted count.');
		// weider löschen
		t3lib_div::rmdir($testfolder, true);
	}

	public function testCleanupFilesRecursiveWithZipAndXml(){
		//@TODO: lifetime testen
		// testverzeichnis anlegen
		$testfolder = t3lib_extMgm::extPath('mklib', 'tests/fixtures/toremove');
		self::createTestfiles($testfolder);

		// das aufräumen!
		$count = tx_mklib_util_File::cleanupFiles($testfolder.'/', array(
			// die dateien werden erst nach der $GLOBALS['EXEC_TIME'] generiert.
			'lifetime' => -3600,
			'recursive' => '1',
			'filetypes' => 'zip, xml',
			'skiptypo3tempcheck' => '1',
		));
		$this->assertEquals(4, $count, 'wrong deleted count.');
		// weider löschen
		t3lib_div::rmdir($testfolder, true);
	}

	/**
	 * getServerPath testen
	 */
	public function testGetServerPath(){
		if(defined('TYPO3_cliMode') && TYPO3_cliMode){
			$this->markTestSkipped('Geht leider nicht unter CLI.');
		}
		$pathSite = tx_mklib_util_File::getDocumentRoot();
		$this->assertEquals($pathSite, tx_mklib_util_File::getServerPath());
		$this->assertEquals($pathSite.'typo3conf/', tx_mklib_util_File::getServerPath(tx_mklib_util_File::getSiteUrl().'/typo3conf'));
		$this->assertEquals($pathSite.'typo3conf/', tx_mklib_util_File::getServerPath('\typo3conf'));
		$this->assertEquals($pathSite.'typo3conf/localconf.php', tx_mklib_util_File::getServerPath('/typo3conf\localconf.php'));
	}

	/**
	 * getRelPath testen
	 */
	public function testGetRelPath(){
		if(defined('TYPO3_cliMode') && TYPO3_cliMode){
			$this->markTestSkipped('Geht leider nicht unter CLI.');
		}
		$pathSite = tx_mklib_util_File::getDocumentRoot();
		$this->assertEquals('/', tx_mklib_util_File::getRelPath());
		$this->assertEquals('/typo3conf/', tx_mklib_util_File::getRelPath(tx_mklib_util_File::getSiteUrl().'\typo3conf'));
		$this->assertEquals('/typo3conf/', tx_mklib_util_File::getRelPath($pathSite.'\typo3conf'));
		$this->assertEquals('/typo3conf/localconf.php', tx_mklib_util_File::getRelPath($pathSite.'/typo3conf\localconf.php'));
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
		$this->assertEquals($siteUrl, tx_mklib_util_File::getWebPath());
		$this->assertEquals($siteUrl.'typo3conf/', tx_mklib_util_File::getWebPath($siteUrl.'/typo3conf'));
		$this->assertEquals($siteUrl.'typo3conf/', tx_mklib_util_File::getWebPath($pathSite.'\typo3conf'));
		$this->assertEquals($siteUrl.'typo3conf/', tx_mklib_util_File::getWebPath('/typo3conf'));
		$this->assertEquals($siteUrl.'typo3conf/localconf.php', tx_mklib_util_File::getWebPath($pathSite.'/typo3conf\localconf.php'));
		$this->assertEquals($siteUrl.'typo3conf/localconf.php', tx_mklib_util_File::getWebPath('typo3conf\localconf.php'));
	}

	public function testParseUrlFromParts() {
		$url = 'https://kunde:mk17@jenkins.project.dmknet.de:80/jenkins?test=param#anchor';
		$parts = parse_url($url);
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		$this->assertEquals($url, $newUrl);

		unset($parts['pass']);
		$url = 'https://kunde@jenkins.project.dmknet.de:80/jenkins?test=param#anchor';
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		$this->assertEquals($url, $newUrl);

		unset($parts['user']);
		$url = 'https://jenkins.project.dmknet.de:80/jenkins?test=param#anchor';
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		$this->assertEquals($url, $newUrl);

		unset($parts['port']);
		$url = 'https://jenkins.project.dmknet.de/jenkins?test=param#anchor';
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		$this->assertEquals($url, $newUrl);

		unset($parts['query']);
		$url = 'https://jenkins.project.dmknet.de/jenkins#anchor';
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		$this->assertEquals($url, $newUrl);

		unset($parts['fragment']);
		$url = 'https://jenkins.project.dmknet.de/jenkins';
		$newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
		$this->assertEquals($url, $newUrl);
	}


}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_File_testcase.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Var_testcase.php']);
}