<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_tests
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
 * benötigte Klassen einbinden
 */
tx_rnbase::load('tx_rnbase_util_TYPO3');
if (!tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
    require_once(PATH_site.'typo3/template.php');
}

/**
 * Statische Hilfsmethoden für Tests
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests
 */
class tx_mklib_tests_mod1_Util
{

    /**
     * Deaktiviert den Cache
     */
    public static function replaceForCli(&$sString)
    {
        if (tx_rnbase_util_TYPO3::isCliMode()) {
            //wir müssen noch mod.php durch cli_dispatch.phpsh ersetzen
            $sString = str_replace('mod.php', 'cli_dispatch.phpsh', $sString);
            //außerdem müssen die get parameter, die im BE gesetzt sind löschen
            $sString = str_replace('&amp;M=tools_txphpunitbeM1', '', $sString);
            $sString = str_replace('M%3Dtools_txphpunitbeM1', '', $sString);
            //für CC
            $sString = str_replace('%2Ftypo3%2Fcli_dispatch.phpsh%3F&amp;', urlencode(PATH_site).'typo3%2Fcli_dispatch.phpsh&amp;', $sString);
            $sString = str_replace('%2Ftypo3%2Fcli_dispatch.phpsh%3F%26', urlencode(PATH_site).'typo3%2Fcli_dispatch.phpsh%3F', $sString);
        }

        return $sString;
    }

    /**
     * Deaktiviert den Cache und den formtoken
     */
    public static function removeVcAndFormToken(&$sString)
    {
        //cache und formtoken weg - diese sind ständig unterschiedlich
        //und deren Funktionalität sollte nicht hier getestet werden
        //auf der cli über cc ist der formtoken um 2 zeichen länger
        //den formToken gibt es erst ab TYPO3 4.5
        $sVcAndFormTokenRegex = (tx_rnbase_util_TYPO3::isTYPO45OrHigher()) ? '/&amp;vC=(.*?)&formToken=(.*?)\'\)/' : '/&amp;vC=(.*?)\'\)/';
        $sString = preg_replace($sVcAndFormTokenRegex, '\')', $sString);
        $moduleTokenRegex = (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) ? '/%26moduleToken%3D(.*?)&amp/' : '//';
        $sString = preg_replace($moduleTokenRegex, '&amp', $sString);
        $sString = str_replace('=1&amp;', '=1\'', $sString);
    }

    /**
     * Löscht die gesetzten Sortierungsoptionen
     * Enter description here ...
     * @param tx_rnbase_mod_BaseModule $mod
     */
    public static function unsetSorting(tx_rnbase_mod_BaseModule $mod)
    {
        unset($GLOBALS['BE_USER']->uc['moduleData'][$mod->getName()]['dummySearcherorderby']);
        if (isset($_GET['sortField'])) {
            unset($_GET['sortField']);
        }
        if (isset($_GET['sortRev'])) {
            unset($_GET['sortRev']);
        }
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_Util.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_Util.php']);
}
