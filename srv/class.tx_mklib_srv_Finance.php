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
 * Service für alles rund um Finanzen.
 *
 * @author Michael Wagner
 */
class tx_mklib_srv_Finance extends Sys25\RnBase\Typo3Wrapper\Service\AbstractService
{
    /**
     * @return tx_mklib_model_Currency
     */
    public function getCurrency()
    {
        // @TODO: aktuellen Code auslesen und übergeben,

        return tx_mklib_model_Currency::getByCurrencyCode();
    }

    /**
     * Berechnet den Nettopreis anhand des Bruttopreises und des Steuersatzes.
     *
     * @param doubleval $gross
     * @param int       $tax
     *
     * @return float
     */
    public function getNetPriceByGrossPriceAndTax($gross, $tax)
    {
        // Rechnen wir mit Double?
        if (is_float($gross)) {
            return $this->getDoubleByInt($this->getIntByDouble($gross) / ((100 + $tax) / 100));
        }

        return $gross / ((100 + $tax) / 100);
    }

    /**
     * Berechnet den Bruttopreis anhand des Nettopreises und des Steuersatzes.
     *
     * @param int $tax
     *
     * @return float
     */
    public function getGrossPriceByNetPriceAndTax($net, $tax)
    {
        // Rechnen wir mit Double?
        if (is_float($net)) {
            return $this->getDoubleByInt($this->getIntByDouble($net) * (1 + $tax / 100));
        }

        return $net * (1 + $tax / 100);
    }

    /**
     * Berechnet den Bruttopreis anhand des Nettopreises und des Steuersatzes.
     *
     * @param int $tax
     *
     * @return float
     */
    public function getTaxAmountByNetPriceAndTax($net, $tax)
    {
        if (is_float($net)) {
            return $this->getDoubleByInt(
                $this->getIntByDouble($net) * ($tax / 100)
            );
        }

        return $net * ($tax / 100);
    }

    /**
     * Multipliziert den Preis mit einem Wert (Anzahl Produkte).
     *
     * @param doubleval $price
     * @param int       $quantity
     * @param bool      $formatted gibt an ob der Preis Formatiert ausgegeben werde soll
     *
     * @return float
     */
    public function getSumPriceByPriceAndQuantity($price, $quantity, $formatted = false)
    {
        $sum = $this->getDoubleByInt(
            $this->getIntByDouble($price) * $quantity
        );

        return $formatted ? $this->getCurrency()->getFormatted($sum) : $sum;
    }

    /**
     * Berechnet den Bruttopreis anhand des Nettopreises und des Steuersatzes.
     *
     * @param doubleval $net
     * @param doubleval $gross
     */
    public function getTaxAmountByNetAndGrossPrice($net, $gross)
    {
        return $this->getDoubleByInt(
            $this->getIntByDouble($gross) - $this->getIntByDouble($net)
        );
    }

    /**
     * Wandelt einen Doubole-Wert für berechnungen in einen Integer-Wert um.
     *
     * Wir wandeln den Wert für die Berechnung in einen Integer
     *
     * @see  http://javathreads.de/2009/03/niemals-mit-den-datentypen-float-oder-double-geldbetraege-berechnen/
     *
     * @param float $double
     * @param int   $digits
     */
    public function getIntByDouble($double, $digits = 4): int
    {
        $digits = intval('1'.str_repeat('0', $digits));

        // erst zu String, danach zu Integer!
        // (int) 40.05 = 4004
        // (int) (string) 40.05 = 4005
        return (int) (string) ($double * $digits);
    }

    /**
     * Wandelt einen Integer-Wert für berechnungen in einen Dounbe-Wert um.
     *
     * Wir wandeln den Wert für die Berechnung in einen Integer
     *
     * @see  http://javathreads.de/2009/03/niemals-mit-den-datentypen-float-oder-double-geldbetraege-berechnen/
     *
     * @param int    $digits
     * @param bool   $format    | soll die double Zahl formatiert werden?
     *                          Bsp: $int=8 --> ohne Format:8 mit Format:8.0000
     * @param string $delimiter
     *
     * @return float
     */
    public function getDoubleByInt($int, $digits = 4, $format = true, $delimiter = '.')
    {
        $baseInt = intval('1'.str_repeat('0', $digits));
        $doubleVal = floatval(floatval($int) / $baseInt);

        // @TODO: hierfür sollte das currency Objekt genutzt werden,
        // das beinhaltet digits, delemiter, etc.
        // das hier ist nur für die berechnung!
        return ($format) ? number_format($doubleVal, $digits, $delimiter, '') : $doubleVal;
    }

    /**
     * Rundet einen Double Wert auf die gegeben Stellen nach dem Komma AUF.
     *
     * @param doubleval $doubleValue
     * @param int       $digits
     * @param bool      $format
     * @param string    $delimiter
     *
     * @return doubleval
     */
    public function roundUpDouble($doubleValue, $digits = 4, $format = true, $delimiter = '.')
    {
        $baseInt = intval('1'.str_repeat('0', $digits));
        $roundedDoubleValue = ($doubleValue * $baseInt);
        // durch einen Bug wird z.B. die Zahl 2.2000 auf 2.21 gerundet. Damit
        // das nicht passiert prüfen wir ob die Zahl eine Kommastelle enthält
        // und runden nur dann weil wir sonst schon eine ganze Zahl haben
        if (strpos($roundedDoubleValue, '.')) {// Ist der $intValue schon eine ganze Zahl?
            $roundedDoubleValue = ceil($roundedDoubleValue) / $baseInt;
        } else {
            $roundedDoubleValue /= $baseInt;
        }

        // @TODO: hierfür sollte das currency Objekt genutzt werden,
        // das beinhaltet digits, delemiter, etc.
        // das hier ist nur für die berechnung!
        return ($format) ? number_format($roundedDoubleValue, $digits, $delimiter, '') : $roundedDoubleValue;
    }

    /**
     * Validate vatregno.
     *
     * @param string $country  cn_iso_2 value of country DE, CH etc...
     * @param string $vatregno
     */
    public function validateVatRegNo($country, $vatregno)
    {
        // if there is a uid, so get from database.
        if (!is_object($country) && (string) (int) $country === (string) $country) {
            $country = tx_mklib_util_ServiceRegistry::getStaticCountriesService()->findByUid($country);
        }

        // get iso from model
        if ($country instanceof tx_mklib_model_StaticCountry) {
            $country = $country->getCnIso_2();
        }

        $result = true;

        return match (strtoupper($country)) {
            'DE' => preg_match('/^DE\d{9}$/', $vatregno) > 0,
            'PL' => preg_match('/^PL\d{10}$/', $vatregno) > 0,
            'FR' => preg_match('/^FR[A-Za-z0-9]{2} \d{9}$/', $vatregno) > 0,
            'LU' => preg_match('/^LU\d{8}$/', $vatregno) > 0,
            'BE' => preg_match('/^BE\d{10}$/', $vatregno) > 0,
            'NL' => preg_match('/^NL[A-Za-z0-9]{10}$/', $vatregno) > 0,
            'DK' => preg_match('/^DK\d{2} \d{2} \d{2} \d{2}$/', $vatregno) > 0,
            'CZ' => preg_match('/^CZ\d{8,10}$/', $vatregno) > 0,
            'AT' => preg_match('/^ATU[A-Za-z0-9]{8}$/', $vatregno) > 0,
            default => $result,
        };
    }
}
