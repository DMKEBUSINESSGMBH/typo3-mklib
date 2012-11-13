<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests
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
tx_rnbase::load('tx_rnbase_cache_Manager');
tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_util_Spyc');

/**
 * Statische Hilfsmethoden für Tests
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests
 */
class tx_mklib_tests_Util {

	private static $aExtConf = array();
	private static $sCacheFile;

	/**
	 * Sichert eine Extension Konfiguration.
	 * Wurde bereits eine Extension Konfiguration gesichert,
	 * wird diese nur überschrieben wenn bOverwrite wahr ist!
	 *
	 * @param string 	$sExtKey
	 * @param boolean 	$bOverwrite
	 */
	public static function storeExtConf($sExtKey='mklib', $bOverwrite = false){
		if(!isset(self::$aExtConf[$sExtKey]) || $bOverwrite){
			self::$aExtConf[$sExtKey] = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey];
		}
	}
	/**
	 * Setzt eine gesicherte Extension Konfiguration zurück.
	 *
	 * @param string $sExtKey
	 * @return boolean 		wurde die Konfiguration zurückgesetzt?
	 */
	public static function restoreExtConf($sExtKey='mklib'){
		if(isset(self::$aExtConf[$sExtKey])) {
			$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey] = self::$aExtConf[$sExtKey];
			return true;
		} return false;
	}

	/**
	 * Setzt eine Vaiable in die Extension Konfiguration.
	 * Achtung im setUp sollte storeExtConf und im tearDown restoreExtConf aufgerufen werden.
	 * @param string 	$sCfgKey
	 * @param string 	$sCfgValue
	 * @param string 	$sExtKey
	 */
	public static function setExtConfVar($sCfgKey, $sCfgValue, $sExtKey='mklib'){
		// aktuelle Konfiguration auslesen
		$extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey]);
		// wenn keine Konfiguration existiert, legen wir eine an.
		if(!is_array($extConfig)) {
			$extConfig = array();
		}
		// neuen Wert setzen
		$extConfig[$sCfgKey] = $sCfgValue;
		// neue Konfiguration zurückschreiben
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey] = serialize($extConfig);
	}

	/**
	 * Liefert eine DateiNamen
	 * @param $filename
	 * @param $dir
	 * @param $extKey
	 * @return string
	 */
	public static function getFixturePath($filename, $dir = 'tests/fixtures/', $extKey = 'mklib') {
		return t3lib_extMgm::extPath($extKey).$dir.$filename;
	}

	/**
	 * Disabled das Logging über die Devlog Extension für die
	 * gegebene Extension
	 *
	 * @param 	string 	$extKey
	 * @param 	boolean 	$bDisable
	 */
	public static function disableDevlog($extKey = 'devlog', $bDisable = true) {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['nolog'] = $bDisable;
	}

	/**
	 * Führt eine beliebige DB-Query aus
	 * @param string $sqlFile
	 */
	public static function queryDB($sqlFile, $statementType = false, $bIgnoreStatementType = false) {
//		$sql = file_get_contents($sqlFile);
		$sql = t3lib_div::getUrl($sqlFile);
		if(empty($sql))
			throw new Exception('SQL-Datei nicht gefunden');
		if($statementType || $bIgnoreStatementType) {
			$statements = self::getSqlStatementArrayDependendOnTypo3Version($sql);
			foreach($statements as $statement){
				if(!$bIgnoreStatementType && t3lib_div::isFirstPartOfStr($statement, $statementType)) {
					$GLOBALS['TYPO3_DB']->admin_query($statement);
				}elseif($bIgnoreStatementType){//alle gefundenen statements ausführen
					$GLOBALS['TYPO3_DB']->admin_query($statement);
				}
			}
		} else {
			$GLOBALS['TYPO3_DB']->admin_query($sql);
		}
	}
	
	/**
	 * @param string $sql
	 * 
	 * @return array
	 */
	private static function getSqlStatementArrayDependendOnTypo3Version($sql) {
		tx_rnbase::load('tx_rnbase_util_TYPO3');
		if(tx_rnbase_util_TYPO3::isTYPO46OrHigher()){
			$dbHandler = tx_rnbase::makeInstance('t3lib_install_Sql');
		} else {
			$dbHandler = tx_rnbase::makeInstance('t3lib_install');
		}
		
		return $dbHandler->getStatementArray($sql, 1);
	}

	/**
	 * Simuliert ein einfaches FE zur testausführung
	 * Wurde tx_phpunit_module1 entnommen da die Methode protected ist
	 * und nicht bei der Ausführung auf dem CLI aufgerufen wird. Das
	 * kann in manchen Fällen aber notwendig sein
	 *
	 * ACHTUNG bei Datenbank Testfällen!!!
	 * Dann muss diese Funktion immer vor dem erstellen der Datenbank etc. ausgeführt
	 * werden da sonst extra eine Seite in "pages" eingefügt werden muss.
	 * In einem normalen TYPO3 gibt es bereits Seiten womit das vor dem
	 * Aufsetzen der Testdatenbank ausgenutzt werden kann!!!
	 *
	 * @see tx_phpunit_module1::simulateFrontendEnviroment
	 * @todo in eigene Klasse auslagern, die von tx_phpunit_module1 erbt und simulateFrontendEnviroment public macht
	 */
	public static function simulateFrontendEnviroment($extKey = 'mklib') {
		//wenn phpunit mindestens in version 3.5.14 installiert ist, nutzen
		//wir deren create frontend methode
		if(t3lib_div::int_from_ver(t3lib_extMgm::getExtensionVersion('phpunit')) >= 3005014){
			$oTestFramework = tx_rnbase::makeInstance('Tx_Phpunit_Framework',$extKey);
			return $oTestFramework->createFakeFrontEnd();
		}


		if (isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])) {
			// avoids some memory leaks
			unset(
				$GLOBALS['TSFE']->tmpl, $GLOBALS['TSFE']->sys_page, $GLOBALS['TSFE']->fe_user,
				$GLOBALS['TSFE']->TYPO3_CONF_VARS, $GLOBALS['TSFE']->config, $GLOBALS['TSFE']->TCAcachedExtras,
				$GLOBALS['TSFE']->imagesOnPage, $GLOBALS['TSFE']->cObj, $GLOBALS['TSFE']->csConvObj,
				$GLOBALS['TSFE']->pagesection_lockObj, $GLOBALS['TSFE']->pages_lockObj
			);
			$GLOBALS['TSFE'] = NULL;
			$GLOBALS['TT'] = NULL;
		}

		$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_TimeTrackNull');
		$frontEnd = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);

		// simulates a normal FE without any logged-in FE or BE user
		$frontEnd->beUserLogin = FALSE;
		$frontEnd->workspacePreview = '';
		$frontEnd->gr_list = '0,-1';

		$frontEnd->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$frontEnd->sys_page->init(TRUE);
		$frontEnd->initTemplate();

		// $frontEnd->getConfigArray() doesn't work here because the dummy FE
		// is not required to have a template.
		$frontEnd->config = array();

		$GLOBALS['TSFE'] = $frontEnd;
	}

 	/**
   	 * Lädt den Inhalt einer Datei
   	 * @param string $filename
   	 * @param array $options
     */
  	public function loadTemplate($filename,$configurations,$extKey = 'mklib',$subpart=null, $dir = 'tests/fixtures/'){
    	$path = self::getFixturePath($filename,$dir,$extKey);

	    $cObj =& $configurations->getCObj();
	    $templateCode = file_get_contents($path);
		if($subpart)
			$templateCode = $cObj->getSubpart($templateCode,$subpart);

		return $templateCode;
	}

	/**
	 * Setzt das fe_user objekt, falls es noch nicht gesetzt wurde
	 *
	 * @param 	tslib_feuserauth 	$oFeUser 	Erzeugt das tslib_feuserauth Objekt wenn nix übergeben wurde
	 * @param 	boolean 			$bForce		Setzt das fe_user Objekt auch, wenn es bereits gesetzt ist.
	 * @return 	void
	 */
	public static function setFeUserObject($oFeUser=null, $bForce=false) {
		if(!($GLOBALS['TSFE']->fe_user instanceof tslib_feuserauth) || $bForce) {
			$GLOBALS['TSFE']->fe_user = is_object($oFeUser) ?
						$oFeUser : tx_rnbase::makeInstance('tslib_feuserauth');
		}
	}
	/**
	 * Setzt Sprach-Labels
	 *
	 * @param 	array 	$labels
	 * @param 	string	$lang
	 * @return 	void
	 */
	public static function setLocallangLabels($labels = array(), $lang = 'default') {
		global $LOCAL_LANG;
		$GLOBALS['LANG']->lang = $lang;
		//ab typo 4.6 ist das mit den lang labels anders
		foreach ($labels as $key => $label) {
			if(tx_rnbase_util_TYPO3::isTYPO46OrHigher()) {
				$LOCAL_LANG[$lang][$key][0]['target'] = $label;
			}
			else{
				$LOCAL_LANG[$lang][$key] = $label;
			}
		}
	}

	
	/**
	 * Speichert den Cache
	 */
	public static function storeCacheFile() {
		//aktuelle Konfiguration sichern
		self::$sCacheFile = $GLOBALS['TYPO3_LOADED_EXT']['_CACHEFILE'];
	}

	/**
	 * Reaktiviert den Cache
	 */
	public static function restoreCacheFile() {
		//aktuelle Konfiguration sichern
		$GLOBALS['TYPO3_LOADED_EXT']['_CACHEFILE'] = self::$sCacheFile;
	}

	/**
	 * Deaktiviert den Cache
	 * damit nicht 'A cache with identifier "tx_extbase_cache_reflection" has already been registered.' kommt.
	 * wenn der text mit einem mkforms formular ist, dann muss auch der testmode gesetzt sein.
	 * nur in TYPO3 4.5.x und damit wegen extbase Version 1.3.2
	 */
	public static function deactivateCacheFile() {
		//aktuelle Konfiguration sichern
		$GLOBALS['TYPO3_LOADED_EXT']['_CACHEFILE'] = null;
	}

	/**
	 * Liefert eine rn_base basierte Action auf Grund der gelieferten
	 * TS Konfig zurück.
	 * Dabei wird automatisch handleRequest aufgerufen.
	 * Parameter können frei gesetzt werden.
	 *
	 * @param 	string			$sActionName
	 * @param	array			$aConfig
	 * @param	string			$sExtKey
	 * @param	array			$aParams
	 * @param 	boolean 		$execute
	 * @return tx_rnbase_action_BaseIOC
	 */
	public static function &getAction($sActionName, $aConfig, $sExtKey, $aParams = array(), $execute = true) {
		$action = tx_rnbase::makeInstance($sActionName);

		$configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');

		//@TODO: warum wird die klasse tslib_cObj nicht gefunden!? (mw: eternit local)
		require_once(t3lib_extMgm::extPath('cms', 'tslib/class.tslib_content.php'));
		$configurations->init(
				$aConfig,
				$configurations->getCObj(1),
				$sExtKey,$sExtKey
			);

		//noch extra params?
		if(!empty($aParams))
			foreach ($aParams as $sName => $mValue)
				$parameters->offsetSet($sName,$mValue);

		$configurations->setParameters($parameters);
		$action->setConfigurations($configurations);
		if($execute) {
			// logoff für phpmyadmin deaktivieren. ist nicht immer notwendig
			// aber sollte auch nicht stören!
			/*
			 * Error in test case test_handleRequest aus mkforms
			 * in file C:\xampp\htdocs\typo3\typo3conf\ext\phpmyadmin\res\class.tx_phpmyadmin_utilities.php
			 * on line 66:
			 * Message:
			 * Cannot modify header information - headers already sent by (output started at C:\xampp\htdocs\typo3\typo3conf\ext\phpunit\mod1\class.tx_phpunit_module1.php:112)
			 *
			 * Diese Fehler passiert, wenn die usersession ausgelesen wird. der feuser hat natürlich keine.
			 * Das Ganze passiert in der t3lib_userauth->fetchUserSession.
			 * Dort wird t3lib_userauth->logoff aufgerufen, da keine session vorhanden ist.
			 * phpmyadmin klingt sich da ein und schreibt daten in die session.
			 */
			if(is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'])){
				foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'] as $k=>$v){
					if($v = 'tx_phpmyadmin_utilities->pmaLogOff'){
						unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'][$k]);
					}
				}
			}

			$out = $action->handleRequest($parameters, $configurations, $configurations->getViewData());

		}
		return $action;
	}

	/**
	 * 
	 * @param array $options
	 * 			initFEuser: verhindert das Schreiben von Headerdaten
	 */
	public static function prepareTSFE(array $options = array()) {
		static $loaded = false;
		if ($loaded && !isset($options['force'])) return;

		if (isset($options['initFEuser'])) {
			self::disablePhpMyAdminLogging();
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieSecure'] = 1;
			$GLOBALS['TYPO3_CONF_VARS']['FE']['dontSetCookie'] = 1;
		}
		
		tx_rnbase::load('tx_rnbase_util_Misc');
		tx_rnbase_util_Misc::prepareTSFE(array('force'=>true));
		$loaded = true;
		
		if (isset($options['initFEuser'])) {
			$GLOBALS['TSFE']->initFEuser();
		}
	}

	/**
	 * deaktiviert extbase. kann notwendig sein damit die meldung
	 * 'A cache with identifier "tx_extbase_cache_reflection" has already been registered.'
	 * nicht erscheint.
	 * nur in TYPO3 4.5.x und damit wegen extbase Version 1.3.2
	 *
	 * @return void
	 */
	public static function deactivateExtbase() {
		global $TYPO3_LOADED_EXT;
		unset($TYPO3_LOADED_EXT['extbase']);

		//und noch die caching konfig löschen
		self::deactivateCacheFile();
	}

	/**
	 * Liefert einen eindeutigen klassenname für einen Mock.
	 * Dies ist sinnvoll, wenn ein Mock mehrfach generiert wird aber nicht gecached werden soll.
	 *
	 * @param string $originalClassName
	 * @param string $mockClassName
	 * @return object
	 */
	public static function generateUniqueMockClassName($originalClassName, $mockClassName='') {
		if ($mockClassName == '') {
			do {
				$mockClassName = 'Mock_' . $originalClassName . '_' .
				substr(md5(microtime()), 0, 8);
			}
			while (class_exists($mockClassName, FALSE));
		}
		return $mockClassName;
	}

	/**
	 * damit nicht
	 * PHP Fatal error:  Call to a member function getHash() on a non-object in
	 * typo3/sysext/cms/tslib/class.tslib_content.php on line 1814
	 * auftritt. passiert zb bei link generierung
	 *
	 * @return void
	 */
	public static function setSysPageToTsfe() {
		tx_rnbase::load('tx_rnbase_util_TYPO3');
		$GLOBALS['TSFE']->sys_page = tx_rnbase_util_TYPO3::getSysPage();
	}
	
	/**
	 * Error in test case test_handleRequest
	 * in file C:\xampp\htdocs\typo3\typo3conf\ext\phpmyadmin\res\class.tx_phpmyadmin_utilities.php
	 * on line 66:
	 * Message:
	 * Cannot modify header information - headers already sent by (output started at C:\xampp\htdocs\typo3\typo3conf\ext\phpunit\mod1\class.tx_phpunit_module1.php:112)
	 *
	 * Diese Fehler passiert, wenn die usersession ausgelesen wird. der feuser hat natürlich keine.
	 * Das Ganze passiert in der t3lib_userauth->fetchUserSession.
	 * Dort wird t3lib_userauth->logoff aufgerufen, da keine session vorhanden ist.
	 * phpmyadmin klingt sich da ein und schreibt daten in die session.
	 */
	public static function disablePhpMyAdminLogging() {
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing']))
			foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'] as $k=>$v){
				if($v = 'tx_phpmyadmin_utilities->pmaLogOff'){
					unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'][$k]);
				}
			}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_Util.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_Util.php']);
}
