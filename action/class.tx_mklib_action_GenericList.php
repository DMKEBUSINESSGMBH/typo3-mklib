<?php

/**
 * benötigte Klassen einbinden.
 */

/**
 * Controller
 * Generische Klasse für List Views.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 *
 * @deprecated Making this action compatible with rn_base >= 1.15.0 would mean a refactoring as we can't use the
 * view right now. As the view is markerbased it's deprecated anyway.
 */
class tx_mklib_action_GenericList extends tx_rnbase_action_BaseIOC
{
    protected $confIdExtended = 'default.';

    /**
     * @param tx_rnbase_IParameters    $parameters
     * @param \Sys25\RnBase\Configuration\Processor $configurations
     * @param ArrayObject              $viewData
     *
     * @return string error msg or null
     */
    public function handleRequest(&$parameters, &$configurations, &$viewData)
    {
        // confIdExtended setzen
        $this->confIdExtended = $configurations->get($this->getConfId().'extendedConfId');
        $this->confIdExtended = $this->confIdExtended ? $this->confIdExtended : 'default';

        $confId = $this->getExtendedConfId();

        // Filter erstellen.
        /* @var $filter tx_rnbase_filter_BaseFilter */
        $filter = tx_rnbase_filter_BaseFilter::createFilter($parameters, $configurations, $viewData, $confId.'filter.');
        $fields = $options = [];

        // Searcher instanzieren. Konfiguriert wird er über die options['searchdef']
        /* @var $searcher \Sys25\RnBase\Search\SearchGeneric */
        $searcher = \Sys25\RnBase\Search\SearchBase::getInstance(\Sys25\RnBase\Search\SearchGeneric::class);

        // Dem Filter den Searcher übergeben, fall er diese Möglichkeit bietet.
        if (method_exists($filter, 'setSearcher')) {
            $filter->setSearcher($searcher);
        }

        // Suche initialisieren und nur ausführen wenn der Filter es erlaubt.
        if ($doSearch = $filter->init($fields, $options)) {
            // Soll ein PageBrowser verwendet werden?
            if ($configurations->get($confId.'pagebrowser')) {
                $pbOptions = ['searchcallback' => [$searcher, 'search']];
                $cbOptions['pbid'] = ($var = $configurations->get($confId.'pagebrowser.cbid')) ? $var : 'pb'.$this->confIdExtended;
                $filter->handlePageBrowser(
                    $configurations,
                    $confId.'pagebrowser',
                    $viewData,
                    $fields,
                    $options,
                    $pbOptions
                );
            }

            // Soll ein CharBrowser verwendet werden?
            if ($configurations->get($confId.'charbrowser')) {
                // optionen sammeln
                $cbOptions = ['searchcallback' => [$searcher, 'search']];
                $cbOptions['colname'] = ($var = $configurations->get($confId.'charbrowser.colname')) ? $var : 'title';
                $cbOptions['specials'] = ($var = $configurations->get($confId.'charbrowser.specials')) ? $var : 'last';
                $cbOptions['cbid'] = ($var = $configurations->get($confId.'charbrowser.cbid')) ? $var : 'cb'.$this->confIdExtended;
                $filter->handleCharBrowser(
                    $configurations,
                    $confId.'charbrowser',
                    $viewData,
                    $fields,
                    $options,
                    $cbOptions
                );
            }

            // items besorgen.
            $items = $searcher->search($fields, $options);
        } else {
            $items = [];
        }

        $viewData->offsetSet('items', $items);
        $viewData->offsetSet('searched', $doSearch);

        return null;
    }

    /**
     * Liefert die Erweiterte ConfId für den View.
     *
     * @return string
     */
    public function getExtendedConfId()
    {
        return $this->getConfId().$this->confIdExtended.'.';
    }

    /**
     * Liefert den Default-Namen des Templates. Über diesen Namen
     * wird per Konvention auch auf ein per TS konfiguriertes HTML-Template
     * geprüft. Dessen Key wird aus dem Name und dem String "Template"
     * gebildet: [tmpname]Template.
     *
     * @return string
     */
    public function getTemplateName()
    {
        return 'genericlist';
    }

    /**
     * Gibt den Name der zugehörigen View-Klasse zurück.
     *
     * @return string
     */
    public function getViewClassName()
    {
        return 'tx_mklib_view_GenericList';
    }
}
