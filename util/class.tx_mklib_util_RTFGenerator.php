<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann (hannes.bochmann@dmk-ebusiness.de)
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
 ***************************************************************/

/**
 * erforderliche Klassen laden.
 */

/**
 * RTF-Generator.
 */
class tx_mklib_util_RTFGenerator
{
    // {\colortbl;\red 0\green 0\blue 0;\red 255\green 0\ blue0;\red0 ...}
    private $colour_table = [];
    private $colour_rgb;
    private $font_face;
    private $windings_font_face;
    private $font_size;
    // {\info {\title <title>} {\author <author>} {\operator <operator>}}
    private $info_table = [];
    private $page_width;
    private $page_height;
    private $page_size;
    private $page_orientation;
    private $rtf_version;
    private $tab_width;
    private $margins = [];

    private $document;
    private $buffer;
    private $shouldInfoTableBeExported;

    protected $aSpecialChars = [
            '&Aacute;' => 'c1',
            '&aacute;' => 'e1',
            '&Agrave;' => 'c0',
            '&agrave;' => 'e0',
            '&Eacute;' => 'c9',
            '&eacute;' => 'e9',
            '&Egrave;' => 'c8',
            '&egrave;' => 'e8',
            '&Iacute;' => 'cd',
            '&iacute;' => 'ed',
            '&Igrave;' => 'cc',
            '&igrave;' => 'ec',
            '&Oacute;' => 'd3',
            '&oacute;' => 'f3',
            '&Ograve;' => 'd2',
            '&ograve;' => 'f2',
            '&Uacute;' => 'da',
            '&uacute;' => 'fa',
            '&Ugrave;' => 'd9',
            '&ugrave;' => 'f9',
            '&#8364;' => '80', // euro
            'â‚¬' => '80', // verkorkstes euro zeichen bei falschem Charset
            '&Ntilde;' => 'd1',
            '&ntilde;' => 'f1',
            '&Ccedil;' => 'c7',
            '&ccedil;' => 'e7',
            '&auml;' => 'e4', // ä
            'ä' => 'e4', // ä
            'Ã¤' => 'e4', // verkorkstes ä bei falschem Charset
            '&Auml' => 'c4', // Ä
            'Ä' => 'c4', // Ä
            'Ã„' => 'c4', // verkorkstes Ä bei falschem Charset
            '&ouml' => 'f6', // ö
            'ö' => 'f6', // ö
            'Ã¶' => 'f6', // verkorkstes ö bei falschem Charset
            '&Ouml' => 'd6', // Ö
            'Ö' => 'd6', // Ö
            'Ã–' => 'd6', // verkorkstes Ö bei falschem Charset
            '&Uuml;' => 'dc', // Ü
            'Ü' => 'dc', // Ü
            'Ãœ' => 'dc', // verkorkstes Ü bei falschem Charset
            '&uuml;' => 'fc', // ü
            'ü' => 'fc', // ü
            'Ã¼' => 'fc', // verkorkstes ü bei falschem Charset
            '&szlig;' => 'df', // ß
            'ß' => 'df', // ß
            'ÃŸ' => 'df', // verkorkstes ß bei falschem Charset
            '&#191;' => 'bf',
            '&#161;' => 'a1',
            '&middot;' => 'b7',
            '&copy;' => 'a9',
            '&reg;' => 'ae',
            '&ordm;' => 'ba',
            '&ordf;' => 'aa',
            '&sup2;' => 'b2',
            '&sup3;' => 'b3',
    ];

    /**
     * Klassen-Konstruktor
     * Setzt die notwendigen Konfigurationen.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->setDefaultFontFace($params['defaultFontFace']);
        $this->setDefaultFontSize($params['defaultFontSize']);
        $this->setPaperSize($params['paperSize']);
        $this->setPaperOrientation($params['paperOrientation']);
        $this->setRTFVersion($params['rtfVersion']);
        // $this->tab_width = \Sys25\RnBase\Configuration\Processor::getExtensionCfgValue('mklib', 'tabWidth');
        // Erstellungszeit einfügen
        $this->setCreateTime();
        // Colour-Table einfügen
        // $this->addColour("#000000");
        $this->exportInfoTable($params['exportInfoTable']);
    }

    /**
     * Soll die Info table ausgegeben werden?
     *
     * @param bool $bSetInfoPart
     */
    private function exportInfoTable($bSetInfoPart)
    {
        $this->shouldInfoTableBeExported = $bSetInfoPart;
    }

