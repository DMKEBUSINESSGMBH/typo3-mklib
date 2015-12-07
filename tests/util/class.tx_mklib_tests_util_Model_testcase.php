<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
require_once(tx_rnbase_util_Extensions::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_util_Model');
tx_rnbase::load('tx_mklib_tests_Util');
	
/**
 * Model util tests
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_Model_testcase extends tx_phpunit_testcase {
	
	/**
	 * Prüfen ob alle leeren Elemente außer dem array gelöscht werden
	 * und keys unberührt bleiben
	 */
	public function testSplitTextIntoTitleAndRest(){
		$aRecord = array('text' => 'ein ganz langer text mit vielen worten','htmltext' => 'ein <span> ganz </span> langer text mit vielen worten');
		$model = tx_rnbase::makeInstance('tx_mklib_model_WordlistEntry', $aRecord);
		tx_mklib_util_Model::splitTextIntoTitleAndRest($model,'text',2);
		
		$this->assertEquals('ein ganz',$model->record['titletext'], 'field:titletext nicht korrekt');
		$this->assertEquals('langer text mit vielen worten',$model->record['restaftertitle'], 'field:restaftertitle nicht korrekt');
		
		tx_mklib_util_Model::splitTextIntoTitleAndRest($model,'text',3);
		
		$this->assertEquals('ein ganz langer',$model->record['titletext'], 'field:titletext nicht korrekt');
		$this->assertEquals('text mit vielen worten',$model->record['restaftertitle'], 'field:restaftertitle nicht korrekt');
		
		tx_mklib_util_Model::splitTextIntoTitleAndRest($model,'htmltext',3,false);
		
		$this->assertEquals('ein <span> ganz',$model->record['titletext'], 'field:titletext nicht korrekt');
		$this->assertEquals('</span> langer text mit vielen worten',$model->record['restaftertitle'], 'field:restaftertitle nicht korrekt');
		
		tx_mklib_util_Model::splitTextIntoTitleAndRest($model,'htmltext',3,true);
		
		$this->assertEquals('ein ganz langer',$model->record['titletext'], 'field:titletext nicht korrekt');
		$this->assertEquals('text mit vielen worten',$model->record['restaftertitle'], 'field:restaftertitle nicht korrekt');
	}
	
	/**
	 * Prüfen ob alle leeren Elemente außer dem array gelöscht werden
	 * und keys unberührt bleiben
	 */
	public function testGetShortenedText(){
		$aRecord = array(
			'othertext' => 'ein ganz langer text mit vielen worten und noch viel viel viel viel mehr',
			'text' => 'ein ganz langer text mit vielen worten und noch viel viel viel viel mehr',
			'htmltext' => 'ein ganz langer text mit vielen worten und <span>noch viel viel</span> viel viel mehr',
		);
		$model = tx_rnbase::makeInstance('tx_mklib_model_WordlistEntry', $aRecord);
		tx_mklib_util_Model::getShortenedText($model,'othertext',50);
		$this->assertEquals('ein ganz langer text mit vielen worten und noch viel',$model->record['othertextshortened'], 'field:othertextshortened nicht korrekt');
		
		tx_mklib_util_Model::getShortenedText($model);
		
		$this->assertEquals('ein ganz langer text mit vielen worten und noch viel viel viel viel mehr',$model->record['textshortened'], 'field:textshortened nicht korrekt');
		
		tx_mklib_util_Model::getShortenedText($model,'htmltext',50,false);
		
		$this->assertEquals('ein ganz langer text mit vielen worten und <span>noch',$model->record['htmltextshortened'], 'field:htmltextshortened nicht korrekt');
		
		tx_mklib_util_Model::getShortenedText($model,'htmltext',50,true);
		
		$this->assertEquals('ein ganz langer text mit vielen worten und noch viel',$model->record['htmltextshortened'], 'field:htmltextshortened nicht korrekt');
	}
	

	
	public function testUniqueModelArray(){
		$aArray = array(
						tx_rnbase::makeInstance('tx_rnbase_model_base', array('uid'=>5,'name'=>'Model Nr. 5')),
						tx_rnbase::makeInstance('tx_rnbase_model_base', array('uid'=>6,'name'=>'Model Nr. 6')),
						tx_rnbase::makeInstance('tx_rnbase_model_base', array('uid'=>5,'name'=>'Model Nr. 5')),
						tx_rnbase::makeInstance('tx_rnbase_model_base', array('uid'=>2,'name'=>'Model Nr. 2')),
					);
		$aUnique = tx_mklib_util_Model::uniqueModels($aArray);
		$this->assertTrue(is_array($aUnique), 'No array given.');
		$this->assertEquals(count($aUnique), 3, 'Array has a wrong count of entries.');
		$this->assertArrayHasKey(2, $aUnique, 'Model with uid 2 not found');
		$this->assertArrayHasKey(5, $aUnique, 'Model with uid 5 not found');
		$this->assertArrayHasKey(6, $aUnique, 'Model with uid 6 not found');
	}
	
	public function testGetEmptyInstance() {
		$this->markTestSkipped('@TODO: implement!');
		// eine dummy tca erstellen und prüfen!
		require_once(tx_rnbase_util_Extensions::extPath('rn_base') . 'class.tx_rnbase.php');
		tx_mklib_tests_Util::getFixturePath('dummyTCA.php');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Model_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Model_testcase.php']);
}