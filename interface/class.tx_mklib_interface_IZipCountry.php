<?php
/*
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 * Interface für PLZ-Validierung von Ländern.
 *
 * @author René Nitzsche
 */
interface tx_mklib_interface_IZipCountry
{
    /**
     * Liefert den ISO2 Code des Landes. DE,CZ,PL usw.
     *
     * @return string
     */
    public function getISO2();

    /**
     * ID der Regel für die PLZ-Validierung.
            9: special rules

     * @return int 1-9
     */
    public function getZipRule();

    /**
     * Erlaubte Anzahl Zeichen der PLZ.
     *
     * @return int
     */
    public function getZipLength();
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php'];
}
