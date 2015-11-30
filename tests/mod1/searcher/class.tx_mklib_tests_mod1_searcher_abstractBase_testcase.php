<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_mod1_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 DMK E-BUSINESS GmbH  <dev@dmk-ebusiness.de>
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
if (!class_exists('template')) {
	require_once(PATH_site.'typo3/template.php');
}
tx_rnbase::load('tx_mklib_tests_fixtures_classes_DummySearcher');
tx_rnbase::load('tx_mklib_tests_mod1_Util');
tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests_mod1_util
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_tests_mod1_searcher_abstractBase_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * @var tx_mklib_tests_fixtures_classes_DummySearcher
	 */
	protected $searcher;

	/**
	 * @var tx_mklib_tests_fixtures_classes_DummyMod
	 */
	protected $mod;

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

		$this->mod = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_DummyMod');
		$this->searcher = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_DummySearcher',$this->mod);
		$GLOBALS['TBE_TEMPLATE'] = t3lib_div::makeInstance('template');
		$GLOBALS['CLIENT']['FORMSTYLE'] = 'something';

		$GLOBALS['emptyTestResult'] = false;

		//immer wieder löschen
		$_GET['SET'] = null;
		tx_mklib_tests_mod1_Util::unsetSorting($this->mod);
		if(isset($GLOBALS['BE_USER']->uc['moduleData'][$this->mod->getName()]['showhidden']))
			unset($GLOBALS['BE_USER']->uc['moduleData'][$this->mod->getName()]['showhidden']);

		// zurücksetzen
		$localLangLoadedProperty = new ReflectionProperty('tx_mklib_mod1_searcher_abstractBase', 'localLangLoaded');
		$localLangLoadedProperty->setAccessible(TRUE);
		$localLangLoadedProperty->setValue(NULL, FALSE);
	}

	public function testGetSearchForm() {
		$searchForm = $this->searcher->getSearchForm();

		$this->assertContains(
			'<table class="filters"><tr><td>' . $GLOBALS['LANG']->getLL('label_search') .
			'</td><td><input type="text" name="SET[dummySearcherSearch]" style="width:96px;" value="" /> <input type="submit" name="dummySearcherSearch" value="search" /></td></tr><tr><td>Hidden entries:</td><td>',
			$searchForm,
			'das suchformular ist falsch.'
		);
		$this->assertContains(
			'<select name="SET[showhidden]" onchange="jumpToUrl',
			$searchForm,
			'das suchformular ist falsch.'
		);
		$this->assertContains(
			'<option value="0">' . $GLOBALS['LANG']->getLL('label_select_hide_hidden') . '</option>',
			$searchForm,
			'das suchformular ist falsch.'
		);
		$this->assertContains(
			'<option value="1">' . $GLOBALS['LANG']->getLL('label_select_show_hidden') . '</option>',
			$searchForm,
			'das suchformular ist falsch.'
		);
		$this->assertContains(
			'</select>',
			$searchForm,
			'das suchformular ist falsch.'
		);
		$this->assertContains(
			'</td></tr><tr><td></td><td><input type="submit" name="dummySearcherSearch" value="Update" /></td></tr></table>',
			$searchForm,
			'das suchformular ist falsch.'
		);
	}

	public function testGetResultListReturnsNoPagerAndEmptyMsgIfResultEmpty() {
		$GLOBALS['emptyTestResult'] = true;
		$aResultList = $this->searcher->getResultList();

		$this->assertEquals('<p><strong>###LABEL_NO_DUMMYSEARCHER_FOUND###</strong></p><br/>', $aResultList['table'], 'Die Tabelle ist falsch.');

		$this->assertEquals(0, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
		$this->assertEquals('<div class="pager"></div>', $aResultList['pager'], 'Der Pager ist falsch.');
	}

	public function testGetResultListReturnsCorrectTableAndPagerIfResults() {
		//damit currenShowHidden gesetzt wird
		$this->searcher->getSearchForm();
		$aResultList = $this->searcher->getResultList();

		$result = $aResultList['table'];
		$this->assertRegExp('/^<table border="0"/', $result, 'Table Tag fehlt.');
		$this->assertRegExp('/<\/table>$/', $result, 'Schließendes Table Tag fehlt.');

		for($i=1; $i<6; $i++) {
			$this->assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert ' . $i .' fehlt in Tabelle');
		}

		$this->assertEquals(5, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
		$this->assertContains(
			'<div class="pager">',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<select name="SET[dummySearcherPagerdata_limit]" onchange="jumpToUrl(',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<option value="10" selected="selected">10 Einträge</option>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<option value="100">100 Einträge</option>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'</select>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<select name="SET[dummySearcherPagerdata_offset]"',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<option value="0" selected="selected">Seite 0</option>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'</div>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
	}

	public function testGetResultReturnsCorrectResultsDependendOnHiddenSettings() {
		$GLOBALS['BE_USER']->uc['moduleData'][$this->mod->getName()]['showhidden'] = 1;
		//damit currenShowHidden gesetzt wird
		$this->searcher->getSearchForm();
		$aResultList = $this->searcher->getResultList();

		$result = $aResultList['table'];
		$this->assertRegExp('/^<table border="0"/', $result, 'Table Tag fehlt.');
		$this->assertRegExp('/<\/table>$/', $result, 'Schließendes Table Tag fehlt.');

		for($i=1; $i<6; $i++) {
			$this->assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert ' . $i .' fehlt in Tabelle');
		}
		$this->assertRegExp('/"><del>6<\/del><\/span>/', $result, 'versteckter Wert 6 fehlt in Tabelle');

		$this->assertEquals(6, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
	}

	public function testGetResultListReturnsCorrectTableAndPagerIfSortLinkIsClickedAndSetsSortOptionTmoduleData() {
		$_GET['sortField'] = 'uid';
		$_GET['sortRev'] = 'asc';

		//damit currenShowHidden gesetzt wird
		$this->searcher->getSearchForm();
		$aResultList = $this->searcher->getResultList();

		//Daten im Modul korrekt?
		$aModuleData = t3lib_BEfunc::getModuleData(array (),t3lib_div::_GP('SET'),$this->mod->getName());
		$this->assertEquals(array('uid' => 'asc'), $aModuleData['dummySearcherorderby'], 'OrderBy in Moduldaten nicht korrekt gesetzt.');

		$result = $aResultList['table'];
		$this->assertRegExp('/^<table border="0"/', $result, 'Table Tag fehlt.');
		$this->assertRegExp('/<\/table>$/', $result, 'Schließendes Table Tag fehlt.');

		// TODO: Die Reihenfolge der Zeilen müsste noch getestet werden.
		for($i=1; $i<6; $i++) {
			$this->assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert ' . $i .' fehlt in Tabelle');
		}

		$this->assertEquals(5, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
		//unberührt?
		$this->assertContains(
			'<div class="pager">',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<select name="SET[dummySearcherPagerdata_limit]" onchange="jumpToUrl(',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<option value="10" selected="selected">10 Einträge</option>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<option value="100">100 Einträge</option>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'</select>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<select name="SET[dummySearcherPagerdata_offset]"',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<option value="0" selected="selected">Seite 0</option>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'</div>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
	}

	public function testGetResultListReturnsCorrectTableAndPagerIfSortingFromModuleDataAndSetsSortOptionToGetParams() {
		//daten fürs modul setzen
		$GLOBALS['BE_USER']->uc['moduleData'][$this->mod->getName()]['dummySearcherorderby'] = array('uid' => 'asc');

		//damit currenShowHidden gesetzt wird
		$this->searcher->getSearchForm();
		$aResultList = $this->searcher->getResultList();

		//Daten in $_GET korrekt?
		$this->assertEquals('uid', $_GET['sortField'], '$_GET[\'sortField\'] nicht korrekt gesetzt.');
		$this->assertEquals('asc', $_GET['sortRev'], '$_GET[\'sortRev\'] nicht korrekt gesetzt.');

		$result = $aResultList['table'];
		$this->assertRegExp('/^<table border="0"/', $result, 'Table Tag fehlt.');
		$this->assertRegExp('/<\/table>$/', $result, 'Schließendes Table Tag fehlt.');

		// TODO: Die Reihenfolge der Zeilen müsste noch getestet werden.
		for($i=1; $i<6; $i++) {
			$this->assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert ' . $i .' fehlt in Tabelle');
		}

		$this->assertEquals(5, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
		//unberührt?
		$this->assertContains(
			'<div class="pager">',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<select name="SET[dummySearcherPagerdata_limit]" onchange="jumpToUrl(',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<option value="10" selected="selected">10 Einträge</option>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<option value="100">100 Einträge</option>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'</select>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<select name="SET[dummySearcherPagerdata_offset]"',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'<option value="0" selected="selected">Seite 0</option>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
		$this->assertContains(
			'</div>',
			$aResultList['pager'],
			'Der Pager ist falsch.'
		);
	}

	/**
	 * @group unit
	 */
	public function testInitLoadsOwnLocalLangNotOverwritingExistingLabels() {
		// ist zwar in der mklib locallang Datei aber sollte nicht überschrieben werden
		$GLOBALS['LOCAL_LANG']['default']['label_button_search'][0]['target'] = 'test search button';
		// gibt es noch nicht
		$GLOBALS['LOCAL_LANG']['default']['my_test_label'][0]['target'] = 'my test label';
		$this->callInaccessibleMethod($this->searcher, 'init', $this->mod, array());

		self::assertEquals('test search button', $GLOBALS['LOCAL_LANG']['default']['label_button_search'][0]['target']);
		self::assertEquals('my test label', $GLOBALS['LOCAL_LANG']['default']['my_test_label'][0]['target']);
		// ist in der mklib locallang Datei und war vorher noch nicht da, sollte also
		// aus lollang Datei geladen werden
		self::assertEquals('Actions', $GLOBALS['LOCAL_LANG']['default']['label_tableheader_actions'][0]['target']);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']);
}