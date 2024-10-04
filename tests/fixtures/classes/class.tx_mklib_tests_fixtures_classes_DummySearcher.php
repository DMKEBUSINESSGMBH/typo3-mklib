<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 * benötigte Klassen einbinden.
 */

/**
 * Hilfsklassen um nach Gewinnspielen im BE zu suchen.
 */
class tx_mklib_tests_fixtures_classes_DummySearcher extends tx_mklib_mod1_searcher_abstractBase
{
    /**
     * @return string
     */
    protected function getSearcherId()
    {
        return 'dummySearcher';
    }

    protected function getService()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_tests_fixtures_classes_Dummy');
    }

    /**
     * @return tx_mklib_mod1_decorator_Base
     */
    protected function getDecorator($mod, array $options = [])
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_mod1_decorator_Base', $mod, $options);
    }

    /**
     * Liefert die Spalten für den Decorator.
     *
     * @param tx_mklib_mod1_decorator_Base $oDecorator
     *
     * @return array
     */
    protected function getDecoratorColumns($oDecorator)
    {
        return [
                'uid' => [
                    'title' => 'label_tableheader_uid',
                    'decorator' => &$oDecorator,
                    'sortable' => 'WORDLIST.',
                ],
                'actions' => [
                    'title' => 'label_tableheader_actions',
                    'decorator' => &$oDecorator,
                ],
            ];
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_mklib_mod1_searcher_abstractBase::getCols()
     */
    protected function getCols()
    {
        return ['WORDLIST.uid'];
    }
}
