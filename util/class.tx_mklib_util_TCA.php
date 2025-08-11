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
 * benötigte Klassen einbinden.
 */

/**
 * Util Methoden für die TCA.
 *
 * @errorcodebase 3000
 *
 * @author  Hannes Bochmann
 * @author  Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_TCA
{
    /**
     * Wurde ext_tables.php der Extension bereits geladen?
     */
    private static array $tcaAdditionsLoaded = [];

    /**
     * Eleminate non-TCA-defined columns from given data.
     *
     * Doesn't do anything if no TCA columns are found.
     *
     * @param array $data Data to be filtered
     *
     * @return array Data now containing only TCA-defined columns
     */
    public static function eleminateNonTcaColumnsByTable($table, array $data)
    {
        return Sys25\RnBase\Utility\Arrays::removeNotIn(
            $data,
            empty($GLOBALS['TCA'][$table]['columns']) ? [] : array_keys($GLOBALS['TCA'][$table]['columns'])
        );
    }

    /**
     * Liefert den Spaltenname für enablecolumns aus der TCA.
     *
     * @FIXME: Nicht alle felder stehen unter ctrlo.enablecolumns. siehe: tstamp, crdate, cruser_id, delete, ...
     *
     * @TODO: wirft keine exception, wenn ein default wert gegeben ist. das ist allerdings falsch.
     *
     * @param string $column  (disabled, starttime, endtime, fe_group')
     * @param string $default
     *
     * @return string
     *
     * @throws Exception
     */
    public static function getEnableColumn(string $tableName, string $column, $default = null)
    {
        $fields = self::getCtrlField(
            $tableName,
            'enablecolumns',
            // wenn ein defaultwert definiert ist,
            // wollen wir als fallback immer ein array!
            null === $default ? null : []
        );
        if (!(is_array($fields) && isset($fields[$column])) && null === $default) {
            throw new LogicException('The enablecolumn "'.$column.'" does not exists in TCA for for table "'.$tableName.'".', intval(ERROR_CODE_MKLIB. 3002));
        }

        return $fields[$column] ?? $default;
    }

    /**
     * Liefert den Spaltenname aus dem ctrl der TCA.
     *
     * @return string
     */
    private static function getCtrlField(string $tableName, string $field, $default = null)
    {
        global $TCA;
        if (!isset($TCA[$tableName])) {
            if (null !== $default) {
                return $default;
            }

            throw new LogicException('The table "'.$tableName.'" not found in TCA!', intval(ERROR_CODE_MKLIB. 3001));
        }

        return $TCA[$tableName]['ctrl'][$field] ?? $default;
    }

    /**
     * Liefert den Spaltenname für das sys_language_uid feld.
     */
    public static function getLanguageField(string $tableName, $default = null)
    {
        return self::getCtrlField($tableName, 'languageField', $default);
    }

    /**
     * Liefert den Spaltenname für das l18n_parent feld.
     */
    public static function getTransOrigPointerField(string $tableName, $default = null)
    {
        return self::getCtrlField($tableName, 'transOrigPointerField', $default);
    }

    /**
     * Liefert den Spaltenname für das l18n_diffsource feld.
     */
    public static function getTransOrigDiffSourceField(string $tableName, $default = null)
    {
        return self::getCtrlField($tableName, 'transOrigDiffSourceField', $default);
    }

    /**
     * @return int
     */
    public static function getParentUidFromReturnUrl()
    {
        if (($parsedQueryParameters = self::getQueryParametersFromReturnUrl())
            && !empty($parsedQueryParameters['P']['uid'])) {
            return $parsedQueryParameters['P']['uid'];
        }

        return null;
    }

    private static function getQueryParametersFromReturnUrl(): array
    {
        $parsedQueryParameters = [];

        if (($returnUrl = Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('returnUrl'))
            && ($parsedUrl = parse_url($returnUrl))
            && isset($parsedUrl['query'])
        ) {
            parse_str($parsedUrl['query'], $parsedQueryParameters);
        }

        return $parsedQueryParameters;
    }

    /**
     * Die Länge kann in $tcaTableInformation['config']['labelLength'] angegeben werden.
     * Default ist 80 Zeichen.
     */
    public static function cropLabels(array &$tcaTableInformation): void
    {
        $items = &$tcaTableInformation['items'];
        $labelLength = self::getLabelLength($tcaTableInformation);

        if (!empty($items)) {
            foreach ($items as &$item) {
                $label = &$item[0];
                if (mb_strlen((string) $label, 'utf-8') > $labelLength) {
                    $label = mb_substr((string) $label, 0, $labelLength, 'utf-8').'...';
                }
            }
        }
    }

    /**
     * @return array
     */
    private static function getLabelLength(array $tcaTableInformation)
    {
        if (isset($tcaTableInformation['config']['labelLength'])
            && intval($tcaTableInformation['config']['labelLength']) > 0) {
            return $tcaTableInformation['config']['labelLength'];
        }

        return 80;
    }

    public static function getGermanStatesField($isRequired = false): array
    {
        $tcaFieldConfig = [
            'exclude' => 1,
            'label' => 'LLL:EXT:mklib/Resources/Private/Language/locallang_db.xlf:tt_address.region',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:mklib/Resources/Private/Language/locallang_db.xlf:please_choose', ''],
                ],
                'foreign_table' => 'static_country_zones',
                'foreign_table_where' => ' AND static_country_zones.zn_country_iso_nr = 276 ORDER BY static_country_zones.zn_name_local',
                'size' => 1,
            ],
        ];

        if ($isRequired) {
            $tcaFieldConfig['config']['minitems'] = 1;
            $tcaFieldConfig['config']['maxitems'] = 1;
            $tcaFieldConfig['config']['eval'] = 'required';
        }

        return $tcaFieldConfig;
    }

    /**
     * entweder DAM oder FAL.
     *
     * @param array $ref
     * @param array $options These options are merged into the resulting TCA
     *
     * @return array
     */
    public static function getMediaTCA($ref, array $options = [])
    {
        // in DAM wurde immer noch _field beim Typ verlangt, bei FAL nicht mehr
        if (isset($options['type'])) {
            $options['type'] = str_replace('_field', '', $options['type']);
        }

        return Sys25\RnBase\Utility\TSFAL::getMediaTCA($ref, $options);
    }
}
