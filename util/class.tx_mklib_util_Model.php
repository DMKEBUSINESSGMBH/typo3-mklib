<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 * Model Service.
 *
 * @author hbochmann
 */
class tx_mklib_util_Model
{
    /**
     * Trennt ein Feld innerhalb des Models nach $wordCount Wörtern in 'titletext' und 'restaftertitle'.
     *
     * @author 2011 hbochmann
     *
     * @param \Sys25\RnBase\Domain\Model\DataInterface   $model
     * @param string                                 $textField  | das feld, welches den text enthält
     * @param unknown_type                           $wordCount  | die anzahl der worte nach denen abgeschnitten wird
     * @param bool                                   $bStripTags Html vor dem Zerschneiden entfernen?
     */
    public static function splitTextIntoTitleAndRest(\Sys25\RnBase\Domain\Model\DataInterface $model, $textField = 'text', $wordCount = 3, $bStripTags = false)
    {
        //Html vorher entfernen? Wenn ja werden auch überflüssige Leerzeichen entfernt. aus "  " wird " "
        $sText = ($bStripTags) ? preg_replace('/\s\s+/', ' ', strip_tags($model->getProperty($textField))) : $model->getProperty($textField);
        //nur wenn der text noch nicht aufgesplittet wurde, sprich alles schon vorher einmal aufgerufen wurde
        $textExploded = explode(' ', $sText); //anhand des Leerzeichens trennen
        foreach ($textExploded as $key => $value) {//Wörter in array mit ersten 3 Worten und Rest trennen
            $newKey = ($key <= ($wordCount - 1)) ? 'titletext' : 'restaftertitle';
            $tempText[$newKey][] = $value;
        }
        //strings wieder durch leerzeichen getrennt zusammensetzen
        $model->setProperty('titletext', implode(' ', $tempText['titletext']));
        //gab es überhaupt mehr Wörter
        if (!empty($tempText['restaftertitle'])) {
            $model->setProperty('restaftertitle', implode(' ', $tempText['restaftertitle']));
        }
    }

    /**
     * Kürzt einen text im gegebenen Feld auf die Anzahl angegebener Zeichen
     * Es wird nach dem ersten Leerzeichen nach der Zeichenanzahl gesucht.
     *
     * @see tx_mklib_util_String::getShortened()
     *
     * @author 2011 hbochmann
     *
     * @param \Sys25\RnBase\Domain\Model\RecordInterface $model
     * @param string                                 $textField  | das feld, welches den text enthält
     * @param int                                    $charCount  | die anzahl der Zeichen nach denen abgeschnitten wird
     * @param bool                                   $bStripTags | Html vorher entfernen?
     * @param string                                 $suffix     für das neue Feld
     */
    public static function getShortenedText(\Sys25\RnBase\Domain\Model\DataInterface $model, $textField = 'text', $charCount = 150, $bStripTags = false, $suffix = 'shortened')
    {
        //Html vorher entfernen?
        $sText = ($bStripTags) ? strip_tags($model->getProperty($textField)) : $model->getProperty($textField);
        $model->setProperty($textField.$suffix, tx_mklib_util_String::crop($sText, $charCount));
    }

    /**
     * Entfernt doppelte Models aus einem Array und setzt den Key mit der Uid.
     *
     * @author 2011 mwagner
     *
     * @param array[\Sys25\RnBase\Domain\Model\RecordInterface] $aModels
     *
     * @return array[\Sys25\RnBase\Domain\Model\RecordInterface]
     */
    public static function uniqueModels($aModels)
    {
        $aUniques = [];
        if (is_array($aModels)) {
            foreach ($aModels as $oModel) {
                if (!isset($aUniques[$oModel->getUid()])) {
                    $aUniques[$oModel->getUid()] = $oModel;
                }
            }
        } // wurde nur ein Model übergeben?
        elseif (is_object($aModels)) {
            $aUniques[$aModels->getUid()] = $aModels;
        }

        return $aUniques;
    }

    /**
     * Generiert eine leere Instanz eines Models.
     * Dabei werden aus der TCA alle Spalten ausgelesen und gesetzt.
     *
     * @param string $sClassName
     *
     * @return \Sys25\RnBase\Domain\Model\RecordInterface
     */
    public static function getEmptyInstance($sClassName)
    {
        $key = 'empty_instance_'.$sClassName;
        $oInstance = tx_mklib_util_StaticCache::get($key);

        if (!is_object($oInstance)) {
            $oInstance = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($sClassName, ['uid' => 0]);
            $aColumns = $oInstance->getColumnNames();
            foreach ($aColumns as $sColumn) {
                $oInstance->setProperty($sColumn, '');
            }
            tx_mklib_util_StaticCache::set($key, $oInstance);
        }

        return $oInstance;
    }
}
