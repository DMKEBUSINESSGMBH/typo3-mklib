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
 * Dummy Service um uns DB Abfragen zu ersparen.
 */
class tx_mklib_tests_fixtures_classes_Dummy extends tx_mklib_repository_Abstract
{
    public function search($fields, $options)
    {
        if ($GLOBALS['emptyTestResult']) {
            $aResults = [];
        } else {
            $aResults = [
                0 => \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Domain\Model\BaseModel::class, ['uid' => 1]),
                1 => \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Domain\Model\BaseModel::class, ['uid' => 2]),
                2 => \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Domain\Model\BaseModel::class, ['uid' => 3]),
                3 => \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Domain\Model\BaseModel::class, ['uid' => 4]),
                4 => \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Domain\Model\BaseModel::class, ['uid' => 5]),
            ];
        }
        // sortieren?
        if (!empty($options['orderby'])) {
            rsort($aResults);
        }// reicht um zu sehen ob die Sortierung anspringt

        // versteckte zurück geben?
        if (1 == $GLOBALS['BE_USER']->uc['moduleData']['dummyMod']['showhidden']) {
            $aResults[5] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Domain\Model\BaseModel::class, ['uid' => 6, 'hidden' => 1]);
        }

        if ($options['count']) {
            return count($aResults);
        }

        return $aResults;
    }

    /**
     * Liefert die zugehörige Search-Klasse zurück.
     *
     * @return string
     */
    public function getSearchClass()
    {
        return 'tx_mklib_search_StaticCountries';
    }
}
