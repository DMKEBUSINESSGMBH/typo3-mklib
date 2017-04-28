<?php
/**
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH  <dev@dmk-ebusiness.de>
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

tx_rnbase::load('tx_mklib_scheduler_GenericFieldProvider');

/**
 * tx_mklib_scheduler_SchedulerTaskFailDetectionFieldProvider
 *
 * @package         TYPO3
 * @subpackage      mklib
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_scheduler_SchedulerTaskFailDetectionFieldProvider extends tx_mklib_scheduler_GenericFieldProvider
{

    /**
     *
     * @return  array
     * @todo CSH einfügen
     */
    protected function getAdditionalFieldConfig()
    {
        return array(
            'failDetectionReceiver' => array(
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_field_receiver',
                'default' => $GLOBALS['BE_USER']->user['email'],
                'eval' => 'email,required',
            ),
            'failDetectionRememberAfter' => array(
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_field_rememberAfter',
                'cshLabel' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_field_rememberAfter', // key aus der ssh locallang zu cshKey
                'default' => 3600, // nach 1 h erneut mail schicken
                'eval' => 'int',
            ),
        );
    }
}
