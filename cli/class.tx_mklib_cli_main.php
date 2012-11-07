<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_cli
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
if (!defined('TYPO3_cliMode'))  die('You cannot run this script directly!');

// Defining PATH_thisScript here: Must be the ABSOLUTE path of this script in the right context:
// This will work as long as the script is called by it's absolute path!
//define('PATH_thisScript',$_ENV['_']?$_ENV['_']:$_SERVER['_']);

// Include basis cli class
require_once(PATH_t3lib.'class.t3lib_cli.php');

/**
 * Basis-klasse für Funktionalitäten auf dem CLI
 * @author	Hannes Bochmann
 * @package tx_mklib
 * @subpackage tx_mklib_cli
 */
class tx_mklib_cli_main extends t3lib_cli {

	protected $commands = array(
      'flush' => array(
            'short' => array(
                  'desc' => 'Das gleiche wie --flush-cache',
                  'name' => '-fc',
	),
            'long' => array(
                  'desc' => 'Den gesamten TYPO3-Cache löschen.',
                  'name' => '--flush-cache',
	),
	),
      'update' => array(
            'short' => array(
                  'desc' => 'Das gleiche wie --update-ext',
                  'name' => '-up',
	),
            'long' => array(
                  'desc' => 'Eine bestimmte Extension innerhalb von TYPO3 updaten.',
                  'name' => '--update-ext',
	),
	),
      'help' => array(
            'short' => array(
                  'desc' => 'Das gleiche wie --help',
                  'name' => '-h',
	),
            'long' => array(
                  'desc' => 'Die möglichen Optionen anzeigen.',
                  'name' => '--help',
	),
	)
	);

	/**
	 * Constructor
	 */
	public function tx_mklib_cli_main () {
		//dreckiger Hack; der CLI-User darf kein Admin sein aber es werden Adminrechte benötigt
		//um den Cache zu leeren --> also setzen wir hart dass wir Admin sind
		$GLOBALS['BE_USER']->user['admin'] = true;

		// Running parent class constructor
		parent::t3lib_cli();

		$this->cli_options = array_merge($this->cli_options, array());

		$this->cli_options[] = array($this->commands['flush']['long']['name'], $this->commands['flush']['long']['desc']);
		$this->cli_options[] = array($this->commands['flush']['short']['name'], $this->commands['flush']['short']['desc']);
		//        $this->cli_options[] = array($this->commands['update']['long']['name'], $this->commands['update']['long']['desc']);
		//        $this->cli_options[] = array($this->commands['update']['short']['name'], $this->commands['update']['short']['desc']);
		$this->cli_options[] = array($this->commands['help']['long']['name'], $this->commands['help']['long']['desc']);
		$this->cli_options[] = array($this->commands['help']['short']['name'], $this->commands['help']['short']['desc']);

		$this->cli_help = array_merge($this->cli_help, array(
      'name' => 'MKLib CLI',
      'synopsis' => $this->extKey . ' command ###OPTIONS###',
      'description' => 'Klasse mit Basisfunktionen um den Cache von Extensions zu löschen und selbige zu updaten!',
      'examples' => 'typo3/cli_dispatch.phpsh mklib --flush-cache',
      'author' => '(c) 2010 das MedienKombinat GmbH Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>',
		));
	}

	/**
	 * Error-Funktion, die aufgerufen wird wenn ein unbekannter Task übergeben wurde
	 *
	 * @param string $error
	 */
	public function exitWithError($error = 'Unbekannter Task!'){
		$this->cli_echo("ERROR:\t".$error.chr(10).'Hilfe mit [-h] oder [--help] anzeigen lassen'.chr(10));
		$this->cli_validateArgs();
		//$this->cli_help();
		exit;
	}

	/**
	 * CLI engine
	 *
	 * @param    array        Command line arguments
	 * @return    string
	 */
	public function cli_main($argv) {
		$commandFound = false;

		if ($this->cli_isArg($this->commands['flush']['short']['name']) || $this->cli_isArg($this->commands['flush']['long']['name'])){
			$this->flushCache();
			$commandFound = true;
		}

		if ($this->cli_isArg($this->commands['update']['short']['name']) || $this->cli_isArg($this->commands['update']['long']['name'])){
			$this->updateExtension($this->cli_isArg($this->commands['update']['short']['name']),$this->cli_isArg($this->commands['update']['long']['name']));
			$commandFound = true;
		}

		if ($this->cli_isArg($this->commands['help']['short']['name']) || $this->cli_isArg($this->commands['help']['long']['name'])){
			$this->cli_help();
			$commandFound = true;
		}

		if(!$commandFound){
			$this->exitWithError();
		} else {
			print_r("Befehl '".$this->commands['flush']['long']['desc']."' erfolgreich ausgeführt.");
		}
			
	}

	/**
	 * flushCache was über CLI aufgerufen werden kann
	 *
	 */
	public function flushCache(){
		require_once (PATH_t3lib."class.t3lib_tcemain.php");
		$tce = t3lib_div::makeInstance('t3lib_TCEmain');

		$tce->start(Array(),Array());
		$tce->clear_cacheCmd('all');
		$tce->clear_cacheCmd('temp_CACHED');
	}

	/**
	 * updateExtension was über CLI aufgerufen werden kann
	 * TODO: funktioniert so noch nicht
	 */
	public function updateExtension($short,$long){
		$this->exitWithError('Update von Extensions nocht nicht implementiert und nicht zwangsläufig notwendig!');
		//@TODO notwendig extensions zu updaten?
		if($short)$command = 'short';
		elseif($long) $command = 'long';

		$extensions =  $this->cli_argValue($this->commands['update'][$command]['name']);

		if(!$extensions) $this->exitWithError('Bitte gib eine Extension an, die geupdatet werden soll!');

		require_once(PATH_typo3.'init.php');
		require_once(PATH_typo3.'template.php');
		require_once(PATH_typo3.'mod/tools/em/class.em_index.php');

		// Make instance:
		$SOBE = t3lib_div::makeInstance('SC_mod_tools_em_index');
		$SOBE->init();
		$SOBE->forceDBupdates('tx_mkmarketplace');
	}
}

// Call the functionality
$cleanerObj = t3lib_div::makeInstance('tx_mklib_cli_main');
$cleanerObj->cli_main($_SERVER['argv']);

?>