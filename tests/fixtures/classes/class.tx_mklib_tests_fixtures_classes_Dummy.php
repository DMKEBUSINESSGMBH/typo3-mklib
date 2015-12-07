<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_srv
 *  @author Hannes Bochmann
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
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * benötigte Klassen einbinden
 */

tx_rnbase::load('tx_mklib_srv_Base');

/**
 * Dummy Service um uns DB Abfragen zu ersparen
 *
 * @package tx_mklib
 * @subpackage tx_mklib_srv
 */
class tx_mklib_tests_fixtures_classes_Dummy extends tx_mklib_srv_base {

	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_srv_Base::search()
	 */
	public function search($fields, $options){
		if($GLOBALS['emptyTestResult'])
			$aResults = array();
		else
			$aResults = array(
				0 => tx_rnbase::makeInstance('tx_mklib_model_WordlistEntry',array('uid' => 1)),
				1 => tx_rnbase::makeInstance('tx_mklib_model_WordlistEntry',array('uid' => 2)),
				2 => tx_rnbase::makeInstance('tx_mklib_model_WordlistEntry',array('uid' => 3)),
				3 => tx_rnbase::makeInstance('tx_mklib_model_WordlistEntry',array('uid' => 4)),
				4 => tx_rnbase::makeInstance('tx_mklib_model_WordlistEntry',array('uid' => 5)),
			);
		//sortieren?
		if(!empty($options['orderby']))
			rsort($aResults);//reicht um zu sehen ob die Sortierung anspringt

		//versteckte zurück geben?
		if($GLOBALS['BE_USER']->uc['moduleData']['dummyMod']['showhidden'] == 1)
			$aResults[5] = tx_rnbase::makeInstance('tx_mklib_model_WordlistEntry',array('uid' => 6, 'hidden' => 1));

		if($options['count'])
			return count($aResults);
		return $aResults;
	}

  /**
   * Liefert die zugehörige Search-Klasse zurück
   *
   * @return string
   */
  public function getSearchClass(){return 'tx_mklib_search_Wordlist';}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Wordlist.php'])	{
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Wordlist.php']);
}
