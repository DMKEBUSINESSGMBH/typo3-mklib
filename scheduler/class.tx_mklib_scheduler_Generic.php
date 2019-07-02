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

/**
 * generic abstract scheduler.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
abstract class tx_mklib_scheduler_Generic extends Tx_Rnbase_Scheduler_Task
{
    /**
     * The DateTime Object with the last run time.
     *
     * @var DateTime
     */
    protected $lastRun = false;

    /**
     * Was used as the scheduler options before making the extension compatible with TYPO3 9. But as private
     * class variables can't be serialized anymore (@see __makeUp() method) this variable can't be used anymore.
     *
     * @var array
     *
     * @deprecated can be removed including the __wakeup() method when support for TYPO3 8.7 and below is dropped.
     */
    private $options = array();

    /**
     * The options of the scheduler task.
     *
     * @var array
     */
    protected $schedulerOptions = [];

    /**
     * Extension key, used for devlog.
     *
     * @return string
     */
    protected function getExtKey()
    {
        return 'mklib';
    }

    /**
     * After the update to TYPO3 9 the private $options variable can't be serialized and therefore not saved in the
     * database anymore as our parent implemented the __sleep() method to return the class variables which should be
     * serialized/saved. So to keep the possibly saved $options we need to move them to $schedulerOptions if present.
     * Otherwise the $options will be lost after the scheduler is executed/saved.
     */
    public function __wakeup()
    {
        if (method_exists(parent::class, '__wakeup')) {
            parent::__wakeup();
        }

        if ($this->options && !$this->schedulerOptions) {
            $this->schedulerOptions = $this->options;
        }
    }

    /**
     * Function executed from the Scheduler.
     *
     * @return bool Returns true on successful execution, false on error
     */
    public function execute()
    {
        /* beispiel für das logging array.
        $devLog = array('message' => '', 'extKey' => 'mklib', 'dataVar' => FALSE);
        $devLog = array(
            tx_rnbase_util_Logger::LOGLEVEL_DEBUG => $devLog,
            tx_rnbase_util_Logger::LOGLEVEL_INFO => $devLog,
            tx_rnbase_util_Logger::LOGLEVEL_NOTICE => $devLog,
            tx_rnbase_util_Logger::LOGLEVEL_WARN => $devLog,
            tx_rnbase_util_Logger::LOGLEVEL_FATAL => $devLog
        );
        */
        $devLog = array();
        $options = $this->getOptions();
        $startTimeInMilliseconds = tx_rnbase_util_Misc::milliseconds();
        $memoryUsageAtStart = memory_get_usage();

        tx_rnbase_util_Logger::info(
            '['.get_class($this).']: Scheduler starts',
            $this->getExtKey()
        );

        try {
            $message = $this->executeTask($options, $devLog);

            $this->setLastRunTime();

            // devlog
            if (tx_rnbase_util_Extensions::isLoaded('devlog')) {
                if (// infolog setzen, wenn devlog leer
                    empty($devLog)
                    // infolog setzen, wenn infolog gesetzt, aber keine message vorhanden ist
                    || (
                            isset($devLog[tx_rnbase_util_Logger::LOGLEVEL_INFO])
                            && empty($devLog[tx_rnbase_util_Logger::LOGLEVEL_INFO]['message'])
                        )
                    ) {
                    $devLog[tx_rnbase_util_Logger::LOGLEVEL_INFO]['message'] = $message;
                }

                foreach ($devLog as $logLevel => $logData) {
                    if (empty($logData['message'])) {
                        continue;
                    }
                    tx_rnbase_util_Logger::devLog(
                        '['.get_class($this).']: '.$logData['message'],
                        isset($logData['extKey']) ? $logData['extKey'] : $this->getExtKey(),
                        $logLevel,
                        isset($logData['dataVar']) ? $logData['dataVar'] : false
                    );
                }
            }
        } catch (Exception $exception) {
            $dataVar = array(
                'errorcode' => $exception->getCode(),
                'errormsg' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'options' => $options,
                // bisherige logs mitgeben
                'devlog' => $devLog,
            );
            if ($exception instanceof tx_rnbase_util_Exception) {
                $dataVar['exception_data'] = $exception->getAdditional(false);
            }
            if (tx_rnbase_util_Logger::isFatalEnabled()) {
                tx_rnbase_util_Logger::fatal(
                    'Task ['.get_class($this).'] failed.'.
                        ' Error('.$exception->getCode().'):'.
                        $exception->getMessage(),
                    $this->getExtKey(),
                    $dataVar
                );
            }
            // Exception Mail an die Entwicker senden
            $mail = tx_rnbase_configurations::getExtensionCfgValue(
                'rn_base',
                'sendEmailOnException'
            );
            if (!empty($mail)) {
                $this->sendErrorMail(
                    $mail,
                    // Wir erstellen eine weitere Exception mit zusätzlichen Daten.
                    tx_rnbase::makeInstance(
                        'tx_rnbase_util_Exception',
                        get_class($exception).': '.$exception->getMessage(),
                        $exception->getCode(),
                        $dataVar,
                        $exception
                    )
                );
            }
            // Wir geben die Exception weiter,
            // damit der Scheduler eine entsprechende Meldung ausgeben kann.
            throw $exception;
        }

        $memoryUsageAtEnd = memory_get_usage();
        tx_rnbase_util_Logger::info(
            '['.get_class($this).']: Scheduler ends successful ',
            $this->getExtKey(),
            array(
                'Execution Time' => (tx_rnbase_util_Misc::milliseconds() - $startTimeInMilliseconds).' ms',
                'Memory Start' => $memoryUsageAtStart.' Bytes',
                'Memory End' => $memoryUsageAtEnd.' Bytes',
                'Memory Consumed' => ($memoryUsageAtEnd - $memoryUsageAtStart).' Bytes',
            )
        );

        return true;
    }

    /**
     * This is the main method that is called when a task is executed
     * It MUST be implemented by all classes inheriting from this one
     * Note that there is no error handling, errors and failures are expected
     * to be handled and logged by the client implementations.
     * Should return true on successful execution, false on error.
     *
     * @param array $options
     * @param array &$devLog Put some informations for the logging here
     *
     * @return string
     */
    abstract protected function executeTask(array $options, array &$devLog);

    /**
     * Liefert die im Scheduler gesetzten Optionen.
     *
     * @param string $info
     *
     * @return string Information to display
     */
    public function getAdditionalInformation($info = '')
    {
        $info .= CRLF.' Options: ';
        $info .= tx_rnbase_util_Arrays::arrayToLogString($this->getOptions(), array(), 64);

        return $info;
    }

    /**
     * Setzt eine Option.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed der gesetzte Wert
     */
    public function setOption($key, $value)
    {
        return $this->schedulerOptions[$key] = $value;
    }

    /**
     * Liefert eine Option.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getOption($key)
    {
        return $this->schedulerOptions[$key];
    }

    /**
     * Setzt alle Otionen.
     *
     * @param array $options
     *
     * @return mixed der gesetzte Wert
     */
    public function setOptions(array $options)
    {
        return $this->schedulerOptions = $options;
    }

    /**
     * Liefert alle Optionen.
     *
     * @return array
     */
    public function getOptions()
    {
        // wir brauchen per default ein array
        return is_array($this->schedulerOptions) ? $this->schedulerOptions : array();
    }

    /**
     * gets the last run time.
     *
     * @return DateTime|null
     */
    protected function getLastRunTime()
    {
        if (false === $this->lastRun) {
            $options = array();
            $options['enablefieldsoff'] = 1;
            $options['where'] = 'uid='.(int) $this->getTaskUid();
            $options['limit'] = 1;
            try {
                $ret = @tx_rnbase_util_DB::doSelect(
                    'tx_mklib_lastrun',
                    'tx_scheduler_task',
                    $options
                );
            } catch (Exception $e) {
                $ret = null;
            }
            $this->lastRun = (
                    empty($ret)
                    || empty($ret[0]['tx_mklib_lastrun'])
                    || '0000-00-00 00:00:00' === $ret[0]['tx_mklib_lastrun']
                ) ? null : new DateTime($ret[0]['tx_mklib_lastrun']);
        }

        return $this->lastRun;
    }

    /**
     * updates the lastrun time with the current time.
     *
     * @return int
     */
    protected function setLastRunTime()
    {
        try {
            $lastRun = new DateTime();
            $return = @tx_rnbase_util_DB::doUpdate(
                'tx_scheduler_task',
                'uid='.(int) $this->getTaskUid(),
                array(
                    'tx_mklib_lastrun' => $lastRun->format('Y-m-d H:i:s'),
                )
            );
        } catch (Exception $e) {
            $return = 0;
        }

        return $return;
    }

    /**
     * sends a exception mail.
     *
     * @param string    $email
     * @param Exception $exception
     */
    protected function sendErrorMail($email, Exception $exception)
    {
        $options = array('ignoremaillock' => true);
        tx_rnbase_util_Misc::sendErrorMail($email, get_class($this), $exception, $options);
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_Generic.php']) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_Generic.php'];
}
