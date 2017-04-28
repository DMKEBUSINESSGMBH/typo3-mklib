<?php
/**
 * Copyright notice
 *
 * (c) 2012 DMK E-Business GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Die Klasse stellt Auswahlmenus zur Verfügung
 *
 * @package TYPO3
 * @subpackage tx_mklib
 * @author Michael Wagner
 * @author Hannes Bochmann
 */
class tx_mklib_mod1_util_Selector
{
    // @TODO: whats this? can be removed?
    public $doc;
    public $modName;

    /**
     * @var tx_rnbase_mod_IModule
     */
    private $mod;

    /**
     * Initialisiert das Objekt mit dem Template und der Modul-Config.
     */
    public function init(tx_rnbase_mod_IModule $module)
    {
        $this->mod = $module;
    }

    /**
     * Method to display a form with an input array, a description and a submit button.
     * Keys are 'field' and 'button'.
     *
     * @param   string  $out                HTML string
     * @param   string  $key                mod key
     * @param   array   $options
     *              string  buttonName      name of the submit button. default is key.
     *              string  buttonValue     value of the sumbit button. default is LLL:label_button_search.
     *              string  label           label of the sumbit button. default is LLL:label_search.
     * @return  string                      search term
     */
    public function showFreeTextSearchForm(&$out, $key, array $options = array())
    {
        $searchstring = $this->getValueFromModuleData($key);

        // Erst das Suchfeld, danach der Button.
        $out['field']    = $this->getFormTool()->createTxtInput('SET['.$key.']', $searchstring, 10);
        $out['button']    = empty($options['submit']) ? '' : $this->getFormTool()->createSubmit(
            $options['buttonName'] ? $options['buttonName'] : $key,
            $options['buttonValue'] ? $options['buttonValue'] : $GLOBALS['LANG']->getLL('label_button_search')
        );
        $out['label'] = $options['label'] ? $options['label'] : $GLOBALS['LANG']->getLL('label_search');

        return $searchstring;
    }

    /**
     * Returns a delete select box. All data is stored in array $data.
     * @param   array   $data
     * @param   array   $options
     * @return  bool
     */
    public function showHiddenSelector(&$data, $options = array())
    {
        $items = array(
            0 => $GLOBALS['LANG']->getLL('label_select_hide_hidden'),
            1 => $GLOBALS['LANG']->getLL('label_select_show_hidden'),
        );

        $options['label'] = $options['label'] ? $options['label'] : $GLOBALS['LANG']->getLL('label_hidden');

        return $this->showSelectorByArray($items, 'showhidden', $data, $options);
    }

    /**
     * Returns a delete select box. All data is stored in array $data.
     * @param   array   $data
     * @param   array   $options
     * @return  bool
     */
    public function showLanguageSelector(&$data, $options = array())
    {
        $items = array(
            '' => '',
            -1 => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages'),
            0 => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_general.xml:LGL.default_value'),
        );

        tx_rnbase::load('tx_mklib_mod1_util_Language');
        $langs = tx_mklib_mod1_util_Language::getLangRecords($options['pid']);
        foreach ($langs as $lang) {
            $items[(int) $lang['uid']] = $lang['title'];
        }

        $options['label'] = $options['label'] ? $options['label'] : $GLOBALS['LANG']->getLL('label_language');

        return $this->showSelectorByArray($items, 'language', $data, $options);
    }

