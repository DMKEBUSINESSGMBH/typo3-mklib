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
 * Generic form view test.
 */
class tx_mklib_tests_util_FileTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        // methoden aufrufen, die einmalig speicherplatz benötigen
        // das ferfälscht nicht die memmory leak und time werte
        tx_mklib_util_File::getFileTool();
        tx_mklib_util_File::getSiteUrl();
        tx_mklib_util_File::getDocumentRoot();
    }

    public function setUp(): void
    {
    }

    protected function tearDown(): void
    {
    }

    private static function createTestfiles($testfolder)
    {
        \Sys25\RnBase\Utility\Files::mkdir_deep($testfolder);
        $files = [
            [$testfolder.'/', 'test.zip'],
            [$testfolder.'/', 'test.xml'],
            [$testfolder.'/', 'test.tmp'],
            [$testfolder.'/', 'test.dat'],
            [$testfolder.'/sub/', 'test.zip'],
            [$testfolder.'/sub/', 'test.tmp'],
            [$testfolder.'/sub/sub/', 'test.xml'],
            [$testfolder.'/sub/sub/', 'test.dat'],
        ];
        foreach ($files as $file) {
            $path = $file[0];
            $file = $file[1];
            if (!is_dir($path)) {
                \Sys25\RnBase\Utility\Files::mkdir_deep($path);
            }
            $iH = fopen($path.$file, 'w+');
            fwrite($iH, 'This is an automatic generated testfile and can be removed.');
            fclose($iH);
        }
    }

    public function testCleanupFilesNotInTypo3temp()
    {
        // @TODO: lifetime testen
        // testverzeichnis anlegen
        $testfolder = \TYPO3\CMS\Core\Core\Environment::getVarPath().'/fixtures/toremove';
        self::createTestfiles($testfolder);

        // das aufräumen!
        $count = tx_mklib_util_File::cleanupFiles($testfolder.'/', [
            // die dateien werden erst nach der $GLOBALS['EXEC_TIME'] generiert.
            'lifetime' => -10800,
            'recursive' => '0',
            'filetypes' => 'zip, xml',
        ]);
        self::assertEquals(0, $count, 'wrong deleted count.');
        // weider löschen
        \Sys25\RnBase\Utility\Files::rmdir($testfolder, true);
    }

    public function testCleanupFilesWithZipAndXml()
    {
        // @TODO: lifetime testen
        // testverzeichnis anlegen
        $testfolder = \TYPO3\CMS\Core\Core\Environment::getVarPath().'/fixtures/toremove';
        self::createTestfiles($testfolder);

        // das aufräumen!
        $count = tx_mklib_util_File::cleanupFiles($testfolder.'/', [
            // die dateien werden erst nach der $GLOBALS['EXEC_TIME'] generiert.
            'lifetime' => -10800,
            'recursive' => '0',
            'filetypes' => 'zip, xml',
            'skiptypo3tempcheck' => '1',
        ]);
        self::assertEquals(2, $count, 'wrong deleted count.');
        // weider löschen
        \Sys25\RnBase\Utility\Files::rmdir($testfolder, true);
    }

    public function testCleanupFilesRecursiveWithZipAndXml()
    {
        // @TODO: lifetime testen
        // testverzeichnis anlegen
        $testfolder = \TYPO3\CMS\Core\Core\Environment::getVarPath().'/fixtures/toremove';
        self::createTestfiles($testfolder);

        // das aufräumen!
        $count = tx_mklib_util_File::cleanupFiles($testfolder.'/', [
            // die dateien werden erst nach der $GLOBALS['EXEC_TIME'] generiert.
            'lifetime' => -10800,
            'recursive' => '1',
            'filetypes' => 'zip, xml',
            'skiptypo3tempcheck' => '1',
        ]);
        self::assertEquals(4, $count, 'wrong deleted count. testfolder: '.$testfolder);
        // weider löschen
        \Sys25\RnBase\Utility\Files::rmdir($testfolder, true);
    }

    public function testParseUrlFromParts()
    {
        $url = 'https://kunde:mk17@jenkins.project.dmknet.de:80/jenkins?test=param#anchor';
        $parts = parse_url($url);
        $newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
        self::assertEquals($url, $newUrl);

        unset($parts['pass']);
        $url = 'https://kunde@jenkins.project.dmknet.de:80/jenkins?test=param#anchor';
        $newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
        self::assertEquals($url, $newUrl);

        unset($parts['user']);
        $url = 'https://jenkins.project.dmknet.de:80/jenkins?test=param#anchor';
        $newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
        self::assertEquals($url, $newUrl);

        unset($parts['port']);
        $url = 'https://jenkins.project.dmknet.de/jenkins?test=param#anchor';
        $newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
        self::assertEquals($url, $newUrl);

        unset($parts['query']);
        $url = 'https://jenkins.project.dmknet.de/jenkins#anchor';
        $newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
        self::assertEquals($url, $newUrl);

        unset($parts['fragment']);
        $url = 'https://jenkins.project.dmknet.de/jenkins';
        $newUrl = tx_mklib_util_File::parseUrlFromParts($parts);
        self::assertEquals($url, $newUrl);
    }

    /**
     * @dataProvider getFiles
     */
    public function testIsValidFile($filepath, $expectedReturnValue)
    {
        self::assertEquals($expectedReturnValue, tx_mklib_util_File::isValidFile($filepath));
    }

    /**
     * @return multitype:multitype:string multitype:Ambigous <string, string, unknown>
     */
    public function getFiles()
    {
        return [
            [tx_mklib_util_File::getServerPath(''), false],
            [tx_mklib_util_File::getServerPath('typo3'), false],
            [tx_mklib_util_File::getServerPath('typo3/index.php'), true],
        ];
    }

    /**
     * @group unit
     */
    public function testGetDocumentRoot()
    {
        $documentRoot = tx_mklib_util_File::getDocumentRoot();
        // to make sure that not only "/" is returned
        self::assertGreaterThan(3, strlen($documentRoot));
        self::assertEquals(\Sys25\RnBase\Utility\Environment::getPublicPath(), $documentRoot);
    }
}
