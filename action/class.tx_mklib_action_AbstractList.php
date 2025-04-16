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

abstract class tx_mklib_action_AbstractList extends Sys25\RnBase\Frontend\Controller\AbstractAction
{
    /**
     * @return string|null
     */
    protected function handleRequest(Sys25\RnBase\Frontend\Request\RequestInterface $request)
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
    protected function prepareRequest(Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        return null;
    }

    /**
     * @return array|false
     */
    protected function getItems(Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $configurations = $request->getConfigurations();
        $request->getParameters();
        $viewData = $request->getViewContext();

        // get the repo
        $repo = $this->getRepository();

        // check the repo interface
        if (!($repo instanceof Sys25\RnBase\Domain\Repository\SearchInterface)) {
            throw new RuntimeException('the repository "'.$repo::class.'" has to implement the interface "\Sys25\RnBase\Domain\Repository\SearchInterface"!', intval(ERROR_CODE_MKLIB.'1'));
        }

        // create filter
        $filter = Sys25\RnBase\Frontend\Filter\BaseFilter::createFilter($request, $this->getConfId().'filter.');
        $fields = [];
        $options = [];
        // let the filter fill the fields end options
        if ($this->prepareFieldsAndOptions($fields, $options)
            && $filter->init($fields, $options)
        ) {
            if ($configurations->get($this->getConfId().'pagebrowser')) {
                $pageBrowserFilter = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                    Sys25\RnBase\Frontend\Filter\Utility\PageBrowserFilter::class
                );
                $pageBrowserFilter->handle(
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

        return [] !== (array) $items ? $items : [];
    }

    /**
     * Childclass can prepare the fields and options
     * for the search in the repository.
     *
     * @return bool
     */
    protected function prepareFieldsAndOptions(
        array &$fields,
        array &$options,
    ) {
        return true;
    }

    /**
     * Gibt den Name der zugehörigen View-Klasse zurück.
     *
     * @return string
     */
    protected function getViewClassName()
    {
        return Sys25\RnBase\Frontend\View\Marker\ListView::class;
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
     * @return Sys25\RnBase\Domain\Repository\SearchInterface
     */
    abstract protected function getRepository();
}