    /**
     * Zeigt eine Datumsauswahl mit einzelnen Selects für Tag, Monat und Jahr.
     * @param array $aItems Array mit den werten der Auswahlbox
     * @param string $sDefId ID-String des Elements
     * @param array $aData enthält die Formularelement für die Ausgabe im Screen. Keys: selector, label
     * @param array $aOptions zusätzliche Optionen: yearfrom, yearto,
     * @return DateTime selected day
     */
    public function showDateSelector($sDefId, &$aData, $aOptions = array())
    {
        $baseId = isset($aOptions['id']) && $aOptions['id'] ? $aOptions['id'] : $sDefId;
        // Da es drei Felder gibt, benötigen wir drei IDs
        $dayId = $baseId.'_day';
        $monthId = $baseId.'_month';
        $yearId = $baseId.'_year';

        // Defaultwerte werden benötigt, wenn noch keine Eingabe erfolgte
        $aDefault = explode('-', $aOptions['default']);

        if (isset($aOptions['id'])) {
            unset($aOptions['id']);
        }
        // Monate
        $tmpDataMonth = array();
        $items = array();
        for ($i = 1; $i < 13; $i++) {
            $date = new DateTime();
            $items[$i] = $date->setDate(2000, $i, 1)->format('F');
        }
        $selectedMonth = $this->getValueFromModuleData($monthId);
        $selectedMonth = $this->showSelectorByArray($items, $monthId, $tmpDataMonth, array('forcevalue' => ($selectedMonth) ? $selectedMonth : $aDefault[1]));

        // Jahre
        $today = new DateTime();
        $from = intval($aOptions['yearfrom']);
        if (!$from) {
            // Default 10 Jahre. Damit wir PHP 5.2. verwenden können, die Berechnung etwas umständlich.
            $from = intval($today->format('Y')) - 10;
        }
        $to = intval($aOptions['yearto']);
        if (!$to) {
            $to = intval($today->format('Y'));
        }

        $tmpDataYear = array();
        $items = array();
        for ($i = $from; $i < $to; $i++) {
            $items[$i] = $i;
        }
        $selectedYear = $this->getValueFromModuleData($yearId);
        $selectedYear = $this->showSelectorByArray($items, $yearId, $tmpDataYear, array('forcevalue' => ($selectedYear) ? $selectedYear : $aDefault[0]));

        // Tage
        $tmpDataDay = array();
        $items = array();
        $totalDays = date('t', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
        for ($i = 1; $i < $totalDays + 1; $i++) {
            $items[$i] = $i;
        }
        $selectedDay = $this->getValueFromModuleData($dayId);
        $selectedDay = ($selectedDay > $totalDays) ? $totalDays : $selectedDay;
        $selectedDay = $this->showSelectorByArray($items, $dayId, $tmpDataDay, array('forcevalue' => ($selectedDay) ? $selectedDay : $aDefault[2]));

        // Rückgabe
        $aData['day_selector'] = $tmpDataDay['selector'];
        $aData['month_selector'] = $tmpDataMonth['selector'];
        $aData['year_selector'] = $tmpDataYear['selector'];

        $ret = new DateTime();
        $ret->setDate($selectedYear, $selectedMonth, $selectedDay);

        return $ret;
    }



    /**
     * Gibt einen selector mit den models im gegebenen array zurück
     *
     * @param Traversable|array $items
     * @param array $data enthält die Formularelement für die Ausgabe im Screen. Keys: selector, label
     * @param array $options zusätzliche Optionen: label, id
     * @return string selected item
     */
    protected function showSelectorByModels(
        $items,
        array &$data,
        array $options = array()
    ) {
        if (!(is_array($items) || $items instanceof Traversable)) {
            throw new Exception(
                'Argument 1 passed to' . __METHOD__ . '() must be of the type array or Traversable.'
            );
        }

        $id = $options['id'];
        if (empty($id)) {
            throw new Exception('No ID for widget given!');
        }

        $pid = $options['pid'] ? $options['pid'] : 0;

        $itemMenu = array();
        if (isset($options['entryall'])) {
            $itemMenu['0'] = is_string($options['entryall']) ? $options['entryall'] : $GLOBALS['LANG']->getLL('label_select_all_entries');
        }

        $titleMethod = $options['titlemethod'] ? $options['titlemethod'] : 'getTcaLabel';
        $idMethod = $options['idmethod'] ? $options['idmethod'] : 'getUid';
        $titleField = $options['titlefield'] ? $options['titlefield'] : 'title';
        $idField = $options['idfield'] ? $options['idfield'] : 'uid';

        foreach ($items as $item) {
            $uid = is_object($item) ? $item->{$idMethod}() : $item[$idField];
            $title = is_object($item) ? $item->{$titleMethod}() : $item[$titleField];
            $itemMenu[$uid] = $title;
        }

        $selectedItem = $this->getValueFromModuleData($id);

        // Build select box items
        $data['selector'] = Tx_Rnbase_Backend_Utility::getFuncMenu(
            $pid,
            'SET[' . $id . ']',
            $selectedItem,
            $itemMenu
        );

        return $selectedItem;
    }

    /**
     * Gibt einen selector mit den elementen im gegebenen array zurück
     * @param array $aItems Array mit den werten der Auswahlbox
     * @param string $sDefId ID-String des Elements
     * @param array $aData enthält die Formularelement für die Ausgabe im Screen. Keys: selector, label
     * @param array $aOptions zusätzliche Optionen: label, id
     * @return string selected item
     */
    protected function showSelectorByArray($aItems, $sDefId, &$aData, $aOptions = array())
    {
        $id = isset($aOptions['id']) && $aOptions['id'] ? $aOptions['id'] : $sDefId;

        $selectedItem = array_key_exists('forcevalue', $aOptions) ? $aOptions['forcevalue'] : $this->getValueFromModuleData($id);

        $pid = $aOptions['pid'] ? $aOptions['pid'] : 0;

        // Build select box items
        $aData['selector'] = Tx_Rnbase_Backend_Utility::getFuncMenu(
            $pid,
            'SET['.$id.']',
            $selectedItem,
            $aItems
        );

        //label
        $aData['label'] = $aOptions['label'];

        // as the deleted fe users have always to be hidden the function returns always false
        //@todo wozu die alte abfrage? return $defId==$id ? false : $selectedItem;
        return $selectedItem;
    }

    /**
     * Gibt einen selector mit den elementen im gegebenen array zurück
     * @return string selected item
     */
    protected function showSelectorByTCA($sDefId, $table, $column, &$aData, $aOptions = array())
    {
        $items = array();
        if (is_array($aOptions['additionalItems'])) {
            $items = $aOptions['additionalItems'];
        }
        if (is_array($GLOBALS['TCA'][$table]['columns'][$column]['config']['items'])) {
            foreach ($GLOBALS['TCA'][$table]['columns'][$column]['config']['items'] as $item) {
                $items[$item[1]] = $GLOBALS['LANG']->sL($item[0]);
            }
        }

        return $this->showSelectorByArray($items, $sDefId, $aData, $aOptions);
    }

    /**
     * Returns an instance of tx_rnbase_mod_IModule
     *
     * @return  tx_rnbase_mod_IModule
     */
    protected function getMod()
    {
        return $this->mod;
    }
    /**
     *
     * @return tx_rnbase_util_FormTool
     */
    protected function getFormTool()
    {
        return $this->getMod()->getFormTool();
    }

    /**
     * Return requested value from module data
     *
     * @param   string $key
     * @return  mixed
     */
    public function getValueFromModuleData($key)
    {
        // Fetch selected company trade
        $modData = Tx_Rnbase_Backend_Utility::getModuleData(
            array($key => ''),
            tx_rnbase_parameters::getPostOrGetParameter('SET'),
            $this->getMod()->getName()
        );
        if (isset($modData[$key])) {
            return $modData[$key];
        }

        return null;
    }

    /**
     * Setzt einen Wert in den Modul Daten. Dabei werden die bestehenden
     * ergänzt oder ggf. überschrieben
     *
     * @param   array $aModuleData
     * @return  void
     */
    public function setValueToModuleData($sModuleName, $aModuleData = array())
    {
        $aExistingModuleData = $GLOBALS['BE_USER']->getModuleData($sModuleName);
        if (!empty($aModuleData)) {
            foreach ($aModuleData as $sKey => $mValue) {
                $aExistingModuleData[$sKey] = $mValue;
            }
        }
        $GLOBALS['BE_USER']->pushModuleData($sModuleName, $aExistingModuleData);
    }


    /**
     *
     * @param   array   $data
     * @return  string
     */
    public function buildFilterTable(array $data)
    {
        $out = '';
        if (count($data)) {
            $out .= '<table class="filters">';
            foreach ($data as $label => $filter) {
                $out .= '<tr>';
                $out .= '<td>'. (isset($filter['label']) ? $filter['label'] : $label).'</td>';
                unset($filter['label']);
                $out .= '<td>'. implode(' ', $filter) .'</td>';

                $out .= '</tr>';
            }
            $out .= '</table>';
        }

        return $out;
    }

    /**
     * @param   array   $out                HTML string
     * @param   string  $key                mod key
     * @param   array   $options            some options like label
     * @return  array[to => int, from => int]
     */
    public function showDateRangeSelector(&$out, $key, $options = array())
    {
        $this->loadAdditionalJsForDatePicker();

        $fromValue = $this->getDateFieldByKey($key . '_from', $out);
        $toValue = $this->getDateFieldByKey($key . '_to', $out);
        $out['label'] = $options['label'] ? $options['label'] : $GLOBALS['LANG']->getLL('label_daterange');

        $this->setValueToModuleData(
            $this->getMod()->getName(),
            array($key . '_from' => $fromValue, $key . '_to' => $toValue)
        );

        return $this->getCrDateReturnArray($fromValue, $toValue);
    }

    /**
     * @return void
     */
    protected function loadAdditionalJsForDatePicker()
    {
        $this->getMod()->getDoc()->getPageRenderer()->loadPrototype();
        $this->getMod()->getDoc()->getPageRenderer()->loadExtJS();
        $this->getFormTool()->getTCEForm()->loadJavascriptLib('../t3lib/jsfunc.evalfield.js');
        $this->getFormTool()->getTCEForm()->loadJavascriptLib('jsfunc.tbe_editor.js');

        $typo3Settings = array(
            'datePickerUSmode' => 0,
            'dateFormat' => array('d-m-Y', 'G:i j-n-Y'),
            'dateFormatUS' => array('n-j-Y', 'G:i n-j-Y'),
        );
        $this->getMod()->getDoc()->getPageRenderer()->addInlineSettingArray('', $typo3Settings);

        tx_rnbase::load('tx_rnbase_util_TYPO3');
        if (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
            $this->getMod()->getDoc()->getPageRenderer()->addJsFile(
                'sysext/backend/Resources/Public/JavaScript/tceforms.js'
            );
            $this->getMod()->getDoc()->getPageRenderer()->addJsFile(
                'js/extjs/ux/Ext.ux.DateTimePicker.js'
            );
        } else {
            $this->getFormTool()->getTCEForm()->loadJavascriptLib('../t3lib/js/extjs/ux/Ext.ux.DateTimePicker.js');
            $this->getFormTool()->getTCEForm()->loadJavascriptLib('../t3lib/js/extjs/tceforms.js');
        }
    }

    /**
     * @param string $key
     *
     * @return string gewählte zeit in d-m-Y
     */
    private function getDateFieldByKey($key, &$out)
    {
        $value = isset($_POST[$key]) ? tx_rnbase_parameters::getPostOrGetParameter($key) : $this->getValueFromModuleData($key);

        tx_rnbase::load('tx_rnbase_mod_Util');
        // TODO: sollte das Element nicht über Tx_Rnbase_Backend_Form_ToolBox gebaut werden???
        $out['field'] .= '<input name="' . $key . '" type="text" id="tceforms-datefield-' . $key . '" value="' . $value . '" />' .
            tx_rnbase_mod_Util::getSpriteIcon(
                'actions-edit-pick-date',
                array(
                    'style' => 'cursor:pointer;',
                    'id' => 'picker-tceforms-datefield-' . $key
                )
            );

        return $value;
    }

    /**
     * @param string $fromValue d-m-Y
     * @param string $toValue d-m-Y
     *
     * @return array[to => int, from => int]
     */
    private function getCrDateReturnArray($fromValue, $toValue)
    {
        tx_rnbase::load('tx_rnbase_util_Dates');
        $fromTimestamp = tx_rnbase_util_Dates::date_mysql2tstamp(
            tx_rnbase_util_Dates::date_german2mysql($fromValue)
        );

        $toTimestamp = tx_rnbase_util_Dates::date_mysql2tstamp(
            tx_rnbase_util_Dates::date_german2mysql($toValue)
        );

        if ($toTimestamp) {
            $toTimestamp = $this->moveTimestampToTheEndOfTheDay($toTimestamp);
        }

        return array('from' => $fromTimestamp, 'to' => $toTimestamp);
    }

    /**
     * @param int $timestamp
     *
     * @return int
     */
    private function moveTimestampToTheEndOfTheDay($timestamp)
    {
        return $timestamp + 86400;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/util/class.tx_mklib_mod1_util_Selector.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/util/class.tx_mklib_mod1_util_Selector.php']);
}
