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
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_scheduler_cleanupTempFiles extends tx_mklib_scheduler_Generic
{
    protected function executeTask(array $aOptions, array &$aDevLog): string
    {
        $sDirectory = $aOptions['folder'];
        $iCount = tx_mklib_util_File::cleanupFiles($sDirectory, $aOptions, $unlinkedFiles);
        $aDevLog[Sys25\RnBase\Utility\Logger::LOGLEVEL_INFO] = ['dataVar' => $unlinkedFiles];

        return sprintf($iCount ? '%d files removed.' : 'No files found for cleanup.', $iCount);
    }

    /**
     * This method returns the destination mail address as additional information.
     *
     * @return string Information to display
     */
    public function getAdditionalInformation($info = '')
    {
        return parent::getAdditionalInformation(
            $GLOBALS['LANG']->sL('LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_cleanupTempFiles_taskinfo')
        );
    }
}
