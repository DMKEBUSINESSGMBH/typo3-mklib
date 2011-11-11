<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib
 *
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('mklib') . 'class.abstract_ext_update.php');

/**
 * Class for updating the db
 *
 * @author	 Michael Wagner <michael.wagner@das-medienkombinat.de>
 * @author	 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 */
class ext_update extends abstract_ext_update {

	/**
	 * Liefert den Namen der Extension für die
	 * @return string
	 */
	protected function getExtensionName() {
		return 'mklib';
	}

	/**
	 * Liefert die Nachricht, was gemacht werden soll
	 * @return string
	 */
	protected function getInfoMsg() {
		return '<p>Update the Static Info Tables with new zip code rules.<br /></p>';
	}
	
	/**
	 * Liefert die Nachricht, was gemacht werden soll
	 * @return string
	 */
	protected function getSuccessMsg() {
		return '<p><big><strong>Import done.</strong></big></p>';
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/class.ext_update.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/class.ext_update.php']);
}
