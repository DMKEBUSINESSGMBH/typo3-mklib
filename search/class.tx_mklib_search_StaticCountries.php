<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2012 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 * benötigte Klassen einbinden.
 */

/**
 * Class to search ads from database.
 */
class tx_mklib_search_StaticCountries extends \Sys25\RnBase\Search\SearchBase
{
    /**
     * getTableMappings().
     */
    protected function getTableMappings()
    {
    }

    /**
     * useAlias().
     */
    protected function useAlias()
    {
        return true;
    }

    /**
     * getBaseTableAlias().
     */
    protected function getBaseTableAlias()
    {
        return 'STATICCOUNTRY';
    }

    /**
     * getBaseTable().
     */
    protected function getBaseTable()
    {
        return 'static_countries';
    }

    /**
     * getWrapperClass().
     */
    public function getWrapperClass()
    {
        return 'tx_mklib_model_StaticCountry';
    }

    /**
     * Liefert alle JOINS zurück.
     *
     * @param array $tableAliases
     *
     * @return string
     */
    protected function getJoins($tableAliases)
    {
    }
}
