<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author 
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
 * benötige Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_configurations');
tx_rnbase::load('tx_mklib_util_MiscTools');

/**
 * Fehlercodes
 * nähere Informationen auf https://www.easykonto.de/kundenbereich/dokumentation.htm
 */
define('EASYKONTO_VALID', 0);
define('EASYKONTO_INVALID', 1);
define('EASYKONTO_NOT_CHECKABLE', 2);
define('EASYKONTO_NOT_IMPLEMENTED', 3);
define('EASYKONTO_BLZ_DELETED', 4);
define('EASYKONTO_BLZ_NOT_FOUND', 5);

/**
 * PHP5-API zum Zugriff auf den easyKonto Online Web-service
 * PHP CURL EXTENSION UNBEDINGT NOTWENDIG
 *
 * @copyright Copyright &copy; 2006-2007 by Oliver Siegmar
 * @license http://www.easykonto.de/agb.htm Proprietäre Lizenz
 * @version 1.0 - $LastChangedDate: 2007-07-17 18:48:01 +0200 (Tue, 17 Jul 2007) $
 * @author Hannes Bochmann
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_EasyKonto {
    /**
     * @var resource Das cURL resource handle
     */
    private $curl;

    /**
     * @var string Die Basis-URL des Web-Services
     */
    private $base_url;
    
    private static $instance = null;

    /**
     * Initialisiert die von dieser Klasse verwendete cURL-Bibliothek mit
     * den angegebenen Zugriffsdaten.
     *
     * @param string $username Der Benutzername, der für den Zugriff auf die
     *                         easyKonto Online-Schnittstelle verwendet werden
     *                         soll. Dieser kann entweder übergeben werden oder wird aus 
     *                         der ext_conf_template geladen
     * @param string $password Das Passwort, der für den Zugriff auf die
     *                         easyKonto Online-Schnittstelle verwendet werden
     *                         soll. Dieses kann entweder übergeben werden oder wird aus 
     *                         der ext_conf_template geladen
     * @param bool $use_ssl Ob SSL beim Zugriff verwendet werden soll.Dieses kann 
     * 							entweder übergeben werden oder wird aus der ext_conf_template geladen
     * @param string $baseUrl Die Url zum easyKonto Service. Diese kann entweder 
     * 							übergeben werden oder wird aus der ext_conf_template geladen           
     */
    private function __construct($username='', $password='', $use_ssl='', $baseUrl='') {
    	//ohne curl geht nix
    	if(!extension_loaded('curl'))
	    	throw tx_rnbase::makeInstance(
	        	'tx_mklib_exception_InvalidConfiguration',
	        	__METHOD__.': Die PHP Curl Extension ist nicht geladen!'
	     	 );
    	//Konfiguration laden
    	if(empty($username))
    		$username = tx_rnbase_configurations::getExtensionCfgValue('mklib', 'easyKontoUser');
		if(empty($password))
    		$password = tx_rnbase_configurations::getExtensionCfgValue('mklib', 'easyKontoPass');    		
		if(empty($use_ssl) && $use_ssl !== false)
    		$use_ssl = tx_rnbase_configurations::getExtensionCfgValue('mklib', 'easyKontoUseSSL');
    	if(empty($baseUrl))
    		$baseUrl = tx_rnbase_configurations::getExtensionCfgValue('mklib', 'easyKontoURL');
    		    	    		
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_NOBODY, 0);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_USERPWD, $username . ':' . $password);

        $this->base_url = (($use_ssl) ? 'https' : 'http') .'://'.$baseUrl;
    }
    
	/**
	 * Liefert die Instance.
	 *
	 * @param int $uid
	 * @return tx_mkjjk_util_WSClient
	 */
	public static function getInstance($username='', $password='', $use_ssl='', $baseUrl='') {
		if(!is_object(self::$instance)) 
			self::$instance = new tx_mklib_util_EasyKonto($username, $password, $use_ssl, $baseUrl);

		return self::$instance;
	}    

    /**
     * Gibt alle von dieser Klasse geöffneten Resourcen wieder frei
     * @see function close
     */
    protected function __destruct(){
        $this->close();
    }

    /**
     * Gibt alle von dieser Klasse geöffneten Resourcen wieder frei
     */
    protected function close() {
        if (!is_null($this->curl))
            curl_close($this->curl);

        $this->curl = null;
    }

    /**
     * Setzt die Proxy-Konfiguration.
     *
     * Wenn die easyKonto Online-Schnittstelle über einen Proxy aufgerufen
     * werden soll, so muss durch diese Methode der Proxy und ggf. die
     * Authentifizierungs-Daten gesetzt werden.
     *
     * Beispiel:
     * <code>
     * <?php
     *     // ...
     *     $easykonto->set_proxy('http://myproxy.de:3128');
     *     // oder:
     *     $easykonto->set_proxy('http://myproxy.de', 'benutzer', 'passwort');
     *     // ...
     * ?>
     * </code>
     *
     * @param string $url Die Proxy-URL (komplette http-URL ggf. mit Port)
     * @param string $username Der Benutzername für den Proxy
     * @param string $password Das Passwort für den Proxy
     */
    public function setProxy($url, $username = null, $password = null) {
        curl_setopt($this->curl, CURLOPT_PROXY, $url);

        if (!is_null($username) && !is_null($password))
            curl_setopt($this->curl, CURLOPT_PROXYUSERPWD,
                $username . ':' . $password);
    }

    /**
     * Ruft die übergebene URL auf und gibt die Service-Antwort (ggf. als
     * deserialisiertes WDDX-Dokument) zurück.
     *
     * @param string $url Die aufzurufende URL
     * @param bool $deserialize Ob die zurückgegebenen Daten deserialisiert
     *                          werden sollen
     * @return mixed Die Service-Antwort (als Code oder als deserialisiertes
     *               WDDX)
     */
    private function getData($url, $deserialize = true) {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        $r = curl_exec($this->curl);
        if (curl_errno($this->curl))
            return null;

		return ($deserialize) ? wddx_deserialize($r) : $r;
    }

    /**
     * Prüft eine Bankverbindung anhand der BLZ (Bankleitzahl) sowie der
     * Kontonummer
     *
     * @param $blz int Die zu prüfende BLZ
     * @param $kto int Die zu prüfende Kontonummer
     * @param $info bool Ob auch Zusatzinformationen zurückgegeben werden
     *                   sollen
     * @return mixed Returncode oder, sofern $info==true, assoziatives Array
     *               mit zusätzlichen Informationen
     */
    public function checkBICAndAccountNumber($blz, $kto, $info = false) {
    	//wenn Easykonto nicht aktiv, werden alle bankdaten als valide betrachtet
    	if(tx_mklib_util_MiscTools::isEasyKontoActive()){
        	$url = sprintf($this->base_url . 'kontocheck?blz=%s&kto=%s&viewmode=%s',$blz, $kto, ($info) ? 'wddx' : 'code');
        	return $this->getData($url, $info);
    	}else{
    		return true;
    	}
    }

    /**
     * Prüft eine Bankverbindung anhand der IBAN (International Bank Account
     * Number)
     *
     * @param $iban string Die zu prüfende IBAN
     * @param $info bool Ob auch Zusatzinformationen zurückgegeben werden sollen
     * @return mixed Returncode oder, sofern $info==true, assoziatives Array
     *               mit zusätzlichen Informationen
     */
    public function checkIBAN($iban, $info = false){
        $url = sprintf($this->base_url . 'kontocheck?iban=%s&viewmode=%s',$iban, ($info) ? 'wddx' : 'code');
        return $this->getData($url, $info);
    }

    /**
     * Liefert Informationen über die zur angegebenen BLZ (Bankleitzahl)
     * gehörende Bank
     *
     * @param $blz int Die zu beauskunftende BLZ
     * @return array Assoziatives array mit den Informationen zur Bank
     */
    public function getBankInfoByBIC($blz) {
        $url = sprintf($this->base_url . 'bankinfo?viewmode=wddx&blz=%s',$iban);
        return $this->getData($url);
    }

    /**
     * Liefert Informationen über die zur angegebenen IBAN (International Bank
     * Account Number) gehörende Bank
     *
     * @param $iban int Die zu beauskunftende IBAN
     * @return array Assoziatives array mit den Informationen zur Bank
     */
    public function getBankInfoByIBAN($iban) {
        $url = sprintf($this->base_url . 'bankinfo?viewmode=wddx&iban=%s',$iban);
        return $this->getData($url);
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_EasyKonto.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_EasyKonto.php']);
}
