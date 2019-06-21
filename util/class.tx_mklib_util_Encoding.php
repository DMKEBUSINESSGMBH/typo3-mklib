<?php

/**
 * Class for encodings.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_Encoding
{
    /**
     * Liefert die Zeichencodierung der Umgebung.
     *
     * @return string
     */
    public static function getTypo3Encoding()
    {
        if (TYPO3_MODE == 'FE') {
            $charset = $GLOBALS['TSFE']->renderCharset;
        } elseif (is_object($GLOBALS['LANG'])) { // BE assumed:
            $charset = $GLOBALS['LANG']->charSet;
        } else { // best guess
            $charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'];
        }

        return $charset;
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
     * @return Ambigous <mixed, Traversable, Tx_Rnbase_Domain_Model_RecordInterface, string>
     */
    public static function convertEncoding(
        $var,
        $toEncoding = null,
        $fromEncoding = null,
        $forceEncoding = false
    ) {
        // use Typo3 encoding
        if (is_null($toEncoding)) {
            $toEncoding = self::getTypo3Encoding();
        }
        // convert array recursive
        if ($var instanceof tx_rnbase_model_data) {
            $var->record = self::convertEncoding(
                $var->record,
                $toEncoding,
                $fromEncoding
            );
        } elseif (is_array($var) || (is_object($var) && $var instanceof Traversable)) {
            foreach ($var as &$value) {
                $value = self::convertEncoding(
                    $value,
                    $toEncoding,
                    $fromEncoding
                );
            }
        } // convert models record
        elseif (is_object($var)) {
            throw new InvalidArgumentException(
                'Object "'.get_class($var).'" was not supportet for convertEncoding.'.
                'Possible types are string, array or an object '.
                '(instanceof "Traversable" or "Tx_Rnbase_Domain_Model_RecordInterface").',
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mklib']['baseExceptionCode'].'5'
            );
        } // do nothing, if we have an empty sting or a number
        elseif (empty($var) || is_numeric($var)) {
            //$var = $var;
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
        switch (strtolower($encoding)) {
            case 'utf-8':
            case 'utf-32':
            case 'utf-16':
                return strtolower($encoding) === strtolower($utf8Detect);
            case 'iso-8859-1':
                return false === $utf8Detect
                    && false !== mb_detect_encoding(strval($var), $encoding, true);
        }
    }

    /**
     * Liefert die.
     *
     * @param string $var
     *
     * @return string|false
     */
    public static function detectUtfEncoding($var)
    {
        tx_rnbase::load('tx_rnbase_util_Strings');
        $bytes = tx_rnbase_util_Strings::isUtf8String($var);
        $encoding = false;
        switch ($bytes) {
            case 2:
                $encoding = 'UTF-8';
                break;
            case 3:
                $encoding = 'UTF-16';
                break;
            case 4:
                $encoding = 'UTF-32';
                break;
        }

        return $encoding;
    }
}

if (defined('TYPO3_MODE')
    && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Encoding.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Encoding.php'];
}
