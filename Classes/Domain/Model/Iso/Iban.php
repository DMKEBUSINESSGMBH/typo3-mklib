<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

tx_rnbase::load('Tx_Mklib_Domain_Model_Iso_Base');

/**
 * Iban Model and Validatort based on jschaedl/Iban
 *
 * @package TYPO3
 * @subpackage Tx_Mklib
 * @author Michael Wagner
 * @author Jan Schaedlich <schaedlich.jan@gmail.com>
 * @copyright 2013 Jan Schaedlich
 * @link https://github.com/jschaedl/Iban
 */
class Tx_Mklib_Domain_Model_Iso_Iban extends Tx_Mklib_Domain_Model_Iso_Base
{
    const LOCALECODE_OFFSET = 0;
    const LOCALECODE_LENGTH = 2;
    const CHECKSUM_OFFSET = 2;
    const CHECKSUM_LENGTH = 2;
    const ACCOUNTIDENTIFICATION_OFFSET = 4;
    const INSTITUTEIDENTIFICATION_OFFSET = 4;
    const INSTITUTEIDENTIFICATION_LENGTH = 8;
    const BANKACCOUNTNUMBER_OFFSET = 12;
    const BANKACCOUNTNUMBER_LENGTH = 10;
    const IBAN_MIN_LENGTH = 15;

    protected static $letterMapping = array(
        1 => 'A',
        2 => 'B',
        3 => 'C',
        4 => 'D',
        5 => 'E',
        6 => 'F',
        7 => 'G',
        8 => 'H',
        9 => 'I',
        10 => 'J',
        11 => 'K',
        12 => 'L',
        13 => 'M',
        14 => 'N',
        15 => 'O',
        16 => 'P',
        17 => 'Q',
        18 => 'R',
        19 => 'S',
        20 => 'T',
        21 => 'U',
        22 => 'V',
        23 => 'W',
        24 => 'X',
        25 => 'Y',
        26 => 'Z'
    );

    protected static $ibanFormatMap = array(
        'AL' => '[0-9]{8}[0-9A-Z]{16}',
        'AD' => '[0-9]{8}[0-9A-Z]{12}',
        'AT' => '[0-9]{16}',
        'BE' => '[0-9]{12}',
        'BA' => '[0-9]{16}',
        'BG' => '[A-Z]{4}[0-9]{6}[0-9A-Z]{8}',
        'HR' => '[0-9]{17}',
        'CY' => '[0-9]{8}[0-9A-Z]{16}',
        'CZ' => '[0-9]{20}',
        'DK' => '[0-9]{14}',
        'EE' => '[0-9]{16}',
        'FO' => '[0-9]{14}',
        'FI' => '[0-9]{14}',
        'FR' => '[0-9]{10}[0-9A-Z]{11}[0-9]{2}',
        'GE' => '[0-9A-Z]{2}[0-9]{16}',
        'DE' => '[0-9]{18}',
        'GI' => '[A-Z]{4}[0-9A-Z]{15}',
        'GR' => '[0-9]{7}[0-9A-Z]{16}',
        'GL' => '[0-9]{14}',
        'HU' => '[0-9]{24}',
        'IS' => '[0-9]{22}',
        'IE' => '[0-9A-Z]{4}[0-9]{14}',
        'IL' => '[0-9]{19}',
        'IT' => '[A-Z][0-9]{10}[0-9A-Z]{12}',
        'KZ' => '[0-9]{3}[0-9A-Z]{13}',
        'KW' => '[A-Z]{4}[0-9]{22}',
        'LV' => '[A-Z]{4}[0-9A-Z]{13}',
        'LB' => '[0-9]{4}[0-9A-Z]{20}',
        'LI' => '[0-9]{5}[0-9A-Z]{12}',
        'LT' => '[0-9]{16}',
        'LU' => '[0-9]{3}[0-9A-Z]{13}',
        'MK' => '[0-9]{3}[0-9A-Z]{10}[0-9]{2}',
        'MT' => '[A-Z]{4}[0-9]{5}[0-9A-Z]{18}',
        'MR' => '[0-9]{23}',
        'MU' => '[A-Z]{4}[0-9]{19}[A-Z]{3}',
        'MC' => '[0-9]{10}[0-9A-Z]{11}[0-9]{2}',
        'ME' => '[0-9]{18}',
        'NL' => '[A-Z]{4}[0-9]{10}',
        'NO' => '[0-9]{11}',
        'PL' => '[0-9]{24}',
        'PT' => '[0-9]{21}',
        'RO' => '[A-Z]{4}[0-9A-Z]{16}',
        'SM' => '[A-Z][0-9]{10}[0-9A-Z]{12}',
        'SA' => '[0-9]{2}[0-9A-Z]{18}',
        'RS' => '[0-9]{18}',
        'SK' => '[0-9]{20}',
        'SI' => '[0-9]{15}',
        'ES' => '[0-9]{20}',
        'SE' => '[0-9]{20}',
        'CH' => '[0-9]{5}[0-9A-Z]{12}',
        'TN' => '[0-9]{20}',
        'TR' => '[0-9]{5}[0-9A-Z]{17}',
        'AE' => '[0-9]{19}',
        'GB' => '[A-Z]{4}[0-9]{14}'
    );

