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
 * @author Hannes Bochmann
 */
class tx_mklib_tests_soap_ClientWrapperTest extends Sys25\RnBase\Testing\BaseTestCase
{
    public const SOAP_TEST_METHOD = 'mySoapTestMethod';

    public const SOAP_TEST_METHOD_RETURN_VALUE = 'myTestSoapMethodResult';

    protected function setUp(): void
    {
        if (!extension_loaded('soap')) {
            $this->markTestSkipped('Skipped because soap is not installed.');
        }
    }

    /**
     * @group unit
     */
    public function testCallSoapMethodWithValidMethodReturnsExpectedResult(): void
    {
        $soapClientWrapper = $this->getSoapClientWrapper();
        $soapMethodReturnValue = $soapClientWrapper->callSoapMethod(self::SOAP_TEST_METHOD);

        self::assertEquals(self::SOAP_TEST_METHOD_RETURN_VALUE, $soapMethodReturnValue);
    }

    /**
     * @group unit
     */
    public function testCallSoapMethodWithValidMethodAndParamsAsArrayReturnsExpectedResult(): void
    {
        $expectedSoapMethodParams = ['someParam' => 'usedInSoapMethod'];
        $soapClientWrapper = $this->getSoapClientWrapper($expectedSoapMethodParams);
        $soapMethodReturnValue = $soapClientWrapper->callSoapMethod(
            self::SOAP_TEST_METHOD,
            [$expectedSoapMethodParams]
        );

        self::assertEquals(self::SOAP_TEST_METHOD_RETURN_VALUE, $soapMethodReturnValue);
    }

    /**
     * @group unit
     */
    public function testCallSoapMethodWithValidMethodAndParamsAsStringReturnsExpectedResult(): void
    {
        $expectedSoapMethodParams = 'soapMethodParam';
        $soapClientWrapper = $this->getSoapClientWrapper($expectedSoapMethodParams);
        $soapMethodReturnValue = $soapClientWrapper->callSoapMethod(
            self::SOAP_TEST_METHOD,
            [$expectedSoapMethodParams]
        );

        self::assertEquals(self::SOAP_TEST_METHOD_RETURN_VALUE, $soapMethodReturnValue);
    }

    /**
     * @group unit
     */
    public function testCallSoapMethodWithInvalidMethodThrowsCorrectException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There was a Soap Exception');
        $this->expectExceptionCode(987654321);

        $expectedSoapMethodParams = ['someParam' => 'usedInSoapMethod'];
        $soapException = new Exception('There was a Soap Exception', 987654321);
        $soapClientWrapper = $this->getSoapClientWrapper(
            $expectedSoapMethodParams,
            $soapException,
            self::exactly(2)
        );
        $soapClientWrapper->callSoapMethod(
            self::SOAP_TEST_METHOD,
            [$expectedSoapMethodParams]
        );
    }

    /**
     * @group unit
     */
    public function testCallSoapMethodHandlesSoapFaultCorrect(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There was a Soap Fault');
        $this->expectExceptionCode(987654321);

        $expectedSoapMethodParams = ['someParam' => 'usedInSoapMethod'];
        $soapException = new SoapFault('987654321', 'There was a Soap Fault');
        $soapClientWrapper = $this->getSoapClientWrapper(
            $expectedSoapMethodParams,
            $soapException,
            self::exactly(2)
        );
        $soapClientWrapper->callSoapMethod(
            self::SOAP_TEST_METHOD,
            [$expectedSoapMethodParams]
        );
    }

    /**
     * @group unit
     */
    public function testCallSoapMethodHandlesSoapFaultWithStringCodeCorrect(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('There was a Soap Fault');
        $this->expectExceptionCode(version_compare(phpversion(), '8', '>=') ? -1 : 0);

        $expectedSoapMethodParams = ['someParam' => 'usedInSoapMethod'];
        $soapException = new SoapFault('a string code', 'There was a Soap Fault');
        $soapClientWrapper = $this->getSoapClientWrapper(
            $expectedSoapMethodParams,
            $soapException,
            self::exactly(2)
        );
        $soapClientWrapper->callSoapMethod(
            self::SOAP_TEST_METHOD,
            [$expectedSoapMethodParams]
        );
    }

    /**
     * @param array $expectedParams
     *
     * @return tx_mklib_soap_ClientWrapper
     */
    private function getSoapClientWrapper(
        array|string $expectedParams = [],
        $exceptionToThrow = null,
        $getSoapClientInvocationCount = null,
    ) {
        $soapClient = $this->getSoapClientMock($expectedParams, $exceptionToThrow);

        $soapClientWrapper = $this->getMock('tx_mklib_soap_ClientWrapper', ['getSoapClient']);

        if (!$getSoapClientInvocationCount) {
            $getSoapClientInvocationCount = self::once();
        }

        $soapClientWrapper->expects($getSoapClientInvocationCount)
            ->method('getSoapClient')
            ->willReturn($soapClient);

        return $soapClientWrapper;
    }

    /**
     * @param array $expectedParams
     *
     * @return SoapClient
     */
    private function getSoapClientMock(
        string|array $expectedParams = [],
        $exceptionToThrow = null,
    ) {
        $soapClient = $this->getMock(
            'tx_mklib_tests_fixtures_classes_SoapClient',
            [self::SOAP_TEST_METHOD],
            [],
            '',
            false
        );

        if ('' !== $expectedParams && '0' !== $expectedParams && [] !== $expectedParams) {
            if (!is_null($exceptionToThrow)) {
                $soapClient->expects(self::once())
                    ->method(self::SOAP_TEST_METHOD)
                    ->with($expectedParams)
                    ->willThrowException($exceptionToThrow);
            } else {
                $soapClient->expects(self::once())
                    ->method(self::SOAP_TEST_METHOD)
                    ->with($expectedParams)
                    ->willReturn(self::SOAP_TEST_METHOD_RETURN_VALUE);
            }
        } elseif (!is_null($exceptionToThrow)) {
            $soapClient->expects(self::once())
                ->method(self::SOAP_TEST_METHOD)
                ->willThrowException($exceptionToThrow);
        } else {
            $soapClient->expects(self::once())
                ->method(self::SOAP_TEST_METHOD)
                ->willReturn(self::SOAP_TEST_METHOD_RETURN_VALUE);
        }

        return $soapClient;
    }
}
