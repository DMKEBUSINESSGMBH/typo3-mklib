<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mklib" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Die Klasse stellt Auswahlmenus zur Verfügung.
 *
 * @author Michael Wagner
 * @author Hannes Bochmann
 */
class tx_mklib_mod1_util_Selector
{
    // @TODO: whats this? can be removed?
    public $doc;

    public $modName;

    private ?Sys25\RnBase\Backend\Module\IModule $mod = null;

    /**
     * Initialisiert das Objekt mit dem Template und der Modul-Config.
     */
    public function init(Sys25\RnBase\Backend\Module\IModule $module): void
    {
        $this->mod = $module;
    }

    /**
     * Method to display a form with an input array, a description and a submit button.
     * Keys are 'field' and 'button'.
     *
     * @param string $out     HTML string
     * @param string $key     mod key
     * @param array  $options
     *                        string  buttonName      name of the submit button. default is key.
     *                        string  buttonValue     value of the sumbit button. default is LLL:label_button_search.
     *                        string  label           label of the sumbit button. default is LLL:label_search.
     *
     * @return string search term
     */
    public function showFreeTextSearchForm(&$out, string $key, array $options = [])
    {
        $searchstring = $this->getValueFromModuleData($key);

        // Erst das Suchfeld, danach der Button.
        $out['field'] = $this->getFormTool()->createTxtInput('SET['.$key.']', $searchstring, 10);
        $out['button'] = empty($options['submit']) ? '' : $this->getFormTool()->createSubmit(
            $options['buttonName'] ?? $key,
            $options['buttonValue'] ?? $this->mod->getLanguageService()->getLL('label_button_search')
        );
        $out['label'] = $options['label'] ?? $this->mod->getLanguageService()->getLL('label_search');

        return $searchstring;
    }

    /**
     * Returns a delete select box. All data is stored in array $data.
     *
     * @return bool
     */
    public function showHiddenSelector(array &$data, array $options = [])
    {
        $items = [
            0 => $this->mod->getLanguageService()->getLL('label_select_hide_hidden'),
            1 => $this->mod->getLanguageService()->getLL('label_select_show_hidden'),
        ];

        $options['label'] ??= $this->mod->getLanguageService()->getLL('label_hidden');

        return $this->showSelectorByArray($items, 'showhidden', $data, $options);
    }

    /**
     * Returns a delete select box. All data is stored in array $data.
     *
     * @return bool
     */
    public function showLanguageSelector(array &$data, array $options = [])
    {
        $items = [
            '' => '',
            -1 => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages'),
            0 => $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_general.xml:LGL.default_value'),
        ];

        $langs = tx_mklib_mod1_util_Language::getLangRecords($options['pid']);
        foreach ($langs as $lang) {
            $items[(int) $lang['uid']] = $lang['title'];
        }

        $options['label'] = $options['label'] ?: $this->mod->getLanguageService()->getLL('label_language');

        return $this->showSelectorByArray($items, 'language', $data, $options);
    }

    /**
     * Zeigt eine Datumsauswahl mit einzelnen Selects für Tag, Monat und Jahr.
     *
     * @param string $sDefId   ID-String des Elements
     * @param array  $aData    enthält die Formularelement für die Ausgabe im Screen. Keys: selector, label
     * @param array  $aOptions zusätzliche Optionen: yearfrom, yearto,
     *
     * @return DateTime selected day
     */
    public function showDateSelector($sDefId, array &$aData, array $aOptions = []): DateTime
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
        $tmpDataMonth = [];
        $items = [];
        for ($i = 1; $i < 13; ++$i) {
            $date = new DateTime();
            $items[$i] = $date->setDate(2000, $i, 1)->format('F');
        }

        $selectedMonth = $this->getValueFromModuleData($monthId);
        $selectedMonth = $this->showSelectorByArray($items, $monthId, $tmpDataMonth, ['forcevalue' => $selectedMonth ?: $aDefault[1]]);

        // Jahre
        $today = new DateTime();
        $from = intval($aOptions['yearfrom']);
        if (0 === $from) {
            // Default 10 Jahre. Damit wir PHP 5.2. verwenden können, die Berechnung etwas umständlich.
            $from = intval($today->format('Y')) - 10;
        }

        $to = intval($aOptions['yearto']);
        if (0 === $to) {
            $to = intval($today->format('Y'));
        }

