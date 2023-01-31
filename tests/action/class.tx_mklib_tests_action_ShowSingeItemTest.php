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
class tx_mklib_tests_action_ShowSingeItemTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @var string
     */
    private $defaultSubstitutedPageTitle = 'please provide the method getPageTitle in your action returning the desired page title';

    protected function setUp(): void
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
        $configurations = \Sys25\RnBase\Testing\TestUtility::createConfigurations([], 'mklib');
        $parameters = new \Sys25\RnBase\Frontend\Request\Parameters();
        $request = new \Sys25\RnBase\Frontend\Request\Request($parameters, $configurations, '');

        self::assertEquals(
            'uid',
            $this->callInaccessibleMethod(
                $action,
                'getSingleItemUidParameterKey',
                $request
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
            [],
            '',
            true,
            true,
            true,
            ['getConfId']
        );
        $action->expects(self::once())
            ->method('getConfId')
            ->will(self::returnValue('myAction.'));
        $configurations = $this->createConfigurations(
            ['myAction.' => ['uidParameterKey' => 'item']],
            'mklib'
        );
        $parameters = new \Sys25\RnBase\Frontend\Request\Parameters();
        $request = new \Sys25\RnBase\Frontend\Request\Request($parameters, $configurations, '');

        self::assertEquals(
            'item',
            $this->callInaccessibleMethod(
                $action,
                'getSingleItemUidParameterKey',
                $request
            )
        );
    }

    /**
     * @group unit
     */
    public function testGetItemNotFound404MessageIfNoMessageConfiguredInConfigurations()
    {
        self::markTestIncomplete('Uncaught require(dmk/typo3-mklib/.Build/Web/typo3conf/LocalConfiguration.php)');

        $action = $this->getMockForAbstractClass('tx_mklib_action_ShowSingeItem');
        $configurations = $this->createConfigurations([], 'mklib');
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
            [],
            '',
            true,
            true,
            true,
            ['getConfId']
        );
        $action->expects(self::once())
            ->method('getConfId')
            ->will(self::returnValue('myAction.'));
        $configurations = $this->createConfigurations(
            ['myAction.' => ['notfound' => 'Model nicht gefunden.']],
            'mklib'
        );
        $parameters = new \Sys25\RnBase\Frontend\Request\Parameters();
        $request = new \Sys25\RnBase\Frontend\Request\Request($parameters, $configurations, '');

        self::assertEquals(
            'Model nicht gefunden.',
            $this->callInaccessibleMethod(
                $action,
                'getItemNotFound404Message',
                $request
            )
        );
    }

    /**
     * @group unit
     *
     * @expectedException \Sys25\RnBase\Exception\PageNotFound404
     *
     * @expectedExceptionMessage Datensatz nicht gefunden.
     */
    public function testThrowItemNotFound404Exception()
    {
        self::markTestIncomplete('Uncaught require(dmk/typo3-mklib/.Build/Web/typo3conf/LocalConfiguration.php)');

        $action = $this->getMockForAbstractClass('tx_mklib_action_ShowSingeItem');
        $configurations = $this->createConfigurations([], 'mklib');
        $action->setConfigurations($configurations);
        $this->callInaccessibleMethod($action, 'throwItemNotFound404Exception');
    }

    /**
     * @group unit
     */
    public function testThrowItemNotFound404ExceptionIfDisabled()
    {
        self::markTestIncomplete('This test did not perform any assertions!');

        $action = $this->getMockForAbstractClass('tx_mklib_action_ShowSingeItem');
        $configurations = $this->createConfigurations(
            [
                '.' => ['disable404ExceptionIfNoItemFound' => true], ],
            'mklib'
        );
        $action->setConfigurations($configurations);
        $this->callInaccessibleMethod($action, 'throwItemNotFound404Exception');
    }

    /**
     * @group unit
     *
     * @expectedException \Sys25\RnBase\Exception\PageNotFound404
     *
     * @expectedExceptionMessage Datensatz nicht gefunden.
     */
    public function testHandleRequestThrowsItemNotFound404ExceptionIfNoItemId()
    {
        self::markTestIncomplete();

        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            [],
            '',
            true,
            true,
            true,
            ['getSingleItemRepository']
        );
        $action->expects(self::never())->method('getSingleItemRepository');

        $parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Frontend\Request\Parameters::class, []);
        $configurations = $this->createConfigurations(
            [],
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod(
            [$action, 'handleRequest'],
            [&$parameters, &$configurations, &$viewData]
        );
    }

    /**
     * @group unit
     */
    public function testHandleRequestThrowsExceptionIfRepositoryNotInheritedFromAbstractRepositoryClass()
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage('Das Repository, welches von getSingleItemRepository() geliefert wird, muss von tx_mklib_repository_Abstract erben!');
        $repository = $this->getMockForAbstractClass(
            'stdClass',
            [],
            '',
            false,
            false,
            false,
            ['findByUid']
        );
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            [],
            '',
            true,
            true,
            true,
            ['getSingleItemRepository']
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \Sys25\RnBase\Frontend\Request\Parameters::class,
            ['uid' => 987654321]
        );
        $configurations = $this->createConfigurations(
            [],
            'mklib',
            'mklib',
            $parameters
        );
        $request = new \Sys25\RnBase\Frontend\Request\Request($parameters, $configurations, '');
        $viewData = $request->getViewContext();

        $this->callInaccessibleMethod(
            [$action, 'handleRequest'],
            [$request]
        );
    }

    /**
     * @group unit
     *
     * @expectedException \Sys25\RnBase\Exception\PageNotFound404
     *
     * @expectedExceptionMessage Datensatz nicht gefunden.
     */
    public function testHandleRequestThrowsItemNotFound404ExceptionIfItemNotFound()
    {
        self::markTestIncomplete('Uncaught require(dmk/typo3-mklib/.Build/Web/typo3conf/LocalConfiguration.php)');

        $repository = $this->getMockForAbstractClass(
            'tx_mklib_repository_Abstract',
            [],
            '',
            false,
            false,
            false,
            ['findByUid']
        );
        $repository->expects(self::once())
            ->method('findByUid')
            ->with(987654321)
            ->will(self::returnValue(null));
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            [],
            '',
            true,
            true,
            true,
            ['getSingleItemRepository']
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \Sys25\RnBase\Frontend\Request\Parameters::class,
            ['uid' => 987654321]
        );
        $configurations = $this->createConfigurations(
            [],
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod(
            [$action, 'handleRequest'],
            [&$parameters, &$configurations, &$viewData]
        );
    }

    /**
     * @group unit
     */
    public function testHandleRequestSetsFoundItemToViewData()
    {
        $repository = $this->getMockForAbstractClass(
            'tx_mklib_repository_Abstract',
            [],
            '',
            false,
            false,
            false,
            ['findByUid']
        );
        $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_model_Page',
            ['uid' => 987654321]
        );
        $repository->expects(self::once())
            ->method('findByUid')
            ->with(987654321)
            ->will(self::returnValue($model));
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            [],
            '',
            true,
            true,
            true,
            ['getSingleItemRepository']
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \Sys25\RnBase\Frontend\Request\Parameters::class,
            ['uid' => 987654321]
        );
        $configurations = $this->createConfigurations(
            [],
            'mklib',
            'mklib',
            $parameters
        );
        $request = new \Sys25\RnBase\Frontend\Request\Request($parameters, $configurations, '');
        $viewData = $request->getViewContext();

        $this->callInaccessibleMethod(
            [$action, 'handleRequest'],
            [$request]
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
            [],
            '',
            false,
            false,
            false,
            ['findByUid']
        );
        $repository->expects(self::once())
            ->method('findByUid')
            ->with(123456789)
            ->will(self::returnValue('model'));
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            [],
            '',
            true,
            true,
            true,
            ['getSingleItemRepository']
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \Sys25\RnBase\Frontend\Request\Parameters::class,
            ['uid' => 987654321]
        );
        $configurations = $this->createConfigurations(
            ['.' => ['uid' => 123456789]],
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $request = new \Sys25\RnBase\Frontend\Request\Request($parameters, $configurations, '');

        $this->callInaccessibleMethod(
            [$action, 'handleRequest'],
            [$request]
        );
    }

    /**
     * @group unit
     */
    public function testHandleRequestSubstitutesPageTitleNotIfNotConfigured()
    {
        self::markTestIncomplete("Error: Class 'TYPO3\CMS\Core\TimeTracker\NullTimeTracker' not found");

        \Sys25\RnBase\Utility\TYPO3::getTSFE()->getPageAndRootline();

        $repository = $this->getMockForAbstractClass(
            'tx_mklib_repository_Abstract',
            [],
            '',
            false,
            false,
            false,
            ['findByUid']
        );
        $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_model_Page',
            ['uid' => 987654321]
        );
        $repository->expects(self::once())
            ->method('findByUid')
            ->will(self::returnValue($model));
        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            [],
            '',
            true,
            true,
            true,
            ['getSingleItemRepository']
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \Sys25\RnBase\Frontend\Request\Parameters::class,
            ['uid' => 987654321]
        );
        $configurations = $this->createConfigurations(
            [],
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod(
            [$action, 'handleRequest'],
            [&$parameters, &$configurations, &$viewData]
        );

        self::assertNotEquals(
            $this->defaultSubstitutedPageTitle,
            \Sys25\RnBase\Utility\TYPO3::getTSFE()->page['title'],
            '\Sys25\RnBase\Utility\TYPO3::getTSFE()->page[\'title\'] doch ersetzt'
        );
        self::assertNotEquals(
            $this->defaultSubstitutedPageTitle,
            \Sys25\RnBase\Utility\TYPO3::getTSFE()->indexedDocTitle,
            '\Sys25\RnBase\Utility\TYPO3::getTSFE()->indexedDocTitle doch ersetzt'
        );
    }

    /**
     * @group unit
     */
    public function testHandleRequestSubstitutesPageTitleIfConfigured()
    {
        self::markTestIncomplete("Error: Class 'TYPO3\CMS\Core\TimeTracker\NullTimeTracker' not found");

        \Sys25\RnBase\Utility\TYPO3::getTSFE()->getPageAndRootline();

        $repository = $this->getMockForAbstractClass(
            'tx_mklib_repository_Abstract',
            [],
            '',
            false,
            false,
            false,
            ['findByUid']
        );
        $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_model_Page',
            ['uid' => 987654321]
        );
        $repository->expects(self::once())
            ->method('findByUid')
            ->will(self::returnValue($model));

        $action = $this->getMockForAbstractClass(
            'tx_mklib_action_ShowSingeItem',
            [],
            '',
            true,
            true,
            true,
            ['getSingleItemRepository', 'getConfId']
        );
        $action->expects(self::once())
            ->method('getSingleItemRepository')
            ->will(self::returnValue($repository));

        $action->expects(self::any())
            ->method('getConfId')
            ->will(self::returnValue('myAction.'));

        $parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \Sys25\RnBase\Frontend\Request\Parameters::class,
            ['uid' => 987654321]
        );
        $configurations = $this->createConfigurations(
            ['myAction.' => ['substitutePageTitle' => true]],
            'mklib',
            'mklib',
            $parameters
        );
        $viewData = $configurations->getViewData();
        $action->setConfigurations($configurations);

        $this->callInaccessibleMethod(
            [$action, 'handleRequest'],
            [&$parameters, &$configurations, &$viewData]
        );

        self::assertEquals(
            $this->defaultSubstitutedPageTitle,
            \Sys25\RnBase\Utility\TYPO3::getTSFE()->page['title'],
            '\Sys25\RnBase\Utility\TYPO3::getTSFE()->page[\'title\'] falsch ersetzt'
        );
        self::assertEquals(
            $this->defaultSubstitutedPageTitle,
            \Sys25\RnBase\Utility\TYPO3::getTSFE()->indexedDocTitle,
            '\Sys25\RnBase\Utility\TYPO3::getTSFE()->indexedDocTitle falsch ersetzt'
        );
    }
}