    /**
     * Soll die Info table ausgegeben werden?
     */
    private function shouldInfoTableBeExported()
    {
        return $this->shouldInfoTableBeExported;
    }

    /**
     * RTF-Version des Dokuments setzen.
     *
     * @param int $versionNumber
     */
    public function setRTFVersion($versionNumber)
    {
        $this->rtf_version = $versionNumber ? $versionNumber : 1;
        $this->info_table['version'] = $this->rtf_version;
    }

    /**
     * Standardmäßige Schriftart setzen
     * entspricht dem key in $aFonts (getFontTable-Methode) der Schriftart in der colortable.
     *
     * @param int $face
     */
    public function setDefaultFontFace($face)
    {
        $this->font_face = !empty($face) ? $face : 0; // $font is integer
        $this->windings_font_face = 1; // $font is integer
    }

    /**
     * Standmäßige Schriftgröße setzen.
     *
     * @param int $size
     */
    public function setDefaultFontSize($size)
    {
        $this->font_size = !empty($size) ? $size : 21;
    }

    /**
     * aktuelle Erstellungszeit setzen.
     */
    public function setCreateTime()
    {
        $this->info_table['creatim'] =
                '\yr'.date('Y').
                '\mo'.date('m').
                '\dy'.date('d').
                '\hr'.date('H').
                '\min'.date('i').
                '\sec0';
    }

    /**
     * Format der Ausgabe setzen.
     *
     * @param int $size
     */
    public function setPaperSize($size = 0)
    {
        // Measurements
        // 1 inch = 1440 twips
        // 1 cm = 567 twips
        // 1 mm = 56.7 twips
        $inch = 1440;
        $mm = 56.7;
        // 1 => Letter (8.5 x 11 inch)
        // 2 => Legal (8.5 x 14 inch)
        // 3 => Executive (7.25 x 10.5 inch)
        // 4 => A3 (297 x 420 mm)
        // 5 => A4 (210 x 297 mm)
        // 6 => A5 (148 x 210 mm)
        // 7 => Wochenspiegel Format (209.85 x 297.01 mm)
        // Orientation considered as Portrait

        switch ($size) {
            case 1:
                $this->page_width = floor(8.5 * $inch);
                $this->page_height = floor(11 * $inch);
                $this->page_size = 1;
                break;
            case 2:
                $this->page_width = floor(8.5 * $inch);
                $this->page_height = floor(14 * $inch);
                $this->page_size = 5;
                break;
            case 3:
                $this->page_width = floor(7.25 * $inch);
                $this->page_height = floor(10.5 * $inch);
                $this->page_size = 7;
                break;
            case 4:
                $this->page_width = floor(297 * $mm);
                $this->page_height = floor(420 * $mm);
                $this->page_size = 8;
                break;
            case 5:
            default:
                $this->page_width = floor(210 * $mm);
                $this->page_height = floor(297 * $mm);
                $this->page_size = 9;
                break;
            case 6:
                $this->page_width = floor(148 * $mm);
                $this->page_height = floor(210 * $mm);
                $this->page_size = 10;
                break;
            case 7:
                $width = 209.85;
                $height = 297.01;
                $this->page_width = floor($width * $mm);
                $this->page_height = floor($height * $mm);
                $this->margins['left'] = floor($this->page_width / ($width / 10));
                $this->margins['right'] = 6637;
                $this->margins['top'] = floor($this->page_width / ($width / 10));
                $this->margins['bottom'] = floor($this->page_width / ($width / 10));
                $this->page_size = 11;
                break;
        }
    }

    /**
     * Ausrichtung der Ausgabe.
     *
     * @param int $orientation
     */
    public function setPaperOrientation($orientation = 1)
    {
        // 1 => Portrait
        // 2 => Landscape

        switch ($orientation) {
            case 1:
            default:
                $this->page_orientation = 1;
                break;
            case 2:
                $this->page_orientation = 2;
                break;
        }
    }

    /**
     * Farbe in der colortable hinzufügen.
     *
     * @param string $hexcode
     */
    public function addColour($hexcode)
    {
        // Get the RGB values
        $this->hex2rgb($hexcode);

        // Register in the colour table array
        $this->colour_table[] = [
            'red' => $this->colour_rgb['red'],
            'green' => $this->colour_rgb['green'],
            'blue' => $this->colour_rgb['blue'],
        ];
    }