        $tmpDataYear = [];
        $items = [];
        for ($i = $from; $i < $to; ++$i) {
            $items[$i] = $i;
        }

        $selectedYear = $this->getValueFromModuleData($yearId);
        $selectedYear = $this->showSelectorByArray($items, $yearId, $tmpDataYear, ['forcevalue' => $selectedYear ?: $aDefault[0]]);

        // Tage
        $tmpDataDay = [];
        $items = [];
        $totalDays = date('t', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
        for ($i = 1; $i < $totalDays + 1; ++$i) {
            $items[$i] = $i;
        }

        $selectedDay = $this->getValueFromModuleData($dayId);
        $selectedDay = ($selectedDay > $totalDays) ? $totalDays : $selectedDay;
        $selectedDay = $this->showSelectorByArray($items, $dayId, $tmpDataDay, ['forcevalue' => $selectedDay ?: $aDefault[2]]);

        // Rückgabe
        $aData['day_selector'] = $tmpDataDay['selector'];
        $aData['month_selector'] = $tmpDataMonth['selector'];
        $aData['year_selector'] = $tmpDataYear['selector'];

        $ret = new DateTime();
        $ret->setDate($selectedYear, $selectedMonth, $selectedDay);

        return $ret;
    }

    /**
     * Gibt einen selector mit den models im gegebenen array zurück.
     *
     * @param Traversable|array $items
     * @param array             $data    enthält die Formularelement für die Ausgabe im Screen. Keys: selector, label
     * @param array             $options zusätzliche Optionen: label, id
     *
     * @return string selected item
     */
    protected function showSelectorByModels(
        $items,
        array &$data,
        array $options = [],
    ) {
        if (!is_array($items) && !$items instanceof Traversable) {
            throw new Exception('Argument 1 passed to'.__METHOD__.'() must be of the type array or Traversable.');
        }

        $id = $options['id'];
        if (empty($id)) {
            throw new Exception('No ID for widget given!');
        }

        $pid = $options['pid'] ?: 0;

        $itemMenu = [];
        if (isset($options['entryall'])) {
            $itemMenu['0'] = is_string($options['entryall']) ? $options['entryall'] : $this->mod->getLanguageService()->getLL('label_select_all_entries');
        }

        $titleMethod = $options['titlemethod'] ?: 'getTcaLabel';
        $idMethod = $options['idmethod'] ?: 'getUid';
        $titleField = $options['titlefield'] ?: 'title';
        $idField = $options['idfield'] ?: 'uid';

        foreach ($items as $item) {
            $uid = is_object($item) ? $item->{$idMethod}() : $item[$idField];
            $title = is_object($item) ? $item->{$titleMethod}() : $item[$titleField];
            $itemMenu[$uid] = $title;
        }

        $selectedItem = $this->getValueFromModuleData($id);

        // Build select box items
        $data['selector'] = Sys25\RnBase\Backend\Utility\BackendUtility::getFuncMenu(
            $pid,
            'SET['.$id.']',
            $selectedItem,
            $itemMenu
        );

        return $selectedItem;
    }

    /**
     * Gibt einen selector mit den elementen im gegebenen array zurück.
     *
     * @param array  $aItems   Array mit den werten der Auswahlbox
     * @param string $sDefId   ID-String des Elements
     * @param array  $aData    enthält die Formularelement für die Ausgabe im Screen. Keys: selector, label
     * @param array  $aOptions zusätzliche Optionen: label, id
     *
     * @return string selected item
     */
    protected function showSelectorByArray($aItems, $sDefId, array &$aData, array $aOptions = [])
    {
        $id = isset($aOptions['id']) && $aOptions['id'] ? $aOptions['id'] : $sDefId;

        $selectedItem = array_key_exists('forcevalue', $aOptions) ? $aOptions['forcevalue'] : $this->getValueFromModuleData($id);

        $pid = $aOptions['pid'] ?: 0;

        // Build select box items
        $aData['selector'] = Sys25\RnBase\Backend\Utility\BackendUtility::getDropdownMenu(
            $pid,
            'SET['.$id.']',
            $selectedItem,
            $aItems
        );

        // label
        $aData['label'] = $aOptions['label'];

        // as the deleted fe users have always to be hidden the function returns always false
        // @todo wozu die alte abfrage? return $defId==$id ? false : $selectedItem;
        return $selectedItem;
    }

