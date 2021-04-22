<?php

/**
 * Controller
 * Generische Klasse für List Views.
 *
 * @author Michael Wagner
 */
abstract class tx_mklib_action_AbstractList extends tx_rnbase_action_BaseIOC
{
    /**
     * Do the magic!
     *
     * @param tx_rnbase_parameters     &$parameters
     * @param tx_rnbase_configurations &$configurations
     * @param ArrayObject              &$viewData
     *
     * @return string error msg or null
     */
    public function handleRequest(&$parameters, &$configurations, &$viewData)
    {
        $out = $this->prepareRequest();
        if (null !== $out) {
            return $out;
        }

        $items = $this->getItems();
        $viewData->offsetSet('items', $items);
        $viewData->offsetSet('searched', false !== $items);

        return null;
    }

    /**
     * Childclass can override this method to prepare the request.
     *
     * @return string error msg or null
     */
    protected function prepareRequest()
    {
        return null;
    }

    /**
     * Searches for the items to show in list.
     *
     * @throws RuntimeException
     *
     * @return array|false
     */
    protected function getItems()
    {
        // get the repo
        $repo = $this->getRepository();

        // check the repo interface
        if (!($repo instanceof Tx_Rnbase_Domain_Repository_InterfaceSearch)) {
            throw new RuntimeException('the repository "'.get_class($repo).'" '.'has to implement the interface "Tx_Rnbase_Domain_Repository_InterfaceSearch"!', intval(ERROR_CODE_MKLIB.'1'));
        }

        // create filter
        $filter = tx_rnbase_filter_BaseFilter::createFilter(
            $this->getParameters(),
            $this->getConfigurations(),
            $this->getViewData(),
            $this->getConfId().'filter.'
        );

        $fields = $options = [];
        // let the filter fill the fields end options
        if ($this->prepareFieldsAndOptions($fields, $options)
            && $filter->init($fields, $options)
        ) {
            if ($this->getConfigurations()->get($this->getConfId().'pagebrowser')) {
                $filter::handlePageBrowser(
                    $this->getConfigurations(),
                    $this->getConfId().'pagebrowser',
                    $this->getConfigurations()->getViewData(),
                    $fields,
                    $options,
                    ['searchcallback' => [$repo, 'search']]
                );
            }
            // we search for the items
            $items = $repo->search($fields, $options);
        } else {
            // it was not carried out search
            return false;
        }

        return !(array) $items ? [] : $items;
    }

    /**
     * Childclass can prepare the fields and options
     * for the search in the repository.
     *
     * @param array &$fields
     * @param array &$options
     *
     * @return bool
     */
    protected function prepareFieldsAndOptions(
        array &$fields,
        array &$options
    ) {
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
     * Liefert den Templatenamen.
     * Darüber wird per Konvention auch auf ein per TS konfiguriertes
     * HTML-Template geprüft und die ConfId gebildet.
     *
     * @return string
     */
    // abstract protected function getTemplateName();

    /**
     * Liefert die Service Klasse, welche das Suchen übernimmt.
     *
     * @return Tx_Rnbase_Domain_Repository_InterfaceSearch
     */
    abstract protected function getRepository();
}
