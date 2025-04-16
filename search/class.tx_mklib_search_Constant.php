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
 * Class to search constants from database.
 */
abstract class tx_mklib_search_Constant extends Sys25\RnBase\Search\SearchBase
{
    /**
     * (non-PHPdoc).
     *
     * @see \Sys25\RnBase\Search\SearchBase::getTableMappings()
     */
    protected function getTableMappings()
    {
        $tableMapping['CONSTANT'] = $this->getBaseTable();
        $tableMapping['CONSTANTTYPE'] = $this->getConstantTypesTable();

        return $tableMapping;
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Sys25\RnBase\Search\SearchBase::useAlias()
     */
    protected function useAlias()
    {
        return true;
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Sys25\RnBase\Search\SearchBase::getBaseTableAlias()
     */
    protected function getBaseTableAlias()
    {
        return 'CONSTANT';
    }

    /**
     * Liefert die Tabelle, welche die Konstantentypen enthält.
     *
     * @return string
     */
    abstract protected function getConstantTypesTable();

    /**
     * (non-PHPdoc).
     *
     * @see \Sys25\RnBase\Search\SearchBase::getJoins()
     */
    protected function getJoins($tableAliases)
    {
        $join = '';

        if (isset($tableAliases['CONSTANTTYPE'])) {
            $join .= ' JOIN '.$this->getConstantTypesTable().' AS CONSTANTTYPE ON CONSTANT.type = CONSTANTTYPE.uid';
        }

        return $join;
    }
}
