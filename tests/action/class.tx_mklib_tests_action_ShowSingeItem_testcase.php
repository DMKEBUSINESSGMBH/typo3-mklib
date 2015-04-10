<?php
/**
 *  Copyright notice
 *
 *  (c) 2015 Hannes Bochmann <dev@dmk-ebusiness.de>
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
tx_rnbase::load('tx_mklib_action_ShowSingeItem');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * tx_mklib_tests_action_ShowSingeItem_testcase
 *
 * @package 		TYPO3
 * @subpackage	 	mklib
 * @author 			Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_tests_action_ShowSingeItem_testcase extends tx_rnbase_tests_BaseTestCase {

	/**
	 * @group unit
	 */
	public function testGetSingleItemUidParameterKeyIfNoneConfigured() {
		$action = $this->getMockForAbstractClass('tx_mklib_action_ShowSingeItem');
		$configurations = $this->createConfigurations(array(), 'mklib');
		$action->setConfigurations($configurations);

		$this->assertEquals(
			'uid',
			$this->callInaccessibleMethod(
				$action,
				'getSingleItemUidParameterKey'
			)
		);
	}

	/**
	 * @group unit
	 */
	public function testGetSingleItemUidParameterKeyIfOneConfigured() {
		$action = $this->getMockForAbstractClass(
			'tx_mklib_action_ShowSingeItem',
			array(), '', TRUE, TRUE, TRUE,
			array('getConfId')
		);
		$action->expects($this->once())
			->method('getConfId')
			->will($this->returnValue('myAction.'));
		$configurations = $this->createConfigurations(
			array('myAction.' => array('uidParameterKey' => 'item')), 'mklib'
		);
		$action->setConfigurations($configurations);

		$this->assertEquals(
			'item',
			$this->callInaccessibleMethod(
				$action,
				'getSingleItemUidParameterKey'
			)
		);
	}

	/**
	 * @group unit
	 */
	public function testGetItemNotFound404MessageIfNoMessageConfiguredInConfigurations() {
		$action = $this->getMockForAbstractClass('tx_mklib_action_ShowSingeItem');
		$configurations = $this->createConfigurations(array(), 'mklib');
		$action->setConfigurations($configurations);

		$this->assertEquals(
			'Datensatz nicht gefunden.',
			$this->callInaccessibleMethod(
				$action,
				'getItemNotFound404Message'
			)
		);
	}

	/**
	 * @group unit
	 */
	public function testGetItemNotFound404MessageIfMessageConfiguredInConfigurations() {
		$action = $this->getMockForAbstractClass(
			'tx_mklib_action_ShowSingeItem',
			array(), '', TRUE, TRUE, TRUE,
			array('getConfId')
		);
		$action->expects($this->once())
			->method('getConfId')
			->will($this->returnValue('myAction.'));
		$configurations = $this->createConfigurations(
			array('myAction.' => array('notfound' => 'Model nicht gefunden.')), 'mklib'
		);
		$action->setConfigurations($configurations);

		$this->assertEquals(
			'Model nicht gefunden.',
			$this->callInaccessibleMethod(
				$action,
				'getItemNotFound404Message'
			)
		);
	}

	/**
	 * @group unit
	 * @expectedException tx_rnbase_exception_ItemNotFound404
 	 * @expectedExceptionMessage Datensatz nicht gefunden.
	 */
	public function testThrowItemNotFound404Exception() {
		$action = $this->getMockForAbstractClass('tx_mklib_action_ShowSingeItem');
		$configurations = $this->createConfigurations(array(), 'mklib');
		$action->setConfigurations($configurations);
		$this->callInaccessibleMethod($action, 'throwItemNotFound404Exception');
	}

	/**
	 * @group unit
	 *
	 * @expectedException tx_rnbase_exception_ItemNotFound404
	 * @expectedExceptionMessage Datensatz nicht gefunden.
	 */
	public function testHandleRequestThrowsItemNotFound404ExceptionIfNoItemId() {
		$action = $this->getMockForAbstractClass(
			'tx_mklib_action_ShowSingeItem',
			array(), '', TRUE, TRUE, TRUE,
			array('getSingleItemRepository')
		);
		$action->expects($this->never())->method('getSingleItemRepository');

		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters', array());
		$configurations = $this->createConfigurations(
			array(), 'mklib', 'mklib', $parameters
		);
		$viewData = $configurations->getViewData();
		$action->setConfigurations($configurations);

		$this->callInaccessibleMethod(
			$action, 'handleRequest', $parameters, $configurations, $viewData
		);
	}

	/**
	 * @group unit
	 *
	 * @expectedException Exception
	 * @expectedExceptionMessage Das Repository, welches von getSingleItemRepository() geliefert wird, muss von tx_mklib_repository_Abstract erben!
	 */
	public function testHandleRequestThrowsExceptionIfRepositoryNotInheritedFromAbstractRepositoryClass() {
		$repository = $this->getMockForAbstractClass(
			'stdClass',
			array(), '', FALSE, FALSE, FALSE, array('findByUid')
		);
		$action = $this->getMockForAbstractClass(
			'tx_mklib_action_ShowSingeItem',
			array(), '', TRUE, TRUE, TRUE,
			array('getSingleItemRepository')
		);
		$action->expects($this->once())
			->method('getSingleItemRepository')
			->will($this->returnValue($repository));

		$parameters = tx_rnbase::makeInstance(
			'tx_rnbase_parameters', array('uid' => 987654321)
		);
		$configurations = $this->createConfigurations(
			array(), 'mklib', 'mklib', $parameters
		);
		$viewData = $configurations->getViewData();
		$action->setConfigurations($configurations);

		$this->callInaccessibleMethod(
			$action, 'handleRequest', $parameters, $configurations, $viewData
		);
	}

	/**
	 * @group unit
	 *
	 * @expectedException tx_rnbase_exception_ItemNotFound404
	 * @expectedExceptionMessage Datensatz nicht gefunden.
	 */
	public function testHandleRequestThrowsItemNotFound404ExceptionIfRecipeNotFound() {
		$repository = $this->getMockForAbstractClass(
			'tx_mklib_repository_Abstract',
			array(), '', FALSE, FALSE, FALSE, array('findByUid')
		);
		$repository->expects($this->once())
			->method('findByUid')
			->with(987654321)
			->will($this->returnValue(NULL));
		$action = $this->getMockForAbstractClass(
			'tx_mklib_action_ShowSingeItem',
			array(), '', TRUE, TRUE, TRUE,
			array('getSingleItemRepository')
		);
		$action->expects($this->once())
			->method('getSingleItemRepository')
			->will($this->returnValue($repository));

		$parameters = tx_rnbase::makeInstance(
			'tx_rnbase_parameters', array('uid' => 987654321)
		);
		$configurations = $this->createConfigurations(
			array(), 'mklib', 'mklib', $parameters
		);
		$viewData = $configurations->getViewData();
		$action->setConfigurations($configurations);

		$this->callInaccessibleMethod(
			$action, 'handleRequest', $parameters, $configurations, $viewData
		);
	}

	/**
	 * @group unit
	 */
	public function testHandleRequestSetsFoundRecipeToViewData() {
		$repository = $this->getMockForAbstractClass(
			'tx_mklib_repository_Abstract',
			array(), '', FALSE, FALSE, FALSE, array('findByUid')
		);
		$model = tx_rnbase::makeInstance(
			'tx_mklib_model_Page', array('uid' => 987654321)
		);
		$repository->expects($this->once())
			->method('findByUid')
			->with(987654321)
			->will($this->returnValue($model));
		$action = $this->getMockForAbstractClass(
			'tx_mklib_action_ShowSingeItem',
			array(), '', TRUE, TRUE, TRUE,
			array('getSingleItemRepository')
		);
		$action->expects($this->once())
			->method('getSingleItemRepository')
			->will($this->returnValue($repository));

		$parameters = tx_rnbase::makeInstance(
			'tx_rnbase_parameters', array('uid' => 987654321)
		);
		$configurations = $this->createConfigurations(
			array(), 'mklib', 'mklib', $parameters
		);
		$viewData = $configurations->getViewData();
		$action->setConfigurations($configurations);

		$this->callInaccessibleMethod(
			$action, 'handleRequest', $parameters, $configurations, $viewData
		);

		$this->assertEquals($model, $viewData->offsetGet('item'));
	}
}