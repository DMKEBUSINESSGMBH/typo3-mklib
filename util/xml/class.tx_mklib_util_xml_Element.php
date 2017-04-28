<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_util
 *
 * (c) 2013 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 */


/**
 * Xml Element
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_xml_Element extends SimpleXMLElement
{

    /**
     *
     * @param string $path
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
     * @return string
     */
    public function getAttributeFromPath($path)
    {
        $paths = explode('.', $path);
        $atribute = array_pop($paths);


        $xml = empty($paths) ? $this : $this->getNodeFromPath($paths);

        if ($xml instanceof tx_mklib_util_xml_Element
                && isset($xml[$atribute])) {
            return (string)  $xml[$atribute];
        }

        return null;
    }

    /**
     * Existiert ein Wert für den angegebenen Pfad
     * @param string $path
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
     *
     * @param string $path
     * @param mixed $default
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
     * @param string $path
     * @return DateTime
     */
    public function getDateTimeFromPath($path)
    {
        $date = $this->getValueFromPath($path);
        tx_rnbase::load('tx_mklib_util_Date');
        $date = tx_mklib_util_Date::getDateTime($date);

        return $date;
    }

    /**
     * Liefert ein double anhand eines Strings im XML.
     * @param string $path
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
     * @return float
     */
    public function getBigIntFromPath($path)
    {
        return null;
    }
    /**
     * Prüft, ob das Tag Attribute oder ChildNodes hat
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->children()) == 0 && count($this->attributes()) == 0;
    }

    /**
     * Liefert ein double anhand eines Strings im XML.
     * @param string $path
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
     * @param int $digits bei 2 wird aus 1999 19,99.
     *      Die Preise sollten also als Centbeträge im Code stehen.
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

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/xml/class.tx_mklib_util_xml_Element.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/xml/class.tx_mklib_util_xml_Element.php']);
}
