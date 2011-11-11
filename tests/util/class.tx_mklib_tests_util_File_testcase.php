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
	/*
	public function testGetFileToolPerformance(){
		tx_rnbase::load('tx_mklib_util_File');
		$count=50000;
		$start = time();
		for($i=0;$i<$count;$i++)
			tx_mklib_util_File::getFileTool();
		$end = time();
		t3lib_div::debug($end-$start, 'with static cache: '.__METHOD__.' Line: '.__LINE__); // @TODO: remove me
		
		$start = time();
		for($i=0;$i<$count;$i++)
			tx_mklib_util_File::getFileTool(array('count'=>$i), array('count'=>$i));
		$end = time();
		t3lib_div::debug($end-$start, 'without static cache: '.__METHOD__.' Line: '.__LINE__); // @TODO: remove me
		
	}
	*/
	
	public function setUp() {
	}
	public function tearDown() {
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
	
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_File_testcase.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Var_testcase.php']);
}