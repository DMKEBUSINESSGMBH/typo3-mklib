<?php
/**
 * @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 michael Wagner <michael.wagner@dmk-ebusiness.de>
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

/**
 * @deprecated we dont want to use static methods anymore but keep backwards
 * compatibilty. So the only way is to move all code to a new class and keep
 * this class just as proxy
 * use Tx_Mklib_Database_Connection instead
 */
class tx_mklib_util_DB extends tx_rnbase_util_DB
{
    /**
     * @var string
     */
    protected static $databaseConnectionClass = 'Tx_Mklib_Database_Connection';

    /**
     * @var int
     */
    const DELETION_MODE_HIDE = 0;

    /**
     * @var int
     */
    const DELETION_MODE_SOFTDELETE = 1;

    /**
     * @var int
     */
    const DELETION_MODE_REALLYDELETE = 2;
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_DB.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_DB.php'];
}
