<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 ***************************************************************/

/**
 * Der Listbuilder erzeugt die ausgabe für den export.
 *
 * @author Michael Wagner
 */
class tx_mklib_mod1_export_ListBuilder extends \Sys25\RnBase\Frontend\Marker\ListBuilder
{
    /**
     * Die ist leider private und muss überschrieben werden.
     *
     * @var array
     */
    private $callbacks = [];

    /**
     * Add a visitor callback. It is called for each item before rendering.
     *
     * @param array $callback
     */
    public function addVisitor(array $callback)
    {
        $this->callbacks[] = $callback;
    }

    /**
     * Render each element.
     *
     * {@inheritdoc}
     *
     * @see \Sys25\RnBase\Frontend\Marker\ListBuilder::renderEach()
     */
    public function renderEach(
        \Sys25\RnBase\Frontend\Marker\IListProvider $provider,
        $viewData,
        $template,
        $markerClassname,
        $confId,
        $marker,
        $formatter,
        $markerParams = null
    ) {
        $outerMarker = $this->getOuterMarker($marker, $template);

        // wir teilen das Template, da der erste teil direkt ausgegeben werden muss!
        list($header, $footer) = $this->getWrapForSubpart($template, $outerMarker.'S');

        tx_mklib_mod1_export_Util::doOutPut($header);

        /* @var $listMarker tx_mklib_mod1_export_ListMarker */
        $listMarker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_mod1_export_ListMarker',
            $this->getInfo()->getListMarkerInfo()
        );

        $templateList = \Sys25\RnBase\Frontend\Marker\Templates::getSubpart(
            $template,
            '###'.$outerMarker.'S###'
        );
        list($listHeader, $listFooter) = $this->getWrapForSubpart(
            $templateList,
            $marker
        );
        $templateEntry = \Sys25\RnBase\Frontend\Marker\Templates::getSubpart(
            $templateList,
            '###'.$marker.'###'
        );

        tx_mklib_mod1_export_Util::doOutPut($listHeader);

        $listMarker->addVisitors($this->callbacks);
        $listMarker->renderEach(
            $provider,
            $templateEntry,
            $markerClassname,
            $confId,
            $marker,
            $formatter,
            $markerParams
        );

        tx_mklib_mod1_export_Util::doOutPut($listFooter);
        tx_mklib_mod1_export_Util::doOutPut($footer);

        return '';
    }

    /**
     * Returns the Wrap for the subpart.
     *
     * @param string $template
     * @param string $marker
     * @param bool   $required
     *
     * @throws Exception
     *
     * @return string
     */
    protected function getWrapForSubpart(
        $template,
        $marker,
        $required = true
    ) {
        // wir teilen das Template, da der erste teil direkt ausgegeben werden muss!
        $token = md5(time()).md5(get_class());
        $wrap = \Sys25\RnBase\Frontend\Marker\Templates::substituteSubpart(
            $template,
            '###'.$marker.'###',
            $token,
            0
        );
        $wrap = explode($token, $wrap);

        if ($required && 2 != count($wrap)) {
            // es ist etwas schiefgelaufen, wir sollten immer 2 teile haben
            // einmal header und einmal footer
            throw new Exception('Marker '.$marker.' not fount in Template', 1361171589);
        }

        return $wrap;
    }

    /**
     * Renders the element.
     *
     * {@inheritdoc}
     *
     * @see \Sys25\RnBase\Frontend\Marker\ListBuilder::render()
     */
    public function render(
        &$dataArr,
        $viewData,
        $template,
        $markerClassname,
        $confId,
        $marker,
        $formatter,
        $markerParams = null
    ) {
        $out = parent::render(
            $dataArr,
            $viewData,
            $template,
            $markerClassname,
            $confId,
            $marker,
            $formatter
        );
        tx_mklib_mod1_export_Util::doOutPut($out);
    }
}

if ((
    defined('TYPO3_MODE')
    && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_ListBuilder.php']
)) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_ListBuilder.php'];
}
