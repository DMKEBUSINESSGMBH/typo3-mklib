<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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

tx_rnbase::load('tx_mklib_util_String');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_String_testcase extends Tx_Phpunit_TestCase {

	/**
	 * Testen ob crop nur richtig kürzt
	 */
	public function testGetShortenedText(){
		$aRecord = array(
			'othertext' => 'ein ganz langer text mit vielen worten und noch viel viel viel viel mehr',
			'text' => 'ein ganz langer text mit vielen worten und noch viel viel viel viel mehr'
		);

		self::assertEquals(
				'ein ganz langer text mit vielen worten und noch viel',
				tx_mklib_util_String::crop($aRecord['othertext'], 50),
				'Nicht korrekt gekürtzt!'
			);

		self::assertEquals(
				'ein ganz langer text mit vielen worten und noch viel...',
				tx_mklib_util_String::crop($aRecord['othertext'], 50, str_repeat('.', 3)),
				'Nicht korrekt gekürtzt!'
			);

		self::assertEquals(
				'ein ganz langer text mit vielen worten und noch viel viel viel viel mehr',
				tx_mklib_util_String::crop($aRecord['text']),
				'Nicht korrekt gekürtzt!'
			);

	}

	/**
	 * Testen ob removeNoneLetters nur Buchstaben und Leerzeichen überlässt
	 */
	public function testRemoveNoneLetters(){
		self::assertEquals('abcdef ghiäöüß',tx_mklib_util_String::removeNoneLetters('a<>|,b;.:-_c!"\'\\d/&%e$§()f =?´`+*~g#{[]}h^°@µ€i0123456789äöüß'),'Der zurückgegebene String wurde nicht korrekt bereingt.');
	}

	/**
	 * Testen ob html2plain sonderzeichen etc entfernt bzw umwandelt
	 */
	public function testHtml2Plain(){
		self::assertEquals(' alert("ohoh") ""\' äö',tx_mklib_util_String::html2plain('<script>alert("ohoh")</script>""\'<!-- my comment -->äö'),'Der zurückgegebene String wurde nicht korrekt bereingt bzw. umgewandelt.');
	}
	/**
	 * isTrueVal testen
	 */
	public function testIsTrueVal(){
		self::assertTrue(tx_mklib_util_String::isTrueVal(true));
		self::assertTrue(tx_mklib_util_String::isTrueVal('true'));
		self::assertTrue(tx_mklib_util_String::isTrueVal('TrUe'));
		self::assertTrue(tx_mklib_util_String::isTrueVal('1'));
		self::assertTrue(tx_mklib_util_String::isTrueVal(1));
		self::assertFalse(tx_mklib_util_String::isTrueVal(false));
		self::assertFalse(tx_mklib_util_String::isTrueVal('false'));
		self::assertFalse(tx_mklib_util_String::isTrueVal('0'));
		self::assertFalse(tx_mklib_util_String::isTrueVal(0));
	}
	/**
	 * isFalseVal testen
	 */
	public function testIsFalseVal(){
		self::assertTrue(tx_mklib_util_String::isFalseVal(false));
		self::assertTrue(tx_mklib_util_String::isFalseVal('false'));
		self::assertTrue(tx_mklib_util_String::isFalseVal('0'));
		self::assertTrue(tx_mklib_util_String::isFalseVal(0));
		self::assertFalse(tx_mklib_util_String::isFalseVal(true));
		self::assertFalse(tx_mklib_util_String::isFalseVal('true'));
		self::assertFalse(tx_mklib_util_String::isFalseVal('TrUe'));
		self::assertFalse(tx_mklib_util_String::isFalseVal('1'));
		self::assertFalse(tx_mklib_util_String::isFalseVal(1));
	}
	/**
	 * removeRepeatedlyOccurrings testen
	 */
	public function testRemoveRepeatedlyOccurrings() {
		self::assertEquals(
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
		self::assertEquals('feUsers',tx_mklib_util_String::toCamelCase('fe_users'));
		self::assertEquals('txMklibWordlist',tx_mklib_util_String::toCamelCase('tx_mklib_wordlist'));
		self::assertEquals('txMklibTestsUtilStringTestcase',tx_mklib_util_String::toCamelCase('tx_mklib_tests_util_String_testcase'));
	}

	public function testObfusicateEmail() {
		$this->initSpamProtectionConfig();

		self::assertEquals(
			'test.mail&#8203;(at)&#8203ein-host.de',
			tx_mklib_util_String::obfusicateEmail(array(0=>'test.mail@ein-host.de')),
			'Mail falsch verschleiert'
		);
	}

	public function testObfusicateContainedEmails() {
		$this->initSpamProtectionConfig();

		self::assertEquals(
			'ein text mit einer mail mail&#8203;(at)&#8203host.de und noch einer anothermail&#8203;(at)&#8203host.de',
			tx_mklib_util_String::obfusicateContainedEmails(
				'ein text mit einer mail mail@host.de und noch einer anothermail@host.de'
			),
			'Mail falsch verschleiert'
		);
	}

	public function testObfusicateContainedEmailsIfNoContained() {
		$this->initSpamProtectionConfig();

		self::assertEquals(
			'ein text mit keiner mail.',
			tx_mklib_util_String::obfusicateContainedEmails(
				'ein text mit keiner mail.'
			),
			'Mail falsch verschleiert'
		);
	}

	public function testConvertEmailToMailToLink() {
		$this->initSpamProtectionConfig();

		$expectedLink = '/\<a href="javascript:linkTo_UnCryptMailto\(\'(.*)\'\);" \>test.mail&#8203;\(at\)&#8203ein\-host.de\<\/a\>/';
		// leerzeichen ab 6.2.3 nicht mehr vorhanden
		if (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
			$expectedLink = str_replace('" \>', '"\>', $expectedLink);
		}
		self::assertRegExp(
			$expectedLink,
			tx_mklib_util_String::convertEmailToMailToLink(array(0=>'test.mail@ein-host.de')),
			'Mailto Link falsch'
		);
	}

	public function testConvertContainedEmailsToMailToLinks() {
		$this->initSpamProtectionConfig();

		$expectedLink = 'ein text mit einer mail <a href="javascript:linkTo_UnCryptMailto(\'ocknvq,ocknBjquv0fg\');" >mail&#8203;(at)&#8203host.de</a> und noch einer <a href="javascript:linkTo_UnCryptMailto(\'ocknvq,cpqvjgtocknBjquv0fg\');" >anothermail&#8203;(at)&#8203host.de</a>';
		// leerzeichen ab 6.2.3 nicht mehr vorhanden
		if (tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
			$expectedLink = str_replace('" >', '">', $expectedLink);
		}
		self::assertEquals(
			$expectedLink,
			tx_mklib_util_String::convertContainedEmailsToMailToLinks(
				'ein text mit einer mail mail@host.de und noch einer anothermail@host.de'
			),
			'Mailto Links falsch'
		);
	}

	public function testConvertContainedEmailsToMailToLinksIfNoContained() {
		$this->initSpamProtectionConfig();

		self::assertEquals(
			'ein text mit keiner mail.',
			tx_mklib_util_String::convertContainedEmailsToMailToLinks(
				'ein text mit keiner mail.'
			),
			'Mailto Links falsch'
		);
	}

	/**
	 * wie über TS
	 */
	public static function initSpamProtectionConfig() {
		tx_rnbase::load('tx_rnbase_util_Misc');
		tx_rnbase_util_Misc::prepareTSFE();

		$GLOBALS['TSFE']->spamProtectEmailAddresses = 2;
		$GLOBALS['TSFE']->config['config']['spamProtectEmailAddresses_atSubst'] = '&#8203;(at)&#8203';

		//tq_seo extension hat einen hook der auf das folgende feld zugreift.
		//wenn dieses nicht da ist bricht der test mit einer php warnung ab, was
		//wir verhindern wollen!
		$GLOBALS['TSFE']->rootLine[0]['uid'] = 1;
	}

	/**
	 * @group unit
	 */
	public function testRemoveLineBreaks() {
		$testString = "test1\ntest2\r\n";

		self::assertEquals(
			"test1test2",
			tx_mklib_util_String::removeLineBreaks($testString),
			'Line breaks nicht enfernt'
		);
		self::assertEquals(
			"test1 test2 ",
			tx_mklib_util_String::removeLineBreaks($testString, ' '),
			'Line breaks nicht enfernt'
		);
	}

	/**
	 * @group unit
	 * @dataProvider getUrls
	 */
	public function testConvertUrlsinTextToLinks(
		$text, $aTagParams, $expectedParsedText
	) {
		self::assertEquals(
			$expectedParsedText,
			tx_mklib_util_String::convertUrlsInTextToLinks($text, $aTagParams),
			'Text falsch geparsed'
		);
	}

	/**
	 * @return array
	 */
	public function getUrls() {
		return array(
			array('text mit www.google.de link', '', 'text mit <a  href="http://www.google.de" >www.google.de</a> link'),
			array('www.google.de link', 'target="_blank"', '<a target="_blank" href="http://www.google.de" >www.google.de</a> link'),
			array('www.google.de?param1=value1&param2=value2-#anchor link', '', '<a  href="http://www.google.de?param1=value1&param2=value2-#anchor" >www.google.de?param1=value1&param2=value2-#anchor</a> link'),
			array('text mit www.google.de', '', 'text mit <a  href="http://www.google.de" >www.google.de</a>'),
			array('text mit http://www.google.de link', '', 'text mit <a  href="http://www.google.de" >http://www.google.de</a> link'),
			array('http://www.google.de link', '', '<a  href="http://www.google.de" >http://www.google.de</a> link'),
			array('text mit http://www.google.de', '', 'text mit <a  href="http://www.google.de" >http://www.google.de</a>'),
			array('text mit https://www.google.de link', '', 'text mit <a  href="https://www.google.de" >https://www.google.de</a> link'),
			array('https://www.google.de link', '', '<a  href="https://www.google.de" >https://www.google.de</a> link'),
			array('text mit https://www.google.de', '', 'text mit <a  href="https://www.google.de" >https://www.google.de</a>'),
			array('text mit <a href="https://www.google.de">link</a>', '', 'text mit <a href="https://www.google.de">link</a>'),
			array('text mit <script type="text/javascript">alert(\'ohoh\');</script>', '', 'text mit <sc<x>ript type="text/javascript">alert(\'ohoh\');</script>'),
			array('<p>http://www.difu.de</p>', '', '<p><a  href="http://www.difu.de" >http://www.difu.de</a></p>'),
			array('<p>www.difu.de</p>', '', '<p><a  href="http://www.difu.de" >www.difu.de</a></p>'),
			array('<p> http://www.difu.de </p>', '', '<p> <a  href="http://www.difu.de" >http://www.difu.de</a> </p>'),
			array('mail@dummy.com', '', '<a href="mailto:mail@dummy.com">mail@dummy.com</a>'),
			array('<p>mail@dummy.com</p>', '', '<p><a href="mailto:mail@dummy.com">mail@dummy.com</a></p>'),
			array('(www.difu.de)', '', '(<a  href="http://www.difu.de" >www.difu.de</a>)'),
			array('-www.difu.de?test-test', '', '-<a  href="http://www.difu.de?test-test" >www.difu.de?test-test</a>'),
			array('_www.difu.de?test_test', '', '_<a  href="http://www.difu.de?test_test" >www.difu.de?test_test</a>'),
			array('*www.difu.de', '', '*<a  href="http://www.difu.de" >www.difu.de</a>'),
		);
	}

	/**
	 * @group unit
	 */
	public function testRemoveMultipleWhitespaces() {
		self::assertEquals(' ', tx_mklib_util_String::removeMultipleWhitespaces('   '));
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_String_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_String_testcase.php']);
}