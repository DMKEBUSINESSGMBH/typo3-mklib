<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_mod1_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 das MedienKombinat <kontakt@das-medienkombinat.de>
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

require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
require_once(PATH_site.'typo3/template.php');
tx_rnbase::load('tx_mklib_tests_fixtures_classes_DummySearcher');
tx_rnbase::load('tx_mklib_tests_mod1_Util');
tx_rnbase::load('tx_rnbase_util_TYPO3');

/**
 * 
 * @package tx_mklib
 * @subpackage tx_mklib_tests_mod1_util
 * @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 */
class tx_mklib_tests_mod1_searcher_abstractBase_testcase extends tx_phpunit_testcase {

	/**
	 * @var tx_mklib_tests_fixtures_classes_DummySearcher
	 */
	protected $oSearcher;
	
	/**
	 * @var tx_mklib_tests_fixtures_classes_DummyMod
	 */
	protected $oMod;
	
	public function setUp() {
		//sprache auf default setzen damit wir die richtigen labels haben
		$GLOBALS['LANG']->lang = 'default';
		
		//wir müssen noch die TCA für die Wordlist laden
		global $TCA;
		$TCA['tx_mklib_wordlist'] = array (
			'ctrl' => array (
				'title'     => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist',
				'label'     => 'word',
				'label_alt' => 'uid',
				'label_alt_force' => false,
				'tstamp'    => 'tstamp',
				'crdate'    => 'crdate',
				'cruser_id' => 'cruser_id',
				'default_sortby' => 'ORDER BY crdate',
				'delete' => 'deleted',
				'enablecolumns' => array (
					'disabled' => 'hidden',
				),
				'dynamicConfigFile' => t3lib_extMgm::extPath('mklib').'tca/tx_mklib_wordlist.php',
				'iconfile'          => t3lib_extMgm::extRelPath('mklib').'icon/icon_tx_mklib_wordlist.gif',
				'dividers2tabs'     => true,
			),
		);

		$this->oMod = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_DummyMod');
		$this->oSearcher = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_DummySearcher',$this->oMod);
		$GLOBALS['TBE_TEMPLATE'] = t3lib_div::makeInstance('template');
		$GLOBALS['CLIENT']['FORMSTYLE'] = 'something';
		
		$GLOBALS['emptyTestResult'] = false;
		
		//immer wieder löschen
		$_GET['SET'] = null;
		tx_mklib_tests_mod1_Util::unsetSorting($this->oMod);
		if(isset($GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()]['showhidden']))
			unset($GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()]['showhidden']);
	}
	
	public function testGetSearchForm() {
		$sSearchForm = $this->oSearcher->getSearchForm();
		$sExpected = file_get_contents(t3lib_extMgm::extPath('mklib').'tests/fixtures/html/searchForm.html');
		//auf der CLI müssen einige Dinge ersetzt werden
		$sExpected = tx_mklib_tests_mod1_Util::replaceForCli($sExpected);
		
		$this->assertEquals(trim($sExpected), trim($sSearchForm), 'das suchformular ist falsch.');
	}
	
	public function testGetResultListReturnsNoPagerAndEmptyMsgIfResultEmpty() {
		$GLOBALS['emptyTestResult'] = true;
		$aResultList = $this->oSearcher->getResultList();
		
		$this->assertEquals('<p><strong>###LABEL_NO_DUMMYSEARCHER_FOUND###</strong></p><br/>', $aResultList['table'], 'Die Tabelle ist falsch.');
		
		$this->assertEquals(0, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
		$this->assertEquals('<div class="pager"></div>', $aResultList['pager'], 'Der Pager ist falsch.');
	}
	
	public function testGetResultListReturnsCorrectTableAndPagerIfResults() {
		//damit currenShowHidden gesetzt wird
		$this->oSearcher->getSearchForm();
		$aResultList = $this->oSearcher->getResultList();
		$sDbListClass = '';
		if(tx_rnbase_util_TYPO3::isTYPO44OrHigher())//wird darunter nicht gesetzt
			$sDbListClass = ' class="typo3-dblist"';

/*		$sExpectedTable = '<table border="0" cellspacing="0" cellpadding="0"'.$sDbListClass.' id="typo3-tmpltable"><tr><td valign="top"><a href="'.t3lib_div::getIndpEnv('TYPO3_REQUEST_URL').'&amp;sortField=uid&amp;sortRev=asc">Uid</a></td><td valign="top">Actions</td></tr><tr><td valign="top">1</td><td valign="top"><a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[tx_mklib_wordlist][1]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[tx_mklib_wordlist][1][hidden]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="Hide UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[tx_mklib_wordlist][1][delete]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/deletedok.gif" width="16" height="16" title="Delete UID: 1" border="0" alt="" /></a></td></tr><tr><td valign="top">2</td><td valign="top"><a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[tx_mklib_wordlist][2]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 2" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[tx_mklib_wordlist][2][hidden]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="Hide UID: 2" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[tx_mklib_wordlist][2][delete]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/deletedok.gif" width="16" height="16" title="Delete UID: 2" border="0" alt="" /></a></td></tr><tr><td valign="top">3</td><td valign="top"><a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[tx_mklib_wordlist][3]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 3" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[tx_mklib_wordlist][3][hidden]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="Hide UID: 3" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[tx_mklib_wordlist][3][delete]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/deletedok.gif" width="16" height="16" title="Delete UID: 3" border="0" alt="" /></a></td></tr><tr><td valign="top">4</td><td valign="top"><a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[tx_mklib_wordlist][4]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 4" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[tx_mklib_wordlist][4][hidden]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="Hide UID: 4" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[tx_mklib_wordlist][4][delete]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/deletedok.gif" width="16" height="16" title="Delete UID: 4" border="0" alt="" /></a></td></tr><tr><td valign="top">5</td><td valign="top"><a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[tx_mklib_wordlist][5]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 5" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[tx_mklib_wordlist][5][hidden]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="Hide UID: 5" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[tx_mklib_wordlist][5][delete]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/deletedok.gif" width="16" height="16" title="Delete UID: 5" border="0" alt="" /></a></td></tr></table>';

		tx_mklib_tests_mod1_Util::replaceForCli($sExpectedTable);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($aResultList['table']);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($sExpectedTable);
*/
		$sExpectedPager = file_get_contents(t3lib_extMgm::extPath('mklib').'tests/fixtures/html/searchPager.html');
		
		tx_mklib_tests_mod1_Util::replaceForCli($sExpectedPager);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($aResultList['pager']);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($sExpectedPager);

		$result = $aResultList['table'];
		$this->assertRegExp('/^<table border="0"/', $result, 'Table Tag fehlt.');
		$this->assertRegExp('/<\/table>$/', $result, 'Schließendes Table Tag fehlt.');

		for($i=1; $i<6; $i++) {
			$this->assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert ' . $i .' fehlt in Tabelle');
		}
		$this->assertEquals(5, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
		$this->assertEquals($sExpectedPager, $aResultList['pager'], 'Der Pager ist falsch.');
	}
	
	public function testGetResultReturnsCorrectResultsDependendOnHiddenSettings() {
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()]['showhidden'] = 1;
		//damit currenShowHidden gesetzt wird
		$this->oSearcher->getSearchForm();
		$aResultList = $this->oSearcher->getResultList();
		
		$sDbListClass = '';
		if(tx_rnbase_util_TYPO3::isTYPO44OrHigher())//wird darunter nicht gesetzt
			$sDbListClass = ' class="typo3-dblist"';
		
		$sExpectedPager = file_get_contents(t3lib_extMgm::extPath('mklib').'tests/fixtures/html/searchPager.html');
		
		tx_mklib_tests_mod1_Util::replaceForCli($sExpectedPager);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($aResultList['pager']);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($sExpectedPager);

		$result = $aResultList['table'];
		$this->assertRegExp('/^<table border="0"/', $result, 'Table Tag fehlt.');
		$this->assertRegExp('/<\/table>$/', $result, 'Schließendes Table Tag fehlt.');

		for($i=1; $i<6; $i++) {
			$this->assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert ' . $i .' fehlt in Tabelle');
		}
		$this->assertRegExp('/"><strike>6<\/strike><\/span>/', $result, 'versteckter Wert 6 fehlt in Tabelle');
		
		$this->assertEquals(6, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
		$this->assertEquals($sExpectedPager, $aResultList['pager'], 'Der Pager ist falsch.');
	}
	
	public function testGetResultListReturnsCorrectTableAndPagerIfSortLinkIsClickedAndSetsSortOptionToModuleData() {
		$_GET['sortField'] = 'uid';
		$_GET['sortRev'] = 'asc';
		
		//damit currenShowHidden gesetzt wird
		$this->oSearcher->getSearchForm();
		$aResultList = $this->oSearcher->getResultList();
		
		//Daten im Modul korrekt?
		$aModuleData = t3lib_BEfunc::getModuleData(array (),t3lib_div::_GP('SET'),$this->oMod->getName());
		$this->assertEquals(array('uid' => 'asc'), $aModuleData['dummySearcherorderby'], 'OrderBy in Moduldaten nicht korrekt gesetzt.');
		
		//HTML Ausgabe korrekt?
		$sDbListClass = '';
		if(tx_rnbase_util_TYPO3::isTYPO44OrHigher())//wird darunter nicht gesetzt
			$sDbListClass = ' class="typo3-dblist"';
		
		$sExpectedPager = file_get_contents(t3lib_extMgm::extPath('mklib').'tests/fixtures/html/searchPager.html');
		
		tx_mklib_tests_mod1_Util::replaceForCli($sExpectedPager);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($aResultList['pager']);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($sExpectedPager);

		$result = $aResultList['table'];
		$this->assertRegExp('/^<table border="0"/', $result, 'Table Tag fehlt.');
		$this->assertRegExp('/<\/table>$/', $result, 'Schließendes Table Tag fehlt.');

		// TODO: Die Reihenfolge der Zeilen müsste noch getestet werden. 
		for($i=1; $i<6; $i++) {
			$this->assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert ' . $i .' fehlt in Tabelle');
		}

		$this->assertEquals(5, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
		//unberührt?
		$this->assertEquals($sExpectedPager, $aResultList['pager'], 'Der Pager ist falsch.');
	}
	
	public function testGetResultListReturnsCorrectTableAndPagerIfSortingFromModuleDataAndSetsSortOptionToGetParams() {
		//daten fürs modul setzen
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()]['dummySearcherorderby'] = array('uid' => 'asc');
		
		//damit currenShowHidden gesetzt wird
		$this->oSearcher->getSearchForm();
		$aResultList = $this->oSearcher->getResultList();
		
		//Daten in $_GET korrekt?
		$this->assertEquals('uid', $_GET['sortField'], '$_GET[\'sortField\'] nicht korrekt gesetzt.');
		$this->assertEquals('asc', $_GET['sortRev'], '$_GET[\'sortRev\'] nicht korrekt gesetzt.');
		
		//HTML Ausgabe korrekt?
		$sDbListClass = '';
		if(tx_rnbase_util_TYPO3::isTYPO44OrHigher())//wird darunter nicht gesetzt
			$sDbListClass = ' class="typo3-dblist"';

		$sExpectedPager = file_get_contents(t3lib_extMgm::extPath('mklib').'tests/fixtures/html/searchPager.html');
		
		tx_mklib_tests_mod1_Util::replaceForCli($sExpectedPager);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($aResultList['pager']);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($sExpectedPager);

		$result = $aResultList['table'];
		$this->assertRegExp('/^<table border="0"/', $result, 'Table Tag fehlt.');
		$this->assertRegExp('/<\/table>$/', $result, 'Schließendes Table Tag fehlt.');

		// TODO: Die Reihenfolge der Zeilen müsste noch getestet werden. 
		for($i=1; $i<6; $i++) {
			$this->assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert ' . $i .' fehlt in Tabelle');
		}

		$this->assertEquals(5, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
		//unberührt?
		$this->assertEquals($sExpectedPager, $aResultList['pager'], 'Der Pager ist falsch.');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']);
}