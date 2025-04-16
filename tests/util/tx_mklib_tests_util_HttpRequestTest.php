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
 * benötigte Klassen einbinden.
 */

/**
 * Http Request Object Tests.
 */
class tx_mklib_tests_util_HttpRequestTest extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @group integration
     */
    public function testHttpRequestWithCurlAndSllAndServerAuth(): void
    {
        $this->markTestIncomplete('phpunit.project.dmknet is no longer used.');

        $time = time();

        $url = 'https://phpunit.project.dmknet.de/tests/httprequest.php?method=POST';
        $config = [
            'sslcainfo' => DMK\Mklib\Utility\Tests::getFixturePath('project.dmknet.de.crt'),
        ];

        $request = new tx_mklib_util_HttpRequest($url, $config);

        $request->addParameter('httprequest', ['time' => $time, 'return' => 'time']);
        $request->setMethod($request::METHOD_POST);

        $response = $request->request();

        self::assertEquals(200, $response->getStatus());
        self::assertEquals('OK', $response->getMessage());
        self::assertEquals($time, $response->getBody());
    }
}
