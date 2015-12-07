<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 ***************************************************************/

/**
 *
 * tx_mklib_util_Scheduler
 *
 * @package 		TYPO3
 * @subpackage	 	mklib
 * @author 			Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_util_Scheduler {

	/**
	 * formatiert die sekunden als eine leserliche ausgabe
	 * wie 1 minute 30 sekunden
	 *
	 * @param integer $seconds
	 * @return string
	 */
	public static function getFormattedTime($seconds) {
		$time = array();
		$time['hours'] = floor($seconds / 3600);
		$time['minutes'] = floor(($seconds-$time['hours'] * 3600) / 60);
		$time['seconds'] = $seconds-$time['hours'] * 3600 - $time['minutes'] * 60;

		$formattedTime = '';
		foreach ($time as $timePart => $value) {
			if($value < 1) {
				continue; //null wollen wir nicht sehen
			}
			//else
			$labelKey = 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_formattedtime_' .
						$timePart . '_' . (($value > 1) ? 'plural' : 'singular');
			$formattedTime .= 	sprintf('%01d', $value) . ' ' .
								$GLOBALS['LANG']->sL($labelKey) . ' ';
		}

		return $formattedTime;
	}
}