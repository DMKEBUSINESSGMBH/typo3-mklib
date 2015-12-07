<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 ***************************************************************/
require_once(tx_rnbase_util_Extensions::extPath('rn_base') . 'class.tx_rnbase.php');

/**
 * Util für session handling.
 *
 * This methods are taken from the great t3users extension.
 * Using this only if t3users not aviable.
 * @see tx_t3users_services_feuser
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_Session {

	/**
	 * Liefert die aktuelle Session id des Nutzers.
	 *
	 * Wenn für den aktuellen Nutzer noch keine Session vorhanden ist,
	 * variert die ID für jeden Seitenaufruf.
	 * Wenn die ID bei jedem Seitenaufruf gleich bleiben soll,
	 * dann ist es notwendig, daten in die Session zu schreiben.
	 * Nur das bewegt Typo3 dazu, sich die Session zu merken!
	 *
	 * @param boolean $keepId
	 * @return string
	 */
	public static function getSessionId($keepId = FALSE) {
		$id = $GLOBALS['TSFE']->fe_user->id;
		if ($keepId && !self::getSessionValue('keepsessid')) {
			self::setSessionValue('keepsessid', TRUE);
			self::storeSessionData();
		}
		return $id;
	}

	/**
	 * Set a session value.
	 * The value is stored in TYPO3 session storage.
	 *
	 * tx_t3users_util_ServiceRegistry::getFeUserService()->setSessionValue()
	 * @see tx_t3users_services_feuser::setSessionValue
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param string $extKey
	 */
	public static function setSessionValue($key, $value, $extKey='mklib') {
		$vars = $GLOBALS['TSFE']->fe_user->getKey('ses', $extKey);
		$vars[$key] = &$value;
		$GLOBALS['TSFE']->fe_user->setKey('ses', $extKey, $vars);
	}

	/**
	 * Returns a session value.
	 *
	 * tx_t3users_util_ServiceRegistry::getFeUserService()->getSessionValue()
	 * @see tx_t3users_services_feuser::getSessionValue
	 *
	 * @param string $key key of session value
	 * @param string $extKey optional
	 * @return mixed
	 */
	public static function getSessionValue($key, $extKey='mklib') {
		$vars = $GLOBALS['TSFE']->fe_user->getKey('ses', $extKey);
		return $vars[$key];
	}

	/**
	 * Removes a session value.
	 *
	 * tx_t3users_util_ServiceRegistry::getFeUserService()->removeSessionValue()
	 * @see tx_t3users_services_feuser::removeSessionValue
	 *
	 * @param string $key key of session value
	 * @param string $extKey optional
	 */
	public static function removeSessionValue($key, $extKey='mklib') {
		$vars = $GLOBALS['TSFE']->fe_user->getKey('ses', $extKey);
		unset($vars[$key]);
		$GLOBALS['TSFE']->fe_user->setKey('ses', $extKey, $vars);
	}

	/**
	 * Saves the session data to database.
	 */
	public static function storeSessionData() {
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}

	/**
	 * @return boolean
	 */
	public static function areCookiesActivatedInFrontend() {
		// fe_typo_user ist im FE immer gesetzt. Außer beim aller ersten Aufruf
		// der Seite
		return isset($_COOKIE['fe_typo_user']);
	}

	/**
	 *
	 * @param string $sessionId
	 * @return void
	 */
	public static function setSessionId($sessionId) {
		$GLOBALS['TSFE']->fe_user->id = $sessionId;
		//sonst werden die Session Daten nicht neu geholt
		$GLOBALS['TSFE']->fe_user->sesData = array();
		// wenn es für die ID keine Session Daten gibt, aber vorher welche vorhanden waren
		// dann wird sessionDataTimestamp von der alten Session übernommen.
		// das führt dazu dass die Session Daten nicht für die neue Session ID gespeichert werden.
		$sessionDataTimestampProperty = new ReflectionProperty(
			get_class($GLOBALS['TSFE']->fe_user), 'sessionDataTimestamp'
		);
		$sessionDataTimestampProperty->setAccessible(TRUE);
		$sessionDataTimestampProperty->setValue($GLOBALS['TSFE']->fe_user, NULL);

		$GLOBALS['TSFE']->fe_user->fetchSessionData();
	}
}
if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Session.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Session.php']);
}