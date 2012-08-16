<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@das-medienkombinat.de>
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
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_ExtensionConfiguration_testcase extends tx_phpunit_testcase {
	
	public function testGetExtensionCfgValue(){
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dummyExtension'] = serialize(
			array('testConfig' => 'testConfigValue')
		);
		
		$extensionConfiguration = 
			tx_rnbase::makeInstance('tx_mklib_util_ExtensionConfigurationTest');
			
		$testConfigValue = $extensionConfiguration->getTestConfig();

		$this->assertEquals('testConfigValue', $testConfigValue);
	}
}

tx_rnbase::load('tx_mklib_util_ExtensionConfiguration');
class tx_mklib_util_ExtensionConfigurationTest extends  tx_mklib_util_ExtensionConfiguration{
	
	/**
	 * @var string
	 */
	protected $extKey = 'dummyExtension';
	
	public function getTestConfig() {
		return $this->getExtensionCfgValue('testConfig');
	}
}