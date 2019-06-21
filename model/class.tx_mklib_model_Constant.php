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
tx_rnbase::load('tx_rnbase_model_base');

/**
 * Model einer Konstante.
 */
abstract class tx_mklib_model_Constant extends tx_rnbase_model_base
{
    /**
     * returns the alias1 of this constant.
     *
     * @return string
     */
    public function getAlias1()
    {
        return $this->record['alias1'];
    }

    /**
     * returns the alias2 of this constant.
     *
     * @return string
     */
    public function getAlias2()
    {
        return $this->record['alias2'];
    }

    /**
     * returns the name of this constant.
     *
     * @return string
     */
    public function getName()
    {
        return $this->record['name'];
    }

    /**
     * returns the name of this constant.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->record['name'];
    }

    /**
     * returns the type number of this constant.
     *
     * @return int
     */
    public function getTypeUid()
    {
        return (int) $this->record['type'];
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/models/class.tx_mklib_models_Constant.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/models/class.tx_mklib_models_Constant.php'];
}
