<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Lars Heber <lars.heber@dmk-ebusiness.de>
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

tx_rnbase::load('tx_mklib_srv_Base');

/**
 * Service for accessing constants.
 */
abstract class tx_mklib_srv_Constant extends tx_mklib_srv_Base
{
    /**
     * Get constants by their type.
     *
     * Additional fields and options can be defined.
     * Constant values are ordered by name by default
     * (important for very special querys with $options['what']
     * (might need to explicitely set $options['ORDERBY']=null).
     *
     * @param mixed $type
     * @param array $fields  Additional fields (table alias: "CONST")
     * @param array $options Additional options
     *
     * @return tx_mklib_models_Constants
     */
    public function getConstantsByType($type, array $fields = array(), array $options = array())
    {
        if (is_array($type)) {
            $fields['CONSTANT.type'] = array(OP_IN_INT => implode(',', $type));
        } else {
            $fields['CONSTANT.type'] = array(OP_EQ_INT => $type);
        }
        if (!array_key_exists('ORDERBY', $options)) {
            $options['ORDERBY'] = array('CONSTANT.NAME' => 'ASC');
        }

        return $this->search($fields, $options);
    }

    /***************************************************
     * Often needed methods for specific constant values
     ***************************************************/

    /**
     * Get textual Yes / No.
     *
     * @param int $yesOrNo Numerical representation of yes / no
     *
     * @return string Textual "Yes" / "No"
     */
    public function getSpecificValue_YesNo($yesOrNo)
    {
        $fields = array('CONSTANT.alias1' => array(OP_EQ_INT => (bool) $yesOrNo));
        $options = array('what' => 'name', 'ORDERBY' => null, 'LIMIT' => 1);
        $foo = $this->getConstantsByType(100, $fields, $options);
        if (count($foo)) {
            return $foo[0]['name'];
        }
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Constant.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Constant.php'];
}
