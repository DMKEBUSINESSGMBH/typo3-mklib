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
require_once(t3lib_extMgm::extPath('mklib') . 'util/easyKontoAutoloader.php');

/**
 * Validatoren für den EasyKonto Service
 *
 * @author hbochmann
 * @package tx_mklib
 * @subpackage tx_mklib_validator
 */
class tx_mklib_validator_EasyKonto {

	/**
	 * Prüft ob die Bankleitzahl und die Kontonummer zusammen passen
   	 *
   	 * @param int $bic
   	 * @param int $accountNumber
   	 * @param string $username
	 * @param string $password
	 *
   	 * @return bool
   	 */
  	public static function checkBICAndAccountNumber ($bic,$accountNumber,$username='', $password='') {
  		//bei nicht aktiven EasyKonto gilt alles als valide. Nur für TESTS!!!
  		if(!tx_mklib_util_MiscTools::isEasyKontoActive())
  			return true;

		//Zugangsdaten für den webservice setzen
		$username = empty($username) ? tx_rnbase_configurations::getExtensionCfgValue('mklib', 'easyKontoUser') : $username;
    	$password = empty($password) ? tx_rnbase_configurations::getExtensionCfgValue('mklib', 'easyKontoPass') : $password;

  		//SINGLE_NODE bedeutet dass die verbindung über ssl hergestellt wird
  		$oConf = new EasyKonto_ConnectionConfiguration(
		    EasyKonto_ConnectionType::SINGLE_NODE,
		    $username,
		    $password
		);

		$oBankService = new EasyKonto_DE_Service($oConf);
  	    $oCheckResult = $oBankService->checkAccount($bic, $accountNumber);

		if ($oCheckResult->isValid())
			return true;
		else
			return false;
  	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/validator/class.tx_mklib_validator_EasyKonto.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/validator/class.tx_mklib_validator_EasyKonto.php']);
}

?>