<?php
/**
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat <kontakt@das-medienkombinat.de>
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

// t3lib_extMgm::addLLrefForTCAdescr('_MOD_tools_txschedulerM1', t3lib_extMgm::extPath($_EXTKEY).'scheduler/locallang.xml');

/**
 * Fügt Felder im scheduler task hinzu
 *
 * @package tx_mketernit
 * @subpackage tx_mketernit_scheduler
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mklib_scheduler_cleanupTempFilesFieldProvider
	extends tx_mklib_scheduler_GenericFieldProvider
	implements tx_scheduler_AdditionalFieldProvider {
	
	/**
	 *
	 * @return 	array
	 */
	protected function getAdditionalFieldConfig(){
		return array(
			'lifetime' => array(
				'type' => 'input',
 				'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_cleanupTempFiles_field_lifetime',
				'default' => 604800, // default is 7 days
				'eval' => 'trim,int',
			),
			'folder' => array(
				'type' => 'input',
 				'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_cleanupTempFiles_field_folder',
				'default' => '', // default is 7 days
				'eval' => 'trim,folder',
			),
			'filetypes' => array(
				'type' => 'input',
 				'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_cleanupTempFiles_field_filetypes',
				'default' => '', // default is 7 days
				'eval' => 'trim',
			),
			'recursive' => array(
				'type' => 'check',
 				'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_cleanupTempFiles_field_recursive',
				'default' => '', // default is 7 days
				'eval' => '',
			),
		);
	}
	
	protected function validateFolder($mValue){
		return (strpos($mValue, 'typo3temp') === false)
			? $GLOBALS['LANG']->sL('LLL:EXT:mklib/scheduler/locallang.xml:scheduler_cleanupTempFiles_field_folder_eval_typo3temp')
			: true;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_cleanupTempFilesFieldProvider.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_cleanupTempFilesFieldProvider.php']);
}