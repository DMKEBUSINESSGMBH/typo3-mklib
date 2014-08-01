<?php
/**
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2014 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_scheduler_GenericFieldProvider');
tx_rnbase::load('tx_mklib_util_DB');

/**
 * Fügt Felder im scheduler task hinzu
 *
 * @package TYPO3
 * @subpackage tx_mklib
 * @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 */
class tx_mklib_scheduler_DeleteFromDatabaseFieldProvider
	extends tx_mklib_scheduler_GenericFieldProvider
	implements tx_scheduler_AdditionalFieldProvider {

	/**
	 *
	 * @return 	array
	 * @todo CSH einfügen
	 */
	protected function getAdditionalFieldConfig(){
		$twentyEightDaysInSeconds = 2419200;
		return array(
			'table' => array(
				'type' => 'input',
				'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_DeleteFromDatabase_field_table',
				'eval' => 'required',
			),
			'where' => array(
				'type' => 'input',
				'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_DeleteFromDatabase_field_where',
				'default' => "hidden = 1 AND tstamp < (UNIX_TIMESTAMP() - $twentyEightDaysInSeconds)",
				'eval' => 'required',
			),
			'mode' => array(
				'type' => 'select',
				'items'	=> array (
					tx_mklib_util_DB::DELETION_MODE_HIDE =>
						$GLOBALS['LANG']->sL('LLL:EXT:mklib/scheduler/locallang.xml:scheduler_DeleteFromDatabase_field_mode_hidden'),
					tx_mklib_util_DB::DELETION_MODE_SOFTDELETE
						 => $GLOBALS['LANG']->sL('LLL:EXT:mklib/scheduler/locallang.xml:scheduler_DeleteFromDatabase_field_mode_delete'),
					tx_mklib_util_DB::DELETION_MODE_REALLYDELETE
						 => $GLOBALS['LANG']->sL('LLL:EXT:mklib/scheduler/locallang.xml:scheduler_DeleteFromDatabase_field_mode_delete_hard')
				),
				'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_DeleteFromDatabase_field_mode',
			),
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_DeleteFromDatabaseFieldProvider.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_DeleteFromDatabaseFieldProvider.php']);
}