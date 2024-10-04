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

        $pageTsConfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig(0);

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
        // ts für die extension auslesen
        $typoScriptConfiguration = self::getTypoScriptConfiguration($mPageUid)[$sDomainKey]['tx_'.$sExtKey.'.'] ?? [];
        $typoScriptConfiguration['lib.'] = $typoScriptConfiguration['lib.'] ?? null;
        $qualifier = $typoScriptConfiguration['qualifier'] ?? $sExtKey;

        // konfiguration erzeugen
        /* @var $configurations \Sys25\RnBase\Configuration\Processor */
        $configurations = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Configuration\Processor::class);
        $configurations->init($typoScriptConfiguration, $configurations->getCObj(1), $sExtKey, $qualifier);

        return $configurations;
    }

    protected static function getTypoScriptConfiguration($pageUid = 0): array
    {
        $rootLine = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Utility\RootlineUtility::class,
            intval($pageUid)
        )->get();

        $tsfe = \Sys25\RnBase\Utility\Misc::prepareTSFE(
            [
                'force' => true,
                'pid' => $pageUid,
                'type' => 0,
            ]
        );
        $tsfe->rootLine = $rootLine;
        $tsfe->no_cache = true;
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $context->setAspect(
            'typoscript',
            \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Context\TypoScriptAspect::class,
                true
            )
        );
        $tsfe->id = $pageUid;
        // @todo the if part can be removed when support for TYPO3 11 is dropped.
        if (is_callable([$tsfe, 'getConfigArray'])) {
            $tsfe->getConfigArray();
            $setup = $tsfe->tmpl->setup;
        } else {
            $GLOBALS['TYPO3_REQUEST'] = $tsfe->getFromCache($GLOBALS['TYPO3_REQUEST'] ?? \TYPO3\CMS\Core\Http\ServerRequestFactory::fromGlobals());
            $setup = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript')
                ->getSetupArray();
        }

        return $setup;
    }
}
