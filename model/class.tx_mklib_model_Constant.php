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
 * Model einer Konstante.
 */
abstract class tx_mklib_model_Constant extends Sys25\RnBase\Domain\Model\BaseModel
{
    /**
     * returns the alias1 of this constant.
     *
     * @return string
     */
    public function getAlias1()
    {
        return $this->getProperty('alias1');
    }

    /**
     * returns the alias2 of this constant.
     *
     * @return string
     */
    public function getAlias2()
    {
        return $this->getProperty('alias2');
    }

    /**
     * returns the name of this constant.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getProperty('name');
    }

    /**
     * returns the name of this constant.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getProperty('name');
    }

    /**
     * returns the type number of this constant.
     *
     * @return int
     */
    public function getTypeUid()
    {
        return (int) $this->getProperty('type');
    }
}
