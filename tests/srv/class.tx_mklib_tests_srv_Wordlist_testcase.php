<?php
/**
 * @author Hannes Bochmann
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
 * benötigte Klassen einbinden.
 */
tx_rnbase::load('tx_mklib_util_ServiceRegistry');
tx_rnbase::load('tx_mklib_tests_Util');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_mklib_srv_Wordlist');

/**
 * Generic form view test.
 *
 *
 * @group integration
 */
class tx_mklib_tests_srv_Wordlist_testcase extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @group unit
     */
    public function testGetWordlistIfNoResult()
    {
        $wordlistSrv = $this->getMock('tx_mklib_srv_Wordlist', array('search'));
        $fields = array('some fields');
        $wordlistSrv->expects(self::once())
            ->method('search')
            ->with($fields, array())
            ->will(self::returnValue(array()));

        self::assertNull($this->callInaccessibleMethod($wordlistSrv, 'getWordlist', $fields));
    }

    /**
     * @group unit
     */
    public function testGetWordlistIfResult()
    {
        $wordlistSrv = $this->getMock('tx_mklib_srv_Wordlist', array('search'));
        $fields = array('some fields');
        $wordlistSrv->expects(self::once())
            ->method('search')
            ->with($fields, array())
            ->will(self::returnValue(array('result')));

        self::assertEquals(
            array('result'),
            $this->callInaccessibleMethod($wordlistSrv, 'getWordlist', $fields)
        );
    }

    /**
     * Testen ob getWordlistEntryByWord null zurück gibt wenn nichts gefunden wurde.
     *
     * @group integration
     */
    public function testGetWordlistEntryByWordReturnsEmptyIfNoMatch()
    {
        $wordlistSrv = $this->getMock('tx_mklib_srv_Wordlist', array('getWordlist'));
        $wordlistSrv->expects(self::once())
            ->method('getWordlist')
            ->will(self::returnValue($this->getTestWordlist()));

        $ret = $wordlistSrv->getWordlistEntryByWord('nothing');

        self::assertTrue(empty($ret), 'Es wurden Treffer zurück gegeben!');
    }

    /**
     * Testen ob getWordlistEntryByWord mehrere Treffer zurück gibt im normale Modus.
     *
     * @group integration
     */
    public function testGetWordlistEntryByWordReturnsMatches()
    {
        $wordlistSrv = $this->getMock('tx_mklib_srv_Wordlist', array('getWordlist'));
        $wordlistSrv->expects(self::exactly(2))
            ->method('getWordlist')
            ->will(self::returnValue($this->getTestWordlist()));

        $ret = $wordlistSrv->getWordlistEntryByWord('fuck shit sfuck');
        self::assertEquals(2, count($ret), 'Das Treffer Array hat nicht die korrekte Größe!');
        self::assertEquals('fuck', $ret[0], 'Das gefundene Wort ist nicht korrekt!');
        self::assertEquals('shit', $ret[1], 'Das gefundene Wort ist nicht korrekt!');

        $ret = $wordlistSrv->getWordlistEntryByWord('who the fuck is alice? shit haha sfuck');
        self::assertEquals(2, count($ret), 'Das Treffer Array hat nicht die korrekte Größe!');
        self::assertEquals('fuck', $ret[0], 'Das gefundene Wort ist nicht korrekt!');
        self::assertEquals('shit', $ret[1], 'Das gefundene Wort ist nicht korrekt!');
    }

    /**
     * Testen ob getWordlistEntryByWord einen Treffer zurück gibt im none greedy Modus.
     *
     * @group integration
     */
    public function testGetWordlistEntryByWordReturns1MatchIfInNoneGreedyMode()
    {
        $wordlistSrv = $this->getMock('tx_mklib_srv_Wordlist', array('getWordlist'));
        $wordlistSrv->expects(self::exactly(2))
            ->method('getWordlist')
            ->will(self::returnValue($this->getTestWordlist()));

        $ret = $wordlistSrv->getWordlistEntryByWord('fuck shit', false);
        self::assertEquals('fuck', $ret, 'Das gefundene Wort ist nicht korrekt!');
        $ret = $wordlistSrv->getWordlistEntryByWord('who the fuck is alice? shit', false);
        self::assertEquals('fuck', $ret, 'Das gefundene Wort ist nicht korrekt!');
    }

    /**
     * Testen ob getWordlistEntryByWord einen Treffer zurück gibt wenn es einen gibt.
     *
     * @group integration
     */
    public function testGetWordlistEntryByWordReturnsMatchWithComplexString()
    {
        $wordlistSrv = $this->getMock('tx_mklib_srv_Wordlist', array('getWordlist'));
        $wordlistSrv->expects(self::once())
            ->method('getWordlist')
            ->will(self::returnValue($this->getTestWordlist()));
        $ret = $wordlistSrv->getWordlistEntryByWord('Einige Worte, blub, da war es!');

        self::assertEquals(1, count($ret), 'Das Treffer Array hat nicht die korrekte Größe!');
        self::assertEquals('blub', $ret[0], 'Das zurückgegebene Wort stimmt nicht!');
    }

    /**
     * Testen ob getWordlistEntryByWord einen Eintrag zurück liefert.
     *
     * @group integration
     */
    public function testGetWordlistEntryByBlacklistedWordReturnsCorrectData()
    {
        $wordlistSrv = $this->getMock('tx_mklib_srv_Wordlist', array('getWordlist'));
        $testWordList = $this->getTestWordlist();
        foreach ($testWordList as $index => $entry) {
            if (!$entry->getBlacklisted()) {
                unset($testWordList[$index]);
            }
        }
        $wordlistSrv->expects(self::exactly(2))
            ->method('getWordlist')
            ->with(array(
                'WORDLIST.blacklisted' => array(OP_EQ_INT => 1),
                'WORDLIST.whitelisted' => array(OP_EQ_INT => 0),
            ))
            ->will(self::returnValue($testWordList));

        $ret = $wordlistSrv->getBlacklistEntryByWord('fuck shit ass');

        self::assertEquals(3, count($ret), 'Das Treffer Array hat nicht die korrekte Größe!');
        self::assertEquals('fuck', $ret[0], 'Das zurückgegebene Wort stimmt nicht!');
        self::assertEquals('shit', $ret[1], 'Das zurückgegebene Wort stimmt nicht!');
        self::assertEquals('ass', $ret[2], 'Das zurückgegebene Wort stimmt nicht!');

        $ret = $wordlistSrv->getBlacklistEntryByWord('some fuck bad shit words ass');

        self::assertEquals(3, count($ret), 'Das Treffer Array hat nicht die korrekte Größe!');
        self::assertEquals('fuck', $ret[0], 'Das zurückgegebene Wort stimmt nicht!');
        self::assertEquals('shit', $ret[1], 'Das zurückgegebene Wort stimmt nicht!');
        self::assertEquals('ass', $ret[2], 'Das zurückgegebene Wort stimmt nicht!');
    }

    /**
     * Testen ob getWordlistEntryByWord einen Eintrag zurück liefert.
     *
     * @group integration
     */
    public function testGetWordlistEntryByWhitelistedWordReturnsCorrectData()
    {
        $wordlistSrv = $this->getMock('tx_mklib_srv_Wordlist', array('getWordlist'));
        $testWordList = $this->getTestWordlist();
        foreach ($testWordList as $index => $entry) {
            if (!$entry->getWhitelisted()) {
                unset($testWordList[$index]);
            }
        }
        $wordlistSrv->expects(self::once())
            ->method('getWordlist')
            ->with(array(
                'WORDLIST.blacklisted' => array(OP_EQ_INT => 0),
                'WORDLIST.whitelisted' => array(OP_EQ_INT => 1),
            ))
            ->will(self::returnValue($testWordList));

        $ret = $wordlistSrv->getWhitelistEntryByWord('nice');

        self::assertEquals(1, count($ret), 'Das Treffer Array hat nicht die korrekte Größe!');
        self::assertEquals('nice', $ret[0], 'Das zurückgegebene Wort stimmt nicht!');
    }

    /**
     * @return multitype:object Ambigous <object, boolean, mixed, \TYPO3\CMS\Core\Utility\array<\TYPO3\CMS\Core\SingletonInterface>, \TYPO3\CMS\Core\SingletonInterface, \TYPO3\CMS\Core\Utility\mixed>
     */
    protected function getTestWordlist()
    {
        return array(
            0 => tx_rnbase::makeInstance(
                'tx_mklib_model_WordlistEntry',
                array('uid' => 1, 'word' => 'some')
            ),
            1 => tx_rnbase::makeInstance(
                'tx_mklib_model_WordlistEntry',
                array('uid' => 2, 'word' => 'fuck,ass', 'blacklisted' => 1)
            ),
            2 => tx_rnbase::makeInstance(
                'tx_mklib_model_WordlistEntry',
                array('uid' => 3, 'word' => 'nice', 'whitelisted' => 1)
            ),
            3 => tx_rnbase::makeInstance(
                'tx_mklib_model_WordlistEntry',
                array('uid' => 4, 'word' => 'bla,blub', 'whitelisted' => 1)
            ),
            4 => tx_rnbase::makeInstance(
                'tx_mklib_model_WordlistEntry',
                array('uid' => 5, 'word' => 'shit', 'blacklisted' => 1)
            ),
        );
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/srv/class.tx_mklib_tests_srv_Wordlist_testcase.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/srv/class.tx_mklib_tests_srv_Wordlist_testcase.php'];
}
