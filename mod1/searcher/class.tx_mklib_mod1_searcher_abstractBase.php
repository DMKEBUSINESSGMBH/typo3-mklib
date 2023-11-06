<?php
/**
 * Copyright notice.
 *
 * (c) 2011 - 2015 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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
 * Basisklasse für Suchfunktionen in BE-Modulen.
 *
 * @author Michael Wagner
 */
abstract class tx_mklib_mod1_searcher_abstractBase implements tx_mklib_mod1_export_ISearcher
{
    /**
     * Wurde die ll bereits geladen?
     *
     * @var bool
     */
    private static $localLangLoaded = false;
    /**
     * Selector Klasse.
     *
     * @var \Sys25\RnBase\Backend\Module\IModule
     */
    private $mod;
    /**
     * Selector Klasse.
     *
     * @var tx_mklib_mod1_util_Selector
     */
    private $selector;
    /**
     * Otions.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Current search term.
     *
     * @var string
     */
    protected $currentSearchWord = '';

    /**
     * Current hidden option.
     *
     * @var string
     */
    protected $currentShowHidden = 1;

    /**
     * @var int
     */
    protected $currentLanguage;

    /**
     * Constructor.
     *
     * @param \Sys25\RnBase\Backend\Module\IModule $mod
     * @param array                 $options
     */
    public function __construct(\Sys25\RnBase\Backend\Module\IModule $mod, array $options = [])
    {
        $this->init($mod, $options);
    }

    /**
     * Init object.
     *
     * @param \Sys25\RnBase\Backend\Module\IModule $mod
     * @param array                 $options
     */
    protected function init(\Sys25\RnBase\Backend\Module\IModule $mod, $options)
    {
        // locallang einlesen
        if (!self::$localLangLoaded) {
            $this->loadOwnLocalLangNotOverwritingExistingLabels();
            self::$localLangLoaded = true;
        }
        $this->setOptions($options);
        $this->mod = $mod;

        // set the baseTable for this searcher. required by language column!
        if (!isset($this->options['baseTableName'])) {
            if ($this->getService() instanceof tx_mklib_repository_Abstract) {
                // $this->options['baseTableName'] = $this->getService()->getTableName();
            }
        }
    }

    /**
     * Es kann sein dass schon vorm aufrufen des tatsächlichen Searchers
     * eine locallang Datei eingebunden wurde, welche Vorrang hat da sie vom konkreten
     * BE Modul stammt und die Labels enthält die auch in EXT:mklib/mod1/locallang.xlf vorhanden sind.
     * Wenn wir dann EXT:mklib/mod1/locallang.xlf ganz normal einbinden, würden
     * diese überschrieben werden, obwohl diese Vorrang haben sollten.
     * Also überschreiben wir die Labels aus mklib mit vorhandenen aus
     * $GLOBALS['LOCAL_LANG'] und schreiben das dann zurück nach $GLOBALS['LOCAL_LANG'],
     * damit die mklib Lables nur eine Ergänzung sind.
     */
    protected function loadOwnLocalLangNotOverwritingExistingLabels()
    {
        $labels = new ReflectionProperty(get_class($GLOBALS['LANG']), 'labels');
        $labels->setAccessible(true);
        $initialLabels = $labels->getValue($GLOBALS['LANG']);
        $labelsFromMklib = $GLOBALS['LANG']->includeLLFile('EXT:mklib/mod1/locallang.xlf');
        $labelsFromMklib = \Sys25\RnBase\Utility\Arrays::mergeRecursiveWithOverrule(
            $labelsFromMklib,
            (array) $initialLabels
        );
        $labels->setValue($GLOBALS['LANG'], $labelsFromMklib);
    }

    /**
     * Bietet die Möglichkeit die Optionen nach der Erstellung noch zu ändern.
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return string
     */
    protected function getSearcherId()
    {
        $pageId = tx_mklib_util_String::toCamelCase(get_class($this));

        return $pageId;
    }

    abstract protected function getService();

    /**
     * Returns the complete search form.
     *
     * @return string
     */
    public function getSearchForm()
    {
        $data = $this->getFilterTableDataForSearchForm();

        $selector = $this->getSelector();
        $out = $selector->buildFilterTable($data);

        return $out;
    }

