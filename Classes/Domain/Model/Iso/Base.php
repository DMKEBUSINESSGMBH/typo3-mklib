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
 * base methods for iso models.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class Tx_Mklib_Domain_Model_Iso_Base
{
    /**
     * The iso value.
     */
    private readonly ?string $value;

    /**
     * Returns the iso value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Make an instanceof ISO model.
     *
     * @param string $value
     *
     * @return Tx_Mklib_Domain_Model_Iso_Base
     */
    public static function getInstance($value)
    {
        return TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(static::class, $value);
    }

    /**
     * Constructor.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $this->normalize($value);
    }

    /**
     * Validates the ISO Value.
     *
     * @return bool
     */
    abstract public function validate();

    /**
     * Normalize the value.
     */
    private function normalize($value): ?string
    {
        $value = trim((string) $value);

        return preg_replace('/\s+/', '', $value);
    }
}
