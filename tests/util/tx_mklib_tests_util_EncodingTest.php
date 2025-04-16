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
 * Class for encodings.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_tests_util_EncodingTest extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * Dies ist die in Hex umgewandelte Form des Strings
     * mit der ISO-8859-1 Zeichen codierung.
     */
    private static string $hexIso88591 = 'c4e4d6f6dcfcdf';

    /**
     * Dies ist die in Hex umgewandelte Form des Strings
     * mit der UTF-8 Zeichen codierung.
     */
    private static string $hexUtf8 = 'c384c3a4c396c3b6c39cc3bcc39f';

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mklib']['baseExceptionCode'] = 400;
    }

    /**
     * @group integration
     */
    public function testIsEncoding(): void
    {
        $strUtf8 = pack('H*', self::$hexUtf8);
        $strIso88591 = pack('H*', self::$hexIso88591);

        if (false) {
            echo '<pre>'.var_export([
                'iso88591' => [
                    'string' => $strIso88591,
                    'utf8 level' => Sys25\RnBase\Utility\Strings::isUtf8String($strIso88591),
                    'utf8 encoding' => tx_mklib_util_Encoding::detectUtfEncoding($strIso88591),
                    'bytelength' => mb_strlen($strIso88591, '8bit'),
                    'bin2hex' => bin2hex($strIso88591),
                    'is utf8' => tx_mklib_util_Encoding::isEncoding($strIso88591, 'UTF-8'),
                    'is iso88591' => tx_mklib_util_Encoding::isEncoding($strIso88591, 'ISO-8859-1'),
                ],
                'utf8' => [
                    'string' => $strUtf8,
                    'utf8 level' => Sys25\RnBase\Utility\Strings::isUtf8String($strUtf8),
                    'utf8 encoding' => tx_mklib_util_Encoding::detectUtfEncoding($strUtf8),
                    'bytelength' => mb_strlen($strUtf8, '8bit'),
                    'bin2hex' => bin2hex($strUtf8),
                    'is utf8' => tx_mklib_util_Encoding::isEncoding($strUtf8, 'UTF-8'),
                    'is iso88591' => tx_mklib_util_Encoding::isEncoding($strUtf8, 'ISO-8859-1'),
                ],
                'DEBUG: '.__FILE__.'&'.__METHOD__.' Line: '.__LINE__,
            ], true).'</pre>';
        } // @TODO: remove me

        self::assertTrue(
            tx_mklib_util_Encoding::isEncoding($strIso88591, 'ISO-8859-1'),
            '$strIso88591 ist NICHT ISO-8859-1'
        );
        self::assertFalse(
            tx_mklib_util_Encoding::isEncoding($strIso88591, 'UTF-8'),
            '$strIso88591 IST UTF-8'
        );

        self::assertTrue(
            tx_mklib_util_Encoding::isEncoding($strUtf8, 'UTF-8'),
            '$strUtf8 ist NICHT UTF-8'
        );
        self::assertFalse(
            tx_mklib_util_Encoding::isEncoding($strUtf8, 'ISO-8859-1'),
            '$strUtf8 IST ISO-8859-1'
        );
    }

    /**
     * @group integration
     */
    public function testConvertStringFromISO88591ToUTF8(): void
    {
        $string = pack('H*', self::$hexIso88591);

        self::assertEquals(
            7,
            strlen($string),
            '$string ist nicht mit ISO-8859-1 codiert.'
        );

        $string = tx_mklib_util_Encoding::convertEncoding(
            $string,
            'UTF-8',
            'ISO-8859-1'
        );

        self::assertEquals(
            14,
            strlen($string),
            '$string wurde nicht nach UTF-8 codiert.'
        );
        self::assertEquals(
            self::$hexUtf8,
            bin2hex($string),
            'Der HEX-Wert von $string stimmt nach der codierung nicht.'
        );
    }

    /**
     * @group integration
     */
    public function testConvertStringFromUTF8ToISO88591(): void
    {
        $string = pack('H*', self::$hexUtf8);

        self::assertEquals(
            14,
            strlen($string),
            '$string ist nicht mit ISO-8859-1 codiert.'
        );

        $string = tx_mklib_util_Encoding::convertEncoding(
            $string,
            'ISO-8859-1',
            'UTF-8'
        );

        self::assertEquals(
            7,
            strlen($string),
            '$string wurde nicht nach UTF-8 codiert.'
        );
        self::assertEquals(
            self::$hexIso88591,
            bin2hex($string),
            'Der HEX-Wert von $string stimmt nach der codierung nicht.'
        );
    }

    /**
     * @depends testConvertStringFromISO88591ToUTF8
     *
     * @group integration
     */
    public function testConvertArrayFromISO88591ToUTF8(): void
    {
        $stringIso = pack('H*', self::$hexIso88591);
        $stringUtf8 = pack('H*', self::$hexUtf8);

        $arrayFrom = [
            'var' => $stringIso,
            'array' => [
                'var' => $stringIso,
                'array' => [
                    'var1' => $stringIso,
                    'var2' => $stringIso,
                ],
            ],
        ];
        $arrayTo = [
            'var' => $stringUtf8,
            'array' => [
                'var' => $stringUtf8,
                'array' => [
                    'var1' => $stringUtf8,
                    'var2' => $stringUtf8,
                ],
            ],
        ];

        $arrayFrom = tx_mklib_util_Encoding::convertEncoding(
            $arrayFrom,
            'UTF-8',
            'ISO-8859-1'
        );

        self::assertEquals(
            $arrayTo,
            $arrayFrom,
            '$array wurde nicht richtig nach UTF-8 codiert.'
        );
    }

    /**
     * @depends testConvertArrayFromISO88591ToUTF8
     *
     * @group integration
     */
    public function testConvertArrayObjectFromISO88591ToUTF8(): void
    {
        $stringIso = pack('H*', self::$hexIso88591);
        $stringUtf8 = pack('H*', self::$hexUtf8);

        $modelFrom = new ArrayObject(
            [
                'uid' => 1,
                'title' => $stringIso,
                'description' => $stringIso,
            ]
        );
        $modelTo = new ArrayObject(
            [
                'uid' => 1,
                'title' => $stringUtf8,
                'description' => $stringUtf8,
            ]
        );

        $modelFrom = tx_mklib_util_Encoding::convertEncoding(
            $modelFrom,
            'UTF-8',
            'ISO-8859-1'
        );

        self::assertEquals(
            $modelTo->getArrayCopy(),
            $modelFrom->getArrayCopy(),
            '$array wurde nicht richtig nach UTF-8 codiert.'
        );
    }

    /**
     * @depends testConvertArrayFromISO88591ToUTF8
     *
     * @group integration
     */
    public function testConvertModelFromISO88591ToUTF8(): void
    {
        $stringIso = pack('H*', self::$hexIso88591);
        $stringUtf8 = pack('H*', self::$hexUtf8);

        $modelFrom = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            [
                'uid' => 1,
                'title' => $stringIso,
                'description' => $stringIso,
            ]
        );
        $modelTo = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            [
                'uid' => 1,
                'title' => $stringUtf8,
                'description' => $stringUtf8,
            ]
        );

        $modelFrom = tx_mklib_util_Encoding::convertEncoding(
            $modelFrom,
            'UTF-8',
            'ISO-8859-1'
        );

        self::assertEquals(
            $modelTo->getProperty(),
            $modelFrom->getProperty(),
            '$array wurde nicht richtig nach UTF-8 codiert.'
        );
    }

    /**
     * @depends testConvertArrayFromISO88591ToUTF8
     *
     * @group integration
     */
    public function testConvertArrayWithModelFromISO88591ToUTF8(): void
    {
        $stringIso = pack('H*', self::$hexIso88591);
        $stringUtf8 = pack('H*', self::$hexUtf8);

        $modelTo = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            Sys25\RnBase\Domain\Model\BaseModel::class,
            [
                'uid' => 1,
                'title' => $stringUtf8,
                'description' => $stringUtf8,
            ]
        );
        $data = [
            'one' => TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                Sys25\RnBase\Domain\Model\BaseModel::class,
                [
                    'uid' => 1,
                    'title' => $stringIso,
                    'description' => $stringIso,
                ]
            ),
            'twoe' => TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                Sys25\RnBase\Domain\Model\BaseModel::class,
                [
                    'uid' => 1,
                    'title' => $stringIso,
                    'description' => $stringIso,
                ]
            ),
        ];

        $data = tx_mklib_util_Encoding::convertEncoding(
            $data,
            'UTF-8',
            'ISO-8859-1'
        );

        self::assertEquals(
            $modelTo->getProperty(),
            $data['one']->getProperty(),
            '$array wurde nicht richtig nach UTF-8 codiert.'
        );
        self::assertEquals(
            $modelTo->getProperty(),
            $data['twoe']->getProperty(),
            '$array wurde nicht richtig nach UTF-8 codiert.'
        );
    }

    public function testConvertModelThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(4005);

        // aufruf mittels falschem object
        tx_mklib_util_Encoding::convertEncoding(
            new Exception(),
            'UTF-8',
            'ISO-8859-1'
        );
    }
}
