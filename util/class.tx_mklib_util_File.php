<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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

tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_rnbase_util_Files');
tx_rnbase::load('tx_rnbase_util_Strings');
tx_rnbase::load('tx_rnbase_util_Typo3Classes');

/**
 * Util Methoden für Datei handling.
 *
 * @author mwagner
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_File {

	/** @var 	array[\TYPO3\CMS\Core\Utility\File\BasicFileUtility] */
	private static $ftInstances = array();
	/** @var 	string 	cache*/
	private static $siteUrl = false;
	/** @var	string 	cache*/
	private static $documentRoot = false;

	/**
	 * Liefert eine Instanz der basicFileFunctions von t3lib
	 * @return 	\TYPO3\CMS\Core\Utility\File\BasicFileUtility
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
			self::$ftInstances[$key] = tx_rnbase::makeInstance(tx_rnbase_util_Typo3Classes::getBasicFileUtilityClass());
			self::$ftInstances[$key]->init($mounts, $f_ext);
		}
		return self::$ftInstances[$key];
	}


	/**
	 * Löscht alle Dateien im in einem typo3temp Verzeichnis.
	 *
	 * @TODO: fehlerbehandlung integrieren!
	 *
	 * @param 	string 	$sDirectory
	 * @param 	array 	$aOptions
	 * @param 	array 	$aUnlinkedFiles Hier werden die Dateien eingetragen, welche gelöscht wurden.
	 * @return 	int
	 */
	public static function cleanupFiles($sDirectory, array $aOptions, &$aUnlinkedFiles = array()) {

		$directoryCheckDir = isset($aOptions['directorycheckdir']) ? $aOptions['directorycheckdir'] : 'typo3temp';
		if (!is_array($aUnlinkedFiles)) { $aUnlinkedFiles = array(); }

		//nur innerhalb von typo3temp zulassen
		if(!$aOptions['skiptypo3tempcheck'] && strpos($sDirectory, $directoryCheckDir) === false) {
			return 0;
		}

		// optionen sammeln.
		$iLifetime = $aOptions['lifetime'] ? $aOptions['lifetime'] : 0;
		$aFiletypes = $aOptions['filetypes'] ? tx_rnbase_util_Strings::trimExplode(',', strtolower($aOptions['filetypes'])) : array();
		$bRecursive = $aOptions['recursive'] ? $aOptions['recursive'] : false;

		$iCount = 0;

		if (@is_dir($sDirectory)) {
			$iHandle = opendir($sDirectory);
			while (($sFile = readdir($iHandle)) !== FALSE) {
				if ($sFile === '.' || $sFile === '..') {
					continue;
				}

				// Dateiendung auslesen
				$sExt = strtolower(substr($sFile, strrpos($sFile, '.') + 1));
				// serverpfad zur datei
				$sFilePath = $sDirectory.$sFile;

				// Es handelt sich um eine Datei.
				if (@is_file($sFilePath)) {
					if (
						// Stimmt der Dateityp?
						(empty($aFiletypes) || in_array($sExt, $aFiletypes))
						// Ist die Datei alt genug, um sie zu löschen?
						&& (@filemtime($sFilePath) < ($GLOBALS['EXEC_TIME'] - $iLifetime))
					) {
						$aUnlinkedFiles[] = $sFilePath;
						// löschen!
						@unlink($sFilePath);
						// count erhöhen
						$iCount++;
					}
				}
				// Es handelt sich um ein Verzeichniss.
				elseif ($bRecursive && @is_dir($sFilePath)){
					//@TODO: $bRecursive!
					$iCount += self::cleanupFiles($sFilePath.'/', $aOptions, $aUnlinkedFiles);
				}
			}
			closedir($iHandle);
		}
		return $iCount;
	}

	/**
	 * Liefert die URL zur Typo3 Seite
	 * http://www.typo3.de
	 * @return 	string
	 */
	public static function getSiteUrl(){
		if(self::$siteUrl===false){
			self::$siteUrl = tx_rnbase_util_Misc::getIndpEnv('TYPO3_SITE_URL');
		}
		return self::$siteUrl;
	}
	/**
	 * Liefert den Absoluten Server Pfad zum Root.
	 * @return 	string
	 */
	public static function getDocumentRoot($slashPath=true){
		if (self::$documentRoot === false){
			if (!self::$documentRoot = tx_rnbase_util_Misc::getIndpEnv('TYPO3_DOCUMENT_ROOT')) {
				// happens for example on the CLI
				self::$documentRoot = PATH_site;
			}
		}

		if ($slashPath) {
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
// 		return preg_replace('|/$|','', $sPath); @TODO: was ist besser!?
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
	 * @param 	string 	$path
	 * @param	boolean $removeStartingSlash
	 *
	 * @return 	string
	 */
	public static function getRelPath($path='/', $removeStartingSlash = FALSE) {
		if (!strcmp($path,'/')){
			return $path;
		}

		$path = self::fixPath($path);

		// Web-Pfad abschneiden
		if(self::isAbsWebPath($path) && strpos($path, self::getSiteUrl()) !== false) {
			$path = str_replace(self::getSiteUrl(), '', $path);
		}

		// wir brauchen den server pfad, um verschiedene prüfungen zu machen
		$path = self::getServerPath($path);
		$path = str_replace(self::removeStartingSlash(self::getDocumentRoot()), '', $path);

		// gegebenenfals ein slash anfügen wenn dieser nicht entfernt werden soll
		if ($removeStartingSlash && $path{0} == '/') {
			$path = self::removeStartingSlash($path);
		} elseif ($path{0} != '/') {
			$path = '/' . $path;
		}
		return $path;
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

		// Nur in einen Absoluten Pfad umwandeln, wenn es noch keiner ist.
		if(!self::isAbsServerPath($sPath)) {
			// Absoluten Pfad generieren
			$sPath = tx_rnbase_util_Files::getFileAbsFileName($sPath);
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
	public static function writeDownloadHeaders($sFilename, $sContentType = 'application/download', $sDisposition = 'attachment') {
		header("Content-type: ".$sContentType);
		header("Content-disposition: ".$sDisposition."; filename=".$sFilename);
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
		header("Content-length: ".strlen($sOutput));
		//jetzt die Datei zum Download anbieten
		print $sOutput;
		//und TYPO3 hindern noch irgend etwas auszugeben
		exit;
	}

	/**
	 * Erzeugt eine URL anhand von den uri parts
	 *
	 * http://www.php.net/manual/en/function.parse-url.php
	 *
	 * @param array $parts
	 * @return string
	 */
	public static function parseUrlFromParts(array $parts) {
		$parts = array_merge(array(
			'scheme' => '', 'host' => '', 'port' => '',
			'user' => '', 'pass' => '',
			'path' => '', 'query' => '', 'fragment' => '',
		), $parts);

		$password = strlen($parts['pass']) > 0 ? ':'.$parts['pass'] : '';
		$auth = strlen($parts['user']) > 0 ? $parts['user'] . $password . '@' : '';
		$port = strlen($parts['port']) > 0 ? ':'.$parts['port'] : '';
		// check excisting ? ???
		//$query = strlen($parts['query']) > 0 ? ($parts['query'][0] == '?' ? $parts['query'] : '?'.$parts['query']) : '';
		$query = strlen($parts['query']) > 0 ? '?'.$parts['query'] : '';
		$fragment = strlen($parts['fragment']) > 0 ? '#'.$parts['fragment'] : '';

		return $parts['scheme'] . '://' . $auth
			. $parts['host'] . $port
			. $parts['path'] . $query . $fragment;
	}

	/**
	 * Legt eine .htaccess an, um ein Verzeichnis vor Zugriffen zu schützen.
	 *
	 * @param string $path
	 * @param string $content
	 * @return boolean
	 */
	public static function createDenyHtaccess($path, $content=null) {
		$theFile = self::getServerPath($path).'.htaccess';
		if (@is_file($theFile)) {
			return false;
		}
		$content = $content ? $content
			: 	'order deny,allow'.PHP_EOL.
				'deny from all'.PHP_EOL.
				'allow from 127.0.0.1'.PHP_EOL
				// Das funktioniert bei den meisten Clustern nicht,
				// da der LoadBallancer die Anfragen intern weiterleitet!
// 				'allow from 192.168'.PHP_EOL
			;
		tx_rnbase_util_Files::writeFile($theFile, $content);
		return @is_file($theFile);
	}

	/**
	 * exisitiert die Datei und ist auch kein Ordner?
	 *
	 * @param string $filepath
	 *
	 * @return boolean
	 */
	public static function isValidFile($filepath) {
		return file_exists($filepath) && is_file($filepath);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_File.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_File.php']);
}
