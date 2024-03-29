<?php

namespace DMK\Mklib\Utility;

/*
 * @package tx_mklib
 * @subpackage tx_mklib_tests
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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

use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DMK\Mklib\Utility$Tests.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class Tests
{
    private static $aExtConf = [];
    private static $aHooks = [];
    private static $sCacheFile;

    /**
     * Stores the RN_Base Hooks from the Extension/Hook.
     *
     * @param unknown $sExtKey
     */
    public static function storeHooks($sExtKey)
    {
        self::$aHooks[$sExtKey] = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$sExtKey];
    }

    /**
     * Loads the RN_Base Hooks from the Cache.
     *
     * @param unknown $sExtKey
     */
    public static function loadHooks($sExtKey)
    {
        if (isset(self::$aExtConf[$sExtKey])) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$sExtKey] = self::$aHooks[$sExtKey];
        }
    }

    /**
     * Removes Extension Hooks from the Global Configuration.
     *
     * @param string $sExtKey
     * @param string $sHookKey
     */
    public static function removeHooks($sExtKey, $sHookKey = null)
    {
        if (!$sHookKey) {
            unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$sExtKey]);
        } else {
            if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$sExtKey]) {
                unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$sExtKey][$sHookKey]);
            }
        }
    }

    /**
     * Sichert eine Extension Konfiguration.
     * Wurde bereits eine Extension Konfiguration gesichert,
     * wird diese nur überschrieben wenn bOverwrite wahr ist!
     *
     * @param string $sExtKey
     * @param bool   $bOverwrite
     */
    public static function storeExtConf($sExtKey = 'mklib', $bOverwrite = false)
    {
        if (!isset(self::$aExtConf[$sExtKey]) || $bOverwrite) {
            self::$aExtConf[$sExtKey] = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][$sExtKey] ?? null;
        }
    }

    /**
     * Setzt eine gesicherte Extension Konfiguration zurück.
     *
     * @param string $sExtKey
     *
     * @return bool wurde die Konfiguration zurückgesetzt?
     */
    public static function restoreExtConf($sExtKey = 'mklib')
    {
        if (isset(self::$aExtConf[$sExtKey])) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][$sExtKey] = self::$aExtConf[$sExtKey];

            return true;
        }

        return false;
    }

    /**
     * Setzt eine Vaiable in die Extension Konfiguration.
     * Achtung im setUp sollte storeExtConf und im tearDown restoreExtConf aufgerufen werden.
     *
     * @param string $sCfgKey
     * @param string $sCfgValue
     * @param string $sExtKey
     */
    public static function setExtConfVar($sCfgKey, $sCfgValue, $sExtKey = 'mklib')
    {
        // aktuelle Konfiguration auslesen
        $extConfig = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][$sExtKey] ?? null;
        // wenn keine Konfiguration existiert, legen wir eine an.
        if (!is_array($extConfig)) {
            $extConfig = [];
        }
        // neuen Wert setzen
        $extConfig[$sCfgKey] = $sCfgValue;
        // neue Konfiguration zurückschreiben
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][$sExtKey] = $extConfig;
    }

    /**
     * Liefert eine DateiNamen.
     *
     * @param $filename
     * @param $dir
     * @param $extKey
     *
     * @return string
     */
    public static function getFixturePath($filename, $dir = 'tests/fixtures/', $extKey = 'mklib')
    {
        return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extKey).$dir.$filename;
    }

    /**
     * Disabled das Logging über die Devlog Extension für die
     * gegebene Extension.
     *
     * @param string $extKey
     * @param bool   $bDisable
     */
    public static function disableDevlog($extKey = 'devlog', $bDisable = true)
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['nolog'] = $bDisable;
    }

    /**
     * Führt eine beliebige DB-Query aus.
     *
     * @param string $sqlFile
     */
    public static function queryDB($sqlFile, $statementType = false, $bIgnoreStatementType = false)
    {
        $sql = \Sys25\RnBase\Utility\Network::getUrl($sqlFile);
        if (empty($sql)) {
            throw new \Exception('SQL-Datei nicht gefunden');
        }
        $databaseConnection = \Sys25\RnBase\Database\Connection::getInstance();
        if ($statementType || $bIgnoreStatementType) {
            $statements = self::getSqlStatementArrayDependendOnTypo3Version($sql);
            foreach ($statements as $statement) {
                if (!$bIgnoreStatementType && \Sys25\RnBase\Utility\Strings::isFirstPartOfStr($statement, $statementType)) {
                    $databaseConnection->doQuery($statement);
                } elseif ($bIgnoreStatementType) {// alle gefundenen statements ausführen
                    $databaseConnection->doQuery($statement);
                }
            }
        } else {
            $databaseConnection->doQuery($sql);
        }
    }

    /**
     * @param string $sql
     *
     * @return array
     */
    private static function getSqlStatementArrayDependendOnTypo3Version($sql)
    {
        $reader = GeneralUtility::makeInstance(SqlReader::class);

        return $reader->getStatementArray($sql);
    }

    /**
     * Lädt den Inhalt einer Datei.
     *
     * @param string $filename
     * @param array  $options
     */
    public function loadTemplate($filename, $configurations, $extKey = 'mklib', $subpart = null, $dir = 'tests/fixtures/')
    {
        $path = self::getFixturePath($filename, $dir, $extKey);

        $markerTemplateService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
        $templateCode = file_get_contents($path);
        if ($subpart) {
            $templateCode = $markerTemplateService->getSubpart($templateCode, $subpart);
        }

        return $templateCode;
    }

    /**
     * Setzt das fe_user objekt, falls es noch nicht gesetzt wurde.
     *
     * @param tslib_feuserauth $oFeUser Erzeugt das tslib_feuserauth Objekt wenn nix übergeben wurde
     * @param bool             $bForce  setzt das fe_user Objekt auch, wenn es bereits gesetzt ist
     */
    public static function setFeUserObject($oFeUser = null, $bForce = false)
    {
        $frontendUserAuthenticationClass = \Sys25\RnBase\Utility\Typo3Classes::getFrontendUserAuthenticationClass();
        if (!$GLOBALS['TSFE']->fe_user instanceof $frontendUserAuthenticationClass
            || $bForce
        ) {
            if (!is_object($oFeUser)) {
                $oFeUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($frontendUserAuthenticationClass);
            }
            if (!is_object($GLOBALS['TSFE'])) {
                self::prepareTSFE(['force' => true]);
            }
            $GLOBALS['TSFE']->fe_user = $oFeUser;
        }
    }

    /**
     * Setzt Sprach-Labels.
     *
     * @param array  $labels
     * @param string $lang
     */
    public static function setLocallangLabels($labels = [], $lang = 'default')
    {
        global $LOCAL_LANG;
        $GLOBALS['LANG']->lang = $lang;
        // ab typo 4.6 ist das mit den lang labels anders
        foreach ($labels as $key => $label) {
            $LOCAL_LANG[$lang][$key][0]['target'] = $label;
        }
    }

    /**
     * Liefert eine rn_base basierte Action auf Grund der gelieferten
     * TS Konfig zurück.
     * Dabei wird automatisch handleRequest aufgerufen.
     * Parameter können frei gesetzt werden.
     *
     * @param string $action
     * @param array  $aConfig
     * @param string $sExtKey
     * @param array  $aParams
     * @param bool   $execute
     * @param string $frontendOutput hier wird die rückgabe der action reingeschrieben
     * @param string $viewData       hier werden die viewData reingeschrieben
     *
     * @return \Sys25\RnBase\Frontend\Controller\AbstractAction
     */
    public static function &getAction(
        $action,
        $aConfig,
        $sExtKey,
        $aParams = [],
        $execute = true,
        &$frontendOutput = '',
        &$viewData = null
    ) {
        if (is_string($action)) {
            $action = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($action);
        }

        $configurations = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Configuration\Processor::class);
        $parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class);

        $aConfig = (array) $aConfig;
        $configurations->init(
            $aConfig,
            $configurations->getCObj(1),
            $sExtKey,
            $sExtKey
        );

        // noch extra params?
        if (!empty($aParams)) {
            foreach ($aParams as $sName => $mValue) {
                $parameters->offsetSet($sName, $mValue);
            }
        }

        $configurations->setParameters($parameters);
        $action->setConfigurations($configurations);
        $parameters->setQualifier($configurations->getQualifier());
        if ($execute) {
            $handleRequest = new \ReflectionMethod(get_class($action), 'handleRequest');
            $handleRequest->setAccessible(true);
            $viewData = $configurations->getViewData();
            $frontendOutput = $handleRequest->invokeArgs(
                $action,
                [
                    &$parameters, &$configurations, &$viewData,
                ]
            );
        }

        return $action;
    }

    /**
     * @param array $options
     *                       initFEuser: verhindert das Schreiben von Headerdaten
     */
    public static function prepareTSFE(array $options = [])
    {
        static $loaded = false;
        if ($loaded && !isset($options['force'])) {
            return;
        }

        if (isset($options['initFEuser'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['cookieSecure'] = 1;
            $GLOBALS['TYPO3_CONF_VARS']['FE']['dontSetCookie'] = 1;
            // sonst wird eine Exception in TYPO3\CMS\Core\Authentication\AbstractUserAuthentication
            // Zeile 548 geworfen
            $GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime'] = 0;
        }

        \Sys25\RnBase\Utility\Misc::prepareTSFE(['force' => true]);
        $loaded = true;

        if (isset($options['initFEuser'])) {
            $GLOBALS['TSFE']->initFEuser();
            // sonst wird eine Exception in TYPO3\CMS\Core\Authentication\AbstractUserAuthentication
            // Zeile 548 geworfen
            $GLOBALS['TSFE']->fe_user->newSessionID = false;
            // ip lock not necessary
            $GLOBALS['TSFE']->fe_user->lockIP = 0;
        }

        if (isset($options['initCObject'])) {
            $GLOBALS['TSFE']->newCObj();
        }
    }

    /**
     * Liefert einen eindeutigen klassenname für einen Mock.
     * Dies ist sinnvoll, wenn ein Mock mehrfach generiert wird aber nicht gecached werden soll.
     *
     * @param string $originalClassName
     * @param string $mockClassName
     *
     * @return object
     */
    public static function generateUniqueMockClassName($originalClassName, $mockClassName = '')
    {
        if ('' == $mockClassName) {
            do {
                $mockClassName = 'Mock_'.$originalClassName.'_'.
                substr(md5(microtime()), 0, 8);
            } while (class_exists($mockClassName, false));
        }

        return $mockClassName;
    }

    /**
     * damit nicht
     * PHP Fatal error:  Call to a member function getHash() on a non-object in
     * typo3/sysext/cms/tslib/class.tslib_content.php on line 1814
     * auftritt. passiert zb bei link generierung.
     */
    public static function setSysPageToTsfe()
    {
        self::prepareTSFE();
        $GLOBALS['TSFE']->sys_page = \Sys25\RnBase\Utility\TYPO3::getSysPage();
    }

    /**
     * @param int $pageId
     */
    public static function enableLinkCreation($pageId = 1)
    {
        \Sys25\RnBase\Utility\Misc::prepareTSFE();

        $GLOBALS['TSFE']->sys_page = \Sys25\RnBase\Utility\TYPO3::getSysPage();
        $GLOBALS['TSFE']->id = $pageId;
    }

    /**
     * @param string $pdfPath
     *
     * @return string
     */
    public static function removeCreationDateFromPdfContent($pdfPath)
    {
        return preg_replace(
            '/\/CreationDate \(D\:[0-9].*\)/',
            '',
            file_get_contents($pdfPath)
        );
    }
}
