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

/**
 * Service for accessing ad entries.
 */
class tx_mklib_srv_Wordlist extends tx_mklib_srv_Base
{
    /**
     * Holt die Wordlist aus der DB und prüft ob ein Wort der Wortliste im gegebenen String vorkommt.
     *
     * @param $word
     * @param $greedy | alle oder nur ein Treffer?
     * @param $sanitizeWord | alle Sonderzeichen vor der Prüfung entfernen
     */
    public function getWordlistEntryByWord($word, $greedy = true, $sanitizeWord = true)
    {
        return $this->checkForWordInWordlist($word, $this->getWordlist(), $greedy, $sanitizeWord);
    }

    /**
     * Holt die Wordlist aus der DB und prüft ob ein Wort der Wortliste im gegebenen String vorkommt
     * nur Einträge die blacklisted sind.
     *
     * @param $word
     * @param $greedy | alle oder nur ein Treffer?
     * @param $sanitizeWord | alle Sonderzeichen vor der Prüfung entfernen
     */
    public function getBlacklistEntryByWord($word, $greedy = true, $sanitizeWord = true)
    {
        $fields = [
            'WORDLIST.blacklisted' => [OP_EQ_INT => 1],
            'WORDLIST.whitelisted' => [OP_EQ_INT => 0],
        ];

        return $this->checkForWordInWordlist($word, $this->getWordlist($fields), $greedy, $sanitizeWord);
    }

    /**
     * Holt die Wordlist aus der DB und prüft ob ein Wort der Wortliste im gegebenen String vorkommt
     * nur Einträge die whitelisted sind.
     *
     * @param $word
     * @param $greedy | alle oder nur ein Treffer?
     * @param $sanitizeWord | alle Sonderzeichen vor der Prüfung entfernen
     */
    public function getWhitelistEntryByWord($word, $greedy = true, $sanitizeWord = true)
    {
        $fields = [
            'WORDLIST.blacklisted' => [OP_EQ_INT => 0],
            'WORDLIST.whitelisted' => [OP_EQ_INT => 1],
        ];

        return $this->checkForWordInWordlist($word, $this->getWordlist($fields), $greedy, $sanitizeWord);
    }

    /**
     * Gibt die gesamte Wordlist zurück.
     *
     * @param array $fields
     *
     * @return array
     */
    protected function getWordlist(array $fields = [])
    {
        $options = [/*'debug' => 1*/];

        $foo = $this->search($fields, $options);
        if (count($foo)) {
            return $foo;
        }
        // else
        return null;
    }

    /**
     * Prüft ob ein Wort der Wortliste im gegebenen String vorkommt.
     *
     * @param string $word
     * @param $wordlist
     * @param $greedy | alle oder nur ein Treffer?
     * @param $sanitizeWord | alle Sonderzeichen vor der Prüfung entfernen
     */
    private function checkForWordInWordlist($word, $wordlist, $greedy = true, $sanitizeWord = true)
    {
        //wenn es kein array ist, dann is die wordlist leer
        if (!is_array($wordlist)) {
            return null;
        }

        if ($sanitizeWord) {//alle Sondzeichen entfernen
            $utilString = tx_rnbase::makeInstance('tx_mklib_util_String');
            $word = $utilString->html2plain($word);
            $word = $utilString->removeNoneLetters($word);
        }

        //die einzelnen Wörter prüfen
        foreach ($wordlist as $entry) {
            //damit in DB nicht für jedes Wort ein Eintrag angelegt werden muss,
            //werden komma-separierte Wörterlisten innerhalb des Wort-Feldes
            //ebenfalls unterstützt --> str_replace
            $sWordlist .= $sEntry = str_replace(',', '|', $entry->getWord());
            // '\b' bedeutet das nur nach ganzen Wörtern gesucht wird. ist fuck
            //geblacklisted wird sfuck nicht bemängelt
            if (!$greedy && preg_match('/\b('.$sEntry.' )\b/i', $word, $matches)) {//nur einen treffer?
                return $matches[0];
            }
            $sWordlist .= '|'; //für die regexp
        }
        // '\b' bedeutet das nur nach ganzen Wörtern gesucht wird. ist fuck
        //geblacklisted wird sfuck nicht bemängelt
        if ($greedy && preg_match_all('/\b('.$sWordlist.')\b/i', $word, $matches)) {//alle treffer?
            //preg_mactch_all gibt ein array zurück, was auch viele leere Werte für die Nicht-Treffer enthält. Diese stören und werden bereinigt
            return tx_mklib_util_Array::removeEmptyArrayValuesSimple($matches[0]);
        }
        //kein Treffer!!!
        return null;
    }

    /**
     * @return array
     */
    public static function loadTca()
    {
        $GLOBALS['TCA']['tx_mklib_wordlist'] = require tx_rnbase_util_Extensions::extPath(
            'mklib',
            'Configuration/TCA/tx_mklib_wordlist.php'
        );
    }

    /**
     * Liefert die zugehörige Search-Klasse zurück.
     *
     * @return string
     */
    public function getSearchClass()
    {
        return 'tx_mklib_search_Wordlist';
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Wordlist.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Wordlist.php'];
}
