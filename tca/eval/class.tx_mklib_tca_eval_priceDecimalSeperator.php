<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 das-medienkombinat
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
 * Ersetzt Komma mit Punkt.
 */
class tx_mklib_tca_eval_priceDecimalSeperator
{
    /**
     * Evaluate value on client-side via JavaScript.
     *
     * @return string
     */
    public function returnFieldJS()
    {
        return 'return value.replace(\',\', \'.\');';
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
        return str_replace(',', '.', $value);
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tca/eval/class.tx_mklib_tca_eval_priceDecimalSeperator.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tca/eval/class.tx_mklib_tca_eval_priceDecimalSeperator.php'];
}
