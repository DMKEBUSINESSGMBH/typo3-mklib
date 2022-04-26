<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

// Register information for the test and sleep tasks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_mklib_scheduler_cleanupTempFiles'] = [
    'extension' => 'mklib',
    'title' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_cleanupTempFiles_name',
    'description' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_cleanupTempFiles_taskinfo',
    'additionalFields' => 'tx_mklib_scheduler_cleanupTempFilesFieldProvider',
];

// prüft ob scheduler schon seit längerer zeit hängen
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_mklib_scheduler_SchedulerTaskFreezeDetection'] = [
    'extension' => 'mklib',
    'title' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_name',
    'description' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_taskinfo',
    'additionalFields' => 'tx_mklib_scheduler_SchedulerTaskFreezeDetectionFieldProvider',
];

// prüft ob scheduler schon seit längerer zeit hängen
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_mklib_scheduler_DeleteFromDatabase'] = [
    'extension' => 'mklib',
    'title' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_DeleteFromDatabase_name',
    'description' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_DeleteFromDatabase_taskinfo',
    'additionalFields' => 'tx_mklib_scheduler_DeleteFromDatabaseFieldProvider',
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_mklib_scheduler_SchedulerTaskFailDetection'] = [
    'extension' => 'mklib',
    'title' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFailDetection_name',
    'description' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFailDetection_taskinfo',
    'additionalFields' => 'tx_mklib_scheduler_SchedulerTaskFailDetectionFieldProvider',
];
