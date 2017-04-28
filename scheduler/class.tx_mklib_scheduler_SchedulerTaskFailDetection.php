<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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


tx_rnbase::load('tx_mklib_scheduler_Generic');
tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_mklib_util_Scheduler');

/**
 * tx_mklib_scheduler_SchedulerTaskFailDetection
 *
 * @package         TYPO3
 * @subpackage      mklib
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_scheduler_SchedulerTaskFailDetection extends tx_mklib_scheduler_Generic
{

    /**
     * Diese werte/optionen werden bei der ausgabe in der scheduler
     * übersicht als eine richtige zeitangabe formatiert wie 1 minute 30 sekunden
     *
     * @var array
     */
    protected $optionsToFormat = array('failDetectionRememberAfter');

    /**
     * (non-PHPdoc)
     * @see tx_mklib_scheduler_Generic::executeTask()
     */
    protected function executeTask(array $options, array &$devLog)
    {
        $this->resetFailedTasksDetection();
        $failedTasks = $this->getFailedTasks();

        if (empty($failedTasks)) {
            return 'keine fehlgeschlagenen Scheduler entdeckt!';
        }

        return $this->handleFailedTasks($failedTasks);
    }

    /**
     * alle bei denen eine errinnerung notwendig ist
     * @return void
     */
    protected function resetFailedTasksDetection()
    {
        $this->getDatabaseConnection()->doUpdate(
            'tx_scheduler_task',
            'faildetected < ' . ($GLOBALS['EXEC_TIME'] - $this->getOption('failDetectionRememberAfter')),
            array('faildetected' => 0)
        );
    }

    /**
     * @return Tx_Mklib_Database_Connection
     */
    protected function getDatabaseConnection()
    {
        return tx_rnbase::makeInstance('Tx_Mklib_Database_Connection');
    }

    /**
     * @param array $failedTasks
     * @return void
     */
    protected function handleFailedTasks($failedTasks)
    {
        //Nachrichten für den error mail versand
        $messages = $aUids = array();
        foreach ($failedTasks as $failedTask) {
            $classname = tx_rnbase_util_TYPO3::isTYPO62OrHigher() ?
                get_class(unserialize($failedTask['serialized_task_object'])) :
                $failedTask['classname'];

            $messages[] = '"' . $classname . ' (Task-Uid: ' . $failedTask['uid'] . ')"';
            $uids[] = $failedTask['uid'];
        }

        //wir bauen eine exception damit die error mail von rnbase gebaut werden kann
        $message =    'Die folgenden Scheduler Tasks sind fehlgeschlagen : ' .
                    implode(', ', $messages);

        $exception = new Exception($message, 0);
        //die Mail soll immer geschickt werden
        $options = array('ignoremaillock' => true);
        // wir rufen die Methode mit call_user_func_array auf, da sie
        // statisch ist, womit wir diese nicht mocken könnten
        call_user_func_array(
            array($this->getMiscUtility(), 'sendErrorMail'),
            array(
                $this->getOption('failDetectionReceiver'),
                'tx_mklib_scheduler_SchedulerTaskFailDetection',
                $exception,
                $options
            )
        );

        $this->setFailDetected($uids);

        return $message;
    }

    /**
     * @return string
     */
    protected function getMiscUtility()
    {
        tx_rnbase::load('tx_rnbase_util_Misc');

        return tx_rnbase_util_Misc;
    }

    /**
     * @param array $uids
     * @return void
     */
    protected function setFailDetected(array $uids)
    {
        $this->getDatabaseConnection()->doUpdate(
            'tx_scheduler_task',
            'uid IN (' . implode(',', $uids) . ')',
            array('faildetected' => $GLOBALS['EXEC_TIME'])
        );
    }

    /**
     * möglicherweise hängen geblibene tasks
     * @return array
     */
    protected function getFailedTasks()
    {
        $selectFields = tx_rnbase_util_TYPO3::isTYPO62OrHigher() ?
            'uid,serialized_task_object' :
            'uid,classname';

        return $this->getDatabaseConnection()->doSelect(
            $selectFields,
            'tx_scheduler_task',
            array(
                //hat keine TCA
                'enablefieldsoff' => true,
                //nicht unser eigener Task, keine deaktivierten und alle mit Fehler
                'where' =>    'uid != ' . intval($this->taskUid) . ' AND ' .
                            'faildetected = 0 AND ' .
                            'lastexecution_failure != "" AND ' .
                            'disable = 0'
            )
        );
    }

    /**
     * Liefert alle Optionen. sekunden werden in einer ordentlichen
     * zeitangabe formatiert
     *
     * @return  array
     */
    public function getOptions()
    {
        $options = parent::getOptions();

        foreach ($this->optionsToFormat as $option) {
            if (isset($options[$option])) {
                $options[$option] = tx_mklib_util_Scheduler::getFormattedTime(
                    $options[$option]
                );
            }
        }

        return $options;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_SchedulerTaskFreezeDetection.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_SchedulerTaskFreezeDetection.php']);
}
