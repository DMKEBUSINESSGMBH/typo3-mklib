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
 * Handelt die über Typoscript definierte Exportfunktionalität.
 *
 * BeispielTS: EXT:mkextension/mod1/pageTSconfig.txt
 *
 * @author Michael Wagner
 */
class tx_mklib_mod1_export_Handler
{
    /**
     * Byte Order Mark = BOM.
     */
    public static function getByteOrderMark(): string
    {
        return chr(0xEF).chr(0xBB).chr(0xBF);
    }

    /**
     * Constructor.
     */
    public function __construct(
        /**
         * The Module or handler.
         */
        private readonly tx_mklib_mod1_export_IModFunc $modFunc,
    ) {
    }

    /**
     * Prüft, ob ein Export durchgeführt werden soll und führt diesen durch.
     */
    public function handleExport(): void
    {
        // den Typ des Exports auslesen;
        $type = $this->getCurrentExportType();
        if (!$type) {
            return;
        }

        $template = $this->getExportTemplate($type);
        $provider = $this->getListProvider();

        if (!$template || !$provider) {
            return;
        }

        // Ignoriere Stop-Button
        ignore_user_abort(true);
        // No Time-Limit
        set_time_limit(0);

        $itemPath = $this->getItemPath($type);

        // Der Subpart für Debug-Ausgaben wird am ende ausgegeben
        $debug = Sys25\RnBase\Frontend\Marker\Templates::getSubpart($template, '###DEBUG###');
        $timeStart = 0;
        $memStart = 0;
        if ($debug) {
            $template = Sys25\RnBase\Frontend\Marker\Templates::substituteSubpart(
                $template,
                '###DEBUG###',
                ''
            );
            $timeStart = microtime(true);
            $memStart = memory_get_usage();
        }

        tx_mklib_mod1_export_Util::sendHeaders($this->getHeaderConfig($type));

        if ($this->isByteOrderMarkRequired($type)) {
            echo self::getByteOrderMark();
        }

        /* @var $listBuilder tx_mklib_mod1_export_ListBuilder */
        $listBuilder = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_mod1_export_ListBuilder'
        );
        $listBuilder->renderEach(
            $provider,
            false,
            $template,
            $this->getMarkerClass($type),
            $this->getModFunc()->getConfId().strtolower($itemPath).'.',
            strtoupper($itemPath),
            $this->getConfigurations()->getFormatter()
        );

        $this->parseDebugs($debug, $timeStart, $memStart);

