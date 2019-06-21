<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 * benötigte Klassen einbinden.
 */
tx_rnbase::load('tx_mklib_abstract_ObservableT3Service');
tx_rnbase::load('tx_mklib_tests_fixtures_classes_ObservableInterface');

/**
 * Dummy Service um uns DB Abfragen zu ersparen.
 */
class tx_mklib_tests_fixtures_classes_ObservableT3Service extends tx_mklib_abstract_ObservableT3Service implements tx_mklib_tests_fixtures_classes_ObservableInterface
{
    protected $aData = array();

    public function getDataForObservers()
    {
        return $this->aData;
    }

    public function setDataForObservers($aData = array())
    {
        $this->aData = $aData;
    }

    public function doSomething($aData = array())
    {
        $this->setDataForObservers($aData);
        $this->notifyObservers();
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Wordlist.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Wordlist.php'];
}
