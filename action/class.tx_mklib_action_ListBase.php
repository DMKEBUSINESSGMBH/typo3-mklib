<?php

/**
 * benötigte Klassen einbinden.
 */

/**
 * Controller
 * Generische Klasse für List Views.
 *
 * @author Hannes Bochmann
 *
 * @deprecated use tx_mklib_action_AbstractList instead
 */
abstract class tx_mklib_action_ListBase extends tx_rnbase_action_BaseIOC
{
    /**
     * @param tx_rnbase_IParameters    $parameters
     * @param tx_rnbase_configurations $configurations
     * @param ArrayObject              $viewData
     *
     * @return string error msg or null
     *
     * @throws RuntimeException
     */
    public function handleRequest(&$parameters, &$configurations, &$viewData)
    {
        $confId = $this->getConfId();
        $srv = $this->getService();

        //@todo interface für die services bereitstellen um nicht prüfen zu müssen ob es die Such methode gibt!
        // mw:
        // eventuell einfach auf tx_rnbase_sv1_Base prüfen !?
        // die methode getSearchCallback ist überflüssig,
        // da es auch bei einem interface immer eine fest definierte sein muss > search!
        $sSearchCallback = $this->getSearchCallback();
        if (!method_exists($srv, $sSearchCallback)) {
            throw new RuntimeException('Der Service '.$srv.' muss die Methode '.$sSearchCallback.' unterstützen!', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mklib']['baseExceptionCode'].'1');
        }

        //Filter setzen
        $filter = tx_rnbase_filter_BaseFilter::createFilter($parameters, $configurations, $viewData, $confId.($this->isOldFilterMode() ? '' : 'filter.'));

        $fields = [];
        $options = [];
        //suche initialisieren
        if ($filter->init($fields, $options)) {
            // @TODO: charbrowser integrieren
            // @TODO: pagebrowser und charbrowser sollte endlich filter übernehmen!
            $filter->handlePageBrowser(
                $configurations,
                $confId.$this->getTsPathPageBrowser(),
                $viewData,
                $fields,
                $options,
                ['searchcallback' => [$srv, $sSearchCallback]]
            );

            $items = $srv->$sSearchCallback($fields, $options);
        }

        $viewData->offsetSet($this->getItemsDesignator(), $items);

        return;
    }

    /**
     * Soll der filter über $confId.filter (neu) oder direkt über die
     * $confId (alt) eingebunden werden.
     *
     * @TODO: sollte die confid liefern, keinen boolischen wert!
     *
     * @return bool
     */
    protected function isOldFilterMode()
    {
        return true;
    }

    /**
     * Gibt den Name der zugehörigen View-Klasse zurück.
     *
     * @return string
     */
    public function getViewClassName()
    {
        return 'tx_rnbase_view_List';
    }

    /**
     * Liefert den bezeichner für die gefundenen elemente in den view daten.
     *
     * @TODO: überflüssig, der item designator sollte immer item sein,
     *  da die basisklassen (rnbase) nur damit arbeiten können!
     *
     * @return string
     */
    protected function getItemsDesignator()
    {
        return 'items';
    }

    /**
     * Liefert die Methode, welche im Service zum Suchen aufgerufen wird.
     *
     * @TODO: überflüssig, sollte immer search sein!
     *
     * @return string
     */
    protected function getSearchCallback()
    {
        return 'search';
    }

    /**
     * Liefert die Service Klasse, welche das Suchen übernimmt.
     *
     * @return tx_mklib_srv_Base
     */
    abstract protected function getService();

    /**
     * Liefert den Ts Pfad für den pagebrowser ausgehend von $this->getConfId().
     *
     * @TODO: sollte nicht abstract sein, sondern immer eine confid liefern.
     * wenn notwendig, wird die methode eben überschrieben!
     *
     * @return string
     */
    abstract protected function getTsPathPageBrowser();
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/action/class.tx_mklib_action_ListBase.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/action/class.tx_mklib_action_ListBase.php'];
}
