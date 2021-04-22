<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2014 DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
 * All rights reserved
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

/**
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_util_FlexForm
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param string $flexForm
     *
     * @return tx_mklib_util_FlexForm
     */
    public static function getInstance($flexForm)
    {
        return tx_rnbase::makeInstance(
            'tx_mklib_util_FlexForm',
            $flexForm
        );
    }

    /**
     * @param string $flexForm
     */
    public function __construct($flexForm)
    {
        $flex = $this->xml2array($flexForm);
        $this->data = empty($flex['data']) ? [] : $flex['data'];
    }

    /**
     * Return value from somewhere inside a FlexForm structure.
     *
     * @param array  $T3FlexForm_array FlexForm data
     * @param string $fieldName        Field name to extract. Can be given like "test/el/2/test/el/field_templateObject" where each part will dig a level deeper in the FlexForm data.
     * @param string $sheet            Sheet pointer, eg. "sDEF
     * @param string $lang             Language pointer, eg. "lDEF
     * @param string $value            Value pointer, eg. "vDEF
     *
     * @return string the content
     */
    public function get($fieldName, $sheet = 'sDEF', $lang = 'lDEF', $value = 'vDEF')
    {
        if (empty($this->data[$sheet][$lang])) {
            return null;
        }
        $sheetArray = $this->data[$sheet][$lang];
        if (!is_array($sheetArray)) {
            return null;
        }

        return $this->getFromSheetArray(
            $sheetArray,
            explode('/', $fieldName),
            $value
        );
    }

    /**
     * Returns part of $sheetArray pointed to by the keys in $fieldNameArray.
     *
     * @param array $sheetArray Multidimensiona array, typically FlexForm contents
     * @param intnteger counterparts, but rather traverse the current position in the array an return element number X (whether this is right behavior is not settled yet...)
     * @param string $value Value for outermost key, typ. "vDEF" depending on language.
     *
     * @return mixed The value, typ. string.
     */
    protected function getFromSheetArray($sheetArray, $fieldNameArr, $value)
    {
        $tempArr = $sheetArray;
        foreach ($fieldNameArr as $k => $v) {
            if (is_numeric($v)) {
                if (is_array($tempArr)) {
                    $c = 0;
                    foreach ($tempArr as $values) {
                        if ($c == $v) {
                            $tempArr = $values;
                            break;
                        }
                        ++$c;
                    }
                }
            } else {
                $tempArr = $tempArr[$v];
            }
        }

        return $tempArr[$value];
    }

    /**
     * liefert die typo3 flexform utils.
     *
     * @return array
     */
    private function xml2array($xmlData)
    {
        $className = '\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility';

        return $className::xml2array($xmlData);
    }
}
