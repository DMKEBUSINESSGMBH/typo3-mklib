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
 * Service for accessing constants.
 */
abstract class tx_mklib_srv_Constant extends tx_mklib_repository_Abstract
{
    /**
     * Get constants by their type.
     *
     * Additional fields and options can be defined.
     * Constant values are ordered by name by default
     * (important for very special querys with $options['what']
     * (might need to explicitely set $options['ORDERBY']=null).
     *
     * @param array $fields  Additional fields (table alias: "CONST")
     * @param array $options Additional options
     *
     * @return tx_mklib_models_Constants
     */
    public function getConstantsByType($type, array $fields = [], array $options = [])
    {
        $fields['CONSTANT.type'] = is_array($type) ? [OP_IN_INT => implode(',', $type)] : [OP_EQ_INT => $type];

        if (!array_key_exists('ORDERBY', $options)) {
            $options['ORDERBY'] = ['CONSTANT.NAME' => 'ASC'];
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
        $fields = ['CONSTANT.alias1' => [OP_EQ_INT => (bool) $yesOrNo]];
        $options = ['what' => 'name', 'ORDERBY' => null, 'LIMIT' => 1];
        $foo = $this->getConstantsByType(100, $fields, $options);
        if (count($foo) > 0) {
            return $foo[0]['name'];
        }

        return '';
    }
}
