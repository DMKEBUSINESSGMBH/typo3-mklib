<?php

/**
 * Numeric Utils.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_Number extends tx_mklib_util_Var
{
    /**
     * Wir wandeln einen Wert in einen Float um.
     *
     * @TODO: um den $float zu erzeugen, sollte nicht der Typecast genutzt werden.
     *     Wir benötigen eine Funktion, welche den String in einen Float umwandelt.
     *     Beispielsweise sollte sowetwas übergeben werden können:
     * @SEE http://www.php.net/manual/de/function.floatval.php#84793
     *
     * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
     *
     * @param string $number
     *
     * @return float
     */
    public static function parseFloat($number)
    {
        // Wir machen aus dem Wert ein Float
        $string = (string) $number;
        // Leerzeichen entfernen
        $string = preg_replace('/\s/', '', $string);
        // Float trennt alles ab, was ungleich Nummer und Punkt ist.
        // Wir wandeln das Komma allerdings nur um,
        // wenn kein Punkt im Text vor kommt.
        // Nur dann können wir uns halbwegs sicher sein,
        // das es sich um ein Dezimaltrennzeichen handelt.
        // Besser wäre, den Wert richtig zu parsen (siehe todo).
        if (substr_count($string, ',')) {
            $string = str_replace(',', '.', $string);
        }

        return (float) $string;
    }

    /**
     * Erzeugt einen technisch richtigen Float-Wert.
     * Bei floatval() werden die Eistellungen über setlocal beachtet.
     * Wurde das beispielsweise auf Deutsch gesetzt, wird aus 5.45 > 5,45.
     * Das Komma macht dann Beispielsweise DB-Abfragen kaputt/fehlerhaft.
     *
     * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
     *
     * @param string $number
     * @param array  $config
     *
     * @return string der float wert als String
     */
    public static function floatVal($number, array $config = [])
    {
        $float = self::parseFloat($number);
        $string = (string) $float;

        // Hier ist die aktuelle Definition für die Umwandlung von Floats enthalten
        $local = localeconv();
        // Das sind die Zeichen, mit denen wir die aus der localeconv ersetzen.
        $config = array_merge(
            // die vordefinierte Konfiguration
            // Nur Punkt als dezimalzeichen und Minus als Negativzeichen.
            // Das ist der technische Wert, der Beispielsweise für DB abfragen benötigt wird.
            [
                'decimal_point' => '.',
                'mon_decimal_point' => '.',
                'thousands_sep' => '',
                'mon_thousands_sep' => '',
                'int_curr_symbol' => '',
                'currency_symbol' => '',
                'positive_sign' => '',
                'negative_sign' => '-',
            ],
            // spezielle Konfiguration, welche dem Aufruf mitgegeben wurde.
            $config
        );

        // Erstmal ersetzen wir die Leerzeichen!
        $string = preg_replace('/\s/', '', $string);
        // Jetzt ersetzen wir Currencies und Signs! (EUR $ + -)
        $string = str_replace(
            [
                $local['int_curr_symbol'],
                $local['currency_symbol'],
                $local['positive_sign'],
                $local['negative_sign'],
            ],
            [
                $config['int_curr_symbol'],
                $config['currency_symbol'],
                $config['positive_sign'],
                $config['negative_sign'],
            ],
            $string
        );

        // Jetzt wird es komplizierter:
        // Wir müssen anhand eines pregreplace, die Werte teilen.
        // Das machen wir, da wir mit string replace aus 4.999,95
        // immer 4.999.95 oder 4,999,95 machen würden.
        $mapping = [
            // Für geldbeträge
            $local['mon_decimal_point'] => $config['mon_decimal_point'],
            $local['mon_thousands_sep'] => $config['mon_thousands_sep'],
            // Für normale Werte (Überschreiben ggf. die Geldwerte)
            $local['decimal_point'] => $config['decimal_point'],
            $local['thousands_sep'] => $config['thousands_sep'],
        ];
        // Wir entfernen leere Einträge.
        // Die können nicht ersetzt werden und verursachen Fehler
        $preg = tx_mklib_util_Array::removeEmptyValues(array_keys($mapping));
        $preg = '/('.implode('|\\', $preg).')/';
        // Wir splitten nun die Werte auf.
        // Dabei werden alle Trennzeichen mitgegeben und leere Werte entfernt!
        $matches = preg_split($preg, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        // Wir ersetzen nun die entsprechenden Zeichen.
        foreach ($matches as &$part) {
            foreach ($mapping as $from => $to) {
                if ($part == $from) {
                    $part = $to;
                    break;
                }
            }
        }

        // Jetzt nur noch zusammenführen und Fertig!
        return implode('', $matches);
    }
}
