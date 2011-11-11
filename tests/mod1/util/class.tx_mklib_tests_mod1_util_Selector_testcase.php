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
tx_rnbase::load('tx_mklib_mod1_util_SearchBuilder');
tx_rnbase::load('tx_mklib_tests_mod1_Util');

/**
 * 
 * @package tx_mklib
 * @subpackage tx_mklib_tests_mod1_util
 * @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 */
class tx_mklib_tests_mod1_util_Selector_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_mklib_mod1_decorator_Base
	 */
	protected $oDecorator;
	
	/**
	 * @var tx_mklib_mod1_util_Selector
	 */
	protected $oSelector;
	
	/**
	 * @var tx_mklib_tests_fixtures_classes_DummyMod
	 */
	protected $oMod;
	
	/**
	 * @var string
	 */
	protected $sModuleKey;
	
	public function setUp() {
		$this->oSelector = tx_rnbase::makeInstance('tx_mklib_mod1_util_Selector');
		$this->oMod = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_DummyMod');
		$this->oSelector->init($this->oMod);
		$this->sModuleKey = 'testSearch';
		
		//Modul daten zurücksetzen
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()] = null;
		$_GET['SET'] = null;
		
		//für cli
		$GLOBALS['TBE_TEMPLATE'] = t3lib_div::makeInstance('template');
		$GLOBALS['CLIENT']['FORMSTYLE'] = 'something';
		
		//damit labels geladen sind
		global $LOCAL_LANG;
		$LOCAL_LANG['default']['label_select_hide_hidden'] = 'Hide hidden entries';
		$LOCAL_LANG['default']['label_select_show_hidden'] = 'Show hidden entries';
	}
	
	public function testBuildFilerTableWithoutData() {
		$aData = array();
		$this->assertEmpty($this->oSelector->buildFilterTable($aData), 'return nicht leer.');
	}
	
	public function testBuildFilerTableWithData() {
		$aData = array(
			'search' => array(
				'field' => 'testField 1',
				'button' => 'testButton 1',
				'label' => 'testLabel 1'
			),
			'hidden' => array(
				'field' => 'testField 2',
				'button' => 'testButton 2'
			),
		);
		$sResult = $this->oSelector->buildFilterTable($aData);
		$sExpected = '<table class="filters"><tr><td>testLabel 1</td><td>testField 1 testButton 1</td></tr><tr><td>hidden</td><td>testField 2 testButton 2</td></tr></table>';
		$this->assertEquals($sExpected, $sResult, 'return nicht korrekt.');
	}
	
	public function testShowFreeTextSearchFormWithEmptySearchString() {
		$out = array();
		$options = array(
				'buttonName' => 'testName',
				'buttonValue' => 'testSearchValue',
				'label' => 'testLabel',
			);
		$sSearchString = $this->oSelector->showFreeTextSearchForm($out, $this->sModuleKey, $options);
		
		$this->assertEmpty($sSearchString,'suchstring ist nicht leer');
		$this->assertEquals('<input type="text" name="SET[testSearch]" style="width:96px;" value="" />', $out['field'], 'field nicht korrekt.');
		$this->assertEquals('<input type="submit" name="testName" value="testSearchValue" />', $out['button'], 'button nicht korrekt.');
		$this->assertEquals('testLabel', $out['label'], 'label nicht korrekt.');
	}
	
	public function testShowFreeTextSearchFormWithSearchString() {
		$out = array();
		$options = array(
			'buttonName' => 'testName',
			'buttonValue' => 'testSearchValue',
			'label' => 'testLabel',
		);
		//suchstring setzen
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
		
		$sSearchString = $this->oSelector->showFreeTextSearchForm($out, $this->sModuleKey, $options);
		
		$this->assertEquals('joh316',$sSearchString,'suchstring ist falsch');
		$this->assertEquals('<input type="text" name="SET[testSearch]" style="width:96px;" value="joh316" />', $out['field'], 'field nicht korrekt.');
		$this->assertEquals('<input type="submit" name="testName" value="testSearchValue" />', $out['button'], 'button nicht korrekt.');
		$this->assertEquals('testLabel', $out['label'], 'label nicht korrekt.');
	}
	
	public function testGetValueFromModuleDataWithExistingKey() {
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
		//key exists
		$this->assertEquals('joh316', $this->oSelector->getValueFromModuleData($this->sModuleKey),'falscher Wert. 1');
	}
	
	public function testGetValueFromModuleDataWithoutExistingKey() {
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
		$this->assertEmpty($this->oSelector->getValueFromModuleData('someotherkey'),'falscher Wert. 2');
	}
	
	public function testGetValueFromModuleDataWithNewValue() {
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
		$_POST = null;
		$_GET['SET'][$this->sModuleKey] = 'john doe';
		
		$this->assertEquals('john doe', $this->oSelector->getValueFromModuleData($this->sModuleKey),'falscher Wert. 3');
	}

	public function testSetValueToModuleDataWithEmptyData() {
		//vorhandene Daten
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
		$aModuleData = $this->oSelector->setValueToModuleData($this->oMod->getName());
		//es sollten unr die vorhandenen Daten zurück kommen
		$this->assertEquals(array('testSearch' => 'joh316'), $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()], 'es gibt doch neue Daten');
	}
	
	public function testSetValueToModuleDataWithData() {
		//vorhandene Daten
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
		$aModuleData = $this->oSelector->setValueToModuleData($this->oMod->getName(),array('newTestSearch' => 'john doe'));
		//es sollten auch die neuen Daten da sein
		$this->assertEquals(array('testSearch' => 'joh316','newTestSearch' => 'john doe'), $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()], 'es gibt doch neue Daten');
	}
	
	public function testShowHiddenSelectorWithDefaultId() {
		$data = array();
		$return = $this->oSelector->showHiddenSelector($data);
		$sExpected = file_get_contents(t3lib_extMgm::extPath('mklib').'tests/fixtures/html/hiddenSelector1.html');
		//auf der CLI müssen einige Dinge ersetzt werden
		tx_mklib_tests_mod1_Util::replaceForCli($sExpected);
		$this->assertEquals($sExpected, $data['selector'],'falscher selector');
		$this->assertNull($return,'falscher return value');
	}
	
	public function testShowHiddenSelectorWithOneSelected() {
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 1;
		$this->oSelector->setValueToModuleData($this->oMod->getName(),array($this->sModuleKey => 1));
		$data = array();
		$options = array('id' => $this->sModuleKey);
		$return = $this->oSelector->showHiddenSelector($data,$options);
		//auf der CLI müssen einige Dinge ersetzt werden
		$sExpected = file_get_contents(t3lib_extMgm::extPath('mklib').'tests/fixtures/html/hiddenSelector2.html');
		tx_mklib_tests_mod1_Util::replaceForCli($sExpected);
		
		$this->assertEquals($sExpected, $data['selector'],'falscher selector');
		$this->assertEquals(1, $return,'falscher return value');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']);
}