<?php
/**
 *  Copyright notice.
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

/**
 * tx_mklib_tests_action_ShowSingeItemTest.
 *
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_tests_action_ShowSingeItemTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @var string
     */
    private $defaultSubstitutedPageTitle = 'please provide the method getPageTitle in your action returning the desired page title';

    protected function setUp()
    {
        // This class is not autoloaded when the mock for the abstract class is created.
        // In normal usage the class is loaded automatically. You can see this be just instantiating
        // a classes that inherits from tx_mklib_repository_Abstract.
        tx_rnbase::load('tx_mklib_repository_Abstract');

        parent::setUp();
    }

    /**
     * @group unit
     */
    public function testGetSingleItemUidParameterKeyIfNoneConfigured()
    {
        $action = $this->getMockForAbstractClass('tx_mklib_action_ShowSingeItem');
        $configurations = $this->createConfigurations(array(), 'mklib');
        $action->setConfigurations($configurations);

        self::assertEquals(
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
    public function testGetSingleItemUidParameterKeyIfOneConfigured()
    {
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            array(),
            '',
            true,
            true,
            true,
            array('getConfId')
        );
        $action->expects(self::once())
            ->method('getConfId')
            ->will(self::returnValue('myAction.'));
        $configurations = $this->createConfigurations(
            array('myAction.' => array('uidParameterKey' => 'item')),
            'mklib'
        );
        $action->setConfigurations($configurations);

        self::assertEquals(
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
    public function testGetItemNotFound404MessageIfNoMessageConfiguredInConfigurations()
    {
        self::markTestIncomplete("Uncaught require(dmk/typo3-mklib/.Build/Web/typo3conf/LocalConfiguration.php)");

        $action = $this->getMockForAbstractClass('tx_mklib_action_ShowSingeItem');
        $configurations = $this->createConfigurations(array(), 'mklib');
        $action->setConfigurations($configurations);

        self::assertEquals(
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
    public function testGetItemNotFound404MessageIfMessageConfiguredInConfigurations()
    {
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            array(),
            '',
            true,
            true,
            true,
            array('getConfId')
        );
        $action->expects(self::once())
            ->method('getConfId')
            ->will(self::returnValue('myAction.'));
        $configurations = $this->createConfigurations(
            array('myAction.' => array('notfound' => 'Model nicht gefunden.')),
            'mklib'
        );
        $action->setConfigurations($configurations);

        self::assertEquals(
            'Model nicht gefunden.',
            $this->callInaccessibleMethod(
                $action,
                'getItemNotFound404Message'
            )
        );
    }

    /**
     * @group unit
     * @expectedException \tx_rnbase_exception_ItemNotFound404
     * @expectedExceptionMessage Datensatz nicht gefunden.
     */
    public function testThrowItemNotFound404Exception()
    {
        self::markTestIncomplete("Uncaught require(dmk/typo3-mklib/.Build/Web/typo3conf/LocalConfiguration.php)");

        $action = $this->getMockForAbstractClass('tx_mklib_action_ShowSingeItem');
        $configurations = $this->createConfigurations(array(), 'mklib');
        $action->setConfigurations($configurations);
        $this->callInaccessibleMethod($action, 'throwItemNotFound404Exception');
    }

    /**
     * @group unit
     */
    public function testThrowItemNotFound404ExceptionIfDisabled()
    {
        self::markTestIncomplete("This test did not perform any assertions!");

        $action = $this->getMockForAbstractClass('tx_mklib_action_ShowSingeItem');
        $configurations = $this->createConfigurations(
            array(
                '.' => array('disable404ExceptionIfNoItemFound' => true), ),
            'mklib'
        );
        $action->setConfigurations($configurations);
        $this->callInaccessibleMethod($action, 'throwItemNotFound404Exception');
    }

    /**
     * @group unit
     *
     * @expectedException \tx_rnbase_exception_ItemNotFound404
     * @expectedExceptionMessage Datensatz nicht gefunden.
     */
    public function testHandleRequestThrowsItemNotFound404ExceptionIfNoItemId()
    {
        self::markTestIncomplete();

        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            array(),
            '',
            true,
            true,
            true,
            array('getSingleItemRepository')
        );
        $action->expects(self::never())->method('getSingleItemRepository');

        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters', array());
        $configurations = $this->createConfigurations(
            array(),
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod(
            array($action, 'handleRequest'),
            array(&$parameters, &$configurations, &$viewData)
        );
    }

    /**
     * @group unit
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Das Repository, welches von getSingleItemRepository() geliefert wird, muss von tx_mklib_repository_Abstract erben!
     */
    public function testHandleRequestThrowsExceptionIfRepositoryNotInheritedFromAbstractRepositoryClass()
    {
        $repository = $this->getMockForAbstractClass(
            'stdClass',
            array(),
            '',
            false,
            false,
            false,
            array('findByUid')
        );
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            array(),
            '',
            true,
            true,
            true,
            array('getSingleItemRepository')
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $parameters = tx_rnbase::makeInstance(
            'tx_rnbase_parameters',
            array('uid' => 987654321)
        );
        $configurations = $this->createConfigurations(
            array(),
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod(
            array($action, 'handleRequest'),
            array(&$parameters, &$configurations, &$viewData)
        );
    }

    /**
     * @group unit
     *
     * @expectedException \tx_rnbase_exception_ItemNotFound404
     * @expectedExceptionMessage Datensatz nicht gefunden.
     */
    public function testHandleRequestThrowsItemNotFound404ExceptionIfItemNotFound()
    {
        self::markTestIncomplete("Uncaught require(dmk/typo3-mklib/.Build/Web/typo3conf/LocalConfiguration.php)");

        $repository = $this->getMockForAbstractClass(
            'tx_mklib_repository_Abstract',
            array(),
            '',
            false,
            false,
            false,
            array('findByUid')
        );
        $repository->expects(self::once())
            ->method('findByUid')
            ->with(987654321)
            ->will(self::returnValue(null));
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            array(),
            '',
            true,
            true,
            true,
            array('getSingleItemRepository')
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $parameters = tx_rnbase::makeInstance(
            'tx_rnbase_parameters',
            array('uid' => 987654321)
        );
        $configurations = $this->createConfigurations(
            array(),
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod(
            array($action, 'handleRequest'),
            array(&$parameters, &$configurations, &$viewData)
        );
    }

    /**
     * @group unit
     */
    public function testHandleRequestSetsFoundItemToViewData()
    {
        $repository = $this->getMockForAbstractClass(
            'tx_mklib_repository_Abstract',
            array(),
            '',
            false,
            false,
            false,
            array('findByUid')
        );
        $model = tx_rnbase::makeInstance(
            'tx_mklib_model_Page',
            array('uid' => 987654321)
        );
        $repository->expects(self::once())
            ->method('findByUid')
            ->with(987654321)
            ->will(self::returnValue($model));
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            array(),
            '',
            true,
            true,
            true,
            array('getSingleItemRepository')
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $parameters = tx_rnbase::makeInstance(
            'tx_rnbase_parameters',
            array('uid' => 987654321)
        );
        $configurations = $this->createConfigurations(
            array(),
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod(
            array($action, 'handleRequest'),
            array(&$parameters, &$configurations, &$viewData)
        );

        self::assertEquals($model, $viewData->offsetGet('item'));
    }

    /**
     * @group unit
     */
    public function testHandleRequestPrefersConfiguredUidOverParameter()
    {
        $repository = $this->getMockForAbstractClass(
            'tx_mklib_repository_Abstract',
            array(),
            '',
            false,
            false,
            false,
            array('findByUid')
        );
        $repository->expects(self::once())
            ->method('findByUid')
            ->with(123456789)
            ->will(self::returnValue('model'));
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            array(),
            '',
            true,
            true,
            true,
            array('getSingleItemRepository')
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $parameters = tx_rnbase::makeInstance(
            'tx_rnbase_parameters',
            array('uid' => 987654321)
        );
        $configurations = $this->createConfigurations(
            array('.' => array('uid' => 123456789)),
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod(
            array($action, 'handleRequest'),
            array(&$parameters, &$configurations, &$viewData)
        );
    }

    /**
     * @group unit
     */
    public function testHandleRequestSubstitutesPageTitleNotIfNotConfigured()
    {
        self::markTestIncomplete("Error: Class 'TYPO3\CMS\Core\TimeTracker\NullTimeTracker' not found");

        tx_rnbase_util_TYPO3::getTSFE()->getPageAndRootline();

        $repository = $this->getMockForAbstractClass(
            'tx_mklib_repository_Abstract',
            array(),
            '',
            false,
            false,
            false,
            array('findByUid')
        );
        $model = tx_rnbase::makeInstance(
            'tx_mklib_model_Page',
            array('uid' => 987654321)
        );
        $repository->expects(self::once())
            ->method('findByUid')
            ->will(self::returnValue($model));
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            array(),
            '',
            true,
            true,
            true,
            array('getSingleItemRepository')
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $parameters = tx_rnbase::makeInstance(
            'tx_rnbase_parameters',
            array('uid' => 987654321)
        );
        $configurations = $this->createConfigurations(
            array(),
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod(
            array($action, 'handleRequest'),
            array(&$parameters, &$configurations, &$viewData)
        );

        self::assertNotEquals(
            $this->defaultSubstitutedPageTitle,
            tx_rnbase_util_TYPO3::getTSFE()->page['title'],
            'tx_rnbase_util_TYPO3::getTSFE()->page[\'title\'] doch ersetzt'
        );
        self::assertNotEquals(
            $this->defaultSubstitutedPageTitle,
            tx_rnbase_util_TYPO3::getTSFE()->indexedDocTitle,
            'tx_rnbase_util_TYPO3::getTSFE()->indexedDocTitle doch ersetzt'
        );
    }

    /**
     * @group unit
     */
    public function testHandleRequestSubstitutesPageTitleIfConfigured()
    {
        self::markTestIncomplete("Error: Class 'TYPO3\CMS\Core\TimeTracker\NullTimeTracker' not found");

        tx_rnbase_util_TYPO3::getTSFE()->getPageAndRootline();

        $repository = $this->getMockForAbstractClass(
            'tx_mklib_repository_Abstract',
            array(),
            '',
            false,
            false,
            false,
            array('findByUid')
        );
        $model = tx_rnbase::makeInstance(
            'tx_mklib_model_Page',
            array('uid' => 987654321)
        );
        $repository->expects(self::once())
            ->method('findByUid')
            ->will(self::returnValue($model));

        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            array(),
            '',
            true,
            true,
            true,
            array('getSingleItemRepository', 'getConfId')
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $action->expects(self::any())
            ->method('getConfId')
            ->will(self::returnValue('myAction.'));

        $parameters = tx_rnbase::makeInstance(
            'tx_rnbase_parameters',
            array('uid' => 987654321)
        );
        $configurations = $this->createConfigurations(
            array('myAction.' => array('substitutePageTitle' => true)),
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod(
            array($action, 'handleRequest'),
            array(&$parameters, &$configurations, &$viewData)
        );

        self::assertEquals(
            $this->defaultSubstitutedPageTitle,
            tx_rnbase_util_TYPO3::getTSFE()->page['title'],
            'tx_rnbase_util_TYPO3::getTSFE()->page[\'title\'] falsch ersetzt'
        );
        self::assertEquals(
            $this->defaultSubstitutedPageTitle,
            tx_rnbase_util_TYPO3::getTSFE()->indexedDocTitle,
            'tx_rnbase_util_TYPO3::getTSFE()->indexedDocTitle falsch ersetzt'
        );
    }
}