    /**
     * Konvertiert HEX Farbwerte zu RGB-Werten (#FFFFFF => r255 g255 b255).
     *
     * @param string $hexcode
     */
    private function hex2rgb($hexcode)
    {
        $hexcode = str_replace('#', '', $hexcode);
        $rgb = [];
        $rgb['red'] = hexdec(substr($hexcode, 0, 2));
        $rgb['green'] = hexdec(substr($hexcode, 2, 2));
        $rgb['blue'] = hexdec(substr($hexcode, 4, 2));

        $this->colour_rgb = $rgb;
    }

    /**
     * Konvertiert neue Zeilen in \par.
     *
     * @param string $text
     */
    private function nl2par($text)
    {
        if ($replaced = str_replace("\n", '\\par ', $text)) {
            return $replaced;
        } else {
            return $text;
        }
    }

    /**
     * Text zum Dokument hinzufügen.
     *
     * @param string $text
     */
    public function addText($text)
    {
        $text = str_replace("\n", '', $text);
        $text = str_replace("\t", '', $text);
        $text = str_replace("\r", '', $text);

        $this->document .= $text;
    }

    /**
     * Den gesamten RTF-inhalt erzeugen.
     *
     * @param string $text
     */
    public function getRTF($text)
    {
        $this->addText($text);

        $this->buffer .= '{';
        // Header
        $this->buffer .= $this->getHeader();
        // Font table
        $this->buffer .= $this->getFontTable();
        // Colour table
        $this->buffer .= $this->getColourTable();
        // File Information
        $this->buffer .= $this->getInformation();
        // Default font values
        $this->buffer .= '{'.$this->getDefaultFont();
        // Page display settings
        // $this->buffer .= $this->getPageSettings();
        // Parse the text into RTF
        $this->buffer .= $this->parseDocument().'}';
        $this->buffer .= '}';

        return $this->buffer;
    }

    /**
     * Gibt den gesamten Kopf der Datei zurück.
     */
    private function getHeader()
    {
        if (!empty($this->tab_width)) {
            $tabWidth = "\\deftab{$this->tab_width}";
        }
        $header_buffer = "\\rtf{$this->rtf_version}\\ansi\\deff0{$tabWidth}";

        return $header_buffer;
    }

    /**
     * Gibt die Font-Table zurück.
     */
    private function getFontTable()
    {
        $aFonts = [
            0 => [
                'name' => 'Arial',
                'family' => 'nil',
                'charset' => 0,
            ],
            1 => [
                'name' => 'Wingdings',
                'family' => 'nil',
                'charset' => 0,
            ],
        ];
        $font_buffer = '{\\fonttbl';
        foreach ($aFonts as $fnum => $farray) {
            $font_buffer .= "{\\f{$fnum}\\fcharset{$farray['charset']}\\f{$farray['family']} {$farray['name']};}";
        }
        $font_buffer .= '}';

        return $font_buffer;
    }

    /**
     * Gibt die colortable zurück.
     */
    private function getColourTable()
    {
        $colour_buffer = '';
        if (sizeof($this->colour_table) > 0) {
            $colour_buffer = '{\\colortbl;';
            foreach ($this->colour_table as $cnum => $carray) {
                $colour_buffer .= "\\red{$carray['red']}\\green{$carray['green']}\\blue{$carray['blue']};";
            }
            $colour_buffer .= '}';
        }

        return $colour_buffer;
    }

    /**
     * Gibt den Informationsteil zurück.
     */
    private function getInformation()
    {
        $info_buffer = '';
        if (sizeof($this->info_table) > 0 && $this->shouldInfoTableBeExported()) {
            $info_buffer = '{\\info';
            foreach ($this->info_table as $name => $value) {
                $info_buffer .= "{\\{$name}{$value}}";
            }
            $info_buffer .= '}';
        }

        return $info_buffer;
    }

    /**
     * gibt die Schriftarteinstellung zurück.
     */
    private function getDefaultFont($withTrailingSpace = true)
    {
        $font_buffer = "\\f{$this->font_face}\\fs{$this->font_size}\\dn0";
        // Leerzeichen anhängen oder nicht
        if ($withTrailingSpace) {
            $font_buffer .= ' ';
        }

        return $font_buffer;
    }