    /**
     * Gibt einen selector mit den elementen im gegebenen array zurück.
     *
     * @return string selected item
     */
    protected function showSelectorByTCA($sDefId, $table, $column, array &$aData, $aOptions = [])
    {
        $items = [];
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
     * Returns an instance of \Sys25\RnBase\Backend\Module\IModule.
     */
    protected function getMod(): ?Sys25\RnBase\Backend\Module\IModule
    {
        return $this->mod;
    }

    /**
     * @return Sys25\RnBase\Backend\Form\ToolBox
     */
    protected function getFormTool()
    {
        return $this->getMod()->getFormTool();
    }

    /**
     * Return requested value from module data.
     *
     * @param string $key
     */
    public function getValueFromModuleData($key)
    {
        // Fetch selected company trade
        $modData = Sys25\RnBase\Backend\Utility\BackendUtility::getModuleData(
            [$key => ''],
            Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('SET'),
            $this->getMod()->getName()
        );

        return $modData[$key] ?? null;
    }

    /**
     * Setzt einen Wert in den Modul Daten. Dabei werden die bestehenden
     * ergänzt oder ggf. überschrieben.
     *
     * @param array $aModuleData
     */
    public function setValueToModuleData($sModuleName, $aModuleData = []): void
    {
        $aExistingModuleData = $GLOBALS['BE_USER']->getModuleData($sModuleName);
        if (!empty($aModuleData)) {
            foreach ($aModuleData as $sKey => $mValue) {
                $aExistingModuleData[$sKey] = $mValue;
            }
        }

        $GLOBALS['BE_USER']->pushModuleData($sModuleName, $aExistingModuleData);
    }

    public function buildFilterTable(array $data): string
    {
        $out = '';
        if ([] !== $data) {
            $out .= '<table class="filters">';
            foreach ($data as $label => $filter) {
                $out .= '<tr>';
                $out .= '<td>'.($filter['label'] ?? $label).'</td>';
                unset($filter['label']);
                $out .= '<td>'.implode(' ', $filter).'</td>';

                $out .= '</tr>';
            }

            $out .= '</table>';
        }

        return $out;
    }

    /**
     * @param array  $out     HTML string
     * @param string $key     mod key
     * @param array  $options some options like label
     *
     * @return array[to => int, from => int]
     */
    public function showDateRangeSelector(array &$out, string $key, $options = []): array
    {
        $fromValue = $this->getDateFieldByKey($key.'_from', $out);
        $toValue = $this->getDateFieldByKey($key.'_to', $out);
        $out['label'] = $options['label'] ?? $this->mod->getLanguageService()->getLL('label_daterange');

        $this->setValueToModuleData(
            $this->getMod()->getName(),
            [$key.'_from' => $fromValue, $key.'_to' => $toValue]
        );

        return $this->getCrDateReturnArray($fromValue, $toValue);
    }

    /**
     * @return string gewählte zeit in d-m-Y
     */
    private function getDateFieldByKey(string $key, &$out)
    {
        $value = isset($_POST[$key]) ?
            Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter($key) : $this->getValueFromModuleData($key);
        if (!($out['field'] ?? null)) {
            $out['field'] = '';
        }

        $out['field'] .= $this->getFormTool()->createDateInput($key, $value);

        return $value;
    }

    /**
     * @param string $fromValue d-m-Y
     * @param string $toValue   d-m-Y
     *
     * @return array[to => int, from => int]
     */
    protected function getCrDateReturnArray($fromValue, $toValue): array
    {
        $fromTimestamp = 0;
        if ($fromValue) {
            // @todo respect timezone
            $dateTime = new DateTime($fromValue);
            $fromTimestamp = $dateTime->getTimestamp();
        }

        $toTimestamp = 0;
        if ($toValue) {
            // @todo respect timezone
            $dateTime = new DateTime($toValue);
            $toTimestamp = $dateTime->getTimestamp();
        }

        if ($toTimestamp) {
            $toTimestamp = $this->moveTimestampToTheEndOfTheDay($toTimestamp);
        }

        return ['from' => $fromTimestamp, 'to' => $toTimestamp];
    }

    /**
     * @param int $timestamp
     *
     * @return int
     */
    private function moveTimestampToTheEndOfTheDay($timestamp): int|float
    {
        return $timestamp + 86400;
    }
}
