<?php

/**
 * Xml Element.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_xml_Element extends SimpleXMLElement
{
    /**
     * @param string $path
     *
     * @return tx_mklib_util_xml_Element
     */
    public function getNodeFromPath($paths)
    {
        $paths = is_array($paths) ? $paths : explode('.', $paths);

        $xml = $this;

        foreach ($paths as $nodeName) {
            if (isset($xml->{$nodeName})
            && $xml->{$nodeName} instanceof tx_mklib_util_xml_Element) {
                $xml = $xml->{$nodeName};
            } else {
                return null;
            }
        }

        // return xml node
        if ($xml instanceof tx_mklib_util_xml_Element) {
            return $xml;
        }

        return null;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getAttributeFromPath($path)
    {
        $paths = explode('.', $path);
        $atribute = array_pop($paths);

        $xml = empty($paths) ? $this : $this->getNodeFromPath($paths);

        if ($xml instanceof tx_mklib_util_xml_Element
                && isset($xml[$atribute])) {
            return (string) $xml[$atribute];
        }

        return null;
    }

    /**
     * Existiert ein Wert für den angegebenen Pfad.
     *
     * @param string $path
     *
     * @return bool
     */
    public function hasValueForPath($path)
    {
        $var = $this->getNodeFromPath($path);
        $var = is_null($var) ? $this->getAttributeFromPath($path) : $var;

        return !is_null($var) && (
                isset($var)
                && strlen((string) $var)
        );
    }

    /**
     * @param string $path
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getValueFromPath($path, $default = null)
    {
        if (!$this->hasValueForPath($path)) {
            return $default;
        }
        $var = $this->getNodeFromPath($path);
        $var = is_null($var) ? $this->getAttributeFromPath($path) : (string) $var;

        return $var;
    }

    /**
     * Liefert ein Datumsobjekt anhand eines Strings im XML.
     *
     * @param string $path
     *
     * @return DateTime
     */
    public function getDateTimeFromPath($path)
    {
        $date = $this->getValueFromPath($path);
        $date = tx_mklib_util_Date::getDateTime($date);

        return $date;
    }

    /**
     * Liefert ein double anhand eines Strings im XML.
     *
     * @param string $path
     *
     * @return float
     */
    public function getIntFromPath($path)
    {
        $value = $this->getValueFromPath($path);
        if (!is_null($value)) {
            $value = (int) $value;
        }

        return $value;
    }

    /**
     * 20121001202520 ist eigentlich ein integer.
     * auf 32-bit Systemen allerdings nicht,
     * deswegen prüfen wir hier nur auf is_numeric!
     *
     * @TODO: kommazahlen abtrennen und umwandeln!
     *
     * @param string $path
     *
     * @return float
     */
    public function getBigIntFromPath($path)
    {
        return null;
    }

    /**
     * Prüft, ob das Tag Attribute oder ChildNodes hat.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return 0 == count($this->children()) && 0 == count($this->attributes());
    }

    /**
     * Liefert ein double anhand eines Strings im XML.
     *
     * @param string $path
     *
     * @return float
     */
    public function getFloatFromPath($path)
    {
        $value = $this->getValueFromPath($path);
        if (!is_null($value)) {
            // komma zu dot umwandeln
            $value = str_replace(',', '.', $value);
            $value = (float) $value;
        }

        return $value;
    }

    /**
     * Liefert ein double anhand eines Strings im XML.
     *
     * @param string $path
     * @param int    $digits bei 2 wird aus 1999 19,99.
     *                       Die Preise sollten also als Centbeträge im Code stehen.
     *
     * @return float
     */
    public function getPriceFromPath($path, $digits = 2)
    {
        $value = $this->getIntFromPath($path);
        if (!is_null($value)) {
            $digits = (int) '1'.str_repeat('0', $digits);
            // @TODO: komma zu dot umwandeln
            $value = (float) ($value / $digits);
        }

        return $value;
    }

    /**
     * Fügt den value als CData ein.
     * Wenn ein Key gesetzt wurde, wird ein Child-Element
     * mit dem Key und dem Value erzeugt.
     * Andernfals wird der Value in den aktuellen Node geschrieben.
     *
     * @param string $value
     * @param string $key
     *
     * @return tx_mklib_util_xml_Element
     */
    public function addCData($value, $key = null)
    {
        if (!is_null($key)) {
            $node = $this->addChild($key);

            return $node->addCData($value);
        } else {
            $node = dom_import_simplexml($this);
            $no = $node->ownerDocument;
            $node->appendChild($no->createCDATASection($value));

            return $this;
        }
    }

    /**
     * Fügt recursiv weitere XML-Nodes hinzu.
     *
     * @param array $childs
     */
    public function addChilds($childs)
    {
        foreach ($childs as $key => $value) {
            // Array value weitergeben
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $this->addChild("$key");
                    $subnode->addChilds($value);
                } else {
                    $this->addChilds($value);
                }
            } // Value speichern, für Text als CDATA!
            else {
                if (is_numeric($value) || empty($value)) {
                    $this->addChild($key, $value);
                } else {
                    $this->addCData($value, $key);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function asString()
    {
        return (string) $this;
    }

    /**
     * Liefert das XML als Array aus.
     *
     * @return array
     */
    public function asArray()
    {
        throw new Exception('asArray has to be implementet.');
        // mal bei merchstore schauen und kopieren.
// 		tx_Base::load('tx_util_XmlToArray');
// 		$array = tx_util_XmlToArray::createArray($this->asXML());
// 		return $array[$this->getName()];
    }
}
