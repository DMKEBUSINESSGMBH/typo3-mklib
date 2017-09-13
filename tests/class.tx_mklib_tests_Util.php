<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_tests
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

// backwards compatibility for TYPO3 version where the autoloading through
// composer is not fully implemented
if (!class_exists('DMK\Mklib\Utility\Tests')) {
    require_once \tx_rnbase_util_Extensions::extPath('mklib') . 'Classes/Utility/Tests.php';
}

/**
 * tx_mklib_tests_Util
 *
 * @package         TYPO3
 * @subpackage      mklib
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 * @deprecated use \DMK\Mklib\Utility\Tests instead
 */
class tx_mklib_tests_Util extends \DMK\Mklib\Utility\Tests
{
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_Util.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_Util.php']);
}
