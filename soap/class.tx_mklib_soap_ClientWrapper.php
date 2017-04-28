<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 DMK E-BUSINESS GmbH (dev@dmk-ebusiness.de)
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
 ***************************************************************/


tx_rnbase::load('tx_rnbase_util_Logger');

/**
 * Soap Client Basis. Im Prinzip nur ein Wrapper für SoapClient.
 *
 * @author Hannes Bochmann
 */
class tx_mklib_soap_ClientWrapper
{

    /**
     * @var string
     */
    protected $url;
    
    /**
     * @var SoapClient
     */
    protected $soapclient;
    
    /**
     * @var integer
     */
    protected $soapVersion = SOAP_1_1;
    
    /**
     * @param string $method
     * @param array $args
     * @return array
     * @throws RuntimeException
     */
    public function callSoapMethod($method, array $args = array())
    {
        try {
            $methodResult = call_user_func_array(
                array($this->getSoapClient(), $method),
                $args
            );
        } catch (Exception $exception) {
            $this->handleException($exception, $args);
        }
        
        return $methodResult;
    }
    
    /**
     * @return SoapClient
     */
    protected function getSoapClient()
    {
        if (is_null($this->soapclient)) {
            $this->soapclient = new SoapClient(
                $this->getUrl(),
                array(
                    'location'        => $this->getUrl(),
                    'uri'            => '',
                    'soap_version'    => $this->soapVersion,
                    'trace'            => 1,
                    'exceptions'    => 1,
                    'compression'    => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP
                )
            );
        }
        
        return $this->soapclient;
    }
    
    /**
     * @param Exception $soapFault
     * @param array $args
     * @return void
     * @throws RuntimeException
     */
    protected function handleException(Exception $exception, array $args = array())
    {
        $this->logException($exception, $args);
        $this->throwRuntimeException($exception);
    }
    
    /**
     * @param Exception $exception
     * @param array $args
     * @return void
     */
    protected function logException(Exception $exception, array $args = array())
    {
        $soapClient = $this->getSoapClient();
        
        if ($soapClient instanceof SoapClient) {
            tx_rnbase_util_Logger::fatal(
                'Access to Soap Interface failed: ' . $exception->getMessage(),
                'mklib',
                array(
                    'Fehler',
                    'functions'    =>    $soapClient->__getFunctions(),
                    'response'    =>    $soapClient->__getLastResponse(),
                    'request'    =>    $soapClient->__getLastRequest(),
                    'args'        =>    $args
                )
            );
        } else {
            tx_rnbase_util_Logger::fatal('Soap Client was not instanciated!', 'mklib');
        }
    }
    
    /**
     * @param Exception $exception
     * @throws RuntimeException
     * @return void
     */
    protected function throwRuntimeException(Exception $exception)
    {
        if ($exception instanceof SoapFault) {
            $errorCode = $exception->faultcode;
        } else {
            $errorCode = $exception->getCode();
        }
        // Der ErrorCode eine SOAP-Fault kann auch ein String sein.
        $errorCode = intval($errorCode) == $errorCode ? intval($errorCode) : -1;

        throw new RuntimeException(
            $exception->getMessage(),
            $errorCode,
            $exception
        );
    }
    
    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
