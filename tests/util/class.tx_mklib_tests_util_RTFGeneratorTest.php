<?php
/**
 * @author Michael Wagner
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
 * benötigte Klassen einbinden.
 */

/**
 * RtfGenerator util tests.
 */
class tx_mklib_tests_util_RTFGeneratorTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * Enter description here ...
     */
    public function setUp()
    {
        \DMK\Mklib\Utility\Tests::storeExtConf('mklib');
        \DMK\Mklib\Utility\Tests::setExtConfVar('specialCharMarker', 'SPECIALCHAR_', 'mklib');
    }

    public function tearDown()
    {
        \DMK\Mklib\Utility\Tests::restoreExtConf('mklib');
    }

    /**
     * Prüft ob korrekter text zurück erzeugt wird
     * Das umfasst auch Sonderzeichen.
     */
    public function testGeneratorReturnsCorrectPlainText()
    {
        self::markTestIncomplete("Uncaught require(typo3-mklib/.Build/Web/typo3conf/LocalConfiguration.php)");

        $aParams = array(
            'defaultFontFace' => 0,
            'defaultFontSize' => 22,
            'paperOrientation' => 1,
            'rtfVersion' => 1,
            'exportInfoTable' => 1,
        );
        $oRTFGenerator = tx_rnbase::makeInstance('tx_mklib_util_RTFGenerator', $aParams);
        $sRTFText = $oRTFGenerator->getRTF('###SPECIALCHAR_(###<strong>Das ist ein schöner Testtext.</strong> Auch mit ###SPECIALCHAR_)### verschiedenen ßonderzeichen und Ähnlichem. Sogar einem ###SPECIALCHAR_*###');

        self::assertEquals('{\rtf1\ansi\deff0{\fonttbl{\f0\fcharset0\fnil Arial;}{\f1\fcharset0\fnil Wingdings;}}{\info{\version1}{\creatim\yr'.date('Y').'\mo'.date('m').'\dy'.date('d').'\hr'.date('H').'\min'.date('i').'\sec0}}{\f0\fs22\dn0 }{\f1\fs25\dn0 (}{\f0\fs22\dn0}{\b\f0\fs22\dn0 Das ist ein sch\\\'f6ner Testtext. }{\f0\fs22\dn0 Auch mit }{\f1\fs25\dn0 )}{\f0\fs22\dn0 verschiedenen \\\'dfonderzeichen und \\\'c4hnlichem. Sogar einem }{\f1\fs25\dn0 *}{\f0\fs22\dn0}}', $sRTFText, 'Der generierte Text ist falsch.');
    }

    /**
     * Prüft ob korrekter text zurück erzeugt wird
     * Das umfasst auch Sonderzeichen.
     */
    public function testGeneratorReturnsCorrectPlainTextWhenNoInfoTable()
    {
        self::markTestIncomplete("Uncaught require(typo3-mklib/.Build/Web/typo3conf/LocalConfiguration.php)");

        $aParams = array(
            'defaultFontFace' => 0,
            'defaultFontSize' => 22,
            'paperOrientation' => 1,
            'rtfVersion' => 1,
        );
        $oRTFGenerator = tx_rnbase::makeInstance('tx_mklib_util_RTFGenerator', $aParams);
        $sRTFText = $oRTFGenerator->getRTF('###SPECIALCHAR_(###<strong>Das ist ein schöner Testtext.</strong> Auch mit ###SPECIALCHAR_)### verschiedenen ßonderzeichen und Ähnlichem. Sogar einem ###SPECIALCHAR_*###');

        self::assertEquals('{\rtf1\ansi\deff0{\fonttbl{\f0\fcharset0\fnil Arial;}{\f1\fcharset0\fnil Wingdings;}}{\f0\fs22\dn0 }{\f1\fs25\dn0 (}{\f0\fs22\dn0}{\b\f0\fs22\dn0 Das ist ein sch\\\'f6ner Testtext. }{\f0\fs22\dn0 Auch mit }{\f1\fs25\dn0 )}{\f0\fs22\dn0 verschiedenen \\\'dfonderzeichen und \\\'c4hnlichem. Sogar einem }{\f1\fs25\dn0 *}{\f0\fs22\dn0}}', $sRTFText, 'Der generierte Text ist falsch.');
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_RTFGeneratorTest.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_RTFGeneratorTest.php'];
}
