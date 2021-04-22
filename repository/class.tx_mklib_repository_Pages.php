<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2015 DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
 * All rights reserved
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
 ***************************************************************/

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
     *
     * @return string
     */
    protected function getSearchClass()
    {
        return 'tx_rnbase_util_SearchGeneric';
    }

    /**
     * Liefert die Model Klasse.
     *
     * @return string
     */
    protected function getWrapperClass()
    {
        return 'tx_mklib_model_Page';
    }

    /**
     * Return an instantiated dummy model without any content.
     *
     * This is used only to access several model info methods like
     * getTableName(), getColumnNames() etc.
     *
     * @return Tx_Rnbase_Domain_Model_RecordInterface
     */
    public function getEmptyModel()
    {
        return parent::getEmptyModel()->setTablename('pages');
    }

    /**
     * returns all subpages of a page on first level.
     *
     * @param tx_mklib_model_Page $page
     *
     * @return array[tx_mklib_model_Page]
     */
    public function getChildren(
        tx_mklib_model_Page $page
    ) {
        $fields = $options = [];
        $fields['PAGES.pid'][OP_EQ_INT] = $page->getUid();

        return $this->search($fields, $options);
    }

    /**
     * Search database.
     *
     * @param array $fields
     * @param array $options
     *
     * @return array[Tx_Rnbase_Domain_Model_RecordInterface]
     */
    public function search(array $fields, array $options)
    {
        if (empty($options['searchdef']) || !is_array($options['searchdef'])) {
            $options['searchdef'] = [];
        }
        $options['searchdef'] = tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
            // default sercher config
            $this->getSearchdef(),
            // searcher config overrides
            $options['searchdef']
        );

        return parent::search($fields, $options);
    }

    /**
     * @return array
     */
    protected function getSearchdef()
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
