<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_search
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


tx_rnbase::load('tx_rnbase_util_SearchBase');


/**
 * Class to search ads from database
 *
 * @package tx_mklib
 * @subpackage tx_mklib_search
 */
class tx_mklib_search_Wordlist extends tx_rnbase_util_SearchBase
{
    /**
     * getTableMappings()
     */
    protected function getTableMappings()
    {
    }

  /**
   * useAlias()
   */
    protected function useAlias()
    {
        return true;
    }

  /**
   * getBaseTableAlias()
   */
    protected function getBaseTableAlias()
    {
        return 'WORDLIST';
    }

  /**
   * getBaseTable()
   */
    protected function getBaseTable()
    {
        return 'tx_mklib_wordlist';
    }

  /**
   * getWrapperClass()
   */
    public function getWrapperClass()
    {
        return 'tx_mklib_model_WordlistEntry';
    }

  /**
   * Liefert alle JOINS zurück
   *
   * @param array $tableAliases
   * @return string
   */
    protected function getJoins($tableAliases)
    {
    }
}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/search/class.tx_mklib_search_Wordlist.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/search/class.tx_mklib_search_Wordlist.php']);
}
