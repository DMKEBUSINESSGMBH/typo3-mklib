<?php
/*
 *
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_filter_SingleItem');

/**
 * @author Hannes Bochmann
 */
class tx_mklib_tests_filter_SingleItem_testcase extends tx_phpunit_testcase{

	/**
	 * @var string
	 */
	private $testParamName = 'johnDoe';
	
	/**
	 * @var string
	 */
	private $testSearchAlias = 'JOHNDOE';
	
	/**
	 * @group unit
	 */
	public function testFilterSetsOptionsAndFieldsCorrectIfNoParameter() {
		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
		$filter = $this->getFilter($parameters);
			
		$fields = array();
		$options = array();
		$filterReturn = $filter->init($fields,$options);
		$this->assertTrue($filterReturn, 'filter gibt nicht true zurück.');
		
		$expectedFields = array(
			$this->testSearchAlias . '.uid' => array(OP_EQ_INT => 0)
		);
		$this->assertEquals($expectedFields, $fields, 'fields nicht leer');
		
		$expectedOptions = array(
			'limit' 	=> 1,
		);
		$this->assertEquals($expectedOptions, $options, 'options nicht richtig gesetzt.');
	}
	
	/**
	 * @group unit
	 */
	public function testFilterSetsOptionsAndFieldsCorrectIfParameterSet() {
		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
		$itemUid = 123;
		$parameters->offsetSet($this->testParamName, $itemUid);
		$filter = $this->getFilter($parameters);
		
		$fields = array();
		$options = array();
		$filterReturn = $filter->init($fields,$options);
		$this->assertTrue($filterReturn, 'filter gibt nicht true zurück.');
		
		$expectedFields = array(
			$this->testSearchAlias . '.uid' => array(OP_EQ_INT => $itemUid)
		);
		$this->assertEquals($expectedFields, $fields, 'fields nicht leer');
		
		$expectedOptions = array(
			'limit' 	=> 1,
		);
		$this->assertEquals($expectedOptions, $options, 'options nicht richtig gesetzt.');
	}
	
	/**
	 * @return tx_mklib_filter_SingleItem
	 */
	private function getFilter($parameters) {
		$configurations = tx_mklib_util_TS::loadConfig4BE('mklib');
		
		$confId = 'doesNotMatter.';
		$filter = $this->getMockForAbstractClass(
			'tx_mklib_filter_SingleItem', array(&$parameters, &$configurations, $confId)
		);
		$filter->expects($this->once())
			->method('getParameterName')
			->will($this->returnValue($this->testParamName));
		$filter->expects($this->once())
			->method('getSearchAlias')
			->will($this->returnValue($this->testSearchAlias));
			
		return $filter;
	}
}