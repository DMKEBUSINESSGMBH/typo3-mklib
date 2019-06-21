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
tx_rnbase::load('tx_mklib_action_AbstractList');

/**
 * @author Thomas Reuleke
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_action_ListPages extends tx_mklib_action_AbstractList
{
    /**
     * Liefert die Service Klasse, welche das Suchen übernimmt.
     *
     * @return tx_mklib_interface_Repository
     */
    protected function getRepository()
    {
        return tx_rnbase::makeInstance('tx_mklib_repository_Pages');
    }

    /**
     * Liefert den Default-Namen des Templates. Über diesen Namen
     * wird per Konvention auch auf ein per TS konfiguriertes HTML-Template
     * geprüft. Dessen Key wird aus dem Name und dem String "Template"
     * gebildet: [tmpname]Template.
     *
     * @return string
     */
    protected function getTemplateName()
    {
        return 'listpages';
    }
}
