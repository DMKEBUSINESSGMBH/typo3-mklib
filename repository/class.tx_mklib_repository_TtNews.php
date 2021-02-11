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
 * tt_news Repository.
 *
 * @author Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_repository_TtNews extends tx_mklib_repository_Abstract
{
    /**
     * {@inheritdoc}
     *
     * @see tx_mklib_repository_Abstract::getSearchClass()
     */
    protected function getSearchClass()
    {
        return 'tx_rnbase_util_SearchGeneric';
    }

    /**
     * {@inheritdoc}
     *
     * @see tx_mklib_repository_Abstract::getEmptyModel()
     */
    public function getEmptyModel()
    {
        return parent::getEmptyModel()->setTablename('tt_news');
    }

    /**
     * {@inheritdoc}
     *
     * @see tx_mklib_repository_Abstract::getWrapperClass()
     */
    protected function getWrapperClass()
    {
        return 'tx_mklib_model_TtNews';
    }

    /**
     * {@inheritdoc}
     *
     * @see tx_mklib_repository_Abstract::search()
     */
    public function search(array $fields = [], array $options = [])
    {
        return parent::search($fields, $this->insertSearchDefinition($options));
    }

    /**
     * {@inheritdoc}
     *
     * @see tx_mklib_repository_Abstract::searchSingle()
     */
    public function searchSingle(array $fields = [], array $options = [])
    {
        return parent::searchSingle($fields, $this->insertSearchDefinition($options));
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function insertSearchDefinition(array $options)
    {
        if (empty($options['searchdef']) || !is_array($options['searchdef'])) {
            $options['searchdef'] = [];
        }
        $options['searchdef'] = tx_rnbase_util_Arrays::mergeRecursiveWithOverrule(
            // default sercher config
            $this->getSearchDefinition(),
            // searcher config overrides
            $options['searchdef']
        );

        return $options;
    }

    /**
     * @return array
     */
    protected function getSearchDefinition()
    {
        return [
            'usealias' => '1',
            'basetable' => 'tt_news',
            'basetablealias' => 'NEWS',
            'wrapperclass' => $this->getWrapperClass(),
            'alias' => [
                'NEWS' => [
                    'table' => 'tt_news',
                ],
            ],
        ];
    }
}
