<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
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

require_once (t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_mklib_scheduler_Generic');

/**
 *
 * @package tx_mklib
 * @subpackage tx_mklib_scheduler
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mklib_scheduler_cleanupTempFiles extends tx_mklib_scheduler_Generic {
	

	/**
	 *
	 * @param 	array 	$options
	 * @return 	string
	 */
	protected function executeTask(array $aOptions, array &$aDevLog) {
		$sDirectory = $aOptions['folder'];
		$iCount = tx_mklib_util_File::cleanupFiles($sDirectory, $aOptions, $unlinkedFiles);
		$aDevLog[tx_rnbase_util_Logger::LOGLEVEL_INFO] = array('dataVar' => $unlinkedFiles);
		return sprintf($iCount ? '%d files removed.' : 'No files found for cleanup.' , $iCount);
	}

	/**
	 * This method returns the destination mail address as additional information
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation() {
		return parent::getAdditionalInformation(
				$GLOBALS['LANG']->sL('LLL:EXT:mklib/scheduler/locallang.xml:scheduler_cleanupTempFiles_taskinfo')
			);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_cleanupTempFiles.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_cleanupTempFiles.php']);
}