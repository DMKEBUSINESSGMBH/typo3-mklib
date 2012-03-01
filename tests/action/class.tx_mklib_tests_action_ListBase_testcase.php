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
tx_rnbase::load('tx_mklib_action_ListBase');

//in abstrakten klassen lassen sich keine nicht-abstrakten methoden mocken
abstract class ListBaseWithNewFilterMode extends tx_mklib_action_ListBase {
	protected function isOldFilterMode(){return false;}
}

/**
 *
 * Enter description here ...
 * @author Hannes Bochmann
 */
class tx_mklib_tests_action_ListBase_testcase extends tx_phpunit_testcase{

	protected function getConfigurations($aConfig) {
		$configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
		
		//@TODO: warum wird die klasse tslib_cObj nicht gefunden!? (mw: eternit local)
		require_once(t3lib_extMgm::extPath('cms', 'tslib/class.tslib_content.php'));
		$configurations->init(
			$aConfig,
			$configurations->getCObj(1),
			'mklib','mklib'
		);
			
		$configurations->setParameters($parameters);
		
		return $configurations;
	}
	
	protected function getSrvMock($expectedFields = array(),$returnValue = array('result' => array('firstItem'))) {
		$oSrv = $this->getMock('dummySrv',array('search'));
		
		if(!empty($expectedFields))
			$this->expectedFields = $expectedFields; 
		else
			$this->expectedFields = array('ANOTHERTEST.ANOTHERFIELD'=>array(OP_EQ=>'anotherValue'),'test'=>'value');
		
		$this->expectedOptions = array('orderby'=>'someOrderBy');
		$oSrv->expects($this->once())
		->method('search')
		//damit testen wir ob search mit den daten aus dem filter aufgerufen wird
		->with($this->expectedFields,$this->expectedOptions)
		//kommt alles korrekt in den view daten an? test weiter unten!
		->will($this->returnValue($returnValue));
		
		return $oSrv;
	}
	
	protected function getActionMock($oSearchSrv,$sClassToMock = 'tx_mklib_action_ListBase') {
		$oListBase = $this->getMockForAbstractClass($sClassToMock);
		//abstrakte methoden mocken
		$oListBase->expects($this->once())
		->method('getService')
		->will($this->returnValue($oSearchSrv));
		
		$oListBase->expects($this->once())
		->method('getTsPathPageBrowser')
		->will($this->returnValue('pagebrowser'));
		
		//damit die konfig stimmt
		$oListBase->expects($this->any())
		->method('getTemplateName')
		->will($this->returnValue('dummyconfig'));
		
		$oListBase->expects($this->any())
		->method('isOldFilterMode')
		->will($this->returnValue($bOldFilterMode));
		
		return $oListBase;
	}
	
	public function testFilterPageBrowserAndSearchServiceIsCalledCorrectWithOldFilterMode() {
		//mock für den searcher
		$oSrv = $this->getSrvMock();
		
		//mock für die zu testende action
		$oListBase = $this->getActionMock($oSrv);
		
		//jetzt den aufruf der action simulieren
		$aConfig = array(
			'dummyconfig.' => array(
				'filter' => 'tx_mklib_tests_fixtures_classes_DummyFilter',
				//fields und options sollte übernommen werden
				'fields.' => array(
					'anotherTest.' => array(
						'anotherField.' => array(
							'OP_EQ' => 'anotherValue'
						)
					)
				),
				'options.' => array(
					'orderby' => 'someOrderBy'
				),
			)
		);
		$configurations = $this->getConfigurations($aConfig);
		
		//handle request sollte nichts zurück geben
		$this->assertNull($oListBase->handleRequest($configurations->getParameters(), $configurations, $configurations->getViewData()),'handleRequest hat doch etwas zurück gegeben!');
		
		//view daten mit den daten des searchers gefüllt?
		$this->assertEquals(array('result' => array('firstItem')),$configurations->getViewData()->offsetGet('items'),'View daten falsch!');
		
		$pageBrowserConfig = $configurations->getViewData()->offsetGet('pageBrowserConfig');
		//daten die dem pagebrowser übergeben wurden korrekt?
		$this->assertEquals('dummyconfig.pagebrowser',$pageBrowserConfig['confid'],'daten für den page browser falsch: confid falsch!');
		$this->assertEquals($configurations,$pageBrowserConfig['config'],'daten für den page browser falsch: config falsch!');
		$this->assertEquals($this->expectedFields,$pageBrowserConfig['fields'],'daten für den page browser falsch: fields falsch!');
		$this->assertEquals($this->expectedOptions,$pageBrowserConfig['options'],'daten für den page browser falsch: options falsch!');
		$this->assertEquals(array('searchcallback'=>array($oSrv, 'search')),$pageBrowserConfig['cfg'],'daten für den page browser falsch: cfg falsch!');
	}
	
