<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 * benötigte Klassen einbinden.
 */

/**
 * String Utilities.
 *
 * @author hbochmann
 */
class tx_mklib_util_String extends tx_mklib_util_Var
{
    public const emailRegex = '/[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,4}))/';

    /**
     * Kürzt einen Text Feld auf die Anzahl angegebener Zeichen
     * Es wird nach dem ersten Leerzeichen nach der Zeichenanzahl gesucht!
     *
     * @param string $sText   Der zu kürzende Text
     * @param int    $iLen    Die Länge des Textes
     * @param string $sSuffix Wird nur angehängt, wenn der text gekürtzt wurde!
     *
     * @return string
     */
    public static function crop($sText, $iLen = 150, $sSuffix = '')
    {
        if (// der Text ist länger als wir ihn brauchen
            strlen($sText) >= $iLen
            && // nur, wenn nach iCharPos noch ein Leerzeichen gefunden wurde.
            false !== ($iCharPos = strpos($sText, ' ', $iLen))
        ) {
            // der Text wird gekürzt
            $sText = substr($sText, 0, $iCharPos).$sSuffix;
        }

        return $sText;
    }

    /**
     * Bereinigt ein String von allen Zeichen außer Buchstaben und Leerzeichen.
     *
     * @TODO Leet beachten -> z.B. 4rsch
     *
     * @param string $string
     *
     * @return string
     */
    public static function removeNoneLetters($string)
    {
        return preg_replace('/[^a-zäöüß ]/i', '', $string);
    }

