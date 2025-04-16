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
 * Class for encodings.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_Encoding
{
    /**
     * Liefert die Zeichencodierung der Umgebung.
     */
    public static function getTypo3Encoding(): string
    {
        return 'utf-8';
    }

    /**
     * Encodes a value using mb_convert_encoding.
     *
     * @param mixed  $var
     *                             The string, array or object being encoded
     * @param string $toEncoding
     *                             The type of encoding that str is being converted to.
     *                             If toEncoding is not specified, the Typo3 encoding will be used.
     * @param string $fromEncoding
     *                             Is specified by character code names before conversion.
     *                             It is either an array, or a comma separated enumerated list.
     *                             If fromEncoding is not specified, the internal encoding will be used.
     *
     * @see Supported Encodings http://www.php.net/manual/en/mbstring.supported-encodings.php
     *
     * @param bool $forceEncoding
     *                            Forces encoding, if mb_detect_encoding returns correct encoding
     *
     * @return Ambigous <mixed, Traversable, Sys25\RnBase\Domain\Model\RecordInterface, string>
     */
    public static function convertEncoding(
        mixed $var,
        $toEncoding = null,
        $fromEncoding = null,
        $forceEncoding = false,
    ) {
        // use Typo3 encoding
        if (is_null($toEncoding)) {
            $toEncoding = self::getTypo3Encoding();
        }

        // convert array recursive
        if ($var instanceof Sys25\RnBase\Domain\Model\DataModel) {
            $var->setProperty(self::convertEncoding(
                $var->getProperty(),
                $toEncoding,
                $fromEncoding
            ));
        } elseif (is_iterable($var)) {
            foreach ($var as &$value) {
                $value = self::convertEncoding(
                    $value,
                    $toEncoding,
                    $fromEncoding
                );
            }
        } // convert models record
        elseif (is_object($var)) {
            throw new InvalidArgumentException('Object "'.$var::class.'" was not supportet for convertEncoding.Possible types are string, array or an object (instanceof "Traversable" or "\Sys25\RnBase\Domain\Model\RecordInterface").', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mklib']['baseExceptionCode'].'5');
        } // do nothing, if we have an empty sting or a number
        elseif (empty($var) || is_numeric($var)) {
            // $var = $var;
        } // convert only, if encoding does not match
        elseif ($forceEncoding
                || (
                    !self::isEncoding(strval($var), $toEncoding)
                    // @TODO: ist diese doppelte prüfung notwendig?
                    && self::isEncoding(strval($var), $fromEncoding)
                )
        ) {
            $var = mb_convert_encoding(
                strval($var),
                $toEncoding,
                $fromEncoding
            );
        }

        return $var;
    }

    /**
     * Prüft, ob ein String ein bestimmtes Encoding hat.
     *  mb_detect_encoding liefert auch bei ISO Codierung UTF-8.
     *  Deshalb prüfen wir immer das UTF-8 Encoding!
     *
     * @param string $var
     * @param string $encoding
     *
     * @return bool
     */
    public static function isEncoding($var, $encoding = null)
    {
        $utf8Detect = self::detectUtfEncoding($var);

        return match (strtolower($encoding)) {
            'utf-8', 'utf-32', 'utf-16' => strtolower($encoding) === strtolower($utf8Detect),
            'iso-8859-1' => false === $utf8Detect
                && false !== mb_detect_encoding(strval($var), $encoding, true),
            default => false,
        };
    }

    /**
     * Liefert die.
     *
     * @param string $var
     */
    public static function detectUtfEncoding($var): string|false
    {
        $bytes = Sys25\RnBase\Utility\Strings::isUtf8String($var);
        $encoding = false;

        return match ($bytes) {
            2 => 'UTF-8',
            3 => 'UTF-16',
            4 => 'UTF-32',
            default => $encoding,
        };
    }
}
