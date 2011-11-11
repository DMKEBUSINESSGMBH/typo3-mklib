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

$extensionPath = t3lib_extMgm::extPath('mklib');
return array(
	'tx_mklib_exception_invalidconfiguration' => $extensionPath . 'exception/class.tx_mklib_exception_InvalidConfiguration.php',
	'tx_mklib_interface_izipcountry' => $extensionPath . 'interface/class.tx_mklib_interface_IZipCountry.php',
	'tx_mklib_model_staticcountry' => $extensionPath . 'model/class.tx_mklib_model_StaticCountry.php',
	'tx_mklib_treelib_config' => $extensionPath . 'treelib/class.tx_mklib_treelib_Config.php',
	'tx_mklib_treelib_renderer' => $extensionPath . 'treelib/class.tx_mklib_treelib_Renderer.php',
	'tx_mklib_treelib_tce' => $extensionPath . 'treelib/class.tx_mklib_treelib_TCE.php',
	'tx_mklib_treelib_treeview' => $extensionPath . 'treelib/class.tx_mklib_treelib_TreeView.php',
	'tx_mklib_util_array' => $extensionPath . 'util/class.tx_mklib_util_Array.php',
	'tx_mklib_util_dam' => $extensionPath . 'util/class.tx_mklib_util_DAM.php',
	'tx_mklib_util_db' => $extensionPath . 'util/class.tx_mklib_util_DB.php',
	'tx_mklib_util_date' => $extensionPath . 'util/class.tx_mklib_util_Date.php',
	'tx_mklib_util_easykonto' => $extensionPath . 'util/class.tx_mklib_util_EasyKonto.php',
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
	'tx_mklib_validator_easykonto' => $extensionPath . 'validator/class.tx_mklib_validator_EasyKonto.php',
	'tx_mklib_validator_wordlist' => $extensionPath . 'validator/class.tx_mklib_validator_WordList.php',
	'tx_mklib_validator_zipcode' => $extensionPath . 'validator/class.tx_mklib_validator_ZipCode.php',
);
