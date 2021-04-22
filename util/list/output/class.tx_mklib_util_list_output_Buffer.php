<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Buffer Handler.
 * Collects all the output internal.
 * Output can be accessed by get Output method.
 *
 * @author Michael Wagner
 */
class tx_mklib_util_list_output_Buffer implements tx_mklib_util_list_output_Interface
{
    /**
     * @var string
     */
    private $output = '';

    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Do the output.
     */
    public function handleOutput()
    {
        if (func_num_args() > 0) {
            foreach (func_get_args() as $output) {
                if ('' != $output) {
                    $this->output .= $output;
                }
            }
        }
    }
}
