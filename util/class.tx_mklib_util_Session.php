<?php

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
        $id = $GLOBALS['TSFE']->fe_user->getUserSession()->getIdentifier();

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
     * @param mixed  $value
     * @param string $extKey
     */
    public static function setSessionValue($key, $value, $extKey = 'mklib')
    {
        $vars = $GLOBALS['TSFE']->fe_user->getKey('ses', $extKey);
        $vars[$key] = &$value;
        $GLOBALS['TSFE']->fe_user->setKey('ses', $extKey, $vars);
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
     *
     * @return mixed
     */
    public static function getSessionValue($key, $extKey = 'mklib')
    {
        $vars = $GLOBALS['TSFE']->fe_user->getKey('ses', $extKey);

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
    public static function removeSessionValue($key, $extKey = 'mklib')
    {
        $vars = $GLOBALS['TSFE']->fe_user->getKey('ses', $extKey);
        unset($vars[$key]);
        $GLOBALS['TSFE']->fe_user->setKey('ses', $extKey, $vars);
    }

    /**
     * Saves the session data to database.
     */
    public static function storeSessionData()
    {
        $GLOBALS['TSFE']->fe_user->storeSessionData();
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
        if (!empty($_COOKIE) || \Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('checkedIfCookiesAreActivated')) {
            $cookiesActivated = !empty($_COOKIE);
        } else {
            // @TODO diesen Abschnitt testen, aber wie (vor allem auf CLI)?
            // Wir versuchen selbst einen Cookie zu setzen.
            setcookie('cookiesActivated', 1, time() + 3600);
            // Wir setzen einen Parameter für den Reload,
            // um einen Infinite Redirect zu verhindern
            // falls keine Cookies erlaubt sind.
            $parsedUrl = parse_url(\Sys25\RnBase\Utility\Misc::getIndpEnv('TYPO3_SITE_SCRIPT'));
            $checkedIfCookiesAreActivatedParameter = ($parsedUrl['query'] ? '&' : '?').'checkedIfCookiesAreActivated=1';
            // Und machen einen Reload um zu sehen ob Cookies gesetzt werden konnten.
            header(
                'Location: /'.
                \Sys25\RnBase\Utility\Misc::getIndpEnv('TYPO3_SITE_SCRIPT').
                $checkedIfCookiesAreActivatedParameter
            );
            exit;
        }

        return $cookiesActivated;
    }

    /**
     * Use this method with absolute caution as it takes over sessions of other users.
     *
     * @param string $sessionId
     */
    public static function setSessionId($sessionId)
    {
        $GLOBALS['TSFE']->fe_user->setUserSession(
            \TYPO3\CMS\Core\Session\UserSessionManager::create('FE')->createSessionFromStorage($sessionId)
        );
    }
}
