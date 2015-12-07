<?php
/**
 *  @package tx_mklib
 *  @subpackage tx_mklib_model
 *  @author Michael Wagner
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
 * Model eines DAM-Records
 *
 * @package tx_mklib
 * @subpackage tx_mklib_model
 *  @author Michael Wagner
 */
class tx_mklib_model_Media extends tx_rnbase_model_base {
	/**
	 * Liefert den Namen der Datenbanktabelle
	 *
	 * @return String
	 */
	public function getTableName() {
		if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			return 'sys_file';
		} else {
			return 'tx_dam';
		}
	}

	/**
	 * Befüllt den Record Pfaden
	 *
	 * @param string $sPath
	 * @return tx_mklib_model_Dam
	 */
	public function fillPath($sPath = false){
		// Pathname immer setzen!
		if (!$this->hasFilePath()) {
			if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
				$this->setFilePathName(
					$this->getUrl()
				);
			} else {
				$this->setFilePathName(
					$this->getFilePath() . $this->getFileName()
				);
			}
		}

		tx_rnbase::load('tx_mklib_util_File');

		// webpath setzen
		if (
			(!$sPath || $sPath == 'webpath')
			&& !$this->hasFileWebpath()
		) {
			$this->setFileWebpath(
				tx_mklib_util_File::getWebPath(
					$this->getFilePathName()
				)
			);
		}

		// serverpath setzen
		if (
			(!$sPath || $sPath == 'serverpath')
			&& !$this->hasFileServerpath()
		) {
			$this->setFileServerpath(
				tx_mklib_util_File::getServerPath(
					$this->getFilePathName()
				)
			);
		}

		// relpath setzen
		if (
			(!$sPath || $sPath == 'relpath')
			&& !$this->hasFileRelpath()
		) {
			$this->setFileRelpath(
				tx_mklib_util_File::getRelPath(
					$this->getFilePathName()
				)
			);
		}

		return $this;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/model/class.tx_mklib_model_Dam.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/model/class.tx_mklib_model_Dam.php']);
}
