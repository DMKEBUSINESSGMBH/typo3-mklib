<?php

declare(strict_types=1);

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

    public function search(array $fields, array $options)
    {
        // TCA gibt es nicht
        $options['enablefieldsoff'] = true;

        return parent::search($fields, $options);
    }

    protected function getSearchClass(): string
    {
        return 'tx_mklib_search_StaticCountries';
    }

    public function create(array $data): never
    {
        throw new Exception("creating a static country  via the service can't be done.");
    }

    public function handleUpdate(Sys25\RnBase\Domain\Model\RecordInterface $model, array $data, $where = '', $debug = 0, $noQuoteFields = ''): never
    {
        throw new Exception("updating a static country  via the service can't be done.");
    }

    public function handleDelete(Sys25\RnBase\Domain\Model\RecordInterface $model, $where = '', $mode = 0, $table = null): never
    {
        throw new Exception("deleting a static country  via the service can't be done.");
    }

    public function handleCreation(array $data): never
    {
        throw new Exception("creating a static country  via the service can't be done.");
    }
}
