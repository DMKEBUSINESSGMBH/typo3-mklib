<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_util
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
 ***************************************************************/
tx_rnbase::load('Tx_Rnbase_Utility_T3General');
tx_rnbase::load('tx_rnbase_util_Typo3Classes');
tx_rnbase::load('tx_rnbase_util_Wizicon');

/**
 * Diese Klasse fügt das Wizzard Icon hinzu
 *
 * Folgendes muss in die ext_tables.php, um das Icon zu registrieren!
 * // Wizzard Icon
 * if (TYPO3_MODE=='BE') {
 *  $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_mklib_util_WizIcon'] = tx_rnbase_util_Extensions::extPath($_EXTKEY).'util/class.tx_mklib_util_WizIcon.php';
 * }
 * in der locallang_db.xml der Extension müssen/sollten folgende label gesetzt sein:
 *      plugin.mklib.label
 *      plugin.mklib.description
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner
 */
class tx_mklib_util_WizIcon extends tx_rnbase_util_Wizicon
{

    /**
     * Das muss von der Kindklasse überschrieben werden!!
     *
     * @var     string
     */
    protected $extKey = 'mklib';

    /**
     * Der Pfad zum Icon, kann Überschrieben werden, wenn nötig.
     *
     * @var     string
     */
    protected $iconPath = '/ext_icon.gif';

    /**
     * @return array
     */
    protected function getPluginData()
    {
        return array(
            'tx_' . $this->extKey => array(
                'icon'        => tx_rnbase_util_Extensions::extRelPath($this->extKey) . 'ext_icon.gif',
                'title'       => 'plugin.' . $this->extKey . '.label',
                'description' => 'plugin.' . $this->extKey . '.description'
            )
        );
    }

    /**
     * @return string
     */
    protected function getLLFile()
    {
        return tx_rnbase_util_Extensions::extPath($this->extKey) . 'locallang_db.xml';
    }
}
