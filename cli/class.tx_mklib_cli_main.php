<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_cli
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.    See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * benötigte Klassen einbinden
 */
if (!defined('TYPO3_cliMode')) {
    die('You cannot run this script directly!');
}
tx_rnbase::load('Tx_Rnbase_CommandLine_Controller');

/**
 * tx_mklib_cli_main
 *
 * @package         TYPO3
 * @subpackage      mklib
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_cli_main extends Tx_Rnbase_CommandLine_Controller
{

    /**
     * @var array $commands
     */
    protected $commands = array(
        'flush' => array(
            'short' => array(
                'desc' => 'The same as --flush-cache',
                'name' => '-fc',
            ),
            'long' => array(
                'desc' => 'Clear the whole TYPO3 Cache',
                'name' => '--flush-cache',
            ),
        ),
        'help' => array(
            'short' => array(
                'desc' => 'The same as --help',
                'name' => '-h',
            ),
            'long' => array(
                'desc' => 'Show possible options',
                'name' => '--help',
            ),
        )
    );

    /**
     * @return tx_mklib_cli_main
     */
    public function __construct()
    {
        //der CLI-User darf kein Admin sein aber es werden Adminrechte benötigt
        //um den Cache zu leeren --> also setzen wir hart dass wir Admin sind
        $GLOBALS['BE_USER']->user['admin'] = true;

        parent::__construct();

        $this->cli_options = array_merge($this->cli_options, array());

        $this->cli_options[] = array($this->commands['flush']['long']['name'], $this->commands['flush']['long']['desc']);
        $this->cli_options[] = array($this->commands['flush']['short']['name'], $this->commands['flush']['short']['desc']);
        $this->cli_options[] = array($this->commands['help']['long']['name'], $this->commands['help']['long']['desc']);
        $this->cli_options[] = array($this->commands['help']['short']['name'], $this->commands['help']['short']['desc']);

        $this->cli_help = array_merge($this->cli_help, array(
            'name' => 'MKLib CLI',
                'synopsis' => $this->extKey . ' command ###OPTIONS###',
                'description' => 'Classes with several functions. Check the --help or documentation which are available.',
                'examples' => 'typo3/cli_dispatch.phpsh mklib --flush-cache',
                'author' => '(c) 2015 DMK E-BUSINESS GmbH Hannes Bochmann <dev@dmk-ebusiness.de>',
        ));
    }

    /**
     * Error-Funktion, die aufgerufen wird wenn ein unbekannter Task übergeben wurde
     *
     * @param string $error
     */
    public function exitWithError($error = 'Unknown Task!')
    {
        $this->cli_echo("ERROR:\t" . $error . chr(10) . 'Show help with [-h] or [--help]' . chr(10));
        $this->cli_validateArgs();
        exit;
    }

    /**
     * CLI engine
     *
     * @return  string
     */
    public function main()
    {
        $commandFound = false;

        if ($this->cli_isArg($this->commands['flush']['short']['name']) ||
            $this->cli_isArg($this->commands['flush']['long']['name'])
        ) {
            $this->flushCache();
            $commandFound = true;
            $commandDescription = $this->commands['flush']['long']['desc'];
        }

        if ($this->cli_isArg($this->commands['help']['short']['name']) ||
            $this->cli_isArg($this->commands['help']['long']['name'])
        ) {
            $this->cli_help();
            $commandFound = true;
            $commandDescription = $this->commands['help']['long']['desc'];
        }

        if (!$commandFound) {
            $this->exitWithError();
        } else {
            $this->cli_echo(utf8_decode(
                "Command '" . $commandDescription . "' executed successfully.\n"
            ));
        }
    }

    /**
     * @return void
     */
    public function flushCache()
    {
        tx_rnbase::load('tx_rnbase_util_TYPO3');
        if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
            $this->flushCacheForTypo6AndHigher();
        } else {
            $this->flushCacheForTypo4AndLower();
        }
    }

    /**
     * @return void
     */
    private function flushCacheForTypo6AndHigher()
    {
        $commonCacheDirectory = tx_rnbase_util_TYPO3::isTYPO87OrHigher() ? 'typo3temp/var/Cache' : 'typo3temp/Cache';
        \TYPO3\CMS\Core\Utility\GeneralUtility::flushDirectory(PATH_site . $commonCacheDirectory, true, true);

        $cacheManager = tx_rnbase::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
        $cacheManager->flushCaches();

        if (tx_rnbase_util_TYPO3::isTYPO76OrHigher()) {
            tx_rnbase::makeInstance('TYPO3\\CMS\\Core\\Service\\OpcodeCacheService')->clearAllActive();
        } else {
            \TYPO3\CMS\Core\Utility\OpcodeCacheUtility::clearAllActive();
        }
    }

    /**
     * @return void
     */
    private function flushCacheForTypo4AndLower()
    {
        $tce = t3lib_div::makeInstance('t3lib_TCEmain');

        $tce->start(array(), array());
        $tce->clear_cacheCmd('all');
        $tce->clear_cacheCmd('temp_CACHED');
    }
}

if (defined('TYPO3_cliMode')) {
    tx_rnbase::makeInstance('tx_mklib_cli_main')->main();
}
