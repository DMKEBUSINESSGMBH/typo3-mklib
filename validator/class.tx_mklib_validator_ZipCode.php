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

/**
 * Die Klasse stellt Funktionen für die Validierung von Postleitzahlen zur Verfügung.
 *
 * @author René Nitzsche
 */
class tx_mklib_validator_ZipCode
{
    public static $instance;

    /**
     * Liefert eine instanz des Validators.
     *
     * @return tx_mklib_validator_ZipCode
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_validator_ZipCode');
        }

        return self::$instance;
    }

    /**
     * Liefert für ein Land einen Hinweistext für das PLZ-Format.
     */
    public static function getFormatInfo(tx_mklib_interface_IZipCountry $country): string
    {
        $rule = 9 == $country->getZipRule() ? $country->getZipRule().'_'.$country->getISO2() : $country->getZipRule();
        $labelKey = 'LLL:EXT:mklib/Resources/Private/Language/locallang.xlf:label_ziperror_r'.$rule;

        return sprintf($GLOBALS['LANG']->sL($labelKey), $country->getZipLength());
    }

    /**
     * Validiert einen PLZ-String für ein Land.
     *
     * @param string $zip
     *
     * @return bool
     */
    public static function validate(tx_mklib_interface_IZipCountry $country, $zip)
    {
        switch ($country->getZipRule()) {
            case 0: // no rule set
                $result = true;
                if (Sys25\RnBase\Utility\Logger::isNoticeEnabled()) {
                    Sys25\RnBase\Utility\Logger::notice('No zip rule for country defined.', 'mklib', ['zip' => $zip, 'getISO2' => $country->getISO2(), 'getZipLength' => $country->getZipLength(), 'getZipRule' => $country->getZipRule()]);
                }

                break;
            case 1: // maximum length without gaps
                $result = self::validateMaxLengthWG($country, $zip);
                break;
            case 2: // maximum length numerical without gaps
                $result = self::validateMaxLengthNumWG($country, $zip);
                break;
            case 3: // exact length without gaps
                $result = self::validateLengthWG($country, $zip);
                break;
            case 4: // exact length numerical without gaps
                $result = self::validateLengthNumWG($country, $zip);
                break;
            case 5: // maximum length with gaps
                $result = self::validateMaxLength($country, $zip);
                break;
            case 6: // maximum length numerical with gaps
                $result = self::validateMaxLengthNum($country, $zip);
                break;
            case 7: // exact length with gaps
                $result = self::validateLength($country, $zip);
                break;
            case 8: // exact length numerical with gaps
                $result = self::validateLengthNum($country, $zip);
                break;
            case 9: // special rules
                $result = self::validateSpecial($country, $zip);
                break;
            default:
                $result = false;
                break;
        }

        return $result;
    }

    /**
     * http://help.sap.com/saphelp_nw2004s/helpdata/en/0d/40bb3acf19c731e10000000a114084/content.htm.
     *
     * @param string $zip
     *
     * @return bool
     */
    private static function validateSpecial(tx_mklib_interface_IZipCountry $country, $zip)
    {
        return match ($country->getISO2()) {
            'CA' => preg_match('/^[A-Za-z]\d[A-Za-z] \d[A-Za-z]\d$/', $zip) > 0,
            'SW', 'GR', 'SK', 'CZ' => preg_match('/^\d\d\d \d\d$/', $zip) > 0,
            'PT' => preg_match('/^\d\d\d\d-\d\d\d$/', $zip) > 0 || preg_match('/^\d\d\d\d$/', $zip) > 0,
            'NL' => preg_match('/^\d\d\d\d [A-Za-z][A-Za-z]$/', $zip) > 0,
            'PL' => preg_match('/^\d\d-\d\d\d$/', $zip) > 0,
            'KR' => preg_match('/^\d\d\d-\d\d\d$/', $zip) > 0,
            default => false,
        };
    }

    /**
     * @param string $zip
     */
    private static function validateMaxLengthWG(tx_mklib_interface_IZipCountry $country, $zip): bool
    {
        return preg_match('/^[A-Za-z0-9]{1,'.$country->getZipLength().'}$/', $zip) > 0;
    }

    /**
     * @param string $zip
     */
    private static function validateMaxLengthNumWG(tx_mklib_interface_IZipCountry $country, $zip): bool
    {
        return preg_match('/^[0-9]{1,'.$country->getZipLength().'}$/', $zip) > 0;
    }

    /**
     * @param string $zip
     */
    private static function validateLengthWG(tx_mklib_interface_IZipCountry $country, $zip): bool
    {
        return preg_match('/^[A-Za-z0-9]{'.$country->getZipLength().'}$/', $zip) > 0;
    }

    /**
     * @param string $zip
     */
    private static function validateLengthNumWG(tx_mklib_interface_IZipCountry $country, $zip): bool
    {
        return preg_match('/^[0-9]{'.$country->getZipLength().'}$/', $zip) > 0;
    }

    /**
     * @param string $zip
     */
    private static function validateMaxLength(tx_mklib_interface_IZipCountry $country, $zip): bool
    {
        return preg_match('/^[ A-Za-z0-9]{1,'.$country->getZipLength().'}$/', $zip) > 0;
    }

    /**
     * @param string $zip
     */
    private static function validateMaxLengthNum(tx_mklib_interface_IZipCountry $country, $zip): bool
    {
        return preg_match('/^[ 0-9]{1,'.$country->getZipLength().'}$/', $zip) > 0;
    }

    /**
     * @param string $zip
     */
    private static function validateLength(tx_mklib_interface_IZipCountry $country, $zip): bool
    {
        return preg_match('/^[ A-Za-z0-9]{'.$country->getZipLength().'}$/', $zip) > 0;
    }

    /**
     * @param string $zip
     */
    private static function validateLengthNum(tx_mklib_interface_IZipCountry $country, $zip): bool
    {
        return preg_match('/^[ 0-9]{'.$country->getZipLength().'}$/', $zip) > 0;
    }
}
