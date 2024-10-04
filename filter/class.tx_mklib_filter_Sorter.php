<?php
/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
 ***************************************************************/

/**
 * BITTE INS WIKI SCHAUEN FÜR EINEN BEISPIEL TESTCASE,
 * DER FÜR ABGELEITETE KLASSEN GESCHRIEBEN WERDEN SOLLTE!
 *
 *  Beispiel TS config:
 *      myConfId.filter.sort{
 *          fields = title, name
 *          default {
 *              field = title
 *              sortOrder = desc
 *          }
 *          link {
 *              pid = alias oder pid
 *              ...
 *          }
 *      }
 *
 *  Beispiel Template:
 *      ###SORT_TITLE_LINK###sortieren nach titel###SORT_TITLE_LINK###
 *
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 *
 * @todo default sortierung per TypoScript konfigurierbar machen
 * @todo mehrfach sortierung unertsützen?
 */
class tx_mklib_filter_Sorter extends \Sys25\RnBase\Frontend\Filter\BaseFilter
{
    /**
     * @var string
     */
    protected $sortConfId = 'sort.';

    /**
     * ausgehend von $sortConfId.
     *
     * @var string
     */
    protected $allowedFieldsConfId = 'fields';

    /**
     * ausgehend von $sortConfId.
     *
     * @var string
     */
    protected $defaultConfigurationConfId = 'default.';

    /**
     * ausgehend von $defaultConfigurationConfId.
     *
     * @var string
     */
    protected $defaultFieldConfId = 'field';

    /**
     * ausgehend von $defaultConfigurationConfId.
     *
     * @var string
     */
    protected $defaultSortOrderConfId = 'sortOrder';

    /**
     * ausgehend von $sortConfId.
     *
     * @var string
     */
    protected $sortLinkConfId = 'link.';

    /**
     * @var string
     */
    protected $sortByParameterName = 'sortBy';

    /**
     * @var string
     */
    protected $sortOrderParameterName = 'sortOrder';

    /**
     * @var string
     */
    protected $markerPrefix = 'SORT';

    /**
     * @var string
     */
    private $sortBy;

    /**
     * @var string
     */
    private $sortOrder;

    /**
     * @var null || boolean
     */
    private $initiatedSorting;

    /**
     * setzt $this->sortBy und $this->sortOrder.
     *
     * @return bool
     */
    protected function initSorting()
    {
        if (!is_null($this->initiatedSorting)) {
            return $this->initiatedSorting;
        }

        $sortBy = $this->getCurrentSortBy();

        if ($sortBy && $this->sortByIsAllowed($sortBy)) {
            $sortOrder = $this->getCurrentSortOrder();
            $sortOrder = $this->assureSortOrderIsValid($sortOrder);

            $this->sortBy = $sortBy;
            $this->sortOrder = $sortOrder;
            $this->initiatedSorting = true;

            return true;
        }
        // else

        $this->initiatedSorting = false;

        return false;
    }

    /**
     * @return string
     */
    private function getCurrentSortBy()
    {
        $parameters = $this->getParameters();

        if (!$sortBy = trim($parameters->get($this->sortByParameterName))) {
            $sortBy = $this->getDefaultValue($this->defaultFieldConfId);
        }

        return $sortBy;
    }

    /**
     * @return string
     */
    private function getCurrentSortOrder()
    {
        $parameters = $this->getParameters();

        if (!$sortOrder = trim($parameters->get($this->sortOrderParameterName))) {
            $sortOrder = $this->getDefaultValue($this->defaultSortOrderConfId);
        }

        return $sortOrder;
    }

    /**
     * @param string $defaultValue
     *
     * @return string
     */
    private function getDefaultValue($confId)
    {
        $defaultConfigurationConfId =
            $this->getConfId().$this->sortConfId.$this->defaultConfigurationConfId;
        $configurations = $this->getConfigurations();

        return $configurations->get($defaultConfigurationConfId.$confId);
    }

