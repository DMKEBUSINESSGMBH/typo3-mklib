<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_srv
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_interface_IObserver');

/**
 * Dummy Service um uns DB Abfragen zu ersparen
 *
 * @package tx_mklib
 * @subpackage tx_mklib_srv
 */
class tx_mklib_tests_fixtures_classes_FirstObserver implements tx_mklib_interface_IObserver{

	/**
	 * speichert die aufrufe von notify
	 * @var array
	 */
	public $aNotified = array();

	/**
	 * wie oft wurde notify aufgerufen?
	 * @var int
	 */
	public $iNotified = 0;

	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_interface_IObserver::notify()
	 */
	public function notify(tx_mklib_interface_IObservable $oObservable) {
		//prüfen ob wir die richtige klasse haben damit wir die daten abgreifen können die wir brauchen
		if(!$oObservable instanceof tx_mklib_tests_fixtures_classes_ObservableInterface)
			return;
		$this->aNotified[] = $oObservable->getDataForObservers();
		$this->iNotified++;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Wordlist.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Wordlist.php']);
}

?>