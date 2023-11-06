<?php
/**
 *  Copyright notice.
 *
 *  (c) 2015 Hannes Bochmann <dev@dmk-ebusiness.de>
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
 * tx_mklib_action_ShowSingeItem.
 *
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
abstract class tx_mklib_action_ShowSingeItem extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{
    /**
     * @param \Sys25\RnBase\Frontend\Request\RequestInterface $request
     *
     * @return null
     *
     * @throws \TYPO3\CMS\Core\Error\Http\PageNotFoundException
     */
    protected function handleRequest(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $configurations = $request->getConfigurations();
        $parameters = $request->getParameters();
        $viewData = $request->getViewContext();

        $itemUid = $this->getSingleItemUidFromConfigurations($request);

        $itemParameterKey = $this->getSingleItemUidParameterKey($request);
        if (!$itemUid
            && !($itemUid = $parameters->getInt($itemParameterKey))
        ) {
            $this->throwItemNotFound404Exception($request);
        }

        $singleItemRepository = $this->getSingleItemRepository();
        // @todo migrate to real interface
        // check for interfaces/classes that we know of having the findByUid method
        // as we need it
        if (!$singleItemRepository instanceof tx_mklib_repository_Abstract
            && !$singleItemRepository instanceof \Sys25\RnBase\Domain\Repository\AbstractRepository
        ) {
            throw new Exception('Das Repository, welches von getSingleItemRepository() geliefert wird, muss von tx_mklib_repository_Abstract erben!');
        }

        if (!($item = $singleItemRepository->findByUid($itemUid))) {
            $this->throwItemNotFound404Exception($request);
        }

        $viewData->offsetSet('item', $item);

        $this->substitutePageTitle($request);

        return null;
    }

    /**
     * @return int
     */
    protected function getSingleItemUidFromConfigurations(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        return $request->getConfigurations()->get($this->getConfId().'uid');
    }

    /**
     * The parameter key can be stored at
     * typoscript: "plugin.tx_myext.myActionConfId.uidParameterKey"
     * default is: uid.
     *
     * @return string
     */
    protected function getSingleItemUidParameterKey(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $uidParameterKey = $request->getConfigurations()->get(
            $this->getConfId().'uidParameterKey'
        );

        return empty($uidParameterKey) ? 'uid' : $uidParameterKey;
    }

    /**
     * @return tx_mklib_repository_Abstract
     */
    abstract protected function getSingleItemRepository();

    /**
     * @throws \TYPO3\CMS\Core\Error\Http\PageNotFoundException
     */
    protected function throwItemNotFound404Exception(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        if (!$request->getConfigurations()->get($this->getConfId().'disable404ExceptionIfNoItemFound')) {
            throw \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Error\Http\PageNotFoundException::class, $this->getItemNotFound404Message($request));
        }
    }

    /**
     * The message can be stored at
     * typoscript: "plugin.tx_myext.myActionConfId.notfound"
     * or locallang: "myActionConfId_notfound".
     *
     * default is: Datensatz nicht gefunden.
     *
     * @return string
     */
    protected function getItemNotFound404Message(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $message = $request->getConfigurations()->getCfgOrLL(
            $this->getConfId().'notfound'
        );

        return empty($message) ? 'Datensatz nicht gefunden.' : $message;
    }

    /**
     * @return string
     */
    protected function getViewClassName()
    {
        return \Sys25\RnBase\Frontend\View\Marker\SingleView::class;
    }

    protected function substitutePageTitle(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        if ($request->getConfigurations()->get($this->getConfId().'substitutePageTitle')) {
            $pageTitle = $this->getPageTitle($request);
            \Sys25\RnBase\Utility\TYPO3::getTSFE()->page['title'] = $pageTitle;
            \Sys25\RnBase\Utility\TYPO3::getTSFE()->indexedDocTitle = $pageTitle;
        }
    }

    /**
     * @return string
     */
    protected function getPageTitle(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        return 'please provide the method getPageTitle in your action returning the desired page title';
    }
}
