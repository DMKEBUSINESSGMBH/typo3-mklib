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
 * Class to search ads from database.
 */
class tx_mklib_search_StaticCountries extends Sys25\RnBase\Search\SearchBase
{
    /**
     * getTableMappings().
     */
    protected function getTableMappings(): array
    {
        return [];
    }

    /**
     * useAlias().
     */
    protected function useAlias(): bool
    {
        return true;
    }

    /**
     * getBaseTableAlias().
     */
    protected function getBaseTableAlias(): string
    {
        return 'STATICCOUNTRY';
    }

    /**
     * getBaseTable().
     */
    protected function getBaseTable(): string
    {
        return 'static_countries';
    }

    /**
     * getWrapperClass().
     */
    public function getWrapperClass(): string
    {
        return 'tx_mklib_model_StaticCountry';
    }

    /**
     * Liefert alle JOINS zurück.
     *
     * @param array $tableAliases
     */
    protected function getJoins($tableAliases): string
    {
        return '';
    }
}
