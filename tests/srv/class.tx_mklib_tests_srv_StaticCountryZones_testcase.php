<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_srv
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_util_ServiceRegistry');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_srv
 */
class tx_mklib_tests_srv_StaticCountryZones_testcase extends tx_phpunit_testcase {
	
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		if(!t3lib_extMgm::isLoaded('static_info_tables')) {
			$this->markTestSkipped('static_info_tables nicht installiert.');
		}
	}
	
	/**
	 * @group integration
	 */
	public function testGetByZnCode(){
		$service = tx_mklib_util_ServiceRegistry::getStaticCountryZonesService();
		$models = $service->getByZnCode('al');//Alabama
		$model = $models[0];

		$this->assertInstanceOf(
			'tx_mklib_model_StaticCountryZone', $model,
			'Statemodel hat falsche Klasse'
		);
		$this->assertEquals(
			'Alabama',
			$model->getZnNameLocal(), 
			'Bundesland falsch.'
		);
	}
}