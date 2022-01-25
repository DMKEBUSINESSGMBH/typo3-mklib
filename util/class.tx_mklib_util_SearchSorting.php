<?php
/**
 * @author Michael Wagner
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
 * Search Sorting.
 * Die Klasse registriert einen Hook für rnbase,
 * um SQL-Anfragen mit einer sortierung zu versehen.
 *
 * @see \Sys25\RnBase\Search\SearchBase::search -> searchbase_handleTableMapping
 *
 * Nützlich ist dies, wenn Einträge immer nach dem Titel sortiert werden sollen,
 * oder die Ausgabe im FE wie im BE mittels der sorting Spalte sortiert werden sollen.
 *
 * @author mwagner
 */
class tx_mklib_util_SearchSorting
{
    /**
     * Liefert den Name dieser Klasse.
     * Dies ist für Kinds-Klassen wichtig, da die Methoden alle statisch aufgerufen werden.
     *
     * @TODO:   wie lässt sich die klasse bei kindsklassen herausfinden!?
     *          Statische dinge können leider nicht überschrieben werden.
     *          Einzige möglichkeit ist zurzeit, diese Variable durch überschreiben von
     *          self::registerSortingAliases zu setzen.
     *
     * @var string
     */
    protected static $className = __CLASS__;

    /**
     * Wurde der hook bereits gesetzt?
     *
     * @var bool
     */
    protected static $hooked = false;

    /**
     * Enthält TableAliases, welche sortiert werden sollen.
     *
     * @var array
     */
    private static $sortingTables = [];

    /**
     * Fügt Tabellen für das Sortieren hinzu und registriert den Hook.
     *
     * @param array $tableAliases array($tableAlias.$tableName => $sortingColumn)
     *                            $tableName muss nicht gesetzt sein, sollte aber um Konflikte zu vermeiden
     *                            Beispiel:
     *
     * array(
     *  'DOWNLOAD.tx_mytable'=>'sorting', // optimal
     *  'CATEGORY'=>'sorting' // nicht optimal
     * )
     */
    public static function registerSortingAliases(array $tableAliases)
    {
        if (count($tableAliases)) {
            foreach ($tableAliases as $tableAlias => $sortingCol) {
                list($tableAlias, $tableName) = \Sys25\RnBase\Utility\Strings::trimExplode('.', $tableAlias);
                // wenn der key numeric ist, wurde keine sorting col übergeben!
                if (is_numeric($tableAlias) && $sortingCol) {
                    $tableAlias = $sortingCol;
                    $sortingCol = 'sorting';
                }
                if (empty($tableAlias)) {
                    continue;
                }
                self::$sortingTables[] = [
                    'alias' => $tableAlias,
                    'column' => $sortingCol,
                    'table' => $tableName,
                ];
            }
            // den hook registrieren
            if (count(self::$sortingTables)) {
                self::registerHook();
            }
        }
    }

    /**
     * Registriert den Hook für rnbase, um die sortierung hinzuzufügen.
     */
    private static function registerHook()
    {
        if (!self::$hooked) {
            // $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['searchbase_handleTableMapping'][] = 'EXT:mklib/util/class.tx_mklib_util_SearchSorting.php:&tx_mklib_util_SearchSorting->handleTableMapping';

            // Die Klasse ist schon geladen, wir brauchen den Pfad also nicht.
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['searchbase_handleTableMapping'][] = self::$className.'->handleTableMapping';

            self::$hooked = true;
        }
    }

    /**
     * Wird von \Sys25\RnBase\Search\SearchBase aufgerufen um die Sortierung hinzuzufügen.
     *
     * @param array                     $params
     * @param \Sys25\RnBase\Search\SearchBase $searcher
     */
    public static function handleTableMapping(&$params, &$searcher)
    {
        if (count(self::$sortingTables)) {
            $tableAliases = &$params['tableAliases'];
            //@TODO: $joinedFields && $customFields zusätzlich zu den $tableAliases beachten!!!
            // $joinedFields = & $params['joinedFields'];
            // $customFields = & $params['customFields'];
            $options = &$params['options'];
            $tableMappings = &$params['tableMappings'];

            // sortierung nur bei self::$sortingTables aufrufen
            foreach (self::$sortingTables as $tableData) {
                $tableAlias = $tableData['alias'];
                $sortingCol = $tableData['column'];
                $tableName = $tableData['table'];
                $field = $tableAlias.'.'.$sortingCol;
                if (isset($tableAliases[$tableAlias]) &&
                    !isset($options['orderby'][$field]) &&
                    (!$tableName || isset($tableMappings[$tableName]))
                ) {
                    // orderby muss ein array sein
                    if (!is_array($options['orderby'])) {
                        $options['orderby'] = [];
                    }
                    // immer zuerst anhand von sorting sortieren!!!
                    $options['orderby'] = [$field => 'ASC'] + $options['orderby'];
                }
            }
        }
    }
}
