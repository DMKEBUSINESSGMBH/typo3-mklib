<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (nitzsche@das-medienkombinat.de)
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
require_once(PATH_t3lib.'class.t3lib_tceforms.php');


class tx_mklib_tests_hooks_t3lib_tceforms_getSingleFieldClass_testcase extends tx_phpunit_testcase {

	protected $oTceForms;
	
	public function setUp() {
		//Fake TCA tabelle für die Tests
		//da es diese nicht in der DB gibt, kommt es zu Fehlern
		//bei der Ausführung. Das ist aber vollkommen irrelevant
		//da wir nichts in Bezug auf die DB testen.
		global $TCA;
		$TCA['tx_mklib_getSingleFieldTest'] = Array (
			'ctrl' => array (
				'title'     => 'tx_mklib_getSingleFieldTest',
				'readOnly' => 1,
				'is_static' => 1
			),
			'columns' => Array (
				'inputNotRequired' => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'input',
					)
				),
				'selectNotRequired1' => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
					)
				),
				'selectNotRequired2' => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'required'
					)
				),
				'selectNotRequired3' => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'maxitems' => 1
					)
				),
				'selectNotRequired4' => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'minitems' => 1
					)
				),
				'selectNotRequired5' => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'required',
						'maxitems' => 1
					)
				),
				'selectRequired1' => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'required',
						'maxitems' => 1,
						'minitems' => 1
					)
				),
				'selectRequired2' => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'int,required',
						'maxitems' => 1,
						'minitems' => 1
					)
				),
				'selectRequired3' => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'required,int',
						'maxitems' => 1,
						'minitems' => 1
					)
				),
				'selectRequired4' => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'int,required,trim',
						'maxitems' => 1,
						'minitems' => 1
					)
				),
			)
		);
		//damit alles auf der CLI funktioniert
		$GLOBALS['BE_USER']->user['admin'] = 1;
		
		//tceforms initialisieren
		$this->oTceForms = t3lib_div::makeInstance('t3lib_tceforms');
	}
	
	
	/**
	 * Wird aus start- und enddatum ein datetime gemacht?
	 * Enter description here ...
	 */
	public function testAddingRequiredEvalForSelects() {
		//testdaten
		$row = array('uid' => 1);
		$this->oTceForms->getSingleField('tx_mklib_getSingleFieldTest', 'inputNotRequired', $row);
		$this->oTceForms->getSingleField('tx_mklib_getSingleFieldTest', 'selectNotRequired1', $row);
		$this->oTceForms->getSingleField('tx_mklib_getSingleFieldTest', 'selectNotRequired2', $row);
		$this->oTceForms->getSingleField('tx_mklib_getSingleFieldTest', 'selectNotRequired3', $row);
		$this->oTceForms->getSingleField('tx_mklib_getSingleFieldTest', 'selectNotRequired4', $row);
		$this->oTceForms->getSingleField('tx_mklib_getSingleFieldTest', 'selectNotRequired5', $row);
		$this->oTceForms->getSingleField('tx_mklib_getSingleFieldTest', 'selectRequired1', $row);
		$this->oTceForms->getSingleField('tx_mklib_getSingleFieldTest', 'selectRequired2', $row);
		$this->oTceForms->getSingleField('tx_mklib_getSingleFieldTest', 'selectRequired3', $row);
		$this->oTceForms->getSingleField('tx_mklib_getSingleFieldTest', 'selectRequired4', $row);
		//test
		$this->assertFalse(isset($this->oTceForms->requiredFields['tx_mklib_getSingleFieldTest_1_inputNotRequired']),'inputNotRequired ist required!');
		$this->assertFalse(isset($this->oTceForms->requiredFields['tx_mklib_getSingleFieldTest_1_selectNotRequired1']),'inputNotRequired ist required!');
		$this->assertFalse(isset($this->oTceForms->requiredFields['tx_mklib_getSingleFieldTest_1_selectNotRequired2']),'inputNotRequired ist required!');
		$this->assertFalse(isset($this->oTceForms->requiredFields['tx_mklib_getSingleFieldTest_1_selectNotRequired3']),'inputNotRequired ist required!');
		$this->assertFalse(isset($this->oTceForms->requiredFields['tx_mklib_getSingleFieldTest_1_selectNotRequired4']),'inputNotRequired ist required!');
		$this->assertFalse(isset($this->oTceForms->requiredFields['tx_mklib_getSingleFieldTest_1_selectNotRequired5']),'inputNotRequired ist required!');
		$this->assertEquals('data[tx_mklib_getSingleFieldTest][1][selectRequired1]', $this->oTceForms->requiredFields['tx_mklib_getSingleFieldTest_1_selectRequired1'],'selectRequired1 ist nicht required!');
		$this->assertEquals('data[tx_mklib_getSingleFieldTest][1][selectRequired2]', $this->oTceForms->requiredFields['tx_mklib_getSingleFieldTest_1_selectRequired2'],'selectRequired2 ist nicht required!');
		$this->assertEquals('data[tx_mklib_getSingleFieldTest][1][selectRequired3]', $this->oTceForms->requiredFields['tx_mklib_getSingleFieldTest_1_selectRequired3'],'selectRequired3 ist nicht required!');
		$this->assertEquals('data[tx_mklib_getSingleFieldTest][1][selectRequired4]', $this->oTceForms->requiredFields['tx_mklib_getSingleFieldTest_1_selectRequired4'],'selectRequired4 ist nicht required!');
		
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mkhoga/tests/class.tx_mkhoga_tests_FacetBuilder_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mkhoga/tests/class.tx_mkhoga_tests_FacetBuilder_testcase.php']);
}

?>