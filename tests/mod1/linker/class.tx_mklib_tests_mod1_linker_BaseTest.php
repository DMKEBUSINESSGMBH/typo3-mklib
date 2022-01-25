<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 DMK E-BUSINESS GmbH  <dev@dmk-ebusiness.de>
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
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_tests_mod1_linker_BaseTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    public function testMakeLink()
    {
        self::markTestIncomplete('Creating default object from empty value');

        //sprache auf default setzen damit wir die richtigen labels haben
        $GLOBALS['LANG']->lang = 'default';
        //damit labels geladen sind
        global $LOCAL_LANG;
        $label = 'Details';
        $LOCAL_LANG['default']['label_show_details'][0]['target'] = $label;
        $oLinker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_tests_fixtures_classes_DummyLinker');
        $oModel = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Domain\Model\BaseModel::class, 1);
        $oModel->uid = 1;
        $oFormTool = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Backend\Form\ToolBox::class);

        self::assertEquals(
            '<input type="submit"  class="btn btn-default btn-sm" name="showTest[Sys25\RnBase\Domain\Model\BaseModel|1]" value="'.$label.'" />',
            $oLinker->makeLink($oModel, $oFormTool),
            'Falscher Link.'
        );
    }
}
