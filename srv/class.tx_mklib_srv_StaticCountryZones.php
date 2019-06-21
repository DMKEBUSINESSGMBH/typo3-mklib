<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Hannes Bochmann <dev@dmk-ebusiness.de>
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

tx_rnbase::load('tx_mklib_srv_Base');

/**
 * @author Hannes Bochmann
 */
class tx_mklib_srv_StaticCountryZones extends tx_mklib_srv_Base
{
    /**
     * @return string
     */
    public function getSearchClass()
    {
        return 'tx_mklib_search_StaticCountryZones';
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_mklib_srv_Base::search()
     */
    public function search($fields, $options)
    {
        // TCA gibt es nicht
        $options['enablefieldsoff'] = true;

        return parent::search($fields, $options);
    }

    /**
     * Liefert alle Regionen anhand eines ISO-2-Länder-Codes.
     *
     * @param string $iso
     *
     * @return array[tx_mklib_model_StaticCountryZone]
     */
    public function getByIso2Code($iso)
    {
        $fields = $options = array();
        $fields['STATICCOUNTRYZONE.zn_country_iso_2'][OP_EQ] = strtoupper($iso);

        return $this->search($fields, $options);
    }

    /**
     * @param string $znCode
     *
     * @return array[tx_mklib_model_StaticCountryZone]
     */
    public function getByZnCode($znCode)
    {
        $fields = $options = array();
        $fields['STATICCOUNTRYZONE.zn_code'][OP_EQ] = strtoupper($znCode);

        return $this->search($fields, $options);
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_mklib_srv_Base::create()
     */
    public function create(array $data)
    {
        tx_rnbase::load('tx_rnbase_util_Debug');
        tx_rnbase_util_Debug::debug(array(
            'creating a static country zone via the service can\'t be done.',
        ), __METHOD__.' Line: '.__LINE__); // @TODO: remove me
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_mklib_srv_Base::handleUpdate()
     */
    public function handleUpdate(Tx_Rnbase_Domain_Model_RecordInterface $model, array $data, $where = '')
    {
        tx_rnbase::load('tx_rnbase_util_Debug');
        tx_rnbase_util_Debug::debug(array(
            'updating a static country zone via the service can\'t be done.',
        ), __METHOD__.' Line: '.__LINE__); // @TODO: remove me
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_mklib_srv_Base::handleDelete()
     */
    public function handleDelete(Tx_Rnbase_Domain_Model_RecordInterface $model, $where = '', $mode = 0, $table = null)
    {
        tx_rnbase::load('tx_rnbase_util_Debug');
        tx_rnbase_util_Debug::debug(array(
            'deleting a static country zone via the service can\'t be done.',
        ), __METHOD__.' Line: '.__LINE__); // @TODO: remove me
    }

    /**
     * (non-PHPdoc).
     *
     * @see tx_mklib_srv_Base::handleCreation()
     */
    public function handleCreation(array $data)
    {
        tx_rnbase::load('tx_rnbase_util_Debug');
        tx_rnbase_util_Debug::debug(array(
            'creating a static country zone via the service can\'t be done.',
        ), __METHOD__.' Line: '.__LINE__); // @TODO: remove me
    }
}
