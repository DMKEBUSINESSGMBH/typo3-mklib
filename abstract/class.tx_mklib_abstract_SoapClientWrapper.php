<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 das MedienKombinat GmbH (kontakt@das-medienkombinat.de)
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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_Logger');

/**
 * Soap Client Basis. Im Prinzip nur ein Wrapper für SoapClient.
 *
 * @author Hannes Bochmann
 */
abstract class tx_mklib_abstract_SoapClientWrapper {

	/**
	 * @var string
	 */
	private $url;
	
	/**
	 * @var SoapClient
	 */
	private $soapclient;
	
	/**
	 * @param string $method
	 * @param array $args
	 * @return array
	 * @throws RuntimeException
	 */
	public function callSoapMethod($method, array $args = array()) {
		try {
			$methodResult = call_user_func_array(
				array($this->getSoapClient(), $method),
				array($args)
			);
		} catch (Exception $exception) {
			$this->handleException($exception, $args);
		}
		
		return $methodResult;
	}
	
	/**
	 * @return SoapClient
	 */
	abstract protected function getSoapClient();
	
	/**
	 * @param Exception $soapFault
	 * @param array $args
	 * @return void
	 * @throws RuntimeException
	 */
	protected function handleException(Exception $exception, array $args = array()) {
		$this->logException($exception,$args);
		$this->throwRuntimeException($exception);
	}
	
	/**
	 * @param Exception $exception
	 * @param array $args
	 * @return void
	 */
	private function logException(Exception $exception, array $args = array()) {
		$soapClient = $this->getSoapClient();
		
		if($soapClient instanceof SoapClient){
			tx_rnbase_util_Logger::fatal(
				'Access to Soap Interface failed: ' . $exception->getMessage(),
				'mkjjk',
				array(
					'Fehler',
					'functions'	=>	$soapClient->__getFunctions(),
					'response'	=>	$soapClient->__getLastResponse(),
					'request' 	=> 	$soapClient->__getLastRequest(),
					'args'		=>	$args
				)
			);
		}else{
			tx_rnbase_util_Logger::fatal('Soap Client was not instanciated!', 'mkjjk');
		}
	}
	
	/**
	 * @param Exception $exception
	 * @throws RuntimeException
	 * @return void
	 */
	protected function throwRuntimeException(Exception $exception) {
		if($exception instanceof SoapFault)
			$errorCode = $exception->faultcode;
		else
			$errorCode = $exception->getCode();
		
		throw new RuntimeException(
			$exception->getMessage(),
			$errorCode,
			$exception
		);
	}
	
	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}
	
	/**
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

}