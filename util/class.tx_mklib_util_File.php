<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@das-medienkombinat.de>
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

/**
 * Util Methoden für Datei handling.
 *
 * @author mwagner
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_File {

	/** @var 	array[t3lib_basicFileFunctions] */
	private static $ftInstances = array();
	/** @var 	string 	cache*/
	private static $siteUrl = false;
	/** @var	string 	cache*/
	private static $documentRoot = false;
	
	/**
	 * Liefert eine Instanz der basicFileFunctions von t3lib
	 * @return 	t3lib_basicFileFunctions
	 */
	public static function getFileTool($mounts=false, $f_ext=false){
		$key = (!$mounts && !$f_ext) ? true : false;
		if(!is_array($mounts)) {
			$mounts = $GLOBALS['FILEMOUNTS'];
		}
		if(!is_array($f_ext)) {
			$f_ext = $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions'];
		}
		$key = $key ? 'base' : md5(serialize($mounts).serialize($f_ext));
		if(!self::$ftInstances[$key]) {
			self::$ftInstances[$key] = tx_rnbase::makeInstance('t3lib_basicFileFunctions');
			self::$ftInstances[$key]->init($mounts, $f_ext);
		}
		return self::$ftInstances[$key];
	}

	/**
	 * Liefert die URL zur Typo3 Seite
	 * http://www.typo3.de
	 * @return 	string
	 */
	public static function getSiteUrl(){
		if(self::$siteUrl===false){
			self::$siteUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
		}
		return self::$siteUrl;
	}
	/**
	 * Liefert den Absoluten Server Pfad zum Root.
	 * @return 	string
	 */
	public static function getDocumentRoot($slashPath=true){
//		return PATH_site;
		if(self::$documentRoot===false){
			self::$documentRoot = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT');
		}
		if($slashPath) {
			return self::slashPath(self::$documentRoot, false);
		}
		return self::$documentRoot;
	}
	
	/**
	 * Entfernt doppelte slashes. Das Schema wird hierbei berücksichtigt
	 * @param 	string 	$sPath
	 * @return 	string
	 */
	function removeDoubleSlash($sPath)	{
		if(!self::isAbsWebPath($sPath)) {
			return str_replace('//','/',$sPath);
		}
		// es ist ein webpfad, aufpassen das das scheme:// nicht weggeschnitten wird.
		$uI = parse_url($sPath);
		return $uI['scheme'].'://'.self::removeDoubleSlash(substr($sPath, strlen($uI['scheme'])+3));
	}
	/**
	 * Entfernt ein führenden Splash (/)
	 * @param 	string 	$sPath
	 * @return 	string
	 */
	public static function removeStartingSlash($sPath) {
		return ($sPath{0} === '/') ? substr($sPath, 1) : $sPath;
	}
	/**
	 * Entfernt ein Splash (/) am ende des Strings
	 * @param 	string 	$sPath
	 * @return 	string
	 */
	public static function removeEndingSlash($sPath) {
		return (substr($sPath, -1) === '/') ? substr($sPath, 0, -1) : $sPath;
	}
	/**
	 * Entfernt ein führenden Splash (/) und einen am Ende des Strings
	 * @param 	string 	$sPath
	 * @return 	string
	 */
	public static function trimSlashes($sPath) {
		return self::removeStartingSlash(self::removeEndingSlash($sPath));
	}

	/**
	 * Behebt eventuelle Fehler im Pfad.
	 * @param 	string 	$sPath
	 * @param 	boolean 	$slashPath
	 * @return 	string
	 */
	public static function fixPath($sPath, $slashPath=true){
		
		// stellt sicher, das keine backslashes vorhanden sind.
//		$sPath = t3lib_div::fixWindowsFilePath($sPath);
		$sPath = str_replace('\\','/', $sPath);
		
		// entfernt überflüssige slashes
		$sPath = self::trimSlashes($sPath);
		
		// Konvertiert alle double slashes (//) zu einem single slash (/)
		$sPath = self::removeDoubleSlash($sPath);
		
		return $slashPath ? self::slashPath($sPath) : $sPath;
	}
	/**
	 * // Bei Verzeichnissen sicherstellen, das ein slash (/) am ende steht
	 *
	 * @param	string 	$sPath
	 * @param	boolean	$directoryCheck prüfen, ob das Verzeichnis existiert.
	 * @return	string
	 */
	public static function slashPath($sPath, $directoryCheck = true)	{
		$oFileTool = self::getFileTool();
		// entfernt überflüssige slashes
		$sPath = self::trimSlashes($sPath);
		// beginnenden slash hinzufügen
		if(!self::isAbsWebPath($sPath) && ((TYPO3_OS == 'WIN' && substr($sPath,1,2) != ':/') || TYPO3_OS != 'WIN')) {
			$sPath = ($sPath{0} != '/' ? '/' : '').$sPath;
		}
		if(!$directoryCheck || $oFileTool->is_directory($sPath)) {
			$sPath = $oFileTool->slashPath($sPath);
		}
		return $sPath;
	}
	
	/**
	 * Prüft ob es sich um einen absoluten Server-Pfad handelt.
	 * @param 	$sPath
	 * @return 	boolean
	 */
	public static function isAbsServerPath($sPath) {
		$sServerRoot = self::removeStartingSlash(self::getDocumentRoot());
		return (substr(self::removeStartingSlash($sPath), 0, strlen($sServerRoot)) === $sServerRoot);
	}
	/**
	 * Prüft ob es sich um einen absoluten Web-Pfad handelt.
	 * @param 	$sPath
	 * @return 	boolean
	 */
	public static function isAbsWebPath($sPath) {
		$uI = parse_url($sPath);
		if($uI['scheme'] && $uI['host']) {
			return true;
		}
		return false;
	}
	
	/**
	 * Gibt einen relativen Pfad zurück.
	 *
	 * @param 	string 	$sPath
	 * @return 	string
	 */
	public static function getRelPath($sPath='/') {
		if (!strcmp($sPath,'/')){
			return $sPath;
		}
		
		$sPath = self::fixPath($sPath);
	
		// Web-Pfad abschneiden
		if(self::isAbsWebPath($sPath) && strpos($sPath, self::getSiteUrl()) !== false) {
			$sPath = str_replace(self::getSiteUrl(), '', $sPath);
		}
		
		// wir brauchen den server pfad, um verschiedene prüfungen zu machen
		$sPath = self::getServerPath($sPath);
		$sPath = str_replace(self::removeStartingSlash(self::getDocumentRoot()), '', $sPath);
		
		// gegebenenfals ein slash anfügen
		return ($sPath{0} != '/' ? '/' : '') . $sPath;
	}
	
	/**
	 * Gibt einen absoluten Server Pfad zurück.
	 *
	 * @param 	string 	$sPath
	 * @return 	string
	 */
	public static function getServerPath($sPath='/'){
		if (!strcmp($sPath,'')) {
			return '';
		}
		if (!strcmp($sPath,'/')){
			return self::slashPath( self::getDocumentRoot() );
		}
		if(self::isAbsWebPath($sPath)) {
			$sPath = self::getRelPath($sPath);
		}
		
		$sPath = self::fixPath($sPath, false);
		
		$oFileTool = self::getFileTool();
		
		// Nur in einen Absoluten Pfad umwandeln, wenn es noch keiner ist.
		if(!self::isAbsServerPath($sPath)) {
			// Absoluten Pfad generieren
			$sPath = t3lib_div::getFileAbsFileName($sPath);
		}
	
		return self::slashPath($sPath);
	}
	
	/**
	 * Gibt einen absoluten Web Pfad zurück.
	 *
	 * @param 	string 	$sPath
	 * @return 	string
	 */
	public static function getWebPath($sPath='/') {
		if (!strcmp($sPath,'/')){
			return self::getSiteUrl();
		}
		$sPath = self::removeEndingSlash(
						self::getSiteUrl()
					). '/' . self::removeStartingSlash(
						self::getRelPath($sPath)
					);
		//@TODO: das funktioniert beim webpfad nicht!
		return $sPath;
	}
	
	/**
	 * Schreibt HTTP-Header um eine Datei zum Download anzubieten
	 * @todo Output Tests schreiben
	 *
	 * @param string $sFilename
	 * @param string $sContentType
	 *
	 * @return void
	 */
	public static function writeDownloadHeaders($sFilename, $sContentType = 'application/download') {
		header("Content-type: ".$sContentType);
		header("Content-disposition: filename=".$sFilename);
		// set special header for ssl requests (ie Problem)
		header("Pragma: private");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Expires: 0");
	}
	/**
	 * Mit diesem Header wird der übergebene Ausgabestring
	 * direkt als Datei zum Download angeboten
	 * @todo Output Tests schreiben
	 *
	 * @param string $sOutput
	 * @param string $sFilename
	 * @param string $sContentType
	 *
	 * @return void
	 */
	public static function offerFileForDownload($sOutput, $sFilename, $sContentType = 'application/download') {
		self::writeDownloadHeaders($sFilename, $sContentType);
		//jetzt die Datei zum Download anbieten
		print $sOutput;
		//und TYPO3 hindern noch irgend etwas auszugeben
		exit;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_File.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_File.php']);
}