        // done!
        exit;
    }

    /**
     * Liefert den aktuell angeforderten ExportTyp (string) oder false (boolean).
     *
     * @return string|bool
     */
    public function getCurrentExportType()
    {
        $parameters = Sys25\RnBase\Frontend\Request\Parameters::getPostAndGetParametersMerged('mklib');
        if (empty($parameters['export'])) {
            return false;
        }

        // den Typ des Exports auslesen;
        $type = reset(array_keys($parameters['export']));
        $types = $this->getExportTypes();

        if (!in_array($type, $types)) {
            return false;
        }

        return $type;
    }

    /**
     * Erzeugt Marker für das Module Template,
     * um die Ausgabe der Export funktionen zu implementieren.
     * Folgende Marker werden erzeugt:
     * ###EXPORT_BUTTONS###.
     *
     * @param string $template
     *
     * @return string
     */
    public function parseTemplate($template)
    {
        if (!Sys25\RnBase\Frontend\Marker\BaseMarker::containsMarker($template, 'EXPORT_BUTTONS')) {
            return $template;
        }

        $buttons = '';
        foreach ($this->getExportTypes() as $type) {
            $buttons .= $this->renderButton($type);
        }

        if ('' !== $buttons && '0' !== $buttons) {
            $buttons = $this->getButtonStyles().$buttons;
        }

        $markerArray = [];
        $markerArray['###EXPORT_BUTTONS###'] = $buttons;

        return Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached(
            $template,
            $markerArray
        );
    }

    /**
     * Rendert einen einzelnen Button inklusive Icons und Beschreibungs-Tooltip.
     */
    protected function renderButton(string $type): string
    {
        static $infoSprite = false;

        if (false == $infoSprite) {
            $infoSprite = Sys25\RnBase\Backend\Utility\ModuleUtility::getSpriteIcon(
                'status-dialog-information'
            );
        }

        $configuration = $this->getConfigurations();
        $confId = $this->getConfId().'types.';

        $label = $configuration->get($confId.$type.'.label');
        $sprite = $configuration->get($confId.$type.'.spriteIcon');
        $button = $this->getModule()->getFormTool()->createSubmit(
            'mklib[export]['.$type.']',
            $label
        );

        if ($sprite) {
            $sprite = Sys25\RnBase\Backend\Utility\ModuleUtility::getSpriteIcon($sprite);
        }

        $description = $configuration->get($confId.$type.'.description');
        if ($description) {
            $description = ('<span class="bgColor2 info popover fade in">'.
                $infoSprite.'<strong>'.$label.'</strong><br />'.
                $description.'</span>'
            );
        }

        $button = '<span class="imgbtn">'.$sprite.$button.'</span>';

        return '<span class="mklibexport">'.$button.$description.'</span>';
    }

    /**
     * The configured export types.
     *
     * @return array
     */
    private function getExportTypes()
    {
        return $this->getConfigurations()->getKeyNames(
            $this->getConfId().'types.'
        );
    }

    /**
     * Returns the module function.
     */
    protected function getModFunc(): tx_mklib_mod1_export_IModFunc
    {
        return $this->modFunc;
    }

    /**
     * Liefert den Searcher des Module.
     *
     * @return tx_mklib_mod1_export_ISearcher
     */
    protected function getSearcher()
    {
        $searcher = $this->getModFunc()->getSearcher();
        if (!$searcher instanceof tx_mklib_mod1_export_ISearcher) {
            throw new Exception('The searcher "'.$searcher::class.'" has to implement the interface "tx_mklib_mod1_export_ISearcher"', 1361174776);
        }

        // wir setzen optional den export handler
        if ($searcher instanceof tx_mklib_mod1_export_IInjectHandler) {
            $searcher->setExportHandler($this);
        }

        return $searcher;
    }

    /**
     * Liefert den Provider für die Listenausgabe.
     *
     * @return Sys25\RnBase\Frontend\Marker\IListProvider
     */
    protected function getListProvider(): false|Sys25\RnBase\Frontend\Marker\IListProvider
    {
        $provider = $this->getSearcher()->getInitialisedListProvider();
        if (!$provider instanceof Sys25\RnBase\Frontend\Marker\IListProvider) {
            $this->getModule()->addMessage(
                'The provider "'.$provider::class.'" has to implement'.
                ' the interface \Sys25\RnBase\Frontend\Marker\IListProvider',
                'Subpart not found',
                2
            );

            return false;
        }

        return $provider;
    }

    /**
     * Returns an instance of \Sys25\RnBase\Backend\Module\IModule.
     *
     * @return Sys25\RnBase\Backend\Module\IModule
     */
    protected function getModule()
    {
        return $this->getModFunc()->getModule();
    }

    /**
     * Liefert das Template für den Export
     * eigentlich private, für tests protected.
     *
     * @return string
     */
    protected function getExportTemplate(string $type)
    {
        $configuration = $this->getConfigurations();
        $confId = $this->getConfId().'types.'.$type.'.template.';

        // template laden
        $absPath = Sys25\RnBase\Utility\Files::getFileAbsFileName(
            $configuration->get($confId.'template')
        );
        $template = Sys25\RnBase\Utility\Network::getUrl($absPath);
        if (!$template) {
            $this->getModule()->addMessage(
                'Could not find the template "'.$absPath.'"'.
                ' defined under '.$confId.'template.',
                'Template not found',
                2
            );

            return false;
        }

        // subpart optional auslesen
        $subpart = $configuration->get($confId.'subpart');
        if ($subpart) {
            $template = Sys25\RnBase\Frontend\Marker\Templates::getSubpart($template, $subpart);
            if (!$template) {
                $this->getModule()->addMessage(
                    'Could not find the the subpart "'.$subpart.'"'.
                    ' in template "'.$absPath.'".',
                    'Subpart not found',
                    2
                );

                return false;
            }
        }

        if ($configuration->getBool($confId.'callModules')) {
            $markerArray = [];
            $subpartArray = [];
            $wrappedSubpartArray = [];
            $params = [];
            Sys25\RnBase\Frontend\Marker\BaseMarker::callModules(
                $template,
                $markerArray,
                $subpartArray,
                $wrappedSubpartArray,
                $params,
                $this->getConfigurations()->getFormatter()
            );
            $template = Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached(
                $template,
                $markerArray,
                $subpartArray,
                $wrappedSubpartArray
            );
        }

        return $template;
    }

    /**
     * The marker class to use for import type.
     */
    protected function getMarkerClass(string $type): string
    {
        $configuration = $this->getConfigurations();
        $confId = $this->getConfId().'types.'.$type.'.template.';
        $class = $configuration->get($confId.'markerclass');
        $class = $class ?: Sys25\RnBase\Frontend\Marker\SimpleMarker::class;
        if (!class_exists($class)) {
            return Sys25\RnBase\Frontend\Marker\SimpleMarker::class;
        }

        return $class;
    }

    /**
     * The conf id to use for import type.
     *
     * @return string
     */
    protected function getItemPath(string $type)
    {
        $configuration = $this->getConfigurations();
        $confId = $this->getConfId().'types.'.$type.'.template.';
        $class = $configuration->get($confId.'itempath');

        return $class ?: 'item';
    }

    /**
     * The conf id to use for import type.
     */
    protected function getHeaderConfig(string $type): array
    {
        $headers = $this->getConfigurations()->get(
            $this->getConfId().'types.'.$type.'.headers.',
            true
        );

        return is_array($headers) ? $headers : [];
    }

    /**
     * Test if BOM is set in configuration.
     *
     * @return bool
     */
    protected function isByteOrderMarkRequired(string $type)
    {
        $configuration = $this->getConfigurations();
        $confId = $this->getConfId().'types.';

        return $configuration->getBool($confId.$type.'.BOM');
    }

    /**
     * The config object.
     *
     * @return Sys25\RnBase\Configuration\ConfigurationInterface
     */
    protected function getConfigurations()
    {
        return $this->getModule()->getConfigurations();
    }

    /**
     * The ConfId.
     */
    protected function getConfId(): string
    {
        return $this->getModFunc()->getConfId().'export.';
    }

    /**
     * Parst den DEBUG Subpart und gibt diesen direkt aus!
     *
     * @param string $template
     * @param int    $timeStart
     * @param int    $memStart
     */
    protected function parseDebugs(
        $template,
        $timeStart = 0,
        $memStart = 0,
        array $markerArr = [],
    ): bool {
        if (empty($template)) {
            return false;
        }

        $memEnd = memory_get_usage();
        $markerArr['###DEBUG_PARSETIME###'] = (microtime(true) - $timeStart);
        $markerArr['###DEBUG_MEMUSED###'] = ($memEnd - $memStart);
        $markerArr['###DEBUG_MEMSTART###'] = $memStart;
        $markerArr['###DEBUG_MEMEND###'] = $memEnd;
        $markerArr['###DEBUG_DATE###'] = tx_mklib_util_Date::getExecDate(DATE_ATOM);
        $markerArr['###DEBUG_ITEMCOUNT###'] = 'N/A';
        // die anzahl der ausgegebenen Datensätze ermitteln.
        $provider = $this->getListProvider();
        if ($provider instanceof Sys25\RnBase\Frontend\Marker\ListProvider) {
            $params = [$provider->fields, $provider->options];
            $params[1]['count'] = 1;
            $count = call_user_func_array($provider->searchCallback, $params);
            $markerArr['###DEBUG_ITEMCOUNT###'] = $count;
        }

        $out = Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($template, $markerArr);
        echo $out;

        return true;
    }

    /**
     * Liefert die styles der Buttons.
     *
     * @return string
     */
    private function getButtonStyles(): string|array
    {
        $css = '<style type="text/css">
        .mklibexport {
            display: block;
            position: relative;
        }
        .mklibexport .imgbtn {
            position: relative;
            margin: 5px;
            float: left;
        }
        .mklibexport .imgbtn span.t3-icon,
        .mklibexport .imgbtn span.t3js-icon{
            left: 8px;
            margin: 0;
            position: absolute;
        }
        .mklibexport .imgbtn span.t3-icon{
            top: 2px;
        }
        .mklibexport .imgbtn span.t3js-icon{
            top: 4px;
        }
        .mklibexport span.info {
            display: none;
            position: absolute;
            padding: 5px;
            top: 25px
        }
        .mklibexport:hover span.info {
            display: block;
        }
        .mklibexport input[type="submit"] {
            float: none;
            padding-left: 24px;
        }
        </style>';

        // alle umbrüche und tabs entfernen
        return str_replace(["\t", "\n", "\r"], '', $css);
    }
}
