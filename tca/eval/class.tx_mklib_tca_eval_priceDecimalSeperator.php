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
 * Ersetzt Komma mit Punkt.
 */
class tx_mklib_tca_eval_priceDecimalSeperator
{
    /**
     * Evaluate value on client-side via JavaScript.
     */
    public function returnFieldJS(): string
    {
        return 'return value.replace(\',\', \'.\');';
    }

    /**
     * Evaluate value on server-side by ourselves.
     *
     * @param string $value
     * @param string $is_in
     * @param bool   $set
     */
    public function evaluateFieldValue($value, $is_in, &$set): string
    {
        return str_replace(',', '.', $value);
    }
}