    /**
     * @param string $sortOrder
     *
     * @return string
     */
    private function assureSortOrderIsValid($sortOrder)
    {
        return ('desc' == $sortOrder) ? 'desc' : 'asc';
    }

    /**
     * @return string
     */
    protected function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @return string
     */
    protected function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param string $sortField
     *
     * @return bool
     */
    private function sortByIsAllowed($sortField)
    {
        return in_array($sortField, $this->getAllowedSortFields());
    }

    /**
     * @return array
     */
    private function getAllowedSortFields()
    {
        $confId = $this->getConfId().$this->sortConfId;
        $configurations = $this->getConfigurations();

        $sortFields = $configurations->get($confId.$this->allowedFieldsConfId);
        $sortFields = $sortFields ? \Sys25\RnBase\Utility\Strings::trimExplode(',', $sortFields, true) : [];

        return $sortFields;
    }

    /**
     * @param string                    $template  HTML template
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil $formatter
     * @param string                    $confId
     * @param string                    $marker
     *
     * @return string
     */
    public function parseTemplate($template, &$formatter, $confId, $marker = 'FILTER')
    {
        $markerArray = $subpartArray = $wrappedSubpartArray = [];

        $this->initSorting();
        $this->insertMarkersForSorting(
            $template,
            $markerArray,
            $subpartArray,
            $wrappedSubpartArray,
            $formatter,
            $confId
        );

        $template = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $template;
    }

    /**
     * @param string                    $template            HTML template
     * @param array                     $markerArray
     * @param array                     $subpartArray
     * @param array                     $wrappedSubpartArray
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil $formatter
     * @param string                    $confId
     * @param string                    $marker
     */
    private function insertMarkersForSorting($template, &$markerArray, &$subpartArray, &$wrappedSubpartArray, &$formatter, $confId)
    {
        $confId = $this->getConfId().$this->sortConfId;
        $configurations = $formatter->getConfigurations();

        $sortFields = $this->getAllowedSortFields();

        if (!empty($sortFields)) {
            $token = md5(microtime());
            $markOrders = [];
            foreach ($sortFields as $field) {
                $isField = ($field == $this->getSortBy());
                // sortOrder ausgeben
                $markOrders[$field.'_order'] = $isField ? $this->getSortOrder() : '';

                $fieldMarker = $this->markerPrefix.'_'.strtoupper($field).'_LINK';
                $makeLink = \Sys25\RnBase\Frontend\Marker\BaseMarker::containsMarker($template, $fieldMarker);
                $makeUrl = \Sys25\RnBase\Frontend\Marker\BaseMarker::containsMarker($template, $fieldMarker.'URL');
                // link generieren
                if ($makeLink || $makeUrl) {
                    // sortierungslinks ausgeben
                    $params = [
                        'sortBy' => $field,
                        'sortOrder' => $isField && ('asc' == $this->getSortOrder()) ? 'desc' : 'asc',
                    ];
                    $link = $configurations->createLink();
                    $link->label($token);
                    $link->initByTS(
                        $configurations,
                        $confId.$this->sortLinkConfId,
                        $params
                    );
                    if ($makeLink) {
                        $wrappedSubpartArray['###'.$fieldMarker.'###'] = explode($token, $link->makeTag());
                    }
                    if ($makeUrl) {
                        $markerArray['###'.$fieldMarker.'URL###'] = $link->makeUrl(false);
                    }
                }
            }
            // die sortOrders parsen
            $markOrders = $formatter->getItemMarkerArrayWrapped($markOrders, $confId, 0, $this->markerPrefix.'_', array_keys($markOrders));
            $markerArray = array_merge($markerArray, $markOrders);
        }
    }

    /**
     * Method is called in \Sys25\RnBase\Frontend\Marker\ListBuilder::render() and used to trigger the
     * parseTemplate() method of this class.
     *
     * @return $this
     */
    public function getMarker(): tx_mklib_filter_Sorter
    {
        return $this;
    }
}
