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
 * Page Repository.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_repository_Pages extends tx_mklib_repository_Abstract
{
    /**
     * Liefert den Namen der Suchklasse.
     */
    protected function getSearchClass(): string
    {
        return Sys25\RnBase\Search\SearchGeneric::class;
    }

    /**
     * Liefert die Model Klasse.
     */
    protected function getWrapperClass(): string
    {
        return 'tx_mklib_model_Page';
    }

    /**
     * Return an instantiated dummy model without any content.
     *
     * This is used only to access several model info methods like
     * getTableName(), getColumnNames() etc.
     *
     * @return Sys25\RnBase\Domain\Model\RecordInterface
     */
    public function getEmptyModel()
    {
        return parent::getEmptyModel()->setTablename('pages');
    }

    /**
     * returns all subpages of a page on first level.
     *
     * @return array[tx_mklib_model_Page]
     */
    public function getChildren(
        tx_mklib_model_Page $page,
    ) {
        $fields = [];
        $options = [];
        $fields['PAGES.pid'][OP_EQ_INT] = $page->getUid();

        return $this->search($fields, $options);
    }

    /**
     * Search database.
     *
     * @return array[\Sys25\RnBase\Domain\Model\RecordInterface]
     */
    public function search(array $fields, array $options)
    {
        if (empty($options['searchdef']) || !is_array($options['searchdef'])) {
            $options['searchdef'] = [];
        }

        $options['searchdef'] = Sys25\RnBase\Utility\Arrays::mergeRecursiveWithOverrule(
            // default sercher config
            $this->getSearchdef(),
            // searcher config overrides
            $options['searchdef']
        );

        return parent::search($fields, $options);
    }

    protected function getSearchdef(): array
    {
        return [
            'usealias' => '1',
            'basetable' => 'pages',
            'basetablealias' => 'PAGES',
            'wrapperclass' => $this->getWrapperClass(),
            'alias' => [
                'PAGES' => [
                    'table' => 'pages',
                ],
                'PAGESPARENT' => [
                    'table' => 'pages',
                    'join' => 'JOIN pages AS PAGESPARENT ON PAGES.pid = PAGESPARENT.uid',
                ],
            ],
        ];
    }
}
