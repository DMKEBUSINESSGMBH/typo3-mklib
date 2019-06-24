<?php
/**
 * @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_model_base');

/**
 * Model eines DAM-Records.
 *
 * @author Michael Wagner
 */
class tx_mklib_model_Media extends tx_rnbase_model_base
{
    /**
     * Liefert den Namen der Datenbanktabelle.
     *
     * @return string
     */
    public function getTableName()
    {
        return 'sys_file';
    }

    /**
     * Befüllt den Record Pfaden.
     *
     * @param string $sPath
     *
     * @return tx_mklib_model_Media
     */
    public function fillPath($sPath = false)
    {
        // Pathname immer setzen!
        if (!$this->hasFilePathName()) {
            $this->setFilePathName(
                $this->getUrl()
            );
        }

        tx_rnbase::load('tx_mklib_util_File');

        // webpath setzen
        if ((!$sPath || 'webpath' == $sPath)
            && !$this->hasFileWebpath()
        ) {
            $this->setFileWebpath(
                tx_mklib_util_File::getWebPath(
                    $this->getFilePathName()
                )
            );
        }

        // serverpath setzen
        if ((!$sPath || 'serverpath' == $sPath)
            && !$this->hasFileServerpath()
        ) {
            $this->setFileServerpath(
                urldecode(
                    tx_mklib_util_File::getServerPath(
                        $this->getFilePathName()
                    )
                )
            );
        }

        // relpath setzen
        if ((!$sPath || 'relpath' == $sPath)
            && !$this->hasFileRelpath()
        ) {
            $this->setFileRelpath(
                tx_mklib_util_File::getRelPath(
                    $this->getFilePathName(),
                    true
                )
            );
        }

        return $this;
    }
}
