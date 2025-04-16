<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mklib" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

/*
 * alle benötigten Klassen einbinden etc.
 */
if (!defined('TYPO3')) {
    exit('Access denied.');
}

$_EXTKEY = 'mklib';

require_once TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'scheduler/ext_localconf.php';
require_once TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'srv/ext_localconf.php';

// das ist nur eine info für entwickler welcher basis exception code
// für diese extension verwendet wird. in diesem fall 400.
// also könnte ein valider exception code dieser extension 4001 sein
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['baseExceptionCode'] = 400;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_mklib_tca_eval_priceDecimalSeperator'] =
    'tx_mklib_tca_eval_priceDecimalSeperator';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_mklib_tca_eval_isoDate'] =
    'tx_mklib_tca_eval_isoDate';

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class] =
    ['className' => DMK\Mklib\Frontend\Authentication\FrontendUserAuthentication::class];
