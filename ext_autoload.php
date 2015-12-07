<?php
// generate automatically by extdeveval::buildAutoloadRegistry.
/*
 * Seit Version 4.3 bietet TYPO3 einen Autoloader der sehr einfach einzusetzen ist.
 * In dieser Datei machen wir nichts weiteres als ein assoziatives Array zurückzugeben.
 *
 * Die grösste Fehlerquelle dabei ist, dass man darauf achten muss,
 * den Schlüssel des assoziativen Arrays klein zu schreiben.
 * Verwendet man den korrekten Klassennamen, also camel case,
 * wird die Klasse nicht eingebunden.
 *
 * Um die Datei automatisch generieren zu lassen, kann die Extension extdeveval genutzt werden.
 */

$extensionPath = tx_rnbase_util_Extensions::extPath('mklib');
return array(
	// exceptions
	'tx_mklib_exception_invalidconfiguration' => $extensionPath . 'exception/class.tx_mklib_exception_InvalidConfiguration.php',

	// interfaces
	'tx_mklib_interface_izipcountry' => $extensionPath . 'interface/class.tx_mklib_interface_IZipCountry.php',

	// models
	'tx_mklib_model_staticcountry' => $extensionPath . 'model/class.tx_mklib_model_StaticCountry.php',

	// treelib
	'tx_mklib_treelib_config' => $extensionPath . 'treelib/class.tx_mklib_treelib_Config.php',
	'tx_mklib_treelib_renderer' => $extensionPath . 'treelib/class.tx_mklib_treelib_Renderer.php',
	'tx_mklib_treelib_tce' => $extensionPath . 'treelib/class.tx_mklib_treelib_TCE.php',
	'tx_mklib_treelib_treeview' => $extensionPath . 'treelib/class.tx_mklib_treelib_TreeView.php',

	// utils
	'tx_mklib_util_array' => $extensionPath . 'util/class.tx_mklib_util_Array.php',
	'tx_mklib_util_dam' => $extensionPath . 'util/class.tx_mklib_util_DAM.php',
	'tx_mklib_util_media' => $extensionPath . 'util/class.tx_mklib_util_Media.php',
	'tx_mklib_util_db' => $extensionPath . 'util/class.tx_mklib_util_DB.php',
	'tx_mklib_util_date' => $extensionPath . 'util/class.tx_mklib_util_Date.php',
	'tx_mklib_util_file' => $extensionPath . 'util/class.tx_mklib_util_File.php',
	'tx_mklib_util_misctools' => $extensionPath . 'util/class.tx_mklib_util_MiscTools.php',
	'tx_mklib_util_model' => $extensionPath . 'util/class.tx_mklib_util_Model.php',
	'tx_mklib_util_rtfgenerator' => $extensionPath . 'util/class.tx_mklib_util_RTFGenerator.php',
	'tx_mklib_util_rtfparser' => $extensionPath . 'util/class.tx_mklib_util_RTFParser.php',
	'tx_mklib_util_searchsorting' => $extensionPath . 'util/class.tx_mklib_util_SearchSorting.php',
	'tx_mklib_util_serviceregistry' => $extensionPath . 'util/class.tx_mklib_util_ServiceRegistry.php',
	'tx_mklib_util_staticcache' => $extensionPath . 'util/class.tx_mklib_util_StaticCache.php',
	'tx_mklib_util_string' => $extensionPath . 'util/class.tx_mklib_util_String.php',
	'tx_mklib_util_tca' => $extensionPath . 'util/class.tx_mklib_util_TCA.php',
	'tx_mklib_util_ts' => $extensionPath . 'util/class.tx_mklib_util_TS.php',
	'tx_mklib_util_var' => $extensionPath . 'util/class.tx_mklib_util_Var.php',
	'tx_mklib_util_Debug' => $extensionPath . 'util/class.tx_mklib_util_Debug.php',

	// validatoren
	'tx_mklib_validator_wordlist' => $extensionPath . 'validator/class.tx_mklib_validator_WordList.php',
	'tx_mklib_validator_zipcode' => $extensionPath . 'validator/class.tx_mklib_validator_ZipCode.php',

	// scheduler
	'tx_mklib_scheduler_cleanuptempfiles' => $extensionPath . 'scheduler/class.tx_mklib_scheduler_cleanupTempFiles.php',
	'tx_mklib_scheduler_cleanuptempfilesfieldprovider' => $extensionPath . 'scheduler/class.tx_mklib_scheduler_cleanupTempFilesFieldProvider.php',
	'tx_mklib_scheduler_emailfieldprovider' => $extensionPath . 'scheduler/class.tx_mklib_scheduler_EmailFieldProvider.php',
	'tx_mklib_scheduler_generic' => $extensionPath . 'scheduler/class.tx_mklib_scheduler_Generic.php',
	'tx_mklib_scheduler_genericfieldprovider' => $extensionPath . 'scheduler/class.tx_mklib_scheduler_GenericFieldProvider.php',
	'tx_mklib_scheduler_schedulertaskfreezedetection' => $extensionPath . 'scheduler/class.tx_mklib_scheduler_SchedulerTaskFreezeDetection.php',
	'tx_mklib_scheduler_schedulertaskfreezedetectionfieldprovider' => $extensionPath . 'scheduler/class.tx_mklib_scheduler_SchedulerTaskFreezeDetectionFieldProvider.php',
	'tx_mklib_scheduler_schedulertaskfaildetection' => $extensionPath . 'scheduler/class.tx_mklib_scheduler_SchedulerTaskFailDetection.php',
	'tx_mklib_scheduler_schedulertaskfaildetectionfieldprovider' => $extensionPath . 'scheduler/class.tx_mklib_scheduler_SchedulerTaskFailDetectionFieldProvider.php',
	'tx_mklib_scheduler_deletefromdatabase' => $extensionPath . 'scheduler/class.tx_mklib_scheduler_DeleteFromDatabase.php',
	'tx_mklib_scheduler_deletefromdatabasefieldprovider' => $extensionPath . 'scheduler/class.tx_mklib_scheduler_DeleteFromDatabaseFieldProvider.php',
);
