<?php
/**
 * @author mwagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
 */

/**
 * Model eine Währung.
 *
 * @TODO:   Die verschiedenen Währungen müssen irgendwo hinterlegt und Konfiguriert werden.
 *          Ideal wäre wahrscheinlich eine eigene Currency-Extension.
 *          Alternativ könnte dies auch über Typoscript oder eine Konfigurationsdatei (XML!?) geschehen.
 *          Die könnte eine Tabelle mit den benötigten Daten bereitstellen.
 *          Ein Scheduler, welcher die aktuellen Kurse aktualisiert,
 *          um zwischen Währungen umzurechnen wäre denkbar.
 */
class tx_mklib_model_Currency
{
    private $record = [];

    public function __construct(array $options = [])
    {
        $data = [];
        $data['symbol'] = $options['symbol'] ? $options['symbol'] : '';
        $data['symbolHtmlEntity'] = $options['symbolHtmlEntity'] ?
                $options['symbolHtmlEntity'] : htmlentities($data['symbol'], ENT_QUOTES, 'UTF-8');
        $data['plusSign'] = $options['plusSign'] ? (bool) $options['plusSign'] : false;
        $data['format'] = $options['format'] ? $options['format'] : '{sign}{value} {currency}';
        $data['decimals'] = $options['decimals'] ? intval($options['decimals']) : 2;
        $data['delimiter'] = $options['delimiter'] ? $options['delimiter'] : '.';
        $data['thousands'] = $options['thousands'] ? $options['thousands'] : '';
        $this->record = $data;
    }

    /**
     * @param array $options
     *
     * @return tx_mklib_model_Currency
     */
    protected static function makeInstance(array $options = [])
    {
        // @TODO: caching umstellen, wenn in den options mehr als nur das symbol steckt.
        // $key = md5(serialize($options));
        $key = $options['symbol'];
        // @todo why is this non static variable used? is static $instances; missing?
        if (!$instances[$key]) {
            $instances[$key] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_model_Currency', $options);
        }

        return $instances[$key];
    }

    /**
     * @see     http://en.wikipedia.org/wiki/ISO_3166-1
     *
     * @param string $country Country in ISO 3166 Alpha 2 code
     *
     * @return tx_mklib_model_Currency
     */
    public static function getByCountry($country = 'DE')
    {
        //@TODO: anhand des landes den currency code herausfinden
        if ('DE' === $country) {
            return self::getByCurrencyCode('EUR');
        } else {
            throw \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_exception_InvalidConfiguration', __METHOD__.': Currency ordentlich implementieren, bzw. Konzept entwickeln!');
        }

        return null;
    }

    /**
     * @TODO    konzept, wo die currencys konfigurieren/die daten holen?
     *
     * @see     http://www.xe.com/symbols.php
     *
     * @param string $currency
     *
     * @return tx_mklib_model_Currency
     */
    public static function getByCurrencyCode($currency = 'EUR')
    {
        if ('EUR' === $currency) {
            $options = [];
            $options['symbol'] = '€';
            $options['plusSign'] = false;
            $options['format'] = '{sign}{value} {currency}';
            $options['decimals'] = 2;
            $options['delimiter'] = ',';
            $options['thousands'] = '.';
        } else {
            throw \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_exception_InvalidConfiguration', __METHOD__.': Currency ordentlich implementieren, bzw. Konzept entwickeln!');
        }

        return self::makeInstance($options);
    }

    /**
     * @param float $value
     *
     * @return string
     */
    protected function numberFormat($value)
    {
        $value = number_format(doubleval($value), $this->record['decimals'], $this->record['delimiter'], $this->record['thousands']);

        return $value;
    }

    /**
     * Formatiert einen Wert anhand der aktuellen Wärung/Konfiguration.
     *
     * @param float $value
     * @param bool  $htmlEntities
     *
     * @return string
     */
    public function getFormatted($value, $htmlEntities = true)
    {
        $neg = doubleval($value) < 0;
        $value = $this->numberFormat(abs($value));

        $replaceArray = [
                '{sign}' => '%1$s', // string
                '{value}' => '%2$s', // string
                '{currency}' => '%3$s', // string
            ];

        $format = str_replace(array_keys($replaceArray), array_values($replaceArray), $this->record['format']);

        return sprintf(
            $format,
            // {sign}
                ($neg) ? '-' : ($this->record['plusSign'] ? '+' : ''),
            // {value}
                $value,
            // {currency}
                ($htmlEntities ? $this->record['symbolHtmlEntity'] : $this->record['symbol'])
        );
    }

    public function __toString()
    {
        $out = get_class($this)."\n\nData:\n";
        while (list($key, $val) = each($this->record)) {
            $out .= $key.' = '.$val."\n";
        }
        reset($this->record);

        return $out;
    }
}