    /**
     * Liefert die Daten für das Basis-Suchformular damit
     * das Html gebaut werden kann.
     *
     * @return array
     */
    protected function getFilterTableDataForSearchForm()
    {
        $data = [];
        $options = ['pid' => $this->options['pid'] ?? 0];
        $selector = $this->getSelector();

        $this->currentSearchWord = $selector->showFreeTextSearchForm(
            $data['search'],
            $this->getSearcherId().'Search',
            array_merge(['submit' => 1], $options)
        );

        // @TODO: check, if the table is internationalable and remove the option!
        if (!empty($options['add_language_filter'])) {
            $this->currentLanguage = $selector->showLanguageSelector(
                $data['language'],
                $options
            );
        }

        $this->currentShowHidden = $selector->showHiddenSelector(
            $data['hidden'],
            $options
        );

        if ($updateButton = $this->getSearchButton()) {
            $data['updatebutton'] = [
                'label' => '',
                'button' => $updateButton,
            ];
        }

        return $data;
    }

    /**
     * Returns the search button.
     *
     * @return string|false
     */
    protected function getSearchButton()
    {
        $out = $this->getFormTool()->createSubmit(
            $this->getSearcherId().'Search',
            $GLOBALS['LANG']->getLL('label_button_update')
        );

        return $out;
    }

    /**
     * Liefert den initialisierten Listbuilder.
     *
     * @return \Sys25\RnBase\Frontend\Marker\ListProvider
     */
    public function getInitialisedListProvider()
    {
        // Wir initialisieren das Formular und damit auch die Filter.
        $this->getFilterTableDataForSearchForm();
        list($fields, $options) = $this->getFieldsAndOptions();
        /* @var $provider \Sys25\RnBase\Frontend\Marker\ListProvider */
        $provider = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Frontend\Marker\ListProvider::class);
        $provider->initBySearch([$this->getService(), 'search'], $fields, $options);

