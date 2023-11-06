<?php
/**
 *  Copyright notice.
 *
 *  (c) 2014 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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
 * Evaluate a value as data type "iso date" as used for MySQL date fields.
 */
class tx_mklib_tca_eval_isoDate
{
    /**
     * Evaluate value on client-side via JavaScript.
     *
     * @return string
     */
    public function returnFieldJS()
    {
        return <<<LH
var regex = /(\d{4})[-\/.]{1}(\d{1,2})[-\/.]{1}(\d{1,2})/;
var result = regex.exec(value);
if (!result) return '0000-00-00';
var year = result[1];
var month = result[2]-1; // January=0
var date = result[3];
dateObj = new Date(year, month, date);
// Check if the "calculated" date is identical to the given one
return dateObj.getFullYear() == year && dateObj.getMonth() == month && dateObj.getDate() == date ?
    result[1]+'-'+result[2]+'-'+result[3] :
    '0000-00-00';
LH;
    }

    /**
     * Evaluate value on server-side by ourselves.
     *
     * @param string $value
     * @param string $is_in
     * @param bool   $set
     *
     * @return string
     */
    public function evaluateFieldValue($value, $is_in, &$set)
    {
        if (!(
            preg_match('/(\d{4})[-\/.]{1}(\d{1,2})[-\/.]{1}(\d{1,2})/', $value, $matches)
            && checkdate($matches[2], $matches[3], $matches[1])
        )) {
            return '0000-00-00';
        }

        // else
        return $matches[1].'-'.$matches[2].'-'.$matches[3];
    }
}
