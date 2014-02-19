<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *
 *  Copyright notice
 *
 *  (c) 2010 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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

/**
 * Debug Util.
 * Sammelt Debug-Meldungen mit Zeit und Speicher verbrauch.
 * Entweder werden die Daten direkt in eine Datei geschrieben (openFile)
 * oder am ende gesammelt ausgegeben (t3Debug).
 *
 *	// schließst die datei beim beenden von php,
 *	// wird auch aufgerufen, wenn die ausgabe
 *	// durch ein fatal error abgebrochen wird.
 *	tx_mklib_util_Debug::registerShutdownFunction();
 *	// legt eine datei für die debugs an,
 *	// damit diese nicht im speicher liegen und
 *	// oder nicht im fe ausgegeben werden
 *	tx_mklib_util_Debug::openFile('c:/xampp/htdocs/t3/buhl/typo3conf/ext/mkbuhl/xmlfeed/', $file);
 *	// initialisiert die startwerte
 *	tx_mklib_util_Debug::init();
 *	// erzeugt einen debug
 *	tx_mklib_util_Debug::addDebug('start testing.');
 *	... do some things here ...
 *	tx_mklib_util_Debug::addDebug('after some things.');
 *	// fertig, datei schließen
 *	tx_mklib_util_Debug::closeFile();
 *
 *	//alternativ kann ohne aufruf von openFile
 *	//der gesammelte bug im fe ausgegeben werden
 *	tx_mklib_util_Debug::t3Debug();
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mklib_util_Debug {

	/**
	 * enthält die debugmeldungen
	 * @var 	array
	 */
	private static $aDebug = array();
	/**
	 * memory beim initialisieren
	 * @var 	int
	 */
	private static $iMemory = 0;
	/**
	 * zeitstempel beim initialisieren
	 * @var 	int
	 */
	private static $iMicroTime = 0;
	/**
	 * memory beim letzten addDebug aufruf
	 * @var 	int
	 */
	private static $iLastMemory = 0;
	/**
	 * zeitstempel beim letzten addDebug aufruf
	 * @var 	int
	 */
	private static $iLastMicroTime = 0;
	/**
	 * Pointer auf die debug datei
	 * @var  int
	 */
	private static $file = 0;
	/**
	 * setzt initiale variablen
	 */
	public static function init() {
		self::$iMicroTime = self::$iLastMicroTime = microtime(true);
		self::$iMemory = self::$iLastMemory = memory_get_usage();
	}
	/**
	 * Öffnet eine Datei für den debug.
	 *
	 * @param 	string 	$path
	 * @param 	string 	$file
	 * @return 	int
	 */
	public static function openFile($path='', $file='') {
		$file = $file ? $file : 'mklib_debug_'.date('Y-m-d_H-i-s', time()).'.txt';
		if (!$path) {
			$path = 'typo3temp/mklib/';
			if (!is_writable(PATH_site.$path)) {
				t3lib_div::mkdir_deep(PATH_site, $path);
			}
			$path = PATH_site.$path;
		}

		self::$file = fopen($path.$file, "a");
		unset($path); unset($file);
		return self::$file;
	}
	/**
	 * Schreibt in die Debugdatei
	 * @param 	string 	$content
	 * @return 	int
	 */
	public static function writeFile($content){
		if (self::$file)
			return fwrite(self::$file, $content/*.LF*/);
		else
			return 0;
		unset($content);
	}
	/**
	 * Schließt die debug datei
	 * @return 	bool
	 */
	public static function closeFile(){
		if (self::$file)
			return fclose(self::$file);
		else
			return true;
	}

	/**
	 * Fügt eine Debug ausgabe hinzu.
	 *
	 * @param unknown_type $msg
	 * @param array $data
	 */
	public static function addDebug($msg, array $data = array()) {
		$iMicroTime = microtime(true);
		$iMemory = memory_get_usage();

		$data['msg'] = $msg;
		$data['time'] = ($iMicroTime - self::$iMicroTime);
		$data['timeLast'] = ($iMicroTime - self::$iLastMicroTime);
		$data['timeCurrent'] = ($iMicroTime);
		$data['memmory'] = ($iMemory - self::$iMemory);
		$data['memmoryMB'] = $data['memmory'] / 1024 / 1024;
		$data['memmoryLast'] = (($iMemory - self::$iLastMemory));
		$data['memmoryLastMB'] = $data['memmoryLast'] / 1024 / 1024;
		$data['memmoryCurrent'] = ($iMemory);
		$data['memmoryCurrentMB'] = $data['memmoryCurrent'] / 1024 / 1024;
		self::$iLastMicroTime = $iMicroTime;
		self::$iLastMemory = $iMemory;
		// debug in eine datei schreiben
		if(self::$file) {
			self::writeFile(print_r($data, true));
		}
		// debug merken
		// ACHTUNG, benötigt zusätzlichen speicher
		else {
			self::$aDebug[] = $data;
		}
		unset($data);
		unset($iMicroTime);
		unset($iMemory);
	}
	/**
	 * Gibt die gesammelten Debug meldungen aus
	 * Funktioiniert nur, wenn die debugs nicht in eine datie geschrieben wird.
	 */
	public static function t3Debug(){
		tx_rnbase_util_Debug::debug(self::$aDebug, 'tx_mklib_util_Debug', 'mklib Debug'); // @TODO: remove me
	}
	/**
	 * Gibt die gesammelten Debug meldungen aus
	 * Funktioiniert nur, wenn die debugs nicht in eine datie geschrieben wird.
	 */
	public static function getDebugArray(){
		return self::$aDebug;
	}
	public static function registerShutdownFunction(){
		register_shutdown_function(array(get_class(),'shutDown'));
	}
	public static function shutDown() {
//		die ausgabe funktioniert bei einigen fehlermeldungen nicht.
//		beispiel: Allowed memory size.
// 		if ($error = error_get_last()){
// 			switch($error['type']){
// 				case E_ERROR:
// 				case E_CORE_ERROR:
// 				case E_COMPILE_ERROR:
// 				case E_USER_ERROR:
// 					break;
// 				default:
// 					$error = 0;
// 			}
// 		} else {
// 			$error = 0;
// 		}
		self::writeFile('tx_mklib_util_Debug::shutDown called.');
		if(self::$file) self::closeFile();
	}
	public static function registerErrorHandler(){
		set_error_handler(array(get_class(),'errorHandler'));
	}
	public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext){
		// throw new ErrorException($string, null, $code, $file, $line);
		if($errno != E_ERROR && strpos($errstr, 'Allowed memory') === FALSE) return true;
		self::addDebug($errstr, array('error' => array($errno, $errstr, $errfile, $errline)));
		self::debug();
		return false;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Debug.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Debug.php']);
}
