<?php
/**
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
if (!tx_rnbase_util_TYPO3::isCliMode()) {
    exit('You cannot run this script directly!');
}

/**
 * tx_mklib_cli_main.
 *
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_cli_main extends Tx_Rnbase_CommandLine_Controller
{
    /**
     * @var array
     */
    protected $commands = [
        'flush' => [
            'short' => [
                'desc' => 'The same as --flush-cache',
                'name' => '-fc',
            ],
            'long' => [
                'desc' => 'Clear the whole TYPO3 Cache',
                'name' => '--flush-cache',
            ],
        ],
        'help' => [
            'short' => [
                'desc' => 'The same as --help',
                'name' => '-h',
            ],
            'long' => [
                'desc' => 'Show possible options',
                'name' => '--help',
            ],
        ],
    ];

    /**
     * @return tx_mklib_cli_main
     */
    public function __construct()
    {
        //der CLI-User darf kein Admin sein aber es werden Adminrechte benötigt
        //um den Cache zu leeren --> also setzen wir hart dass wir Admin sind
        $GLOBALS['BE_USER']->user['admin'] = true;

        parent::__construct();

        $this->cli_options = array_merge($this->cli_options, []);

        $this->cli_options[] = [$this->commands['flush']['long']['name'], $this->commands['flush']['long']['desc']];
        $this->cli_options[] = [$this->commands['flush']['short']['name'], $this->commands['flush']['short']['desc']];
        $this->cli_options[] = [$this->commands['help']['long']['name'], $this->commands['help']['long']['desc']];
        $this->cli_options[] = [$this->commands['help']['short']['name'], $this->commands['help']['short']['desc']];

        $this->cli_help = array_merge($this->cli_help, [
            'name' => 'MKLib CLI',
                'synopsis' => $this->extKey.' command ###OPTIONS###',
                'description' => 'Classes with several functions. Check the --help or documentation which are available.',
                'examples' => 'typo3/cli_dispatch.phpsh mklib --flush-cache',
                'author' => '(c) 2015 DMK E-BUSINESS GmbH Hannes Bochmann <dev@dmk-ebusiness.de>',
        ]);
    }

    /**
     * Error-Funktion, die aufgerufen wird wenn ein unbekannter Task übergeben wurde.
     *
     * @param string $error
     */
    public function exitWithError($error = 'Unknown Task!')
    {
        $this->cli_echo("ERROR:\t".$error.chr(10).'Show help with [-h] or [--help]'.chr(10));
        $this->cli_validateArgs();
        exit;
    }

    /**
     * CLI engine.
     *
     * @return string
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
                "Command '".$commandDescription."' executed successfully.\n"
            ));
        }
    }

    public function flushCache()
    {
        $commonCacheDirectory = 'typo3temp/var/Cache';
        \TYPO3\CMS\Core\Utility\GeneralUtility::flushDirectory(\Sys25\RnBase\Utility\Environment::getPublicPath().$commonCacheDirectory, true, true);

        $cacheManager = tx_rnbase::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
        $cacheManager->flushCaches();

        tx_rnbase::makeInstance('TYPO3\\CMS\\Core\\Service\\OpcodeCacheService')->clearAllActive();
    }
}

if (tx_rnbase_util_TYPO3::isCliMode()) {
    tx_rnbase::makeInstance('tx_mklib_cli_main')->main();
}
