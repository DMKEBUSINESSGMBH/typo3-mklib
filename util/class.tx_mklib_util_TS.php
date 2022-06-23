<?php

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
     * @param string $extKeyTS             Extension, deren Konfig innerhalb der
     *                                     TS Config geladen werden soll.
     *                                     Es kann also zb. das TS von mklib geladen werden aber darin die konfig für
     *                                     das plugin von mkxyz
     * @param string $sStaticPath          pfad zum TS
     * @param array  $aConfig              zusätzliche Konfig, die die default  überschreibt
     * @param bool   $resolveReferences    sollen referenzen die in lib.
     *                                     und plugin.tx_$extKeyTS stehen aufgelöst werden?
     * @param bool   $forceTsfePreparation
     *
     * @return \Sys25\RnBase\Configuration\Processor
     */
    public static function loadConfig4BE(
        $extKey,
        $extKeyTs = null,
        $sStaticPath = '',
        $aConfig = [],
        $resolveReferences = false,
        $forceTsfePreparation = false
    ) {
        $extKeyTs = is_null($extKeyTs) ? $extKey : $extKeyTs;

        if (!$sStaticPath) {
            $sStaticPath = '/static/ts/setup.txt';
        }

        if (file_exists(\Sys25\RnBase\Utility\Files::getFileAbsFileName('EXT:'.$extKey.$sStaticPath))) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$extKey.$sStaticPath.'">');
        }

        $tsfePreparationOptions = [];
        if ($forceTsfePreparation) {
            $tsfePreparationOptions['force'] = true;
        }

        // Ist bei Aufruf aus BE notwendig! (@TODO: sicher???)
        \Sys25\RnBase\Utility\Misc::prepareTSFE($tsfePreparationOptions);
        $GLOBALS['TSFE']->config = [];

        $cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Utility\Typo3Classes::getContentObjectRendererClass());

        $pageTsConfig = self::getPagesTSconfig(0);

        $tempConfig = $pageTsConfig['plugin.']['tx_'.$extKeyTs.'.'];
        $tempConfig['lib.'][$extKeyTs.'.'] = $pageTsConfig['lib.'][$extKeyTs.'.'];
        $tempConfig['lib.']['links.'] = $pageTsConfig['lib.']['links.'];

        if ($resolveReferences) {
            $GLOBALS['TSFE']->tmpl->setup['lib.'][$extKeyTs.'.'] =
                $tempConfig['lib.'][$extKeyTs.'.'];
            $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_'.$extKeyTs.'.'] =
                $pageTsConfig['plugin.']['tx_'.$extKeyTs.'.'];
        }

        $pageTsConfig = $tempConfig;

        $qualifier = $pageTsConfig['qualifier'] ? $pageTsConfig['qualifier'] : $extKeyTs;

        // möglichkeit die default konfig zu überschreiben
        $pageTsConfig = \Sys25\RnBase\Utility\Arrays::mergeRecursiveWithOverrule($pageTsConfig, $aConfig);

        $configurations = new \Sys25\RnBase\Configuration\Processor();
        $configurations->init($pageTsConfig, $cObj, $extKeyTs, $qualifier);

        return $configurations;
    }

    /**
     * wrapper funktion.
     *
     * @param number $pageId
     *
     * @return array
     */
    public static function getPagesTSconfig($pageId = 0)
    {
        // ab TYPO3 6.2.x wird die TS config gecached wenn nicht direkt eine
        // rootline ungleich NULL übergeben wird.
        // wir müssen die rootline auf nicht NULL setzen und kein array damit
        // die rootline korrekt geholt wird und nichts aus dem Cache. Sonst werde
        // die gerade hinzugefügten TS Dateien nicht beachtet
        $rootLine = 1;

        return \Sys25\RnBase\Backend\Utility\BackendUtility::getPagesTSconfig($pageId, $rootLine);
    }

    /**
     * load ts from page.
     *
     * @param mixed  $mPageUid   page uid
     * @param string $sExtKey
     * @param string $sDomainKey
     *
     * @return \Sys25\RnBase\Configuration\Processor
     *
     * @TODO: static caching integrieren!?
     */
    public static function loadTSFromPage(
        $mPageUid = 0,
        $sExtKey = 'mklib',
        $sDomainKey = 'plugin.'
    ) {
        // rootlines der pid auslesen
        $aRootLine = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Utility\RootlineUtility::class,
            intval($mPageUid)
        )->get();

        // ts für die rootlines erzeugen
        /* @var $tsObj \TYPO3\CMS\Core\TypoScript\ExtendedTemplateService */
        $tsObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Utility\Typo3Classes::getExtendedTypoScriptTemplateServiceClass());
        $tsObj->tt_track = 0;
        $tsObj->runThroughTemplates($aRootLine);
        $tsObj->generateConfig();

        if (isset($GLOBALS['TSFE'])) {
            $GLOBALS['TSFE']->tmpl = $tsObj;
            // tsfe config setzen (wird in der \Sys25\RnBase\Configuration\Processor gebraucht (language))
            $GLOBALS['TSFE']->tmpl->setup = array_merge(
                is_array($GLOBALS['TSFE']->config) ? $GLOBALS['TSFE']->config : [],
                is_array($tsObj->setup['config.']) ? $tsObj->setup['config.'] : []
            );
            // tsfe config setzen (ansonsten funktionieren refereznen nicht (fpdf <= lib.fpdf))
            // @TODO: Konfigurierbar machen
            $GLOBALS['TSFE']->tmpl->setup = array_merge(
                is_array($tsObj->setup) ? $tsObj->setup : [],
                is_array($GLOBALS['TSFE']->tmpl->setup) ? $GLOBALS['TSFE']->tmpl->setup : []
            );
        }

        // ts für die extension auslesen
        $pageTsConfig = $tsObj->setup[$sDomainKey]['tx_'.$sExtKey.'.'] ?? [];
        $pageTsConfig['lib.'] = $pageTsConfig['lib.'] ?? null;
        $qualifier = $pageTsConfig['qualifier'] ?? $sExtKey;

        // konfiguration erzeugen
        /* @var $configurations \Sys25\RnBase\Configuration\Processor */
        $configurations = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Configuration\Processor::class);
        $configurations->init($pageTsConfig, $configurations->getCObj(1), $sExtKey, $qualifier);

        return $configurations;
    }
}
