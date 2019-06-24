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

/**
 * generischer observer.
 * einfach die execute methode bereitstellen und das
 * eventuell unterstütze interface, welches genutzt wird
 * um die daten des observable abzufragen.
 */
abstract class tx_mklib_abstract_Observer implements tx_mklib_interface_IObserver
{
    /**
     * wenn das observable nicht diese interface implementiert
     * dann wird execute() nicht ausgeführt.
     * wenn nicht auf ein interface geprüft werden soll dann einfach
     * leer lassen.
     * Beispiel:
     * protected $sSupportedInterface = tx_mkextension_interface_ForSomething
     * die klasse einfach angeben. nicht als string!
     *
     * @var const
     */
    protected $sSupportedInterface;

    /**
     * kann nicht überschrieben werden!!!
     * (non-PHPdoc).
     *
     * @see tx_mklib_interface_IObserver::notify()
     */
    final public function notify(tx_mklib_interface_IObservable $oObservable)
    {
        //wird nur ein bestimmtes interface unterstützt?
        $supportedInterface = $this->getSupportedInterface();
        if ($supportedInterface && !$oObservable instanceof $supportedInterface) {
            return;
        }
        $this->execute($oObservable);
    }

    protected function getSupportedInterface()
    {
        return null;
    }

    /**
     * führt die eigentiche arbeit des jeweiligen
     * observers aus.
     *
     * @param tx_mklib_interface_IObservable $oObservable
     */
    abstract protected function execute(tx_mklib_interface_IObservable $oObservable);
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mkjjk/srv/class.tx_mkjjk_srv_JJKInterface.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mkjjk/srv/class.tx_mkjjk_srv_JJKInterface.php'];
}
