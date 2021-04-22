<?php
/**
 * @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 michael Wagner <michael.wagner@dmk-ebusiness.de>
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
 * Miscellaneous common methods.
 */
class tx_mklib_util_MiscTools
{
    /**
     * Liefert einen Wert aus der Extension-Konfiguration.
     * Gibt es für die angegebene Extension keine Konfiguration,
     * wird als Fallback der Wert von mklib zurückgegeben.
     *
     * @param string $sValueKey
     * @param string $sExtKey
     * @param bool   $bFallback
     *
     * @return mixed
     */
    public static function getExtensionValue($sValueKey, $sExtKey = 'mklib', $bFallback = false)
    {
        if (!$sExtKey) {
            $sExtKey = 'mklib';
        }
        $mValue = tx_rnbase_configurations::getExtensionCfgValue($sExtKey, $sValueKey);
        if ($bFallback && false === $mValue && 'mklib' != $sExtKey) {
            $mValue = tx_rnbase_configurations::getExtensionCfgValue('mklib', $sValueKey);
        }

        return $mValue;
    }

    /**
     * Liefert eine BE-Account.
     * Dieser Nutzer wird für TCE Operationen verwendet. Er sollte Admin-Rechte haben.
     *
     * @param string $sExtKey
     * @param bool   $bFallback
     *
     * @return int
     */
    public static function getProxyBeUserId($sExtKey = 'mklib', $bFallback = true)
    {
        return intval(self::getExtensionValue('proxyBeUserId', $sExtKey, $bFallback));
    }

    /**
     * Liefert den Pfad zu den Bildern.
     *
     * @param string $sExtKey
     * @param bool   $bFallback
     *
     * @return int
     */
    public static function getPicturesUploadPath($sExtKey = 'mklib', $bFallback = true)
    {
        return self::getExtensionValue('picturesUploadPath', $sExtKey, $bFallback);
    }

    /**
     * Liefert die Page-ID, wo alle Portaldaten gespeichert sind.
     *
     * @param string $sExtKey
     * @param bool   $bFallback
     *
     * @return int
     */
    public static function getPortalPageId($sExtKey = 'mklib', $bFallback = true)
    {
        return intval(self::getExtensionValue('portalPageId', $sExtKey, $bFallback));
    }

    /**
     * Gibt die Extension Konfiguration für den Sonderzeichen Marker zurück
     * Diese wird aber lediglich angegeben. Die Mehrwertsteuer wird durch die Extension
     * Konfiguration NICHT angelegt!
     *
     * @param string $sExtKey
     * @param bool   $bFallback
     *
     * @return int
     */
    public static function getSpecialCharMarker($sExtKey = 'mklib', $bFallback = true)
    {
        return self::getExtensionValue('specialCharMarker', $sExtKey, $bFallback);
    }

    /**
     * workaround for HTTP authorization in CGI environment.
     *
     * Requires Redirect in .htaccess:
     *   RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
     */
    public static function enableHttpAuthForCgi()
    {
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) =
                explode(
                    ':',
                    base64_decode(
                        substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)
                    )
                );
        }
    }
}
