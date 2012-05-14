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

/**
 * Bietet ein Feld für eine Email Adresse
 *
 * @package TYPO3
 * @subpackage tx_mklib
 * @author Hannes Bochmann <hann.bochmann@das-medienkombinat.de>
 */
class tx_mklib_scheduler_EmailFieldProvider extends tx_mklib_scheduler_GenericFieldProvider {

	/**
	 *
	 * @return 	array
	 * @todo CSH einfügen
	 */
	protected function getAdditionalFieldConfig(){
		return array(
			// wir brauchen einen eindeutigen namen da es das email
			// feld schon im scheduler test task gibt. dieser überschreibt
			// dann unseren email wert da er später im quelltext auftaucht.
			'mklibEmail' => array(
				'type' => 'input',
 				'label' => 'LLL:EXT:scheduler/mod1/locallang.xml:label.email',
				'default' => $GLOBALS['BE_USER']->user['email'], 
				'eval' => 'email',
			),
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/scheduler/examples/class.tx_scheduler_testtask_additionalfieldprovider.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/scheduler/examples/class.tx_scheduler_testtask_additionalfieldprovider.php']);
}

?>