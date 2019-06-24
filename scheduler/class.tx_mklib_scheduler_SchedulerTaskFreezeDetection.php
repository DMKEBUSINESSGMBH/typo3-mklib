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
tx_rnbase::load('tx_rnbase_util_DB');
tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_mklib_util_Scheduler');

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_scheduler_SchedulerTaskFreezeDetection extends tx_mklib_scheduler_Generic
{
    /**
     * Diese werte/optionen werden bei der ausgabe in der scheduler
     * übersicht als eine richtige zeitangabe formatiert wie 1 minute 30 sekunden.
     *
     * @var array
     */
    protected $aOptionsToFormat = array('threshold', 'rememberAfter');

    /**
     * @param array $options
     *
     * @return string
     */
    protected function executeTask(array $aOptions, array &$aDevLog)
    {
        //alle die nicht mehr laufen auf freezedetected = 0 setzen
        //alle bei denen exectime > freezedeteceted + rememberafter auf freezedetecetd = 0
        $this->resetPossiblyFrozenTasks();

        //wir brauchen alle laufenden Tasks die freezedeteceted=0 sind
        $aPossiblyFrozenTasks = $this->getPossiblyFrozenTasks();

        //nix zu tun
        if (empty($aPossiblyFrozenTasks)) {
            return 'keine hängengebliebene Scheduler entdeckt!';
        }

        return $this->handleFrozenTasks($aPossiblyFrozenTasks);
    }

    /**
     * sendet eine error mail für alle tasks die hängen geblieben
     * sind. außerdem werden diese tasks auf freezdeteced = exectime gesetzt.
     *
     * @param array $aPossiblyFrozenTasks
     */
    protected function handleFrozenTasks($aPossiblyFrozenTasks)
    {
        //Nachrichten für den error mail versand
        $aMessages = $aUids = array();
        foreach ($aPossiblyFrozenTasks as $aPossiblyFrozenTask) {
            $classname = get_class(unserialize($aPossiblyFrozenTask['serialized_task_object']));

            $aMessages[] = '"'.$classname.' (Task-Uid: '.$aPossiblyFrozenTask['uid'].')"';
            $aUids[] = $aPossiblyFrozenTask['uid'];
        }

        //wir bauen eine exception damit die error mail von rnbase gebaut werden kann
        $sMsg = LF.'Die folgenden Scheduler Tasks hängen seit mindestens '.
            tx_mklib_util_Scheduler::getFormattedTime($this->getOption('threshold')).' : '.implode(', ', $aMessages);
        $oException = new Exception($sMsg, 0);
        tx_rnbase::load('tx_rnbase_util_Misc');
        //die Mail soll immer geschickt werden
        $aOptions = array('ignoremaillock' => true);
        tx_rnbase_util_Misc::sendErrorMail($this->getOption('receiver'), 'tx_mklib_scheduler_CheckRunningTasks', $oException, $aOptions);

        //bei allen hängen geblibenen tasks freezedetected setzen
        //damit erst nach der errinerungszeit wieder eine mail versendet wird
        $this->setFreezeDetected($aUids);

        return $sMsg;
    }

    /**
     * alle uids (tasks) auf freezedetected = exec time setzen.
     *
     * @param array $aUids
     */
    protected function setFreezeDetected($aUids)
    {
        tx_rnbase_util_DB::doUpdate(
            'tx_scheduler_task',
            'uid IN ('.implode(',', $aUids).')',
            array('freezedetected' => $GLOBALS['EXEC_TIME'])
        );
    }

    /**
     * alle die nicht laufen oder bei denen
     * eine errinnerung notwendig ist.
     */
    protected function resetPossiblyFrozenTasks()
    {
        tx_rnbase_util_DB::doUpdate(
            'tx_scheduler_task',
            'LENGTH(serialized_executions) = 0 OR freezedetected < '.($GLOBALS['EXEC_TIME'] - $this->getOption('rememberAfter')),
            array('freezedetected' => 0)
        );
    }

    /**
     * möglicherweise hängen geblibene tasks.
     *
     * @return array
     */
    protected function getPossiblyFrozenTasks()
    {
        $selectFields = 'uid,serialized_task_object';

        return tx_rnbase_util_DB::doSelect(
            $selectFields,
            'tx_scheduler_task',
            array(
                //hat keine TCA
                'enablefieldsoff' => true,
                //nicht unser eigener Task und nur aktuell laufende, die vor dem
                //threshold gestartet haben
                'where' => 'uid != '.intval($this->taskUid).
                ' AND LENGTH(serialized_executions) > 0'.
                ' AND freezedetected = 0'.
                ' AND lastexecution_time < '.($GLOBALS['EXEC_TIME'] - $this->getOption('threshold')),
            )
        );
    }

    /**
     * This method returns the destination mail address as additional information.
     *
     * @return string Information to display
     */
    public function getAdditionalInformation($info = '')
    {
        return parent::getAdditionalInformation(
            $GLOBALS['LANG']->sL('LLL:EXT:mklib/scheduler/locallang.xml:scheduler_CheckRunningTasks_taskinfo')
        );
    }

    /**
     * Liefert alle Optionen. sekunden werden in einer ordentlichen
     * zeitangabe formatiert.
     *
     * @return array
     */
    public function getOptions()
    {
        $aOptions = parent::getOptions();

        foreach ($this->aOptionsToFormat as $sOption) {
            if (isset($aOptions[$sOption])) {
                $aOptions[$sOption] = tx_mklib_util_Scheduler::getFormattedTime(
                    $aOptions[$sOption]
                );
            }
        }

        return $aOptions;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_SchedulerTaskFreezeDetection.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_SchedulerTaskFreezeDetection.php'];
}