    public function validate()
    {
        if (!$this->isLengthValid()) {
            return false;
        } elseif (!$this->isLocalCodeValid()) {
            return false;
        } elseif (!$this->isFormatValid()) {
            return false;
        } elseif (!$this->isChecksumValid()) {
            return false;
        } else {
            return true;
        }
    }

    const VALIDATE_ERROR_LENGTH = 1;
    const VALIDATE_ERROR_LOCALCODE = 2;
    const VALIDATE_ERROR_FORMAT = 3;
    const VALIDATE_ERROR_CHECKSUM = 4;

    public function getValidateError()
    {
        if (!$this->isLengthValid()) {
            return self::VALIDATE_ERROR_LENGTH;
        } elseif (!$this->isLocalCodeValid()) {
            return self::VALIDATE_ERROR_LOCALCODE;
        } elseif (!$this->isFormatValid()) {
            return self::VALIDATE_ERROR_FORMAT;
        } elseif (!$this->isChecksumValid()) {
            return self::VALIDATE_ERROR_CHECKSUM;
        } else {
            return true;
        }
    }

    public function format()
    {
        return sprintf(
            '%s %s %s %s %s %s',
            $this->getLocaleCode() . $this->getChecksum(),
            substr($this->getInstituteIdentification(), 0, 4),
            substr($this->getInstituteIdentification(), 4, 4),
            substr($this->getBankAccountNumber(), 0, 4),
            substr($this->getBankAccountNumber(), 4, 4),
            substr($this->getBankAccountNumber(), 8, 2)
        );
    }

    public function getLocaleCode()
    {
        return substr($this->getValue(), self::LOCALECODE_OFFSET, self::LOCALECODE_LENGTH);
    }

    public function getChecksum()
    {
        return substr($this->getValue(), self::CHECKSUM_OFFSET, self::CHECKSUM_LENGTH);
    }

    public function getAccountIdentification()
    {
        return substr($this->getValue(), self::ACCOUNTIDENTIFICATION_OFFSET);
    }

    public function getInstituteIdentification()
    {
        return substr($this->getValue(), self::INSTITUTEIDENTIFICATION_OFFSET, self::INSTITUTEIDENTIFICATION_LENGTH);
    }

    public function getBankAccountNumber()
    {
        return substr($this->getValue(), self::BANKACCOUNTNUMBER_OFFSET, self::BANKACCOUNTNUMBER_LENGTH);
    }

    private function isLengthValid()
    {
        return strlen($this->getValue()) < self::IBAN_MIN_LENGTH ? false : true;
    }

    private function isLocalCodeValid()
    {
        $localeCode = $this->getLocaleCode();

        return !(isset(self::$ibanFormatMap[$localeCode]) === false);
    }

    private function isFormatValid()
    {
        $localeCode = $this->getLocaleCode();
        $accountIdentification = $this->getAccountIdentification();

        return !(preg_match('/' . self::$ibanFormatMap[$localeCode] . '/', $accountIdentification) !== 1);
    }

    private function isChecksumValid()
    {
        $localeCode = $this->getLocaleCode();
        $checksum = $this->getChecksum();
        $accountIdentification = $this->getAccountIdentification();
        $numericLocalCode = $this->getNumericLocaleCode($localeCode);
        $numericAccountIdentification = $this->getNumericAccountIdentification($accountIdentification);
        $invertedIban = $numericAccountIdentification . $numericLocalCode . $checksum;

        return $this->local_bcmod($invertedIban, 97) === '1';
    }

    private function getNumericLocaleCode($localeCode)
    {
        return $this->getNumericRepresentation($localeCode);
    }

    private function getNumericAccountIdentification($accountIdentification)
    {
        return $this->getNumericRepresentation($accountIdentification);
    }

    private function getNumericRepresentation($letterRepresentation)
    {
        $numericRepresentation = '';
        foreach (str_split($letterRepresentation) as $char) {
            if (array_search($char, self::$letterMapping)) {
                $numericRepresentation .= array_search($char, self::$letterMapping) + 9;
            } else {
                $numericRepresentation .= $char;
            }
        }

        return $numericRepresentation;
    }

    private function local_bcmod($x, $y)
    {
        if (!function_exists('bcmod')) {
            // workaround http://php.net/manual/en/function.bcmod.php#38474
            // or http://php.net/manual/en/function.bcmod.php#110896
            // not working!
            throw new Exception(
                'BC-Math module not installed.' .
                ' BC-Math functions are required for IBAN validation.'
            );
        }

        return bcmod($x, $y);
    }
}
