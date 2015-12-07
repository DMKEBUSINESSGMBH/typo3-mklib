<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Rene Nitzsche (nitzsche@dmk-ebusiness.de)
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




class tx_mklib_tests_hooks_t3lib_tceforms_getSingleFieldClass_testcase extends tx_phpunit_testcase {

	protected $oTceForms;

	/**
	 * damit ersichtlicher ist, was welches feld repräsentiert.
	 * @var array
	 */
	private $fieldMappings = array(
		'inputNotRequiredField' 	=> 't3ver_oid',
		'selectNotRequired1Field' 	=> 't3ver_id',
		'selectNotRequired2Field' 	=> 't3ver_wsid',
		'selectNotRequired3Field' 	=> 't3ver_label',
		'selectNotRequired4Field' 	=> 't3ver_count',
		'selectRequired1Field' 		=> 't3ver_tstamp',
		'selectRequired2Field' 		=> 't3ver_swapmode',
		'selectRequired3Field' 		=> 't3ver_move_id',
		'selectRequired4Field' 		=> 't3_origuid',
		'selectRequired6Field' 		=> 't3ver_state',
	);

	/**
	 * @var string
	 */
	private $testTable = 'pages';

	/**
	 * @var array
	 */
	private $tcaBackup;

	public function setUp() {
		//wir nutzen hier die pages tabelle und überschreiben die TCA
		//für ein paar felder. wir müssen das mit bestehenden feldern
		//in einer echten tabelle testen da es sonst zu warnungen kommt.
		global $TCA;
		$this->tcaBackup = $TCA[$this->testTable];
		$TCA[$this->testTable] = Array (
			'ctrl' => array (
				'title'     => $this->testTable,
				'readOnly' => 1,
				'is_static' => 1
			),
			'columns' => Array (
				$this->fieldMappings['inputNotRequiredField'] => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'input',
					)
				),
				$this->fieldMappings['selectNotRequired1Field'] => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
					)
				),
				$this->fieldMappings['selectNotRequired2Field'] => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'required'
					)
				),
				$this->fieldMappings['selectNotRequired3Field'] => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'maxitems' => 1
					)
				),
				$this->fieldMappings['selectRequired1Field'] => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'required',
						'maxitems' => 1,
						'minitems' => 1
					)
				),
				$this->fieldMappings['selectRequired2Field'] => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'int,required',
						'maxitems' => 1,
						'minitems' => 1
					)
				),
				$this->fieldMappings['selectRequired3Field'] => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'required,int',
						'maxitems' => 1,
						'minitems' => 1
					)
				),
				$this->fieldMappings['selectRequired4Field'] => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'int,required,trim',
						'maxitems' => 1,
						'minitems' => 1
					)
				),
				$this->fieldMappings['selectRequired6Field'] => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'minitems' => 1
					)
				),
				$this->fieldMappings['selectNotRequired4Field'] => array (
					'exclude' => 1,
					'config'  => array (
						'type'    => 'select',
						'eval' => 'required',
						'maxitems' => 1
					)
				),
			)
		);
		//damit alles auf der CLI funktioniert
		$GLOBALS['BE_USER']->user['admin'] = 1;

		//tceforms initialisieren
		$this->oTceForms = t3lib_div::makeInstance('t3lib_tceforms');

		// sonst Warning in typo3/sysext/backend/Classes/Utility/IconUtility.php line 594
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			$GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] =
				(array)$GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'];
		}
	}

	/**
	 */
	public function tearDown() {
		global $TCA;
		$TCA[$this->testTable] = $this->tcaBackup;
	}


	/**
	 * Wird aus start- und enddatum ein datetime gemacht?
	 * Enter description here ...
	 */
	public function testAddingRequiredEvalForSelects() {
		//testdaten
		$row = array('uid' => 1);
		$this->oTceForms->getSingleField(
			$this->testTable, $this->fieldMappings['inputNotRequiredField'], $row
		);
		$this->oTceForms->getSingleField(
			$this->testTable, $this->fieldMappings['selectNotRequired1Field'], $row
		);
		$this->oTceForms->getSingleField(
			$this->testTable, $this->fieldMappings['selectNotRequired2Field'], $row
		);
		$this->oTceForms->getSingleField(
			$this->testTable, $this->fieldMappings['selectNotRequired3Field'], $row
		);
		$this->oTceForms->getSingleField(
			$this->testTable, $this->fieldMappings['selectRequired6Field'], $row
		);
		$this->oTceForms->getSingleField(
			$this->testTable, $this->fieldMappings['selectNotRequired4Field'], $row
		);
		$this->oTceForms->getSingleField(
			$this->testTable, $this->fieldMappings['selectRequired1Field'], $row
		);
		$this->oTceForms->getSingleField(
			$this->testTable, $this->fieldMappings['selectRequired2Field'], $row
		);
		$this->oTceForms->getSingleField(
			$this->testTable, $this->fieldMappings['selectRequired3Field'], $row
		);
		$this->oTceForms->getSingleField(
			$this->testTable, $this->fieldMappings['selectRequired4Field'], $row
		);

		//test
		$this->assertFalse(
			isset($this->oTceForms->requiredFields[$this->testTable . '_1_' . $this->fieldMappings['inputNotRequiredField']]),
			$this->fieldMappings['inputNotRequiredField']. ' ist required!'
		);
		$this->assertFalse(
			isset($this->oTceForms->requiredFields[$this->testTable . '_1_' . $this->fieldMappings['selectNotRequired1Field']]),
			$this->fieldMappings['selectNotRequired1Field']. ' ist required!'
		);
		$this->assertFalse(
			isset($this->oTceForms->requiredFields[$this->testTable . '_1_' . $this->fieldMappings['selectNotRequired2Field']]),
			$this->fieldMappings['selectNotRequired2Field']. ' ist required!'
		);
		$this->assertFalse(
			isset($this->oTceForms->requiredFields[$this->testTable . '_1_' . $this->fieldMappings['selectNotRequired3Field']]),
			$this->fieldMappings['selectNotRequired3Field']. ' ist required!'
		);
		$this->assertFalse(
			isset($this->oTceForms->requiredFields[$this->testTable . '_1_' . $this->fieldMappings['selectNotRequired4Field']]),
			$this->fieldMappings['selectNotRequired4Field']. ' ist required!'
		);
		$this->assertEquals(
			'data[pages][1][t3ver_state]',
			$this->oTceForms->requiredFields[$this->testTable . '_1_' . $this->fieldMappings['selectRequired6Field']],
			$this->fieldMappings['selectRequired6Field']. ' ist nicht required!'
		);
		$this->assertEquals(
			'data[pages][1][t3ver_tstamp]',
			$this->oTceForms->requiredFields[$this->testTable . '_1_' . $this->fieldMappings['selectRequired1Field']],
			$this->fieldMappings['selectRequired1Field']. ' ist nicht required!'
		);
		$this->assertEquals(
			'data[pages][1][t3ver_swapmode]',
			$this->oTceForms->requiredFields[$this->testTable . '_1_' . $this->fieldMappings['selectRequired2Field']],
			$this->fieldMappings['selectRequired2Field']. ' ist nicht required!'
		);
		$this->assertEquals(
			'data[pages][1][t3ver_move_id]',
			$this->oTceForms->requiredFields[$this->testTable . '_1_' . $this->fieldMappings['selectRequired3Field']],
			$this->fieldMappings['selectRequired3Field']. ' ist nicht required!'
		);
		$this->assertEquals(
			'data[pages][1][t3_origuid]',
			$this->oTceForms->requiredFields[$this->testTable . '_1_' . $this->fieldMappings['selectRequired4Field']],
			$this->fieldMappings['selectRequired4Field']. 'ist nicht required!'
		);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mkhoga/tests/class.tx_mkhoga_tests_FacetBuilder_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mkhoga/tests/class.tx_mkhoga_tests_FacetBuilder_testcase.php']);
}
