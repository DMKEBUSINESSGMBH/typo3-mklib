<?php

tx_rnbase::load('tx_rnbase_util_BaseMarker');

/**
 * Base class for Markers.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_mod1_export_Util
{
    /**
     * Sendet die Headerdaten.
     *
     * @param array $options
     */
    public static function sendHeaders(array $options = array())
    {
        $fileName = empty($options['filename']) ? 'export.dat' : $options['filename'];
        $contentType = empty($options['contenttype']) ? 'application/octet-stream' : $options['contenttype'];

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: public', false);
        header('Content-Description: File Transfer');
        header('Content-Type: '.$contentType.'');
        header('Accept-Ranges: bytes');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Content-Transfer-Encoding: binary');

        if (!empty($options['additional.']) && is_array($options['additional.'])) {
            foreach ($options['additional.'] as $name => $value) {
                header($name.': '.$value, null, null);
            }
        }

        // Ausgabe-Puffer leeren.
        // Damit wird direkt der Download-Dialig geöffnet
        // und direkt an den Client gestreamt.
        // hier keinesfalls ob_end_clean nutzen,
        // da sonnst der TYPO3 compression handler umgangen
        // und der output zerstört wird
        ob_flush();
    }

    /**
     * erzeugt direkt den output.
     *
     * @param string $content
     */
    public static function doOutPut($content)
    {
        $doFlush = false;
        foreach (func_get_args() as $out) {
            if ($out) {
                $doFlush = true;
                echo $out;
            }
        }

        // Ausgabe-Puffer leeren und den inhalt direkt an den client senden.
        if ($doFlush) {
            ob_flush();
        }
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_Util.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_Util.php'];
}
