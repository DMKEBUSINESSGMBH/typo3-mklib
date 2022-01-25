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
abstract class tx_mklib_scheduler_Generic extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{
    /**
     * The DateTime Object with the last run time.
     *
     * @var DateTime
     */
    protected $lastRun = false;

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
     * Function executed from the Scheduler.
     *
     * @return bool Returns true on successful execution, false on error
     */
    public function execute()
    {
        /* beispiel für das logging array.
        $devLog = array('message' => '', 'extKey' => 'mklib', 'dataVar' => FALSE);
        $devLog = array(
            \Sys25\RnBase\Utility\Logger::LOGLEVEL_DEBUG => $devLog,
            \Sys25\RnBase\Utility\Logger::LOGLEVEL_INFO => $devLog,
            \Sys25\RnBase\Utility\Logger::LOGLEVEL_NOTICE => $devLog,
            \Sys25\RnBase\Utility\Logger::LOGLEVEL_WARN => $devLog,
            \Sys25\RnBase\Utility\Logger::LOGLEVEL_FATAL => $devLog
        );
        */
        $devLog = [];
        $options = $this->getOptions();
        $startTimeInMilliseconds = \Sys25\RnBase\Utility\Misc::milliseconds();
        $memoryUsageAtStart = memory_get_usage();

        \Sys25\RnBase\Utility\Logger::info(
            '['.get_class($this).']: Scheduler starts',
            $this->getExtKey()
        );

        try {
            $message = $this->executeTask($options, $devLog);

            $this->setLastRunTime();

            // devlog
            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('devlog')) {
                if (// infolog setzen, wenn devlog leer
                    empty($devLog)
                    // infolog setzen, wenn infolog gesetzt, aber keine message vorhanden ist
                    || (
                            isset($devLog[\Sys25\RnBase\Utility\Logger::LOGLEVEL_INFO])
                            && empty($devLog[\Sys25\RnBase\Utility\Logger::LOGLEVEL_INFO]['message'])
                        )
                    ) {
                    $devLog[\Sys25\RnBase\Utility\Logger::LOGLEVEL_INFO]['message'] = $message;
                }

                foreach ($devLog as $logLevel => $logData) {
                    if (empty($logData['message'])) {
                        continue;
                    }
                    \Sys25\RnBase\Utility\Logger::devLog(
                        '['.get_class($this).']: '.$logData['message'],
                        isset($logData['extKey']) ? $logData['extKey'] : $this->getExtKey(),
                        $logLevel,
                        isset($logData['dataVar']) ? $logData['dataVar'] : false
                    );
                }
            }
        } catch (Exception $exception) {
            $dataVar = [
                'errorcode' => $exception->getCode(),
                'errormsg' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'options' => $options,
                // bisherige logs mitgeben
                'devlog' => $devLog,
            ];
            if ($exception instanceof \Sys25\RnBase\Exception\AdditionalException) {
                $dataVar['exception_data'] = $exception->getAdditional(false);
            }
            if (\Sys25\RnBase\Utility\Logger::isFatalEnabled()) {
                \Sys25\RnBase\Utility\Logger::fatal(
                    'Task ['.get_class($this).'] failed.'.
                        ' Error('.$exception->getCode().'):'.
                        $exception->getMessage(),
                    $this->getExtKey(),
                    $dataVar
                );
            }
            // Exception Mail an die Entwicker senden
            $mail = \Sys25\RnBase\Configuration\Processor::getExtensionCfgValue(
                'rn_base',
                'sendEmailOnException'
            );
            if (!empty($mail)) {
                $this->sendErrorMail(
                    $mail,
                    // Wir erstellen eine weitere Exception mit zusätzlichen Daten.
                    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                        \Sys25\RnBase\Exception\AdditionalException::class,
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
        \Sys25\RnBase\Utility\Logger::info(
            '['.get_class($this).']: Scheduler ends successful ',
            $this->getExtKey(),
            [
                'Execution Time' => (\Sys25\RnBase\Utility\Misc::milliseconds() - $startTimeInMilliseconds).' ms',
                'Memory Start' => $memoryUsageAtStart.' Bytes',
                'Memory End' => $memoryUsageAtEnd.' Bytes',
                'Memory Consumed' => ($memoryUsageAtEnd - $memoryUsageAtStart).' Bytes',
            ]
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
        foreach ($this->getOptions() as $key => $value) {
            $info .= CRLF.$key.': '.$value;
        }

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
        return is_array($this->schedulerOptions) ? $this->schedulerOptions : [];
    }

    /**
     * gets the last run time.
     *
     * @return DateTime|null
     */
    protected function getLastRunTime()
    {
        if (false === $this->lastRun) {
            $options = [];
            $options['enablefieldsoff'] = 1;
            $options['where'] = 'uid='.(int) $this->getTaskUid();
            $options['limit'] = 1;
            try {
                $ret = @\Sys25\RnBase\Database\Connection::getInstance()->doSelect(
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
            $return = @\Sys25\RnBase\Database\Connection::getInstance()->doUpdate(
                'tx_scheduler_task',
                'uid='.(int) $this->getTaskUid(),
                [
                    'tx_mklib_lastrun' => $lastRun->format('Y-m-d H:i:s'),
                ]
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
        $options = ['ignoremaillock' => true];
        \Sys25\RnBase\Utility\Misc::sendErrorMail($email, get_class($this), $exception, $options);
    }
}
