<?php
/*
 *
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
 * Interface für ein Subject Objekt bei Verwendung des Subject/Observer Patterns
 *
 * @package tx_mklib
 * @subpackage tx_mklib_model
 * @author Hannes Bochmann
 */
interface tx_mklib_interface_IObservable {

	/**
	 * regisriert einen Observer
	 * @param tx_mklib_interface_IObserver $oObserver
	 *
	 * @return void
	 */
	public function registerObserver(tx_mklib_interface_IObserver $oObserver);

	/**
	 * löscht einen regisrierten einen Observer
	 * @param tx_mklib_interface_IObserver $oObserver
	 *
	 * @return void
	 */
	public function unregisterObserver(tx_mklib_interface_IObserver $oObserver);

	/**
	 * ruft notify() auf allen regisrtierten Observern
	 * aus
	 *
	 * @return void
	 */
	public function notifyObservers();

	/**
	 * liefert die registrierten observer
	 *
	 * @return array[tx_mklib_interface_IObserver]
	 */
	public function getObservers();
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php']);
}