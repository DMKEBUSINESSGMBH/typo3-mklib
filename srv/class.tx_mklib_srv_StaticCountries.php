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
class tx_mklib_srv_StaticCountries extends tx_mklib_repository_Abstract
{
    /**
     * @param int $isoNumber
     */
    public function getCountryByIsoNr($isoNumber)
    {
        $options = [];

        $fields = [
            'STATICCOUNTRY.cn_iso_nr' => [OP_EQ_INT => $isoNumber],
        ];

        return $this->searchSingle($fields, $options);
    }

    /**
     * @param string $germanShortName
     */
    public function getCountryByGermanShortName($germanShortName)
    {
        $options = [];

        $fields = [
            'STATICCOUNTRY.cn_short_de' => [OP_EQ => $germanShortName],
        ];

        return $this->searchSingle($fields, $options);
    }

    public function search($fields, $options)
    {
        // TCA gibt es nicht
        $options['enablefieldsoff'] = true;

        return parent::search($fields, $options);
    }

    /**
     * @return string
     */
    public function getSearchClass()
    {
        return 'tx_mklib_search_StaticCountries';
    }

    public function create(array $data)
    {
        throw new \Exception('creating a static country  via the service can\'t be done.');
    }

    public function handleUpdate(\Sys25\RnBase\Domain\Model\RecordInterface $model, array $data, $where = '', $debug = 0, $noQuoteFields = '')
    {
        throw new \Exception('updating a static country  via the service can\'t be done.');
    }

    public function handleDelete(\Sys25\RnBase\Domain\Model\RecordInterface $model, $where = '', $mode = 0, $table = null)
    {
        throw new \Exception('deleting a static country  via the service can\'t be done.');
    }

    public function handleCreation(array $data)
    {
        throw new \Exception('creating a static country  via the service can\'t be done.');
    }
}
