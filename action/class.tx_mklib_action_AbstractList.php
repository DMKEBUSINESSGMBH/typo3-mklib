<?php

/**
 * Controller
 * Generische Klasse für List Views.
 *
 * @author Michael Wagner
 */
abstract class tx_mklib_action_AbstractList extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{
    /**
     * @param \Sys25\RnBase\Frontend\Request\RequestInterface $request
     *
     * @return string|null
     */
    public function handleRequest(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $out = $this->prepareRequest($request);
        if (null !== $out) {
            return $out;
        }

        $items = $this->getItems($request);
        $request->getViewContext()->offsetSet('items', $items);
        $request->getViewContext()->offsetSet('searched', false !== $items);

        return null;
    }

    /**
     * Childclass can override this method to prepare the request.
     *
     * @return string error msg or null
     */
    protected function prepareRequest(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        return null;
    }

    /**
     * @param \Sys25\RnBase\Frontend\Request\RequestInterface $request
     *
     * @return array|false
     */
    protected function getItems(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $configurations = $request->getConfigurations();
        $parameters = $request->getParameters();
        $viewData = $request->getViewContext();

        // get the repo
        $repo = $this->getRepository();

        // check the repo interface
        if (!($repo instanceof \Sys25\RnBase\Domain\Repository\SearchInterface)) {
            throw new RuntimeException('the repository "'.get_class($repo).'" '.'has to implement the interface "\Sys25\RnBase\Domain\Repository\SearchInterface"!', intval(ERROR_CODE_MKLIB.'1'));
        }

        // create filter
        $filter = \Sys25\RnBase\Frontend\Filter\BaseFilter::createFilter($request, $this->getConfId().'filter.');

        $fields = $options = [];
        // let the filter fill the fields end options
        if ($this->prepareFieldsAndOptions($fields, $options)
            && $filter->init($fields, $options)
        ) {
            if ($configurations->get($this->getConfId().'pagebrowser')) {
                $filter::handlePageBrowser(
                    $configurations,
                    $this->getConfId().'pagebrowser',
                    $viewData,
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
        return \Sys25\RnBase\Frontend\View\Marker\ListView::class;
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
     * @return \Sys25\RnBase\Domain\Repository\SearchInterface
     */
    abstract protected function getRepository();
}
