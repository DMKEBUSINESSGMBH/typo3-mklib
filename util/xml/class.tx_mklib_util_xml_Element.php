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
 * Xml Element.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_xml_Element extends SimpleXMLElement
{
    public function getNodeFromPath($paths): ?tx_mklib_util_xml_Element
    {
        $paths = is_array($paths) ? $paths : explode('.', (string) $paths);

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
        return $xml;
    }

    /**
     * @param string $path
     */
    public function getAttributeFromPath($path): ?string
    {
        $paths = explode('.', $path);
        $atribute = array_pop($paths);

        $xml = [] === $paths ? $this : $this->getNodeFromPath($paths);

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
     */
    public function hasValueForPath($path): bool
    {
        $var = $this->getNodeFromPath($path);
        $var = is_null($var) ? $this->getAttributeFromPath($path) : $var;

        return !is_null($var) && strlen((string) $var);
    }

    /**
     * @param string $path
     */
    public function getValueFromPath($path, $default = null)
    {
        if (!$this->hasValueForPath($path)) {
            return $default;
        }

        $var = $this->getNodeFromPath($path);

        return is_null($var) ? $this->getAttributeFromPath($path) : (string) $var;
    }

    /**
     * Liefert ein Datumsobjekt anhand eines Strings im XML.
     *
     * @param string $path
     */
    public function getDateTimeFromPath($path): DateTime
    {
        $date = $this->getValueFromPath($path);

        return tx_mklib_util_Date::getDateTime($date);
    }

    /**
     * Liefert ein double anhand eines Strings im XML.
     *
     * @param string $path
     *
     * @return float
     */
    public function getIntFromPath($path): ?int
    {
        $value = $this->getValueFromPath($path);
        if (!is_null($value)) {
            return (int) $value;
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
     */
    public function isEmpty(): bool
    {
        return 0 == count($this->children()) && 0 == count($this->attributes());
    }

    /**
     * Liefert ein double anhand eines Strings im XML.
     *
     * @param string $path
     */
    public function getFloatFromPath($path): ?float
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
     */
    public function getPriceFromPath($path, $digits = 2): float
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
        }

        $node = dom_import_simplexml($this);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($value));

        return $this;
    }

    /**
     * Fügt recursiv weitere XML-Nodes hinzu.
     *
     * @param array $childs
     */
    public function addChilds($childs): void
    {
        foreach ($childs as $key => $value) {
            // Array value weitergeben
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $this->addChild($key);
                    $subnode->addChilds($value);
                } else {
                    $this->addChilds($value);
                }
            } elseif (is_numeric($value) || empty($value)) {
                $this->addChild($key, $value);
            } else {
                $this->addCData($value, $key);
            }
        }
    }

    public function asString(): string
    {
        return (string) $this;
    }

    /**
     * Liefert das XML als Array aus.
     */
    public function asArray(): void
    {
        throw new Exception('asArray has to be implementet.');
        // mal bei merchstore schauen und kopieren.
        // 		tx_Base::load('tx_util_XmlToArray');
        // 		$array = tx_util_XmlToArray::createArray($this->asXML());
        // 		return $array[$this->getName()];
    }
}
