<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

/**
 * Util Methoden für das TS, speziell im BE
 * @author	Hannes Bochmann
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_TS {


  	/**
   	 * Lädt ein COnfigurations Objekt nach mit der TS aus der Extension
   	 * Dabei wird alles geholt was in "plugin.tx_$extKey", "lib.$extKey." und
   	 * "lib.links." liegt
   	 * @param string $extKey 	| 	Extension, deren TS Config geladen werden soll
   	 * @param string $extKeyTS 	|	Extension, deren Konfig innerhalb der TS Config geladen werden soll.
   	 * 								es kann also zb. das TS von mklib geladen werden aber darin die konfig für
   	 * 								das plugin von mkxyz
   	 * @param string $sStaticPath | pfad zum TS
   	 * @param array $aConfig | zusätzliche Konfig, die die default Konfig überschreibt
   	 * @param boolean $resolveReferences | sollen referenzen die in lib. und plugin.tx_$extKeyTS stehen aufgelöst werden?
   	 * @param boolean $forceTsfePreparation
   	 * 
   	 * @return tx_rnbase_configurations
   	 */
  	public static function loadConfig4BE(
  		$extKey, $extKeyTS = null, $sStaticPath = '', $aConfig = array(), $resolveReferences = false,
  		$forceTsfePreparation = false
  	) {
  		$extKeyTS = is_null($extKeyTS) ? $extKey : $extKeyTS;

  		if(!$sStaticPath) {
  			$sStaticPath = '/static/ts/setup.txt';	
  		}
  		
  		if(file_exists(t3lib_div::getFileAbsFileName('EXT:'.$extKey.$sStaticPath))) {
	    	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$extKey.$sStaticPath.'">');
  		}

	    tx_rnbase::load('tx_rnbase_configurations');
	    tx_rnbase::load('tx_rnbase_util_Misc');

	    $tsfePreparationOptions = array();
	    if($forceTsfePreparation) {
	    	$tsfePreparationOptions['force'] = true;
	    }
	    tx_rnbase_util_Misc::prepareTSFE($tsfePreparationOptions); // Ist bei Aufruf aus BE notwendig!
	    $GLOBALS['TSFE']->config = array();
	    $cObj = t3lib_div::makeInstance('tslib_cObj');

	    $pageTSconfig = t3lib_BEfunc::getPagesTSconfig(0);

	    $tempConfig = $pageTSconfig['plugin.']['tx_'.$extKeyTS.'.'];
	    $tempConfig['lib.'][$extKeyTS.'.'] = $pageTSconfig['lib.'][$extKeyTS.'.'];
	    $tempConfig['lib.']['links.'] = $pageTSconfig['lib.']['links.'];
	    
	    if($resolveReferences) {
	    	$GLOBALS['TSFE']->tmpl->setup['lib.'][$extKeyTS . '.'] = 
	    		$tempConfig['lib.'][$extKeyTS . '.'];
	    	$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_'.$extKeyTS.'.'] =
	    		$pageTSconfig['plugin.']['tx_'.$extKeyTS.'.'];
	    }

	    $pageTSconfig = $tempConfig;

	    $qualifier = $pageTSconfig['qualifier'] ? $pageTSconfig['qualifier'] : $extKeyTS;

	    //möglichkeit die default konfig zu überschreiben
	    $pageTSconfig = t3lib_div::array_merge_recursive_overrule($pageTSconfig,$aConfig);

	    $configurations = new tx_rnbase_configurations();
	    $configurations->init($pageTSconfig, $cObj, $extKeyTS, $qualifier);

	  	return $configurations;
  	}

  	/**
  	 * @TODO: static caching integrieren!?
  	 *
  	 * @param 	mixed 		$iPageUid		alias or uid
  	 * @return 	tx_rnbase_configurations
  	 *
	 * @author Michael Wagner
  	 */
  	public static function loadTSFromPage($mPageUid=0, $sExtKey='mklib'){
  		// rootlines der pid auslesen
  		/* @var $sysPageObj t3lib_pageSelect */
		$sysPageObj = tx_rnbase::makeInstance('t3lib_pageSelect');
		$aRootLine = $sysPageObj->getRootLine(
						// wenn ein alias übergeben wurde, müssen wir uns die uid besorgen
						is_numeric($mPageUid) ? intval($mPageUid) : $sysPageObj->getPageIdFromAlias($mPageUid)
					);

		// ts für die rootlines erzeugen
  		/* @var $TSObj t3lib_tsparser_ext */
		$TSObj = tx_rnbase::makeInstance('t3lib_tsparser_ext');
		$TSObj->tt_track = 0;
		$TSObj->init();
		$TSObj->runThroughTemplates($aRootLine);
		$TSObj->generateConfig();

		// tsfe config setzen (wird in der tx_rnbase_configurations gebraucht (language))
	    if(!is_array($GLOBALS['TSFE']->config))
	    	$GLOBALS['TSFE']->config = $TSObj->setup['config.'];

        // tsfe config setzen (ansonsten funktionieren refereznen nicht (fpdf <= lib.fpdf))
		// @TODO: Konfigurierbar machen
	    $GLOBALS['TSFE']->tmpl->setup = array_merge($TSObj->setup, $GLOBALS['TSFE']->tmpl->setup);
//	    if(!is_array($GLOBALS['TSFE']->setup)) // @TODO: müssen wir das in die tsfe speichern?
//	    	$GLOBALS['TSFE']->setup = $TSObj->setup;


	    // ts für die extension auslesen
		$pageTSconfig = $TSObj->setup['plugin.']['tx_'.$sExtKey.'.'];
	    $pageTSconfig['lib.'] = $pageTSconfig['lib.']; // libs mit nehmen
	    $qualifier = $pageTSconfig['qualifier'] ? $pageTSconfig['qualifier'] : $sExtKey;

	    // konfiguration erzeugen
  		/* @var $configurations tx_rnbase_configurations */
	    $configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
	    $configurations->init($pageTSconfig, $configurations->getCObj(1), $sExtKey, $qualifier);

		return $configurations;
  	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_TS.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_TS.php']);
}