    /**
     * Convert HTML to plain text.
     *
     * Removes HTML tags and HTML comments and converts HTML entities
     * to their applicable characters.
     *
     * @param string $t
     *
     * @return string Converted string (utf8-encoded)
     */
    public static function html2plain($t)
    {
        return html_entity_decode(
            preg_replace(
                ['/(\s+|(<.*?>)+)/', '/<!--.*?-->/'],
                [' ', ''],
                $t
            ),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    /**
     * Entfernt hintereinander mehrfach vorkommende Inhalte.
     * Fünf Leerzeichen auf eines reduzieren: 'a b' = removeRepeatedlyOccurrings('a     b', ' ');.
     *
     * @param string $sHaystack
     * @param mixed  $mValue
     */
    public static function removeRepeatedlyOccurrings($sHaystack, $mValue = [' ', "\t", LF, CR, CRLF])
    {
        $mValue = is_array($mValue) ? $mValue : [$mValue];
        // Alle Werte Prprüfen/ersetzen
        foreach ($mValue as $sValue) {
            // Ist der String mindestens 2x hintereinander enthalten
            while (false !== strpos($sHaystack, $sValue.$sValue)) {
                // doppeltes vorkommen entfernen
                $sHaystack = str_replace($sValue.$sValue, $sValue, $sHaystack);
            }
        }

        return $sHaystack;
    }

    /**
     * lcfirst gibt es erst ab php 5.3.
     *
     * @param string $sString
     *
     * @return string
     */
    public static function lcfirst($sString)
    {
        if (function_exists('lcfirst')) {
            return lcfirst($sString);
        }
        $sString[0] = strtolower($sString[0]);

        return $sString;
    }

    /**
     * Wandelt einen String anhand eines Trennzeichens in CamelCase um.
     *
     * @param string $sString
     * @param string $sDelimiter
     *
     * @return string
     */
    public static function toCamelCase($sString, $sDelimiter = '_')
    {
        // $sCamelCase = implode('', array_map('ucfirst', explode($sDelimiter, $sString)));
        // das ist schneller als die array_map methode!
        $sCamelCase = '';
        foreach (explode($sDelimiter, $sString) as $sPart) {
            $sCamelCase .= ucfirst($sPart);
        }

        return self::lcfirst($sCamelCase);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public static function obfusicateContainedEmails($text)
    {
        $text = preg_replace_callback(
            self::emailRegex,
            [self, 'obfusicateEmail'],
            $text
        );

        return $text;
    }

    /**
     * @param array $emailParts | provided e.g. from preg_replace
     *
     * @return string
     */
    public static function obfusicateEmail($emailParts)
    {
        static $cObj;

        if (!$cObj) {
            $cObj = \Sys25\RnBase\Utility\TYPO3::getContentObject();
        }

        $emailMailTo = $cObj->getMailTo($emailParts[0], $emailParts[0]);

        return $emailMailTo[1];
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public static function convertContainedEmailsToMailToLinks($text)
    {
        $text = preg_replace_callback(
            self::emailRegex,
            [self, 'convertEmailToMailToLink'],
            $text
        );

        return $text;
    }

    /**
     * @param array $emailParts | provided e.g. from preg_replace
     *
     * @return string
     *
     * @todo prüfen ob es die email schon als link im text gibt und dann nicht parsen
     */
    public static function convertEmailToMailToLink($emailParts)
    {
        static $configurations;

        if (!$configurations) {
            /* @var $configurations \Sys25\RnBase\Configuration\Processor */
            $configurations = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Configuration\Processor::class);
        }

        $link = $configurations->createLink();
        $link->destination($emailParts[0]);

        return $link->makeTag();
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function removeLineBreaks($value, $replacement = '')
    {
        $value = str_replace("\r\n", $replacement, $value);
        $value = str_replace("\n", $replacement, $value);

        return $value;
    }

    /**
     * macht aus http://www.google.de, https://www.google.de, ftp://www.google.de,
     * ftps://www.google.de, www.google.de, email@domain.tld die passenden HTML Links.
     *
     * dabei wird der Text auch XSS geschützt
     *
     * @see http://buildinternet.com/2010/05/how-to-automatically-linkify-text-with-php-regular-expressions/
     *
     * @param string $text
     * @param string $aTagParams
     *
     * @return string
     */
    public static function convertUrlsInTextToLinks($text, $aTagParams = 'target="_blank"')
    {
        $nonebreakingSpaceChar = chr(160);
        $patternPrefix = "/(^|[\n\r\t$nonebreakingSpaceChar >\*({\-_])";
        $patternSuffix = "[^$nonebreakingSpaceChar \,\"\n\r\t<)}\*]*";
        $text = preg_replace("$patternPrefix([\w]*?)((ht|f)tp(s)?:\/\/[\w]+$patternSuffix)/is", "$1$2&lt;a $aTagParams href=\"$3\" &gt;$3&lt;/a&gt;", $text);
        $text = preg_replace("$patternPrefix([\w]*?)((www|ftp)\.$patternSuffix)/is", "$1$2&lt;a $aTagParams href=\"http://$3\" &gt;$3&lt;/a&gt;", $text);
        $text = preg_replace("$patternPrefix([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", '$1&lt;a href="mailto:$2@$3"&gt;$2@$3&lt;/a&gt;', $text);

        return html_entity_decode($text);
    }

    /**
     * Convert a telephone number into a full-featured RFC 3966 telephone number.
     *
     * @param string $orig Original telephone number, may be partial
     * @param array  $conf Configuration. Keys:
     *                     - "countryCode": default country code, "49" for Germany
     *                     - "areaCode": default area code, without leading zero
     *
     * @return string Full RFC 3966-compatible telephone number
     *
     * @author Christian Weiske <cweiske@cweiske.de>
     */
    public static function formatTelephoneNumberRfc3966($orig, $conf)
    {
        if (!isset($conf['countryCode'])) {
            $conf['countryCode'] = '49'; // germany
        }
        $num = preg_replace('#[^+0-9]#', '', $orig);
        if ('+' == substr($num, 0, 1)) {
            // full telephone number
            $tel = $num;
        } elseif ('00' == substr($num, 0, 2)) {
            // full number with country code, but 00 instead of +
            $tel = '+'.substr($num, 2);
        } elseif ('0' == substr($num, 0, 1)) {
            // full number without country code
            $tel = '+'.$conf['countryCode'].substr($num, 1);
        } else {
            // partial number, no country or area code
            $tel = '+'.$conf['countryCode'].$conf['areaCode'].$num;
        }

        return $tel;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function removeMultipleWhitespaces($string)
    {
        return preg_replace('/\s+/', ' ', $string);
    }
}
