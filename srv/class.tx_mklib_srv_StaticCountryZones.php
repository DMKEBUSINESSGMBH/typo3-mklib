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

/**
 * @author Hannes Bochmann
 */
class tx_mklib_srv_StaticCountryZones extends tx_mklib_repository_Abstract
{
    /**
     * @return string
     */
    public function getSearchClass()
    {
        return 'tx_mklib_search_StaticCountryZones';
    }

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
        $fields = $options = [];
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
        $fields = $options = [];
        $fields['STATICCOUNTRYZONE.zn_code'][OP_EQ] = strtoupper($znCode);

        return $this->search($fields, $options);
    }

    public function create(array $data)
    {
        \Sys25\RnBase\Utility\Debug::debug([
            'creating a static country zone via the service can\'t be done.',
        ], __METHOD__.' Line: '.__LINE__); // @TODO: remove me
    }

    public function handleUpdate(\Sys25\RnBase\Domain\Model\RecordInterface $model, array $data, $where = '')
    {
        \Sys25\RnBase\Utility\Debug::debug([
            'updating a static country zone via the service can\'t be done.',
        ], __METHOD__.' Line: '.__LINE__); // @TODO: remove me
    }

    public function handleDelete(\Sys25\RnBase\Domain\Model\RecordInterface $model, $where = '', $mode = 0, $table = null)
    {
        \Sys25\RnBase\Utility\Debug::debug([
            'deleting a static country zone via the service can\'t be done.',
        ], __METHOD__.' Line: '.__LINE__); // @TODO: remove me
    }

    public function handleCreation(array $data)
    {
        \Sys25\RnBase\Utility\Debug::debug([
            'creating a static country zone via the service can\'t be done.',
        ], __METHOD__.' Line: '.__LINE__); // @TODO: remove me
    }
}
