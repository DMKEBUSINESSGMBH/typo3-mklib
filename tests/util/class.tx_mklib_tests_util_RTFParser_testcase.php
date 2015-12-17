<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Michael Wagner
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
 * benötigte Klassen einbinden
 */

tx_rnbase::load('tx_mklib_util_RTFParser');
	
/**
 * RTFParser util tests
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_RTFParser_testcase extends tx_phpunit_testcase {
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function setUp() {
		tx_mklib_tests_Util::storeExtConf('mklib');
		tx_mklib_tests_Util::setExtConfVar('specialCharMarker','SPECIALCHAR_','mklib');
	}
	
	public function tearDown() {
		tx_mklib_tests_Util::restoreExtConf('mklib');
	}
	
	/**
	 * Prüft ob korrekter text zurück gegeben wird
	 */
	public function testParseReturnsCorrectPlainText(){
		$rtfParser = tx_rnbase::makeInstance('tx_mklib_util_RTFParser',
		'{\rtf1\ansi\deff0{\fonttbl{\f0\fcharset0\fnil Arial;}}{\info{\version1}{\creatim\yr2011\mo02\dy25\hr16\min10\sec0}}{\f0\fs21\dn0 }{\b\f0\fs21\dn0 Das ist unsere }{\f0\fs21\dn0 erste Kleinanzeige}}'
		);
		self::assertEquals('Das ist unsere erste Kleinanzeige', $rtfParser->parse(), 'Der geparste text ist falsch. Vielleicht Magic Quotes ausgeschaltet oder Ähnliches?');
	}
	
	/**
	 * Prüft ob korrekter text zurück gegeben wird
	 */
	public function testParseReturnsCorrectPlainTextInHtmlMode(){
		$rtfParser = tx_rnbase::makeInstance('tx_mklib_util_RTFParser',
		'{\rtf1\ansi\deff0{\fonttbl{\f0\fcharset0\fnil Arial;}}{\info{\version1}{\creatim\yr2011\mo02\dy25\hr16\min10\sec0}}{\f0\fs21\dn0 }{\b\f0\fs21\dn0 Das ist unsere }{\f0\fs21\dn0 erste Kleinanzeige}}'
		);
		$rtfParser->setOutputType('html');
		self::assertEquals('<div align="left"><span class="f0s21"><b>Das ist unsere </b><div align="left"><span class="f0s21">erste Kleinanzeige', $rtfParser->parse(), 'Der geparste text ist falsch. Vielleicht Magic Quotes ausgeschaltet oder Ähnliches?');
	}
	
	/**
	 * Prüft ob korrekter text zurück gegeben wird
	 */
	public function testParseReturnsCorrectPlainTextInXmlMode(){
		$rtfParser = tx_rnbase::makeInstance('tx_mklib_util_RTFParser',
		'{\rtf1\ansi\deff0{\fonttbl{\f0\fcharset0\fnil Arial;}}{\info{\version1}{\creatim\yr2011\mo02\dy25\hr16\min10\sec0}}{\f0\fs21\dn0 }{\b\f0\fs21\dn0 Das ist unsere }{\f0\fs21\dn0 erste Kleinanzeige}}'
		);
		$rtfParser->setOutputType('xml');
		self::assertEquals('<rtf><group><control word="rtf" param="1"/><control word="ansi"/><control word="deff" param="0"/><group><control word="fonttbl"/><group><control word="f" param="0"/><control word="fcharset" param="0"/><control word="fnil"/></group></group><group><control word="info"/><group><control word="version" param="1"/></group><group><control word="creatim"/><control word="yr" param="2011"/><control word="mo" param="02"/><control word="dy" param="25"/><control word="hr" param="16"/><control word="min" param="10"/><control word="sec" param="0"/></group></group><group><control word="f" param="0"/><control word="fs" param="21"/><control word="dn" param="0"/></group><group><control word="b"/><control word="f" param="0"/><control word="fs" param="21"/><control word="dn" param="0"/><plain>Das ist unsere </plain></group><group><control word="f" param="0"/><control word="fs" param="21"/><control word="dn" param="0"/><plain>erste Kleinanzeige</plain></group></group></rtf>', $rtfParser->parse(), 'Der geparste text ist falsch. Vielleicht Magic Quotes ausgeschaltet oder Ähnliches?');
	}
	
	/**
	 * Prüft ob korrekter text zurück gegeben wird
	 */
	public function testParseReturnsCorrectPlainTextWhenCleaningInput(){
		$rtfParser = tx_rnbase::makeInstance('tx_mklib_util_RTFParser',
		'{\rtf1\ansi\deff0{\fonttbl{\f0\fnil Arial;}{\f29\fnil Wingdings;}}{\colortbl\red0\green0\blue0;\red0\green0\blue255;\red0\green255\blue255;\red0\green255\blue0;\red255\green0\blue255;\red255\green0\blue0;\red255\green255\blue0;\red255\green255\blue255;\red0\green0\blue127;\red0\green127\blue127;\red0\green127\blue0;\red127\green0\blue127;\red127\green0\blue0;\red127\green127\blue0;\red127\green127\blue127;\red192\green192\blue192}{\info{\title Frau}{\author Ilona Schr}{\creatim\yr0\mo2\dy3\hr2\min15\sec31}{\revtim\yr0\mo10\dy25\hr15\min14\sec0}{\printim\yr0\mo10\dy12\hr10\min35\sec0}{\version1}{\vern332832}}\paperw11898\paperh16840\margl566\margr6637\margt566\margb566\deftab850\pard\qj\li0\fi0\ri1037{\b\f0\fs22\cf0\up0\dn0 Wolfgang, 58 J., bin}{\f29\fs25\dn0 )}{\f0\fs22\cf0\up0\dn0  e. netter }\qj{\f0\fs22\cf0\up0\dn0 Polizist, freundl. u. hilfsber., leider }\qj{\f0\fs22\cf0\up0\dn0 verw. u. suche e. liebensw. Frau, der }\qj{\f0\fs22\cf0\up0\dn0 ich viell. f\'fcrs ganze Leben }\qj{\f0\fs22\cf0\up0\dn0 "Handschellen" anlegen darf. Ich }\qj{\f0\fs22\cf0\up0\dn0 freue mich auf Ihren Anruf. Tel. }\qj{\f0\fs22\cf0\up0\dn0 0371/3367765 o. Post \'fc. Inst. Katrin, }\qj{\f0\fs22\cf0\up0\dn0 Bergstr. 64, 09113 ;}}Chemnitz.} {\f29\fs25\dn0 *}}',
		true
		);
		self::assertEquals('Wolfgang, 58 J., bin###SPECIALCHAR_)### e. netter Polizist, freundl. u. hilfsber., leider verw. u. suche e. liebensw. Frau, der ich viell. f&uuml;rs ganze Leben "Handschellen" anlegen darf. Ich freue mich auf Ihren Anruf. Tel. 0371/3367765 o. Post &uuml;. Inst. Katrin, Bergstr. 64, 09113 ;Chemnitz. ###SPECIALCHAR_*###', $rtfParser->parse(), 'Der geparste text ist falsch. Vielleicht Magic Quotes ausgeschaltet oder Ähnliches?');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_RTFParser_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_RTFParser_testcase.php']);
}