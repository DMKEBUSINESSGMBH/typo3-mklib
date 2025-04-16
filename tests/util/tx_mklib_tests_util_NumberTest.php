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
 * Numeric Util Tests.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_tests_util_NumberTest extends Sys25\RnBase\Testing\BaseTestCase
{
    private string|bool $oldLocal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oldLocal = setlocale(LC_ALL, 0);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        setlocale(LC_ALL, $this->oldLocal);
    }

    #[PHPUnit\Framework\Attributes\DataProvider('providerFloatVal')]
    public function testFloatVal(string $expected, string $actual, array $config): void
    {
        if (!is_array($config)) {
            $config = [];
        }

        // bei einem normalen float sollte nun eine Kommazahl herauskommen.

        self::assertEquals($expected, tx_mklib_util_Number::floatVal($actual, $config));
    }

    #[PHPUnit\Framework\Attributes\DataProvider('providerFloatVal')]
    public function testFloatValLcDe(string $expected, string $actual, array $config): void
    {
        // Locale auf deutsch stellen.
        // Damit sind Beispielsweise die Dezimaltrennzeichen falsch (,anstatt.)
        setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'deu_deu', 'de', 'ge');

        $this->testFloatVal($expected, $actual, $config);
    }

    public static function providerFloatVal(): array
    {
        return [
            // über die parseFloat, sollte genau das herauskommen, was wir benötigen
            // ein Float mit einem Punkt als Dezimaltrennzeichen.
            1 => ['5.43', '5.43', []],
            2 => ['-5.43', '-5,43', []],
            // hierzu muss erst der Todo aus parseFloat abgearbeidet werden.
            // 'Line:'.__LINE__ => array('5435.55', '5.435,55', array()),
            // 'Line:'.__LINE__ => array('5435.55', '5,435.55', array()),
            // Jetzt wollen wir eine Pipe als Dezimaltrennzeichen, nur so zum Spaß ;)
            3 => ['5|43', '5.43', ['decimal_point' => '|']],
        ];
    }
}
