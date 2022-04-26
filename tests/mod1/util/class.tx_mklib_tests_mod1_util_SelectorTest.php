<?php
/**
 * @author Hannes Bochmann
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

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_tests_mod1_util_SelectorTest extends \Sys25\RnBase\Testing\BaseTestCase
{
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

    public function setUp(): void
    {
        $this->oSelector = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_mod1_util_Selector');
        self::markTestIncomplete('Class BaseScriptClass is deprecated and will be removed in TYPO3 v10.0');
        $this->oMod = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_tests_fixtures_classes_DummyMod');
        $this->oSelector->init($this->oMod);
        $this->sModuleKey = 'testSearch';

        // Modul daten zurücksetzen
        $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()] = null;
        $_GET['SET'] = null;

        // für cli
        $GLOBALS['TBE_TEMPLATE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Backend\Template\Override\DocumentTemplate::class);
        $GLOBALS['CLIENT']['FORMSTYLE'] = 'something';

        // sprache auf default setzen damit wir die richtigen labels haben
        $GLOBALS['LANG']->lang = 'default';

        // damit labels geladen sind
        global $LOCAL_LANG;
        // ab typo 4.6 ist das mit den lang labels anders
        $mHideEntry = 'Hide hidden entries';
        $mShowEntry = 'Show hidden entries';
        $LOCAL_LANG['default']['label_select_hide_hidden'][0]['target'] = $mHideEntry;
        $LOCAL_LANG['default']['label_select_show_hidden'][0]['target'] = $mShowEntry;

        $this->whitespaceByTypo3Version = ' ';
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        if (isset($_POST['test'])) {
            unset($_POST['test']);
        }

        if (isset($_POST['test_from'])) {
            unset($_POST['test_from']);
        }

        if (isset($_POST['test_to'])) {
            unset($_POST['test_to']);
        }
    }

    public function testBuildFilerTableWithoutData()
    {
        $aData = [];
        self::assertEmpty($this->oSelector->buildFilterTable($aData), 'return nicht leer.');
    }

    public function testBuildFilerTableWithData()
    {
        $aData = [
            'search' => [
                'field' => 'testField 1',
                'button' => 'testButton 1',
                'label' => 'testLabel 1',
            ],
            'hidden' => [
                'field' => 'testField 2',
                'button' => 'testButton 2',
            ],
        ];
        $sResult = $this->oSelector->buildFilterTable($aData);
        $sExpected = '<table class="filters"><tr><td>testLabel 1</td><td>testField 1 testButton 1</td></tr><tr><td>hidden</td><td>testField 2 testButton 2</td></tr></table>';
        self::assertEquals($sExpected, $sResult, 'return nicht korrekt.');
    }

    public function testShowFreeTextSearchFormWithEmptySearchString()
    {
        $out = [];
        $options = [
            'buttonName' => 'testName',
            'buttonValue' => 'testSearchValue',
            'label' => 'testLabel',
            'submit' => true, // wird vom searcher abstractBase gesetzt
        ];
        $sSearchString = $this->oSelector->showFreeTextSearchForm($out, $this->sModuleKey, $options);

        self::assertEmpty($sSearchString, 'suchstring ist nicht leer');
        self::assertEquals('<input type="text" name="SET[testSearch]" style="width:96px;" value="" />', $out['field'], 'field nicht korrekt.');
        self::assertEquals('<input type="submit"  class="btn btn-default btn-sm" name="testName" value="testSearchValue" />', $out['button'], 'button nicht korrekt.');
        self::assertEquals('testLabel', $out['label'], 'label nicht korrekt.');
    }

    public function testShowFreeTextSearchFormWithSearchString()
    {
        $out = [];
        $options = [
            'buttonName' => 'testName',
            'buttonValue' => 'testSearchValue',
            'label' => 'testLabel',
            'submit' => true, // wird vom searcher abstractBase gesetzt
        ];
        // suchstring setzen
        $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';

        $sSearchString = $this->oSelector->showFreeTextSearchForm($out, $this->sModuleKey, $options);

        self::assertEquals('joh316', $sSearchString, 'suchstring ist falsch');
        self::assertEquals('<input type="text" name="SET[testSearch]" style="width:96px;" value="joh316" />', $out['field'], 'field nicht korrekt.');
        self::assertEquals('<input type="submit"  class="btn btn-default btn-sm" name="testName" value="testSearchValue" />', $out['button'], 'button nicht korrekt.');
        self::assertEquals('testLabel', $out['label'], 'label nicht korrekt.');
    }

    public function testGetValueFromModuleDataWithExistingKey()
    {
        $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
        // key exists
        self::assertEquals('joh316', $this->oSelector->getValueFromModuleData($this->sModuleKey), 'falscher Wert. 1');
    }

    public function testGetValueFromModuleDataWithoutExistingKey()
    {
        $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
        self::assertEmpty($this->oSelector->getValueFromModuleData('someotherkey'), 'falscher Wert. 2');
    }

    public function testGetValueFromModuleDataWithNewValue()
    {
        $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
        $_POST = null;
        $_GET['SET'][$this->sModuleKey] = 'john doe';

        self::assertEquals('john doe', $this->oSelector->getValueFromModuleData($this->sModuleKey), 'falscher Wert. 3');
    }

    public function testSetValueToModuleDataWithEmptyData()
    {
        // vorhandene Daten
        $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
        $this->oSelector->setValueToModuleData($this->oMod->getName());
        // es sollten unr die vorhandenen Daten zurück kommen
        self::assertEquals(['testSearch' => 'joh316'], $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()], 'es gibt doch neue Daten');
    }

    public function testSetValueToModuleDataWithData()
    {
        // vorhandene Daten
        $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 'joh316';
        $this->oSelector->setValueToModuleData($this->oMod->getName(), ['newTestSearch' => 'john doe']);
        // es sollten auch die neuen Daten da sein
        self::assertEquals(['testSearch' => 'joh316', 'newTestSearch' => 'john doe'], $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()], 'es gibt doch neue Daten');
    }

    public function testShowHiddenSelectorWithDefaultId()
    {
        $data = [];
        $return = $this->oSelector->showHiddenSelector($data);

        self::assertRegExp(
            '/<select (?(?=>)|.*)name="SET\[showhidden\]" onchange="jumpToUrl\(/',
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
        self::assertNull($return, 'falscher return value');
    }

    public function testShowHiddenSelectorWithOneSelected()
    {
        $GLOBALS['BE_USER']->uc['moduleData'][$this->oMod->getName()][$this->sModuleKey] = 1;
        $this->oSelector->setValueToModuleData($this->oMod->getName(), [$this->sModuleKey => 1]);
        $data = [];
        $options = ['id' => $this->sModuleKey];
        $return = $this->oSelector->showHiddenSelector($data, $options);

        self::assertRegExp(
            '/<select (?(?=>)|.*)name="SET\[testSearch\]" onchange="jumpToUrl\(/',
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
        self::assertEquals(1, $return, 'falscher return value');
    }

    /**
     * @group unit
     */
    public function testGetCrDateReturnArrayFormatsDatesCorrect()
    {
        $selector = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_mod1_util_Selector');

        $method = new ReflectionMethod('tx_mklib_mod1_util_Selector', 'getCrDateReturnArray');
        $method->setAccessible(true);

        $returnArray = $method->invoke($selector, '2013-07-08T00:00:00Z', '2013-07-09T00:00:00Z');
        $expectedReturnArray = [
            'from' => 1373241600,
            'to' => 1373414400,
        ];
        self::assertEquals($expectedReturnArray, $returnArray, 'Datum falsch formatiert');
    }

    /**
     * @group unit
     */
    public function testGetCrDateReturnArrayFormatsDatesCorrectIfValuesEmpty()
    {
        $selector = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_mod1_util_Selector');

        $method = new ReflectionMethod('tx_mklib_mod1_util_Selector', 'getCrDateReturnArray');
        $method->setAccessible(true);

        $returnArray = $method->invoke($selector, '', '');
        $expectedReturnArray = [
            'from' => 0,
            'to' => 0,
        ];
        self::assertEquals($expectedReturnArray, $returnArray, 'Datum falsch formatiert');
    }

    /**
     * @group unit
     */
    public function testGetDateFieldByKeyWhenNoPost()
    {
        $key = 'test';
        $formTool = $this->getMock(\Sys25\RnBase\Backend\Form\ToolBox::class, ['createDateInput']);
        $formTool
            ->expects(self::once())
            ->method('createDateInput')
            ->with($key, 'testValue')
            ->will(self::returnValue('created'));

        $selector = $this->getMock('tx_mklib_mod1_util_Selector', ['getFormTool', 'getValueFromModuleData']);
        $selector
            ->expects(self::once())
            ->method('getFormTool')
            ->will(self::returnValue($formTool));
        $selector
            ->expects(self::once())
            ->method('getValueFromModuleData')
            ->with($key)
            ->will(self::returnValue('testValue'));

        $selector->init(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_tests_fixtures_classes_DummyMod'));

        $out = ['field' => ''];

        self::assertEquals(
            'testValue',
            $this->callInaccessibleMethod([$selector, 'getDateFieldByKey'], [$key, &$out])
        );
        self::assertEquals('created', $out['field']);
    }

    /**
     * @group unit
     */
    public function testGetDateFieldByKeyWhenPost()
    {
        $key = 'test';
        $_POST[$key] = 'valueFromPost';
        $formTool = $this->getMock(\Sys25\RnBase\Backend\Form\ToolBox::class, ['createDateInput']);
        $formTool
            ->expects(self::once())
            ->method('createDateInput')
            ->with($key, 'valueFromPost')
            ->will(self::returnValue('created'));

        $selector = $this->getMock('tx_mklib_mod1_util_Selector', ['getFormTool', 'getValueFromModuleData']);
        $selector
            ->expects(self::once())
            ->method('getFormTool')
            ->will(self::returnValue($formTool));
        $selector
            ->expects(self::never())
            ->method('getValueFromModuleData');

        $selector->init(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_tests_fixtures_classes_DummyMod'));

        $out = ['field' => ''];

        self::assertEquals(
            'valueFromPost',
            $this->callInaccessibleMethod([$selector, 'getDateFieldByKey'], [$key, &$out])
        );
        self::assertEquals('created', $out['field']);
    }

    /**
     * @group unit
     */
    public function testShowDateRangeSelectorReturnsCorrectTimestampArray()
    {
        $formTool = $this->getMock(\Sys25\RnBase\Backend\Form\ToolBox::class, ['createDateInput']);
        $formTool
            ->expects(self::any())
            ->method('createDateInput');

        $selector = $this->getMock(
            'tx_mklib_mod1_util_Selector',
            ['getFormTool']
        );
        $selector->init($this->oMod);

        $selector->expects(self::any())
            ->method('getFormTool')
            ->will(self::returnValue($formTool));

        $key = 'test';
        $out = ['field' => ''];
        $timestampArray = $selector->showDateRangeSelector($out, $key);

        $expectedReturnArray = [
            'from' => 0,
            'to' => 0,
        ];
        self::assertEquals($expectedReturnArray, $timestampArray, 'Datum falsch formatiert');
    }

    /**
     * @group unit
     */
    public function testShowDateRangeSelectorReturnsCorrectTimestampArrayWhenPostValuesSet()
    {
        $formTool = $this->getMock(\Sys25\RnBase\Backend\Form\ToolBox::class, ['createDateInput']);
        $formTool
            ->expects(self::any())
            ->method('createDateInput');

        $_POST['test_from'] = '2013-07-08T00:00:00Z';
        $_POST['test_to'] = '2013-07-09T00:00:00Z';

        $selector = $this->getMock(
            'tx_mklib_mod1_util_Selector',
            ['getFormTool', 'getCrDateReturnArray']
        );
        $selector->init($this->oMod);

        $selector->expects(self::any())
            ->method('getFormTool')
            ->will(self::returnValue($formTool));

        $expectedReturnArray = [
            'from' => 1373234400,
            'to' => 1373407200,
        ];
        $selector->expects(self::once())
            ->method('getCrDateReturnArray')
            ->with('2013-07-08T00:00:00Z', '2013-07-09T00:00:00Z')
            ->will(self::returnValue($expectedReturnArray));

        $key = 'test';
        $out = ['field' => ''];
        $timestampArray = $selector->showDateRangeSelector($out, $key);

        self::assertEquals($expectedReturnArray, $timestampArray, 'Datum falsch formatiert');
    }

    /**
     * @group unit
     */
    public function testShowDateRangeSelectorReturnsCorrectInputs()
    {
        $formTool = $this->getMock(\Sys25\RnBase\Backend\Form\ToolBox::class, ['createDateInput']);
        $formTool
            ->expects(self::at(0))
            ->method('createDateInput')
            ->with('test_from', null)
            ->will(self::returnValue('parsed_from'));
        $formTool
            ->expects(self::at(1))
            ->method('createDateInput')
            ->with('test_to', null)
            ->will(self::returnValue('parsed_to'));

        $selector = $this->getMock(
            'tx_mklib_mod1_util_Selector',
            ['getFormTool']
        );
        $selector->init($this->oMod);

        $selector->expects(self::any())
            ->method('getFormTool')
            ->will(self::returnValue($formTool));

        $key = 'test';
        $out = ['field' => ''];
        $selector->showDateRangeSelector($out, $key);

        $fieldHtml = tx_mklib_util_String::removeMultipleWhitespaces($out['field']);

        self::assertContains('parsed_to', $fieldHtml);
        self::assertContains('parsed_from', $fieldHtml);
    }

    /**
     * @group unit
     */
    public function testShowDateRangeSelectorReturnsCorrectInputsWhenPostValuesSet()
    {
        $_POST['test_from'] = '2013-07-08';
        $_POST['test_to'] = '2013-07-09';

        $formTool = $this->getMock(\Sys25\RnBase\Backend\Form\ToolBox::class, ['createDateInput']);
        $formTool
            ->expects(self::at(0))
            ->method('createDateInput')
            ->with('test_from', '2013-07-08')
            ->will(self::returnValue('parsed_from'));
        $formTool
            ->expects(self::at(1))
            ->method('createDateInput')
            ->with('test_to', '2013-07-09')
            ->will(self::returnValue('parsed_to'));

        $selector = $this->getMock(
            'tx_mklib_mod1_util_Selector',
            ['getFormTool']
        );
        $selector->init($this->oMod);

        $selector->expects(self::any())
            ->method('getFormTool')
            ->will(self::returnValue($formTool));

        $key = 'test';
        $out = ['field' => ''];
        $selector->showDateRangeSelector($out, $key);

        $fieldHtml = tx_mklib_util_String::removeMultipleWhitespaces($out['field']);

        self::assertContains('parsed_to', $fieldHtml);
        self::assertContains('parsed_from', $fieldHtml);
    }

    /**
     * @group unit
     */
    public function testShowDateRangeSelectorSetModuleDataCorrect()
    {
        $formTool = $this->getMock(\Sys25\RnBase\Backend\Form\ToolBox::class, ['createDateInput']);
        $formTool
            ->expects(self::any())
            ->method('createDateInput');

        $_POST['test_from'] = '2013-07-08';
        $_POST['test_to'] = '2013-07-09';

        $selector = $this->getMock(
            'tx_mklib_mod1_util_Selector',
            ['getFormTool', 'setValueToModuleData']
        );
        $selector->init($this->oMod);

        $selector->expects(self::any())
            ->method('getFormTool')
            ->will(self::returnValue($formTool));

        $key = 'test';
        $selector->expects(self::any())
            ->method('setValueToModuleData')
            ->with('dummyMod', [$key.'_from' => $_POST['test_from'], $key.'_to' => $_POST['test_to']]);

        $out = ['field' => ''];
        $selector->showDateRangeSelector($out, $key);
    }
}
