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
 * Util Methoden für das TS, speziell im BE.
 *
 * @author  Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @author  Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_TS
{
    /**
     * Lädt ein COnfigurations Objekt nach mit der TS aus der Extension
     * Dabei wird alles geholt was in "plugin.tx_$extKey", "lib.$extKey." und
     * "lib.links." liegt.
     *
     * @param string $extKey               Extension, deren TS Config geladen werden soll
     * @param string $sStaticPath          pfad zum TS
     * @param array  $aConfig              zusätzliche Konfig, die die default  überschreibt
     * @param bool   $resolveReferences    sollen referenzen die in lib.
     *                                     und plugin.tx_$extKeyTS stehen aufgelöst werden?
     * @param bool   $forceTsfePreparation
     */
    public static function loadConfig4BE(
        string $extKey,
        $extKeyTs = null,
        $sStaticPath = '',
        array $aConfig = [],
        $resolveReferences = false,
        $forceTsfePreparation = false,
    ): Sys25\RnBase\Configuration\Processor {
        $extKeyTs = is_null($extKeyTs) ? $extKey : $extKeyTs;

        if (!$sStaticPath) {
            $sStaticPath = '/static/ts/setup.txt';
        }

        if (file_exists(Sys25\RnBase\Utility\Files::getFileAbsFileName('EXT:'.$extKey.$sStaticPath))) {
            TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$extKey.$sStaticPath.'">');
        }

        $tsfePreparationOptions = [];
        if ($forceTsfePreparation) {
            $tsfePreparationOptions['force'] = true;
        }

        // Ist bei Aufruf aus BE notwendig! (@TODO: sicher???)
        Sys25\RnBase\Utility\Misc::prepareTSFE($tsfePreparationOptions);
        $GLOBALS['TSFE']->config = [];

        $cObj = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Utility\Typo3Classes::getContentObjectRendererClass());

        $pageTsConfig = TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig(0);

        $tempConfig = $pageTsConfig['plugin.']['tx_'.$extKeyTs.'.'];
        $tempConfig['lib.'][$extKeyTs.'.'] = $pageTsConfig['lib.'][$extKeyTs.'.'];
        $tempConfig['lib.']['links.'] = $pageTsConfig['lib.']['links.'];

        if ($resolveReferences) {
            $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['lib.'][$extKeyTs.'.'] =
                $tempConfig['lib.'][$extKeyTs.'.'];
            $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')->getSetupArray()['plugin.']['tx_'.$extKeyTs.'.'] =
                $pageTsConfig['plugin.']['tx_'.$extKeyTs.'.'];
        }

        $pageTsConfig = $tempConfig;

        $qualifier = $pageTsConfig['qualifier'] ?: $extKeyTs;

        // möglichkeit die default konfig zu überschreiben
        $pageTsConfig = Sys25\RnBase\Utility\Arrays::mergeRecursiveWithOverrule($pageTsConfig, $aConfig);

        $configurations = new Sys25\RnBase\Configuration\Processor();
        $configurations->init($pageTsConfig, $cObj, $extKeyTs, $qualifier);

        return $configurations;
    }

    /**
     * load ts from page.
     *
     * @param mixed  $mPageUid   page uid
     * @param string $sDomainKey
     *
     * @return Sys25\RnBase\Configuration\Processor
     *
     * @TODO: static caching integrieren!?
     */
    public static function loadTSFromPage(
        mixed $mPageUid = 0,
        string $sExtKey = 'mklib',
        $sDomainKey = 'plugin.',
    ) {
        // ts für die extension auslesen
        $typoScriptConfiguration = self::getTypoScriptConfiguration($mPageUid)[$sDomainKey]['tx_'.$sExtKey.'.'] ?? [];
        $typoScriptConfiguration['lib.'] ??= null;
        $qualifier = $typoScriptConfiguration['qualifier'] ?? $sExtKey;

        // konfiguration erzeugen
        /* @var $configurations \Sys25\RnBase\Configuration\Processor */
        $configurations = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Configuration\Processor::class);
        $configurations->init($typoScriptConfiguration, $configurations->getCObj(1), $sExtKey, $qualifier);

        return $configurations;
    }

    protected static function getTypoScriptConfiguration($pageUid = 0): array
    {
        // @todo the if part can be removed when support for TYPO3 12 is dropped.
        if (!Sys25\RnBase\Utility\TYPO3::isTYPO130OrHigher()) {
            $rootLine = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                TYPO3\CMS\Core\Utility\RootlineUtility::class,
                intval($pageUid)
            )->get();

            $tsfe = Sys25\RnBase\Utility\Misc::prepareTSFE(
                [
                    'force' => true,
                    'pid' => $pageUid,
                    'type' => 0,
                ]
            );
            $tsfe->rootLine = $rootLine;
            $tsfe->no_cache = true;

            $tsfe->id = $pageUid;
            $GLOBALS['TYPO3_REQUEST'] = $tsfe->getFromCache($GLOBALS['TYPO3_REQUEST'] ?? TYPO3\CMS\Core\Http\ServerRequestFactory::fromGlobals());

            return $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')
                ->getSetupArray();
        }

        $configurationManager = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::class
        );
        if (TYPO3\CMS\Core\Core\Environment::isCli()) {
            $configurationManager->setRequest(
                (new TYPO3\CMS\Core\Http\ServerRequest())
                    ->withAttribute('extbase', [])
                    ->withAttribute(
                        'applicationType', TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_BE
                    )
                    ->withParsedBody(['id' => $pageUid])
            );
        }

        return $configurationManager->getConfiguration(
            TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
    }
}
