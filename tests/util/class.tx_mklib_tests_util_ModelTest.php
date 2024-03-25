<?php
/**
 * @author Michael Wagner
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
 * benötigte Klassen einbinden.
 */

/**
 * Model util tests.
 */
class tx_mklib_tests_util_ModelTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * Prüfen ob alle leeren Elemente außer dem array gelöscht werden
     * und keys unberührt bleiben.
     */
    public function testSplitTextIntoTitleAndRest()
    {
        $aRecord = ['text' => 'ein ganz langer text mit vielen worten', 'htmltext' => 'ein <span> ganz </span> langer text mit vielen worten'];
        $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Domain\Model\DataModel::class, $aRecord);
        tx_mklib_util_Model::splitTextIntoTitleAndRest($model, 'text', 2);

        self::assertEquals('ein ganz', $model->getProperty('titletext'), 'field:titletext nicht korrekt');
        self::assertEquals('langer text mit vielen worten', $model->getProperty('restaftertitle'), 'field:restaftertitle nicht korrekt');

        tx_mklib_util_Model::splitTextIntoTitleAndRest($model, 'text', 3);

        self::assertEquals('ein ganz langer', $model->getProperty('titletext'), 'field:titletext nicht korrekt');
        self::assertEquals('text mit vielen worten', $model->getProperty('restaftertitle'), 'field:restaftertitle nicht korrekt');

        tx_mklib_util_Model::splitTextIntoTitleAndRest($model, 'htmltext', 3, false);

        self::assertEquals('ein <span> ganz', $model->getProperty('titletext'), 'field:titletext nicht korrekt');
        self::assertEquals('</span> langer text mit vielen worten', $model->getProperty('restaftertitle'), 'field:restaftertitle nicht korrekt');

        tx_mklib_util_Model::splitTextIntoTitleAndRest($model, 'htmltext', 3, true);

        self::assertEquals('ein ganz langer', $model->getProperty('titletext'), 'field:titletext nicht korrekt');
        self::assertEquals('text mit vielen worten', $model->getProperty('restaftertitle'), 'field:restaftertitle nicht korrekt');
    }

    /**
     * Prüfen ob alle leeren Elemente außer dem array gelöscht werden
     * und keys unberührt bleiben.
     */
    public function testGetShortenedText()
    {
        $aRecord = [
            'othertext' => 'ein ganz langer text mit vielen worten und noch viel viel viel viel mehr',
            'text' => 'ein ganz langer text mit vielen worten und noch viel viel viel viel mehr',
            'htmltext' => 'ein ganz langer text mit vielen worten und <span>noch viel viel</span> viel viel mehr',
        ];
        $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Domain\Model\DataModel::class, $aRecord);
        tx_mklib_util_Model::getShortenedText($model, 'othertext', 50);
        self::assertEquals('ein ganz langer text mit vielen worten und noch viel', $model->getProperty('othertextshortened'), 'field:othertextshortened nicht korrekt');

        tx_mklib_util_Model::getShortenedText($model);

        self::assertEquals('ein ganz langer text mit vielen worten und noch viel viel viel viel mehr', $model->getProperty('textshortened'), 'field:textshortened nicht korrekt');

        tx_mklib_util_Model::getShortenedText($model, 'htmltext', 50, false);

        self::assertEquals('ein ganz langer text mit vielen worten und <span>noch', $model->getProperty('htmltextshortened'), 'field:htmltextshortened nicht korrekt');

        tx_mklib_util_Model::getShortenedText($model, 'htmltext', 50, true);

        self::assertEquals('ein ganz langer text mit vielen worten und noch viel', $model->getProperty('htmltextshortened'), 'field:htmltextshortened nicht korrekt');
    }

    public function testUniqueModelArray()
    {
        $aArray = [
            \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Domain\Model\BaseModel::class, ['uid' => 5, 'name' => 'Model Nr. 5']),
            \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Domain\Model\BaseModel::class, ['uid' => 6, 'name' => 'Model Nr. 6']),
            \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Domain\Model\BaseModel::class, ['uid' => 5, 'name' => 'Model Nr. 5']),
            \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Sys25\RnBase\Domain\Model\BaseModel::class, ['uid' => 2, 'name' => 'Model Nr. 2']),
        ];
        $aUnique = tx_mklib_util_Model::uniqueModels($aArray);
        self::assertTrue(is_array($aUnique), 'No array given.');
        self::assertEquals(count($aUnique), 3, 'Array has a wrong count of entries.');
        self::assertArrayHasKey(2, $aUnique, 'Model with uid 2 not found');
        self::assertArrayHasKey(5, $aUnique, 'Model with uid 5 not found');
        self::assertArrayHasKey(6, $aUnique, 'Model with uid 6 not found');
    }

    public function testGetEmptyInstance()
    {
        $this->markTestSkipped('@TODO: implement!');
        // eine dummy tca erstellen und prüfen!

        \DMK\Mklib\Utility\Tests::getFixturePath('dummyTCA.php');
    }
}
