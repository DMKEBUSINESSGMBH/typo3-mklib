<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_util_String');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_String_testcase extends tx_phpunit_testcase {

	/**
	 * Testen ob crop nur richtig kürzt
	 */
	public function testGetShortenedText(){
		$aRecord = array(
			'othertext' => 'ein ganz langer text mit vielen worten und noch viel viel viel viel mehr',
			'text' => 'ein ganz langer text mit vielen worten und noch viel viel viel viel mehr'
		);

		$this->assertEquals(
				'ein ganz langer text mit vielen worten und noch viel',
				tx_mklib_util_String::crop($aRecord['othertext'], 50),
				'Nicht korrekt gekürtzt!'
			);

		$this->assertEquals(
				'ein ganz langer text mit vielen worten und noch viel...',
				tx_mklib_util_String::crop($aRecord['othertext'], 50, str_repeat('.', 3)),
				'Nicht korrekt gekürtzt!'
			);

		$this->assertEquals(
				'ein ganz langer text mit vielen worten und noch viel viel viel viel mehr',
				tx_mklib_util_String::crop($aRecord['text']),
				'Nicht korrekt gekürtzt!'
			);

	}

	/**
	 * Testen ob removeNoneLetters nur Buchstaben und Leerzeichen überlässt
	 */
	public function testRemoveNoneLetters(){
		$this->assertEquals('abcdef ghiäöüß',tx_mklib_util_String::removeNoneLetters('a<>|,b;.:-_c!"\'\\d/&%e$§()f =?´`+*~g#{[]}h^°@µ€i0123456789äöüß'),'Der zurückgegebene String wurde nicht korrekt bereingt.');
	}

	/**
	 * Testen ob html2plain sonderzeichen etc entfernt bzw umwandelt
	 */
	public function testHtml2Plain(){
		$this->assertEquals(' alert("ohoh") ""\' äö',tx_mklib_util_String::html2plain('<script>alert("ohoh")</script>""\'<!-- my comment -->äö'),'Der zurückgegebene String wurde nicht korrekt bereingt bzw. umgewandelt.');
	}
	/**
	 * isTrueVal testen
	 */
	public function testIsTrueVal(){
		$this->assertTrue(tx_mklib_util_String::isTrueVal(true));
		$this->assertTrue(tx_mklib_util_String::isTrueVal('true'));
		$this->assertTrue(tx_mklib_util_String::isTrueVal('TrUe'));
		$this->assertTrue(tx_mklib_util_String::isTrueVal('1'));
		$this->assertTrue(tx_mklib_util_String::isTrueVal(1));
		$this->assertFalse(tx_mklib_util_String::isTrueVal(false));
		$this->assertFalse(tx_mklib_util_String::isTrueVal('false'));
		$this->assertFalse(tx_mklib_util_String::isTrueVal('0'));
		$this->assertFalse(tx_mklib_util_String::isTrueVal(0));
	}
	/**
	 * isFalseVal testen
	 */
	public function testIsFalseVal(){
		$this->assertTrue(tx_mklib_util_String::isFalseVal(false));
		$this->assertTrue(tx_mklib_util_String::isFalseVal('false'));
		$this->assertTrue(tx_mklib_util_String::isFalseVal('0'));
		$this->assertTrue(tx_mklib_util_String::isFalseVal(0));
		$this->assertFalse(tx_mklib_util_String::isFalseVal(true));
		$this->assertFalse(tx_mklib_util_String::isFalseVal('true'));
		$this->assertFalse(tx_mklib_util_String::isFalseVal('TrUe'));
		$this->assertFalse(tx_mklib_util_String::isFalseVal('1'));
		$this->assertFalse(tx_mklib_util_String::isFalseVal(1));
	}
	/**
	 * removeRepeatedlyOccurrings testen
	 */
	public function testRemoveRepeatedlyOccurrings() {
		$this->assertEquals(
				'Hallo String,'.LF.'Du wurdest bereinigt!',
				tx_mklib_util_String::removeRepeatedlyOccurrings(
					'Hallo  String,'.LF.LF.LF.'Du  wurdest    bereinigt!'
				)
			);
	}
	/**
	 * toCamelCase testen
	 */
	public function testToCamelCase(){
		$this->assertEquals('feUsers',tx_mklib_util_String::toCamelCase('fe_users'));
		$this->assertEquals('txMklibWordlist',tx_mklib_util_String::toCamelCase('tx_mklib_wordlist'));
		$this->assertEquals('txMklibTestsUtilStringTestcase',tx_mklib_util_String::toCamelCase('tx_mklib_tests_util_String_testcase'));
	}

	public function testObfusicateEmail() {
		$this->initSpamProtectionConfig();

		$this->assertEquals(
			'test.mail&#8203;(at)&#8203ein-host.de',
			tx_mklib_util_String::obfusicateEmail(array(0=>'test.mail@ein-host.de')),
			'Mail falsch verschleiert'
		);
	}

	public function testObfusicateContainedEmails() {
		$this->initSpamProtectionConfig();

		$this->assertEquals(
			'ein text mit einer mail mail&#8203;(at)&#8203host.de und noch einer anothermail&#8203;(at)&#8203host.de',
			tx_mklib_util_String::obfusicateContainedEmails(
				'ein text mit einer mail mail@host.de und noch einer anothermail@host.de'
			),
			'Mail falsch verschleiert'
		);
	}

	public function testConvertEmailToMailToLink() {
		$this->initSpamProtectionConfig();

		$this->assertEquals(
			'<a href="javascript:linkTo_UnCryptMailto(\'ocknvq,vguv0ocknBgkp/jquv0fg\');" >test.mail&#8203;(at)&#8203ein-host.de</a>',
			tx_mklib_util_String::convertEmailToMailToLink(array(0=>'test.mail@ein-host.de')),
			'Mailto Link falsch'
		);
	}

	public function testConvertContainedEmailsToMailToLinks() {
		$this->initSpamProtectionConfig();

		$this->assertEquals(
			'ein text mit einer mail <a href="javascript:linkTo_UnCryptMailto(\'ocknvq,ocknBjquv0fg\');" >mail&#8203;(at)&#8203host.de</a> und noch einer <a href="javascript:linkTo_UnCryptMailto(\'ocknvq,cpqvjgtocknBjquv0fg\');" >anothermail&#8203;(at)&#8203host.de</a>',
			tx_mklib_util_String::convertContainedEmailsToMailToLinks(
				'ein text mit einer mail mail@host.de und noch einer anothermail@host.de'
			),
			'Mailto Links falsch'
		);
	}

	/**
	 * wie über TS
	 */
	private function initSpamProtectionConfig() {
		$GLOBALS['TSFE']->spamProtectEmailAddresses = 2;
		$GLOBALS['TSFE']->config['config']['spamProtectEmailAddresses_atSubst'] = '&#8203;(at)&#8203';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_String_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_String_testcase.php']);
}