	public function testFilterPageBrowserAndSearchServiceIsCalledCorrectWithNewFilterMode() {
		//mock für den searcher
		$oSrv = $this->getSrvMock();
	
		//mock für die zu testende action
		$oListBase = $this->getActionMock($oSrv,'ListBaseWithNewFilterMode');
	
		//jetzt den aufruf der action simulieren
		$aConfig = array(
			'dummyconfig.' => array(
				'filter.' => array(
					'class'  => 'tx_mklib_tests_fixtures_classes_DummyFilter',
					//fields und options sollte übernommen werden
					'fields.' => array(
						'anotherTest.' => array(
							'anotherField.' => array(
								'OP_EQ' => 'anotherValue'
							)
						)
					),
					'options.' => array(
						'orderby' => 'someOrderBy'
					),
				)
			)
		);
		$configurations = $this->getConfigurations($aConfig);
	
		//handle request sollte nichts zurück geben
		$this->assertNull($oListBase->handleRequest($configurations->getParameters(), $configurations, $configurations->getViewData()),'handleRequest hat doch etwas zurück gegeben!');
	
		//view daten mit den daten des searchers gefüllt?
		$this->assertEquals(array('result' => array('firstItem')),$configurations->getViewData()->offsetGet('items'),'View daten falsch!');
	
		$pageBrowserConfig = $configurations->getViewData()->offsetGet('pageBrowserConfig');
		//daten die dem pagebrowser übergeben wurden korrekt?
		$this->assertEquals('dummyconfig.pagebrowser',$pageBrowserConfig['confid'],'daten für den page browser falsch: confid falsch!');
		$this->assertEquals($configurations,$pageBrowserConfig['config'],'daten für den page browser falsch: config falsch!');
		$this->assertEquals($this->expectedFields,$pageBrowserConfig['fields'],'daten für den page browser falsch: fields falsch!');
		$this->assertEquals($this->expectedOptions,$pageBrowserConfig['options'],'daten für den page browser falsch: options falsch!');
		$this->assertEquals(array('searchcallback'=>array($oSrv, 'search')),$pageBrowserConfig['cfg'],'daten für den page browser falsch: cfg falsch!');
	}
	
	public function testViewDataIsCorrectWithoutItems() {
		//mock für den searcher
		$oSrv = $this->getSrvMock(null,null);
		
		//mock für die zu testende action
		$oListBase = $this->getActionMock($oSrv);
		
		//jetzt den aufruf der action simulieren
		$aConfig = array(
			'dummyconfig.' => array(
				'filter' => 'tx_mklib_tests_fixtures_classes_DummyFilter',
				//fields und options sollte übernommen werden
				'fields.' => array(
					'anotherTest.' => array(
						'anotherField.' => array(
							'OP_EQ' => 'anotherValue'
						)
					)
				),
				'options.' => array(
					'orderby' => 'someOrderBy'
				),
			)
		);
		$configurations = $this->getConfigurations($aConfig);
		
		//handle request sollte nichts zurück geben
		$this->assertNull($oListBase->handleRequest($configurations->getParameters(), $configurations, $configurations->getViewData()),'handleRequest hat doch etwas zurück gegeben!');
		
		//view daten mit den daten des searchers leer?
		$this->assertFalse($configurations->getViewData()->offsetExists('items'),'View daten falsch!');
		
		$pageBrowserConfig = $configurations->getViewData()->offsetGet('pageBrowserConfig');
		//daten die dem pagebrowser übergeben wurden korrekt?
		$this->assertEquals('dummyconfig.pagebrowser',$pageBrowserConfig['confid'],'daten für den page browser falsch: confid falsch!');
		$this->assertEquals($configurations,$pageBrowserConfig['config'],'daten für den page browser falsch: config falsch!');
		$this->assertEquals($this->expectedFields,$pageBrowserConfig['fields'],'daten für den page browser falsch: fields falsch!');
		$this->assertEquals($this->expectedOptions,$pageBrowserConfig['options'],'daten für den page browser falsch: options falsch!');
		$this->assertEquals(array('searchcallback'=>array($oSrv, 'search')),$pageBrowserConfig['cfg'],'daten für den page browser falsch: cfg falsch!');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php']);
}