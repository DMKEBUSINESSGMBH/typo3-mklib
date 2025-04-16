<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mklib" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Util für session handling.
 *
 * This methods are taken from the great t3users extension.
 * Using this only if t3users not aviable.
 *
 * @see tx_t3users_services_feuser
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_Session
{
    /**
     * Liefert die aktuelle Session id des Nutzers.
     *
     * Wenn für den aktuellen Nutzer noch keine Session vorhanden ist,
     * variert die ID für jeden Seitenaufruf.
     * Wenn die ID bei jedem Seitenaufruf gleich bleiben soll,
     * dann ist es notwendig, daten in die Session zu schreiben.
     * Nur das bewegt Typo3 dazu, sich die Session zu merken!
     *
     * @param bool $keepId
     *
     * @return string
     */
    public static function getSessionId($keepId = false)
    {
        $id = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->getUserSession()->getIdentifier();

        if ($keepId && !self::getSessionValue('keepsessid')) {
            self::setSessionValue('keepsessid', true);
            self::storeSessionData();
        }

        return $id;
    }

    /**
     * Set a session value.
     * The value is stored in TYPO3 session storage.
     *
     * tx_t3users_util_ServiceRegistry::getFeUserService()->setSessionValue()
     *
     * @see tx_t3users_services_feuser::setSessionValue
     *
     * @param string $key
     * @param string $extKey
     */
    public static function setSessionValue($key, $value, $extKey = 'mklib'): void
    {
        $vars = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->getKey('ses', $extKey);
        $vars[$key] = &$value;
        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->setKey('ses', $extKey, $vars);
    }

    /**
     * Returns a session value.
     *
     * tx_t3users_util_ServiceRegistry::getFeUserService()->getSessionValue()
     *
     * @see tx_t3users_services_feuser::getSessionValue
     *
     * @param string $key    key of session value
     * @param string $extKey optional
     */
    public static function getSessionValue($key, $extKey = 'mklib')
    {
        $vars = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->getKey('ses', $extKey);

        return $vars[$key] ?? null;
    }

    /**
     * Removes a session value.
     *
     * tx_t3users_util_ServiceRegistry::getFeUserService()->removeSessionValue()
     *
     * @see tx_t3users_services_feuser::removeSessionValue
     *
     * @param string $key    key of session value
     * @param string $extKey optional
     */
    public static function removeSessionValue($key, $extKey = 'mklib'): void
    {
        $vars = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->getKey('ses', $extKey);
        unset($vars[$key]);
        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->setKey('ses', $extKey, $vars);
    }

    /**
     * Saves the session data to database.
     */
    public static function storeSessionData(): void
    {
        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->storeSessionData();
    }

    /**
     * Diese Methode funktioniert nur wenn der aktuelle Request kein POST
     * Request ist. Wenn es ein POST Request ist, dann einfach vor dem
     * absenden mit JS einen Cookie setzen und ggf. noch checkedIfCookiesAreActivated=1
     * in den Parametern übermitteln. Oder das Formular wird gar nicht erst
     * gerendered wenn diese Methode FALSE liefert.
     *
     * @return bool
     */
    public static function areCookiesActivated()
    {
        if ([] !== $_COOKIE || Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('checkedIfCookiesAreActivated')) {
            $cookiesActivated = [] !== $_COOKIE;
        } else {
            // @TODO diesen Abschnitt testen, aber wie (vor allem auf CLI)?
            // Wir versuchen selbst einen Cookie zu setzen.
            setcookie('cookiesActivated', 1, ['expires' => time() + 3600]);
            // Wir setzen einen Parameter für den Reload,
            // um einen Infinite Redirect zu verhindern
            // falls keine Cookies erlaubt sind.
            $parsedUrl = parse_url(Sys25\RnBase\Utility\Misc::getIndpEnv('TYPO3_SITE_SCRIPT'));
            $checkedIfCookiesAreActivatedParameter = (($parsedUrl['query'] ?? '') ? '&' : '?').'checkedIfCookiesAreActivated=1';
            // Und machen einen Reload um zu sehen ob Cookies gesetzt werden konnten.
            header(
                'Location: /'.
                Sys25\RnBase\Utility\Misc::getIndpEnv('TYPO3_SITE_SCRIPT').
                $checkedIfCookiesAreActivatedParameter
            );
            exit;
        }

        return $cookiesActivated;
    }

    /**
     * Use this method with absolute caution as it takes over sessions of other users.
     */
    public static function setSessionId(string $sessionId): void
    {
        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->setUserSession(
            TYPO3\CMS\Core\Session\UserSessionManager::create('FE')->createSessionFromStorage($sessionId)
        );
    }
}
