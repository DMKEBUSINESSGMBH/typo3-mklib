<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 * Basis Linker.
 */
abstract class tx_mklib_mod1_linker_Base
{
    /**
     * gibt den Namen des Links/der Action zurück.
     *
     * @return string
     */
    abstract protected function getActionName();

    /**
     * Linker Html ausgeben.
     *
     * @param Tx_Rnbase_Domain_Model_RecordInterface $item
     * @param tx_rnbase_util_FormTool                $formTool
     *
     * @return string
     */
    public function makeLink(Tx_Rnbase_Domain_Model_RecordInterface $oItem, $oFormTool)
    {
        $sOut = $oFormTool->createSubmit(
            $this->getActionName().'['.get_class($oItem).'|'.$oItem->getUid().']',
            $GLOBALS['LANG']->getLL('label_show_details')
        );

        return $sOut;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/decorator/class.tx_mklib_mod1_decorator_Base.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/decorator/class.tx_mklib_mod1_decorator_Base.php'];
}