        return $provider;
    }

    /**
     * Bildet die Resultliste mit Pager.
     *
     * @return string
     */
    public function getResultList()
    {
        /* @var $pager \Sys25\RnBase\Backend\Utility\BEPager */
        $pager = $this->usePager() ? \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \Sys25\RnBase\Backend\Utility\BEPager::class,
            $this->getSearcherId().'Pager',
            $this->getModule()->getName(),
            // @TODO: die PageId solle noch konfigurierbar gemacht werden.
            $pid = 0
        ) : null;

        list($fields, $options) = $this->getFieldsAndOptions();

        // Get counted data
        $cnt = $this->getCount($fields, $options);

        if ($pager) {
            $pager->setListSize($cnt);
            $pager->setOptions($options);
        }

        // Get data
        $search = $this->searchItems($fields, $options);
        $items = &$search['items'];
        $content = '';
        $this->showItems($content, $items, ['items_map' => $search['map']]);

        $data = [
            'table' => $content,
            'totalsize' => $cnt,
            'items' => $items,
        ];

        if ($pager) {
            $pagerData = $pager->render();

            // der zusammengeführte Pager für die Ausgabe
            // nur wenn es auch Ergebnisse gibt. sonst reicht die noItemsFoundMsg
            $sPagerData = '';
            if ($cnt) {
                $sPagerData = $pagerData['limits'].' - '.$pagerData['pages'];
            }
            $data['pager'] = '<div class="pager">'.$sPagerData.'</div>';
        }

        return $data;
    }

    /**
     * searches for te items to list.
     *
     * TODO make more abstract!?
     *
     * @param array $fields
     * @param array $options
     *
     * @return array
     */
    protected function searchItems(array $fields, array $options)
    {
        $firstPrev = $lastNext = false;
        $secondPrev = null;
        if (($this->options['baseTableName'] ?? null)
            && \Sys25\RnBase\Backend\Utility\TCA::getSortbyFieldForTable($this->options['baseTableName'])
            && ($options['limit'] || $options['offset'])
        ) {
            // normalize limit and offset values to int
            array_key_exists('offset', $options) ? $options['offset'] = (int) $options['offset'] : null;
            array_key_exists('limit', $options) ? $options['limit'] = (int) $options['limit'] : null;
            // wir haben ein offset und benötigen die beiden elemente element davor.
            if (!empty($options['offset'])) {
                $firstPrev = true;
                $downStep = $options['offset'] > 2 ? 2 : 1;
                $options['offset'] -= $downStep;
                // das limit um eins erhöhen um das negative offset zu korrigieren
                if (isset($options['limit'])) {
                    $options['limit'] += $downStep;
                }
            }
            // wir haben ein limit und benötigen das element danach.
            if (!empty($options['limit'])) {
                $lastNext = true;
                ++$options['limit'];
            }
        }

        $items = $this->getService()->search($fields, $options);

        if ($firstPrev || $lastNext) {
            // @FIXME !!! That's only an workaround. An ArrayObject shoul be retain!
            $items = (array) $items;
            // das letzte entfernen, aber nur wenn genügend elemente im result sind
            if ($lastNext && count($items) >= $options['limit']) {
                $lastNext = array_pop($items);
            }
            // das erste entfernen, wenn der offset reduziert wurde.
            if ($firstPrev) {
                $firstPrev = array_shift($items);
                // das zweite entfernen, wenn der offset um 2 reduziert wurde
                if ($downStep > 1) {
                    $secondPrev = array_shift($items);
                }
            }
        }

        // build uidmap
        $map = [];
        if ($firstPrev instanceof \Sys25\RnBase\Domain\Model\RecordInterface) {
            $map[$firstPrev->getUid()] = [];
        }
        if ($secondPrev instanceof \Sys25\RnBase\Domain\Model\RecordInterface) {
            $map[$secondPrev->getUid()] = [];
        }
        foreach ($items as $item) {
            $map[$item->getUid()] = [];
        }
        if ($lastNext instanceof \Sys25\RnBase\Domain\Model\RecordInterface) {
            $map[$lastNext->getUid()] = [];
        }

        return [
            'items' => $items,
            'map' => $map,
        ];
    }

    protected function usePager()
    {
        if (isset($this->options['usepager'])) {
            return false === $this->options['usepager']
                || 0 !== intval($this->options['usepager']);
        }

        return true;
    }

    /**
     * Erzeugt die Fields und Options für den Service.
     *
     * @return array[fields, options]
     */
    protected function getFieldsAndOptions()
    {
        $fields = $options = [];
        if (!empty($this->options['baseOptions'])
            && is_array($this->options['baseOptions'])) {
            $options = $this->options['baseOptions'];
        }
        if (!empty($this->options['baseFields'])
            && is_array($this->options['baseFields'])) {
            $fields = $this->options['baseFields'];
        }

        // @todo tests schreiben
        // es könnte sein dass ein sorting gewählt wurde. dann wollen wir dieses
        // auch nutzen
        $this->prepareSorting($options);
        $this->prepareFieldsAndOptions($fields, $options);

        return [$fields, $options];
    }

    /**
     * Sortierung vorbereiten.
     *
     * @param array $options
     */
    private function prepareSorting(&$options)
    {
        $sortedCols = [];
        if (\Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('sortField') && \Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('sortRev')) {
            $sortedCols = [\Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('sortField') => \Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('sortRev')];
            // wir setzen die daten noch für das Modul um bei einem seiten wechsel
            // weiterhin die richtige sortierung zu haben
            $this->getSelector()->setValueToModuleData(
                $this->getModule()->getName(),
                [$this->getSearcherId().'orderby' => $sortedCols]
            );
        } elseif ($aOrderByByModuleData = $this->getSelector()->getValueFromModuleData($this->getSearcherId().'orderby')) {
            $sortedCols = $aOrderByByModuleData;
            // wenn die sortierung aus dem Modul kommt, müssen wir die Sortierung in
            // den $_GET Daten setzen damit die richtigen Pfeile angezeigt werden
            // siehe \Sys25\RnBase\Backend\Form\ToolBox::createSortLink
            $aKeys = array_keys($aOrderByByModuleData);
            $_GET['sortField'] = $aKeys[0];
            $aValues = array_values($aOrderByByModuleData);
            $_GET['sortRev'] = $aValues[0];
        }

        if (!empty($sortedCols)) {
            $cols = $this->getDecoratorColumns($this->getDecorator($this->getModule()));

            foreach ($sortedCols as $colLabel => $sortOrder) {
                $configuredCol = $cols[$colLabel];
                if (!$configuredCol || !array_key_exists('sortable', $configuredCol)) {
                    continue;
                }

                // das Label in die notwendige SQL-Anweisung umwandeln. Normalerweise ein Spaltenname.
                $sortCol = $configuredCol['sortable'];
                // Wenn am Ende ein Punkt steht, muss die Spalte zusammengefügt werden.
                $sortCol = '.' === substr($sortCol, -1) ? $sortCol.$colLabel : $sortCol;
                $options['orderby'][$sortCol] = ('asc' == strtolower($sortOrder) ? 'asc' : 'desc');
            }
        }
    }

    /**
     * Kann von der Kindklasse überschrieben werden, um weitere Filter zu setzen.
     *
     * @param array $fields
     * @param array $options
     */
    protected function prepareFieldsAndOptions(array &$fields, array &$options)
    {
        $options['distinct'] = 1;

        if (!$this->currentShowHidden) {
            $options['enablefieldsfe'] = 1;
        } else {
            $options['enablefieldsbe'] = 1;
        }

        // die fields nun mit dem Suchbegriff und den Spalten,
        // in denen gesucht werden soll, füllen
        tx_mklib_mod1_util_SearchBuilder::buildFreeText(
            $fields,
            $this->currentSearchWord,
            $this->getSearchColumns()
        );

        if ($this->currentLanguage) {
            $options['i18n'] = $this->currentLanguage;
        } else {
            $options['ignorei18n'] = true;
            // prefer the master record instead of the overlay, if no language is filtered!
            $options['uniquemode'] = 'master';
        }
    }

    /**
     * Liefert die Spalten, in denen gesucht werden soll.
     *
     * @return array
     */
    protected function getSearchColumns()
    {
        return [];
    }

    /**
     * Start creation of result list.
     *
     * @param string            &$content
     * @param Traversable|array $items
     * @param array             $options
     *
     * @return string
     */
    protected function showItems(
        &$content,
        $items,
        array $options = []
    ) {
        if (!(is_array($items) || $items instanceof Traversable)) {
            throw new Exception('Argument 2 passed to'.__METHOD__.'() must be of the type array or Traversable.');
        }

        if (!(array) $items) {
            $content = $this->getNoItemsFoundMsg();

            return '';
        }
        $columns = $this->getDecoratorColumns($this->getDecorator($this->getModule(), $options));
        list($tableData, $tableLayout) = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Backend\Utility\Tables::class)->prepareTable(
            $items,
            $columns,
            $this->getFormTool(),
            $this->getOptions()
        );

        /* @var $tables \Sys25\RnBase\Backend\Utility\Tables */
        $tables = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Backend\Utility\Tables::class);
        $out = $tables->buildTable($tableData, $tableLayout);
        $content .= $out;

        return $out;
    }

    /**
     * the decorator instace.
     *
     * @param \Sys25\RnBase\Backend\Module\IModule &$mod
     * @param array                 $options
     *
     * @return tx_mklib_mod1_decorator_Base
     */
    protected function getDecorator($mod, array $options = [])
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            $this->getDecoratorClass(),
            $mod,
            $options
        );
    }

    /**
     * @return string
     */
    protected function getDecoratorClass()
    {
        return 'tx_mklib_mod1_decorator_Base';
    }

    /**
     * Liefert die Spalten für den Decorator.
     *
     * @param tx_mklib_mod1_decorator_Base $oDecorator
     *
     * @return array
     */
    protected function getDecoratorColumns($oDecorator)
    {
        $columns = [];
        $this
            ->addDecoratorColumnLabel($columns, $oDecorator)
            ->addDecoratorColumnLanguage($columns, $oDecorator)
            ->addDecoratorColumnActions($columns, $oDecorator);

        return $columns;
    }

    /**
     * Adds the column 'uid' to the be list.
     *
     * @param array                    $columns
     * @param \Sys25\RnBase\Backend\Decorator\InterfaceDecorator $oDecorator
     *
     * @return tx_mklib_mod1_searcher_abstractBase
     */
    protected function addDecoratorColumnUid(
        array &$columns,
        \Sys25\RnBase\Backend\Decorator\InterfaceDecorator &$oDecorator = null
    ) {
        $columns['uid'] = [
            'title' => 'label_tableheader_uid',
            'decorator' => &$oDecorator,
        ];

        return $this;
    }

    /**
     * Adds the column 'uid' to the be list.
     *
     * @param array                    $columns
     * @param \Sys25\RnBase\Backend\Decorator\InterfaceDecorator $oDecorator
     *
     * @return tx_mklib_mod1_searcher_abstractBase
     */
    protected function addDecoratorColumnLabel(
        array &$columns,
        \Sys25\RnBase\Backend\Decorator\InterfaceDecorator &$oDecorator = null
    ) {
        if (!empty($this->options['baseTableName'])) {
            $labelField = \Sys25\RnBase\Backend\Utility\TCA::getLabelFieldForTable($this->options['baseTableName']);
            if (!empty($labelField)) {
                $columns['label'] = [
                    'title' => 'label_tableheader_title',
                    'decorator' => &$oDecorator,
                ];
            }
        }
        // fallback, the uid column
        if (!isset($columns['label']) && !isset($columns['uid'])) {
            $this->addDecoratorColumnUid($columns, $oDecorator);
        }

        return $this;
    }

    /**
     * Adds the column 'sys_language_uid' to the be list.
     *
     * @param array                    $columns
     * @param \Sys25\RnBase\Backend\Decorator\InterfaceDecorator $oDecorator
     *
     * @return tx_mklib_mod1_searcher_abstractBase
     */
    protected function addDecoratorColumnLanguage(
        array &$columns,
        \Sys25\RnBase\Backend\Decorator\InterfaceDecorator &$oDecorator = null
    ) {
        if (!empty($this->options['baseTableName'])) {
            $sysLanguageUidField = \Sys25\RnBase\Backend\Utility\TCA::getLanguageFieldForTable($this->options['baseTableName']);
            if (!empty($sysLanguageUidField)) {
                $columns['sys_language_uid'] = [
                    'title' => 'label_tableheader_language',
                    'decorator' => &$oDecorator,
                ];
            }
        }

        return $this;
    }

    /**
     * Adds the column 'actions' to the be list.
     * this column contains the edit, hide, remove, ... actions.
     *
     * @param array                    $columns
     * @param \Sys25\RnBase\Backend\Decorator\InterfaceDecorator $oDecorator
     *
     * @return tx_mklib_mod1_searcher_abstractBase
     */
    protected function addDecoratorColumnActions(
        array &$columns,
        \Sys25\RnBase\Backend\Decorator\InterfaceDecorator &$oDecorator = null
    ) {
        $columns['actions'] = [
            'title' => 'label_tableheader_actions',
            'decorator' => &$oDecorator,
        ];

        return $this;
    }

    /**
     * @return tx_mklib_mod1_util_Selector
     */
    protected function getSelector()
    {
        if (!$this->selector) {
            $this->selector = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($this->getSelectorClass());
            $this->selector->init($this->getModule());
        }

        return $this->selector;
    }

    /**
     * liefert die klasse für den selector.
     * kann von der kindklasse überschrieben werden.
     *
     * @return string
     */
    protected function getSelectorClass()
    {
        return 'tx_mklib_mod1_util_Selector';
    }

    /**
     * @param array $fields
     * @param array $options
     */
    protected function getCount(array &$fields, array $options)
    {
        // Get counted data
        $options['count'] = 1;

        return $this->getService()->search($fields, $options);
    }

    /**
     * Returns an instance of \Sys25\RnBase\Backend\Module\IModule.
     *
     * @return \Sys25\RnBase\Backend\Module\IModule
     */
    protected function getModule()
    {
        return $this->mod;
    }

    /**
     * Returns an instance of \Sys25\RnBase\Backend\Module\IModule.
     *
     * @return \Sys25\RnBase\Backend\Module\IModule
     */
    protected function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns an instance of \Sys25\RnBase\Backend\Module\IModule.
     *
     * @return \Sys25\RnBase\Backend\Form\ToolBox
     */
    protected function getFormTool()
    {
        return $this->mod->getFormTool();
    }

    /**
     * Returns the message in case no items could be found in showItems().
     *
     * @return string
     */
    protected function getNoItemsFoundMsg()
    {
        return '<p><strong>###LABEL_NO_'.strtoupper($this->getSearcherId()).'_FOUND###</strong></p><br/>';
    }
}
