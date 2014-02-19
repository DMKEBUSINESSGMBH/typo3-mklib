<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Hannes Bochmann <kontakt@das-medienkombinat.de>
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
 ***************************************************************/

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_srv_Base');

/**
 * @author Hannes Bochmann
 *
 */
class tx_mklib_srv_StaticCountries extends tx_mklib_srv_Base {

	/**
	 * @param int $isoNumber
	 * 
	 * @return null || tx_mklib_model_StaticCountry
	 */
	public function getCountryByIsoNr($isoNumber){
		$options = array();
		
		$fields = array(
		  	'STATICCOUNTRY.cn_iso_nr' => array(OP_EQ_INT => $isoNumber)
	    );
	    
	    return $this->searchSingle($fields, $options);
	}
	
	/**
	 * @param string $germanShortName
	 * 
	 * @return null || tx_mklib_model_StaticCountry
	 */
	public function getCountryByGermanShortName($germanShortName){
		$options = array();
		
		$fields = array(
		  	'STATICCOUNTRY.cn_short_de' => array(OP_EQ => $germanShortName)
	    );
	    
	    return $this->searchSingle($fields, $options);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_srv_Base::search()
	 */
	public function search($fields, $options) {
		// TCA gibt es nicht
		$options['enablefieldsoff'] = true;

		return parent::search($fields, $options);
	}
	
	
	/**
	 * @return string
	 */
	public function getSearchClass(){
		return 'tx_mklib_search_StaticCountries';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_srv_Base::create()
	 */
	public function create(array $data) {
		tx_rnbase::load('tx_rnbase_util_Debug');
		tx_rnbase_util_Debug::debug(array(
			'creating a static country via the service can\'t be done.'
		),__METHOD__.' Line: '.__LINE__); // @TODO: remove me
	}

	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_srv_Base::handleUpdate()
	 */
	public function handleUpdate(tx_rnbase_model_base $model, array $data, $where='') {
		tx_rnbase::load('tx_rnbase_util_Debug');
		tx_rnbase_util_Debug::debug(array(
			'updating a static country via the service can\'t be done.'
		),__METHOD__.' Line: '.__LINE__); // @TODO: remove me
	}


	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_srv_Base::handleDelete()
	 */
	public function handleDelete(tx_rnbase_model_base $model, $where='', $mode=0, $table=null) {
		tx_rnbase::load('tx_rnbase_util_Debug');
		tx_rnbase_util_Debug::debug(array(
			'deleting a static country via the service can\'t be done.'
		),__METHOD__.' Line: '.__LINE__); // @TODO: remove me
	}

	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_srv_Base::handleCreation()
	 */
	public function handleCreation(array $data){
		tx_rnbase::load('tx_rnbase_util_Debug');
		tx_rnbase_util_Debug::debug(array(
			'creating a static country via the service can\'t be done.'
		),__METHOD__.' Line: '.__LINE__); // @TODO: remove me
	}
}