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


tx_rnbase::load('tx_mklib_mod1_util_SearchBuilder');
tx_rnbase::load('tx_mklib_tests_mod1_Util');
tx_rnbase::load('tx_rnbase_util_TYPO3');

/**
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests_mod1_util
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_tests_mod1_util_Selector_testcase extends Tx_Phpunit_TestCase {
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
	private $whitespaceByTypo3Version;

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
		$GLOBALS['TBE_TEMPLATE'] = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getDocumentTemplateClass());
		$GLOBALS['CLIENT']['FORMSTYLE'] = 'something';

		//sprache auf default setzen damit wir die richtigen labels haben
		$GLOBALS['LANG']->lang = 'default';

		//damit labels geladen sind
		global $LOCAL_LANG;
		//ab typo 4.6 ist das mit den lang labels anders
		$mHideEntry = 'Hide hidden entries';
		$mShowEntry = 'Show hidden entries';
		if(tx_rnbase_util_TYPO3::isTYPO46OrHigher()){
			$LOCAL_LANG['default']['label_select_hide_hidden'][0]['target'] = $mHideEntry;
			$LOCAL_LANG['default']['label_select_show_hidden'][0]['target'] = $mShowEntry;
		}else{
			$LOCAL_LANG['default']['label_select_hide_hidden'] = $mHideEntry;
			$LOCAL_LANG['default']['label_select_show_hidden'] = $mShowEntry;
		}

		// sonst fehler die icon klassen
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			\TYPO3\CMS\Backend\Sprite\SpriteManager::initialize();
		}

		$this->whitespaceByTypo3Version = tx_rnbase_util_TYPO3::isTYPO76OrHigher() ? ' ' : '&nbsp;';
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		if(isset($_POST['test'] )) {
			unset($_POST['test']);
		}

		if(isset($_POST['test_from'] )) {
			unset($_POST['test_from']);
		}

		if(isset($_POST['test_to'] )) {
			unset($_POST['test_to']);
		}

	}

	public function testBuildFilerTableWithoutData() {
		$aData = array();
		self::assertEmpty($this->oSelector->buildFilterTable($aData), 'return nicht leer.');
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
		self::assertEquals($sExpected, $sResult, 'return nicht korrekt.');
	}

	public function testShowFreeTextSearchFormWithEmptySearchString() {
		$out = array();
		$options = array(
			'buttonName' => 'testName',
			'buttonValue' => 'testSearchValue',
			'label' => 'testLabel',
			'submit' => true, // wird vom searcher abstractBase gesetzt
		);
		$sSearchString = $this->oSelector->showFreeTextSearchForm($out, $this->sModuleKey, $options);

		self::assertEmpty($sSearchString,'suchstring ist nicht leer');
		self::assertEquals('<input type="text" name="SET[testSearch]" style="width:96px;" value="" />', $out['field'], 'field nicht korrekt.');
		self::assertEquals('<input type="submit" name="testName" value="testSearchValue" />', $out['button'], 'button nicht korrekt.');
		self::assertEquals('testLabel', $out['label'], 'label nicht korrekt.');
	}

	public function testShowFreeTextSearchFormWithSearchString() {
		$out = array();
		$options = array(
			'buttonName' => 'testName',
			'buttonValue' => 'testSearchValue',
			'label' => 'testLabel',
			'submit' => true, // wird vom searcher abstractBase gesetzt
		);
		//suchstring setzen
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';

		$sSearchString = $this->oSelector->showFreeTextSearchForm($out, $this->sModuleKey, $options);

		self::assertEquals('joh316',$sSearchString,'suchstring ist falsch');
		self::assertEquals('<input type="text" name="SET[testSearch]" style="width:96px;" value="joh316" />', $out['field'], 'field nicht korrekt.');
		self::assertEquals('<input type="submit" name="testName" value="testSearchValue" />', $out['button'], 'button nicht korrekt.');
		self::assertEquals('testLabel', $out['label'], 'label nicht korrekt.');
	}

	public function testGetValueFromModuleDataWithExistingKey() {
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
		//key exists
		self::assertEquals('joh316', $this->oSelector->getValueFromModuleData($this->sModuleKey),'falscher Wert. 1');
	}

	public function testGetValueFromModuleDataWithoutExistingKey() {
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
		self::assertEmpty($this->oSelector->getValueFromModuleData('someotherkey'),'falscher Wert. 2');
	}

	public function testGetValueFromModuleDataWithNewValue() {
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
		$_POST = null;
		$_GET['SET'][$this->sModuleKey] = 'john doe';

		self::assertEquals('john doe', $this->oSelector->getValueFromModuleData($this->sModuleKey),'falscher Wert. 3');
	}

	public function testSetValueToModuleDataWithEmptyData() {
		//vorhandene Daten
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
		$aModuleData = $this->oSelector->setValueToModuleData($this->oMod->getName());
		//es sollten unr die vorhandenen Daten zurück kommen
		self::assertEquals(array('testSearch' => 'joh316'), $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()], 'es gibt doch neue Daten');
	}

	public function testSetValueToModuleDataWithData() {
		//vorhandene Daten
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
		$aModuleData = $this->oSelector->setValueToModuleData($this->oMod->getName(),array('newTestSearch' => 'john doe'));
		//es sollten auch die neuen Daten da sein
		self::assertEquals(array('testSearch' => 'joh316','newTestSearch' => 'john doe'), $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()], 'es gibt doch neue Daten');
	}

	public function testShowHiddenSelectorWithDefaultId() {
		$data = array();
		$return = $this->oSelector->showHiddenSelector($data);

		self::assertContains(
			'<select name="SET[showhidden]" onchange="jumpToUrl',
			$data['selector'],
			'falscher selector'
		);
		self::assertContains(
			'<option value="0">Hide hidden entries</option>',
			$data['selector'],
			'falscher selector'
		);
		self::assertContains(
			'<option value="1">Show hidden entries</option>',
			$data['selector'],
			'falscher selector'
		);
		self::assertContains(
			'</select>',
			$data['selector'],
			'falscher selector'
		);
		self::assertNull($return,'falscher return value');
	}

	public function testShowHiddenSelectorWithOneSelected() {
		$GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 1;
		$this->oSelector->setValueToModuleData($this->oMod->getName(),array($this->sModuleKey => 1));
		$data = array();
		$options = array('id' => $this->sModuleKey);
		$return = $this->oSelector->showHiddenSelector($data,$options);

		self::assertContains(
			'<select name="SET[testSearch]" onchange="jumpToUrl',
			$data['selector'],
			'falscher selector'
		);
		self::assertContains(
			'<option value="0">Hide hidden entries</option>',
			$data['selector'],
			'falscher selector'
		);
		self::assertContains(
			'<option value="1" selected="selected">Show hidden entries</option>',
			$data['selector'],
			'falscher selector'
		);
		self::assertContains(
			'</select>',
			$data['selector'],
			'falscher selector'
		);
		self::assertEquals(1, $return,'falscher return value');
	}


	/**
	 * @group unit
	 */
	public function testGetCrDateReturnArrayFormatsDatesCorrect() {
		$selector = tx_rnbase::makeInstance('tx_mklib_mod1_util_Selector');

		$method = new ReflectionMethod('tx_mklib_mod1_util_Selector', 'getCrDateReturnArray');
		$method->setAccessible(true);

		$returnArray = $method->invoke($selector, '08-07-2013', '09-07-2013');
		$expectedReturnArray = array(
			'from'	=> 1373234400,
			'to'	=> 1373407200
		);
		self::assertEquals($expectedReturnArray, $returnArray, 'Datum falsch formatiert');
	}

	/**
	 * @group unit
	 */
	public function testGetCrDateReturnArrayFormatsDatesCorrectIfValuesEmpty() {
		$selector = tx_rnbase::makeInstance('tx_mklib_mod1_util_Selector');

		$method = new ReflectionMethod('tx_mklib_mod1_util_Selector', 'getCrDateReturnArray');
		$method->setAccessible(true);

		$returnArray = $method->invoke($selector, '', '');
		$expectedReturnArray = array(
			'from'	=> 0,
			'to'	=> 0
		);
		self::assertEquals($expectedReturnArray, $returnArray, 'Datum falsch formatiert');
	}

	/**
	 * @group unit
	 */
	public function testGetDateFieldByKeyWhenNoPostValueReturnsEmptyValue() {
		$selector = $this->oSelector;

		$method = new ReflectionMethod('tx_mklib_mod1_util_Selector', 'getDateFieldByKey');
		$method->setAccessible(true);

		$key = 'test';
		$out = array('field' => '');
		$arguments = array($key, &$out);
		$returnValue = $method->invokeArgs($selector, $arguments);

		self::assertEmpty($returnValue, 'doch ein return value');
	}

	/**
	 * @group unit
	 */
	public function testGetDateFieldByKeyWhenNoPostValueSetsInputCorrect() {
		$selector = $this->oSelector;

		$method = new ReflectionMethod('tx_mklib_mod1_util_Selector', 'getDateFieldByKey');
		$method->setAccessible(true);

		$key = 'test';
		$out = array('field' => '');
		$arguments = array($key, &$out);
		$returnValue = $method->invokeArgs($selector, $arguments);

		$expectedInput = '<input name="test" type="text" id="tceforms-datefield-test" ' .
			'value="" /><span style="cursor:pointer;" id="picker-tceforms-datefield-test" ' .
			'class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-pick-date">' .
			$this->whitespaceByTypo3Version . '</span>';

		self::assertEquals($expectedInput, tx_mklib_util_String::removeMultipleWhitespaces($out['field']), 'input feld falsch');
	}

	/**
	 * @group unit
	 */
	public function testGetDateFieldByKeyKeepsExistingOutFieldValue() {
		$selector = $this->oSelector;

		$method = new ReflectionMethod('tx_mklib_mod1_util_Selector', 'getDateFieldByKey');
		$method->setAccessible(true);

		$key = 'test';
		$out = array('field' => 'test');
		$arguments = array($key, &$out);
		$returnValue = $method->invokeArgs($selector, $arguments);

		$expectedInput = 'test<input name="test" type="text" id="tceforms-datefield-test" ' .
			'value="" /><span style="cursor:pointer;" id="picker-tceforms-datefield-test" ' .
			'class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-pick-date">' .
			$this->whitespaceByTypo3Version . '</span>';
		self::assertEquals($expectedInput, $out['field'], 'input feld falsch');
	}

	/**
	 * @group unit
	 */
	public function testGetDateFieldByKeyWhenPostValueReturnsCorrectValue() {
		$_POST['test'] = 123;
		$selector = $this->oSelector;

		$method = new ReflectionMethod('tx_mklib_mod1_util_Selector', 'getDateFieldByKey');
		$method->setAccessible(true);

		$key = 'test';
		$out = array('field' => '');
		$arguments = array($key, &$out);
		$returnValue = $method->invokeArgs($selector, $arguments);

		self::assertEquals(123, $returnValue, 'return value falsch');
	}

	/**
	 * @group unit
	 */
	public function testGetDateFieldByKeyWhenPostValueSetsInputCorrect() {
		$_POST['test'] = 123;
		$selector = $this->oSelector;

		$method = new ReflectionMethod('tx_mklib_mod1_util_Selector', 'getDateFieldByKey');
		$method->setAccessible(true);

		$key = 'test';
		$out = array('field' => '');
		$arguments = array($key, &$out);
		$returnValue = $method->invokeArgs($selector, $arguments);

		$expectedInput = '<input name="test" type="text" id="tceforms-datefield-test" ' .
			'value="123" /><span style="cursor:pointer;" id="picker-tceforms-datefield-test" ' .
			'class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-pick-date">' .
			$this->whitespaceByTypo3Version . '</span>';
		self::assertEquals($expectedInput, $out['field'], 'input feld falsch');
	}

	/**
	 * @group unit
	 */
	public function testGetDateFieldByKeyWhenPrefersGetPostDataOverModuleData() {
		$_POST['test'] = 123;
		$selector = $this->getMock(
			'tx_mklib_mod1_util_Selector',
			array('getValueFromModuleData')
		);
		$selector->init($this->oMod);

		$key = 'test';
		$selector->expects(self::never())
			->method('getValueFromModuleData');

		$method = new ReflectionMethod('tx_mklib_mod1_util_Selector', 'getDateFieldByKey');
		$method->setAccessible(true);

		$out = array('field' => '');
		$arguments = array($key, &$out);
		$returnValue = $method->invokeArgs($selector, $arguments);

		self::assertEquals(123, $returnValue, 'return value falsch');
	}

	/**
	 * @group unit
	 */
	public function testGetDateFieldByKeyWhenUsesModuleDataWhenNoPostData() {
		$selector = $this->getMock(
			'tx_mklib_mod1_util_Selector',
			array('getValueFromModuleData')
		);
		$selector->init($this->oMod);

		$key = 'test';
		$selector->expects(self::once())
			->method('getValueFromModuleData')
			->with($key)
			->will(self::returnValue('test'));

		$method = new ReflectionMethod('tx_mklib_mod1_util_Selector', 'getDateFieldByKey');
		$method->setAccessible(true);

		$out = array('field' => '');
		$arguments = array($key, &$out);
		$returnValue = $method->invokeArgs($selector, $arguments);

		self::assertEquals('test', $returnValue, 'return value falsch');
	}

	/**
	 * @group unit
	 */
	public function testShowDateRangeSelectorReturnsCorrectTimestampArray() {
		$selector = $this->getMock(
			'tx_mklib_mod1_util_Selector',
			array('loadAdditionalJsForDatePicker', 'getFormTool')
		);
		$selector->init($this->oMod);

		$selector->expects(self::any())
			->method('getFormTool')
			->will(self::returnValue(tx_rnbase::makeInstance('tx_rnbase_util_FormTool')));

		$key = 'test';
		$out = array('field' => '');
		$timestampArray = $selector->showDateRangeSelector($out, $key);

		$expectedReturnArray = array(
			'from'	=> 0,
			'to'	=> 0
		);
		self::assertEquals($expectedReturnArray, $timestampArray, 'Datum falsch formatiert');
	}

	/**
	 * @group unit
	 */
	public function testShowDateRangeSelectorReturnsCorrectTimestampArrayWhenPostValuesSet() {
		$_POST['test_from'] = '08-07-2013';
		$_POST['test_to'] = '09-07-2013';

		$selector = $this->getMock(
			'tx_mklib_mod1_util_Selector',
			array('loadAdditionalJsForDatePicker', 'getFormTool')
		);
		$selector->init($this->oMod);

		$selector->expects(self::any())
			->method('getFormTool')
			->will(self::returnValue(tx_rnbase::makeInstance('tx_rnbase_util_FormTool')));

		$key = 'test';
		$out = array('field' => '');
		$timestampArray = $selector->showDateRangeSelector($out, $key);

		$expectedReturnArray = array(
			'from'	=> 1373234400 ,
			'to'	=> 1373407200
		);
		self::assertEquals($expectedReturnArray, $timestampArray, 'Datum falsch formatiert');
	}

	/**
	 * @group unit
	 */
	public function testShowDateRangeSelectorReturnsCorrectInputs() {
		$selector = $this->getMock(
			'tx_mklib_mod1_util_Selector',
			array('loadAdditionalJsForDatePicker', 'getFormTool')
		);
		$selector->init($this->oMod);

		$selector->expects(self::any())
			->method('getFormTool')
			->will(self::returnValue(tx_rnbase::makeInstance('tx_rnbase_util_FormTool')));

		$key = 'test';
		$out = array('field' => '');
		$selector->showDateRangeSelector($out, $key);

		$expectedOut = '<input name="test_from" type="text" id="tceforms-datefield-test_from" ' .
			'value="" /><span style="cursor:pointer;" id="picker-tceforms-datefield-test_from" ' .
			'class="t3-icon t3-icon-actions t3-icon-actions-edit t3-icon-edit-pick-date">' .
			$this->whitespaceByTypo3Version . '</span><input name="test_to" type="text" ' .
			'id="tceforms-datefield-test_to" value="" /><span style="cursor:pointer;" ' .
			'id="picker-tceforms-datefield-test_to" class="t3-icon ' .
			't3-icon-actions t3-icon-actions-edit t3-icon-edit-pick-date">' .
			$this->whitespaceByTypo3Version . '</span>';
		self::assertEquals($expectedOut, $out['field'], 'out falsch');
	}

	/**
	 * @group unit
	 */
	public function testShowDateRangeSelectorReturnsCorrectInputsWhenPostValuesSet() {
		$_POST['test_from'] = '08-07-2013';
		$_POST['test_to'] = '09-07-2013';

		$selector = $this->getMock(
			'tx_mklib_mod1_util_Selector',
			array('loadAdditionalJsForDatePicker', 'getFormTool')
		);
		$selector->init($this->oMod);

		$selector->expects(self::any())
			->method('getFormTool')
			->will(self::returnValue(tx_rnbase::makeInstance('tx_rnbase_util_FormTool')));

		$key = 'test';
		$out = array('field' => '');
		$selector->showDateRangeSelector($out, $key);

		$expectedOut = '<input name="test_from" type="text" id="tceforms-datefield-test_from" ' .
			'value="08-07-2013" /><span style="cursor:pointer;" id="picker-tceforms-' .
			'datefield-test_from" class="t3-icon t3-icon-actions t3-icon-actions-edit ' .
			't3-icon-edit-pick-date">' . $this->whitespaceByTypo3Version . '</span><input ' .
			'name="test_to" type="text" ' .
			'id="tceforms-datefield-test_to" value="09-07-2013" /><span style="cursor:pointer;" ' .
			'id="picker-tceforms-datefield-test_to" class="t3-icon t3-icon-actions ' .
			't3-icon-actions-edit t3-icon-edit-pick-date">' .
			$this->whitespaceByTypo3Version . '</span>';
		self::assertEquals($expectedOut, $out['field'], 'out falsch');
	}

	/**
	 * @group unit
	 */
	public function testShowDateRangeSelectorSetModuleDataCorrect() {
		$_POST['test_from'] = '08-07-2013';
		$_POST['test_to'] = '09-07-2013';

		$selector = $this->getMock(
			'tx_mklib_mod1_util_Selector',
			array('loadAdditionalJsForDatePicker', 'getFormTool', 'setValueToModuleData')
		);
		$selector->init($this->oMod);

		$selector->expects(self::any())
			->method('getFormTool')
			->will(self::returnValue(tx_rnbase::makeInstance('tx_rnbase_util_FormTool')));

		$key = 'test';
		$selector->expects(self::any())
			->method('setValueToModuleData')
			->with('dummyMod', array($key . '_from' => $_POST['test_from'], $key . '_to' => $_POST['test_to']));

		$out = array('field' => '');
		$selector->showDateRangeSelector($out, $key);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']);
}