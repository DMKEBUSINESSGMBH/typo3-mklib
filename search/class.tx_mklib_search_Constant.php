<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 * Class to search constants from database.
 */
abstract class tx_mklib_search_Constant extends tx_rnbase_util_SearchBase
{
    /**
     * (non-PHPdoc).
     *
     * @see tx_rnbase_util_SearchBase::getTableMappings()
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
     * @see tx_rnbase_util_SearchBase::useAlias()
     */
    protected function useAlias()
    {
        return true;
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_rnbase_util_SearchBase::getBaseTableAlias()
     */
    public function getBaseTableAlias()
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
     * @see tx_rnbase_util_SearchBase::getJoins()
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
