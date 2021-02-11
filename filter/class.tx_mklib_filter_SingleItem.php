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
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
abstract class tx_mklib_filter_SingleItem extends tx_rnbase_filter_BaseFilter
{
    /**
     * (non-PHPdoc).
     *
     * @see tx_rnbase_filter_BaseFilter::initFilter()
     */
    protected function initFilter(&$fields, &$options, &$parameters, &$configurations, $confId)
    {
        $singleItemUid = $parameters->getInt($this->getParameterName());
        $fields[$this->getSearchAlias().'.uid'] = [OP_EQ_INT => $singleItemUid];
        $options['limit'] = 1;

        return true;
    }

    /**
     * @return string
     */
    abstract protected function getParameterName();

    /**
     * @return string
     */
    abstract protected function getSearchAlias();
}
