<?php

declare(strict_types=1);

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
 * benötigte Klassen einbinden.
 */
/**
 * Hilfsklassen um nach Gewinnspielen im BE zu suchen.
 */
class tx_mklib_tests_fixtures_classes_DummySearcher extends tx_mklib_mod1_searcher_abstractBase
{
    protected function getSearcherId(): string
    {
        return 'dummySearcher';
    }

    protected function getService(): object
    {
        return TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_tests_fixtures_classes_Dummy');
    }

    /**
     * @return tx_mklib_mod1_decorator_Base
     */
    protected function getDecorator($mod, array $options = []): object
    {
        return TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_mod1_decorator_Base', $mod, $options);
    }

    /**
     * Liefert die Spalten für den Decorator.
     *
     * @param tx_mklib_mod1_decorator_Base $oDecorator
     */
    protected function getDecoratorColumns($oDecorator): array
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
    protected function getCols(): array
    {
        return ['WORDLIST.uid'];
    }
}
