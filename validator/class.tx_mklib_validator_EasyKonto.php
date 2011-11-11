<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_validator
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
tx_rnbase::load('tx_mklib_util_EasyKonto');

/**
 * Validatoren für den EasyKonto Service
 *
 * @author hbochmann
 * @package tx_mklib
 * @subpackage tx_mklib_validator
 */
class tx_mklib_validator_EasyKonto {
	
	protected $username;
	protected $password;
	protected $use_ssl;
	protected $baseUrl;
	
	/**
	 * Klassen konstruktor
	 * @param string $username
	 * @param string $password
	 * @param bool $use_ssl
	 * @param string $baseUrl
	 */
	public function __construct($username='', $password='', $use_ssl='', $baseUrl='') {
		$this->username = $username;
		$this->password = $password;
		$this->use_ssl = $use_ssl;
		$this->baseUrl = $baseUrl;
	}

	/**
	 * Prüft ob die Bankleitzahl und die Kontonummer zusammen passen
	 * Wrapper für tx_mklib_util_EasyKonto::checkBICAndAccountNumber()
   	 *
   	 * @param int $bic
   	 * @param int $accountNumber
   	 * @return bool 
   	 */
  	public function checkBICAndAccountNumber ($bic,$accountNumber) {
  		$easyKontoSrv = tx_mklib_util_EasyKonto::getInstance($this->username,$this->password,$this->use_ssl,$this->baseUrl);
  	    $checkResult = $easyKontoSrv->checkBICAndAccountNumber($bic, $accountNumber);

  	    //Achtung: Wenn hier auf einen string geprüft wird, führt das
  	    //im Falle von 0 zur fehlerhaften Einstufung als Fehler. 0 bedeutet
  	    //valide
    	if (is_null($checkResult))//Fehler bei der Prüfung
        	return false;

        switch ($checkResult){
            case EASYKONTO_VALID:
            case EASYKONTO_NOT_CHECKABLE:
                return true;
                break;
            default:
                return false;
        }
  	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/validator/class.tx_mklib_validator_EasyKonto.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/validator/class.tx_mklib_validator_EasyKonto.php']);
}

?>