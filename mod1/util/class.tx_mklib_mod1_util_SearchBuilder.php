<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 * Hilfsklasse für Suchen im BE.
 */
class tx_mklib_mod1_util_SearchBuilder
{
    /**
     * Suche nach einem Freitext bei der Wordlist-Suche. Wird ein leerer String
     * übergeben, dann wird nicht gesucht.
     *
     * @param array  $fields
     * @param string $searchword
     */
    public static function buildFeUserFreeText(&$fields, $searchword)
    {
        $result = false;
        if (strlen(trim($searchword))) {
            $joined['value'] = trim($searchword);
            $joined['cols'] = ['FEUSER.uid', 'FEUSER.LAST_NAME', 'FEUSER.FIRST_NAME', 'FEUSER.username', 'FEUSER.email'];
            $joined['operator'] = OP_LIKE;
            $fields[SEARCH_FIELD_JOINED][] = $joined;
            $result = true;
        }

        return $result;
    }

    /**
     * Build a wildcard query. Support for phrases:
     * "bad ar" will be turned into "+field:bad* +field:ar*"
     * Note: the following signs will be ignored: ,.&*+-%/.
     *
     * @param string $term
     * @param string $fieldName
     * @param bool   $leadingWC force leading wildcard query
     *
     * @return string
     */
    public static function makeWildcardTerm($term, $fieldName = '', $leadingWC = false)
    {
        $term = $term ? mb_strtolower(trim($term), 'UTF-8') : '*';

        // wir brauchen 3 backslashes (\\\) um einen einfachen zu entwerten.
        // der erste entwertet den zweiten für die hochkommas. der zweite
        // entwertet den dritten für regex.
        // ansonsten sind das alle Zeichen, die in Solr nicht auftauchen
        // dürfen da sie zur such-syntax gehören
        // genau dürfen nicht auftauchen: + - & | ! ( ) { } [ ] ^ " ~ * ? : \
        // außerdem nehmen wir folgende raus um die Suche zu verfeinern:
        // , . / # '
        // eigentlich sollte dies aber ebenfalls durch Solr filter realisiert
        // werden
        $pattern = '/[\s%,.&*+-\/\'!?#()\[\]\{\}"^|:\\\~]+/';
        $arr = preg_split($pattern, $term);

        $terms = [];
        $field = $fieldName ? $fieldName.':' : '';
        foreach ($arr as $term) {
            // einen leeren string ignorieren
            if (empty($term)) {
                continue;
            }
            // @FIXME: warum Hochkommas um den string?
            // es handelt sich um ein einzelnes wort!
            // bei buhl musste dies wieder entfernt werden, da es mit hochkommas nicht funktionierte.
            $terms[] = '+'.$field.($leadingWC ? '*' : '').'"'.$term.'"*';
        }

        return implode(' ', $terms);
    }

    /**
     * Suche nach einem Freitext. Wird ein leerer String
     * übergeben, dann wird nicht gesucht.
     *
     * @param array  $fields
     * @param string $searchword
     * @param array  $cols
     */
    public static function buildFreeText(&$fields, $searchword, array $cols = [])
    {
        $result = false;
        if (strlen(trim($searchword))) {
            $joined['value'] = trim($searchword);
            $joined['cols'] = $cols;
            $joined['operator'] = OP_LIKE;
            $fields[SEARCH_FIELD_JOINED][] = $joined;
            $result = true;
        }

        return $result;
    }
}