    /**
     * gibt die Schriftarteinstellung zurück.
     */
    private function getWindingsFont()
    {
        // Wingdings Zeichen müssen im Verhätnis größer sein als Arial Schriftzeichen
        // Verhätnis von JJK übernommen
        $iWingdingsFontSize = ceil(($this->font_size * 113.63) / 100);

        return "\\f{$this->windings_font_face}\\fs{$iWingdingsFontSize}\\dn0";
    }

    /**
     * Konvertiert spezielle Zeichen zu ASCII.
     *
     * @param string $text
     */
    private function specialCharacters($text)
    {
        // einfach alle bekannten Sonderzeichen ersetzen
        foreach ($this->aSpecialChars as $sSpecialChar => $sReplacement) {
            // Sonderzeichen ersetzen. immer \' als Prefix
            // damit es als Sonderzeichen im RTF gilt
            $text = str_replace($sSpecialChar, '\\\''.$sReplacement, $text);
        }

        // dann noch die Windings Sonderzeichen ersetzten
        $sSpecialCharMarker = tx_mklib_util_MiscTools::getSpecialCharMarker();
        $text = preg_replace("/###$sSpecialCharMarker(.*?)###/mi", "}{{$this->getWindingsFont()} \\1}{{$this->getDefaultFont(false)}", $text);

        return $text;
    }

    /**
     * Konvertiert HTML-Zeichen zur entsprechenden RTF-Formatierung.
     */
    private function parseDocument()
    {
        $doc_buffer = $this->specialCharacters($this->document);

        //        if(preg_match("/<UL>(.*?)<\/UL>/mi", $doc_buffer)) {
        //            $doc_buffer = str_replace("<UL>", "", $doc_buffer);
        //            $doc_buffer = str_replace("</UL>", "", $doc_buffer);
        //            $doc_buffer = preg_replace("/<LI>(.*?)<\/LI>/mi", "\\f3\\'B7\\tab\\f{$this->font_face} \\1\\par", $doc_buffer);
        //        }

        //        $doc_buffer = preg_replace("/<P>(.*?)<\/P>/mi", "\\1\\par ", $doc_buffer);
        $doc_buffer = preg_replace('/<STRONG>(.*?)<\/STRONG>/mi', "}{\b{$this->getDefaultFont()}\\1 }{{$this->getDefaultFont(false)}", $doc_buffer);
        //        $doc_buffer = preg_replace("/<EM>(.*?)<\/EM>/mi", "\\i \\1\\i0 ", $doc_buffer);
        //        $doc_buffer = preg_replace("/<U>(.*?)<\/U>/mi", "\\ul \\1\\ul0 ", $doc_buffer);
        //        $doc_buffer = preg_replace("/<STRIKE>(.*?)<\/STRIKE>/mi", "\\strike \\1\\strike0 ", $doc_buffer);
        //        $doc_buffer = preg_replace("/<SUB>(.*?)<\/SUB>/mi", "{\\sub \\1}", $doc_buffer);
        //        $doc_buffer = preg_replace("/<SUP>(.*?)<\/SUP>/mi", "{\\super \\1}", $doc_buffer);

        // $doc_buffer = preg_replace("/<H1>(.*?)<\/H1>/mi", "\\pard\\qc\\fs40 \\1\\par\\pard\\fs{$this->font_size} ", $doc_buffer);
        // $doc_buffer = preg_replace("/<H2>(.*?)<\/H2>/mi", "\\pard\\qc\\fs32 \\1\\par\\pard\\fs{$this->font_size} ", $doc_buffer);

        //        $doc_buffer = preg_replace("/<H1>(.*?)<\/H1>/mi", "\\fs48\\b \\1\\b0\\fs{$this->font_size}\\par ", $doc_buffer);
        //        $doc_buffer = preg_replace("/<H2>(.*?)<\/H2>/mi", "\\fs36\\b \\1\\b0\\fs{$this->font_size}\\par ", $doc_buffer);
        //        $doc_buffer = preg_replace("/<H3>(.*?)<\/H3>/mi", "\\fs27\\b \\1\\b0\\fs{$this->font_size}\\par ", $doc_buffer);
        //
        //
        //        $doc_buffer = preg_replace("/<HR(.*?)>/i", "\\brdrb\\brdrs\\brdrw30\\brsp20 \\pard\\par ", $doc_buffer);
        //        $doc_buffer = str_replace("<BR>", "\\par ", $doc_buffer);
        //        $doc_buffer = str_replace("<TAB>", "\\tab ", $doc_buffer);

        $doc_buffer = $this->nl2par($doc_buffer);

        return $doc_buffer;
    }
}
