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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_interface_IObservable');
tx_rnbase::load('t3lib_svbase');

/**
 * Interface für ein Subject Objekt bei Verwendung des Subject/Observer Patterns
 *
 * erbt von t3lib_svbase um die klasse in services nutzen zu können
 *
 * @package tx_mklib
 * @subpackage tx_mklib_model
 * @author René Nitzsche
 */
abstract class tx_mklib_abstract_ObservableT3Service extends t3lib_svbase implements tx_mklib_interface_IObservable{

	/**
	 * alle registrierten Observer
	 * @var array[tx_mklib_interface_IObserver]
	 */
	protected $aObservers = array();

	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_interface_IObservable::notifyObservers()
	 */
	public function notifyObservers(array $aData = array()) {
		if(!empty($this->aObservers)){
			foreach ($this->aObservers as $oObserver) {
				$oObserver->notify($aData);
			}
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_interface_IObservable::registerObserver()
	 */
	public function registerObserver(tx_mklib_interface_IObserver $oObserver) {
		$this->aObservers[get_class($oObserver)] = $oObserver;
	}

	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_interface_IObservable::unregisterObserver()
	 */
	public function unregisterObserver(tx_mklib_interface_IObserver $oObserver) {
		if(isset($this->aObservers[get_class($oObserver)]))
			unset($this->aObservers[get_class($oObserver)]);
	}

	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_interface_IObservable::getObservers()
	 */
	public function getObservers() {
		return $this->aObservers;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php']);
}