<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_util
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 *
 * Copyright notice
 *
 * (c) 2013 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

require_once tx_rnbase_util_Extensions::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_util_httprequest_adapter_Interface');

/**
 * HttpRequest
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_httprequest_adapter_Curl
	implements tx_mklib_util_httprequest_adapter_Interface {


	/**
	 * Parameters array
	 *
	 * @var array
	 */
	protected $config = array();


	/**
	 * What host are we connected to?
	 *
	 * @var string
	 */
	protected $host = NULL;

	/**
	 * What port are we connected to?
	 *
	 * @var int
	 */
	protected $port = NULL;

	/**
	 * The curl session handle
	 *
	 * @var resource|null
	 */
	protected $curl = NULL;

	/**
	 * Response gotten from server
	 *
	 * @var string
	 */
	protected $response = NULL;

	/**
	 * Adapter constructor
	 *
	 * Config is set using setConfig()
	 *
	 * @return void
	 */
	public function __construct() {
		if (!extension_loaded('curl')) {
			throw new Exception('cURL extension has to be loaded to use this adapter.');
		}
	}

	/**
	 * Set the configuration array for the adapter
	 *
	 * @param array $config
	 */
	public function setConfig(array $config = array()) {

		if(isset($config['proxy_user']) && isset($config['proxy_pass'])) {
			$this->setCurlOption(CURLOPT_PROXYUSERPWD, $config['proxy_user'].':'.$config['proxy_pass']);
			unset($config['proxy_user'], $config['proxy_pass']);
		}

		foreach ($config as $k => $v) {
			$option = strtolower($k);
			switch($option) {
				case 'proxy_host':
					$this->setCurlOption(CURLOPT_PROXY, $v);
					break;
				case 'proxy_port':
					$this->setCurlOption(CURLOPT_PROXYPORT, $v);
					break;
				default:
					$this->config[$option] = $v;
					break;
			}
		}
	}

	/**
	 * Direct setter for cURL adapter related options.
	 *
	 * @param  string|int $option
	 * @param  mixed $value
	 * @return Zend_Http_Adapter_Curl
	 */
	protected function setCurlOption($option, $value) {
		if (!isset($this->config['curloptions'])) {
			$this->config['curloptions'] = array();
		}
		$this->config['curloptions'][$option] = $value;
		return $this;
	}


	/**
	 * Initialize curl
	 *
	 * @param  string  $host
	 * @param  int	 $port
	 * @param  boolean $secure
	 * @return void
	 * @throws Zend_Http_Client_Adapter_Exception if unable to connect
	 */
	public function connect($host, $port = 80, $secure = false)
	{
		// If we're already connected, disconnect first
		if ($this->curl) {
			$this->close();
		}

		// If we are connected to a different server or port, disconnect first
		if ($this->curl
			&& (
				$this->host != $host
				|| $this->port != $port
			)
		) {
			$this->close();
		}

		// Do the actual connection
		$this->curl = curl_init();
		if ($port != 80) {
			curl_setopt($this->curl, CURLOPT_PORT, (int) $port);
		}

		// Set timeout
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->config['timeout']);

		// Set Max redirects
		if (isset($this->config['maxredirects'])) {
			curl_setopt($this->curl, CURLOPT_MAXREDIRS, (int) $this->config['maxredirects']);
		}

		if (!$this->curl) {
			$this->close();
			throw new Exception('Unable to Connect to ' .  $host . ':' . $port);
		}

		if ($secure !== FALSE) {
			// we use an private key!
			if (isset($this->config['sslcert'])) {
				curl_setopt($this->curl, CURLOPT_SSLCERT, $this->config['sslcert']);
			}
			if (isset($this->config['sslpassphrase'])) {
				curl_setopt($this->curl, CURLOPT_SSLCERTPASSWD, $this->config['sslpassphrase']);
			}

			// we use an ssl certificate to auth
			if (isset($this->config['sslcainfo'])) {
				curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, true);
				curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($this->curl, CURLOPT_CAINFO, $this->config['sslcainfo']);
			}
		}

		// Update connected_to
		$this->host = $host;
		$this->port = $port;
	}



	/**
	 * Send request to the remote server
	 *
	 * @param  string $method
	 * @param  string $uri
	 * @param  float $http_ver
	 * @param  array $headers
	 * @param  string $body
	 * @return string $request
	 */
	public function write($method, $uri, $headers = array(), $body = '')
	{
		// Make sure we're properly connected
		if (!$this->curl) {
			throw new Exception('Trying to write but we are not connected');
		}

		$uriParts = parse_url($uri);

		if ($this->host != $uriParts['host'] || $this->port != $uriParts['port']) {
			throw new Exception('Trying to write but we are connected to the wrong host');
		}

		// set URL
		curl_setopt($this->curl, CURLOPT_URL, $uri);

		// ensure correct curl call
		switch ($method) {
			case tx_mklib_util_HttpRequest::METHOD_GET:
				$curlMethod = CURLOPT_HTTPGET;
				break;

			case tx_mklib_util_HttpRequest::METHOD_POST:
				$curlMethod = CURLOPT_POST;
				break;

			default:
				throw new Exception('Method currently not supported');
		}


		// mark as HTTP request and set HTTP method
		curl_setopt($this->curl, CURL_HTTP_VERSION_1_1, true);
		curl_setopt($this->curl, $curlMethod, TRUE);

		// ensure headers are also returned
		curl_setopt($this->curl, CURLOPT_HEADER, true);

		// ensure actual response is returned
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

		// set additional headers
		$headers['Accept'] = '';
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

		/**
		 * Make sure POSTFIELDS is set after $curlMethod is set:
		 * @link http://de2.php.net/manual/en/function.curl-setopt.php#81161
		 */
		if ($method == tx_mklib_util_HttpRequest::METHOD_POST) {
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
		}

		// set additional curl options
		if (isset($this->config['curloptions'])) {
			foreach ((array) $this->config['curloptions'] as $k => $v) {
				if (curl_setopt($this->curl, $k, $v) == false) {
					throw new Exception('Unknown or erroreous cURL option "'.$k.'" set');
				}
			}
		}

		// send the request
		$response = curl_exec($this->curl);

		$this->response = $response;

		$request  = curl_getinfo($this->curl, CURLINFO_HEADER_OUT);
		$request .= $body;

		if (empty($this->response)) {
			throw new Exception('Error in cURL request: ' . curl_error($this->curl));
		}

		// cURL automatically decodes chunked-messages, this means we have to disallow the response to do it again
		if (stripos($this->response, "Transfer-Encoding: chunked\r\n")) {
			$this->response = str_ireplace("Transfer-Encoding: chunked\r\n", '', $this->response);
		}

		// cURL automatically handles Proxy rewrites, remove the "HTTP/1.0 200 Connection established" string:
		if (stripos($this->response, "HTTP/1.0 200 Connection established\r\n\r\n") !== false) {
			$this->response = str_ireplace("HTTP/1.0 200 Connection established\r\n\r\n", '', $this->response);
		}

		return $request;
	}

	/**
	 * Return read response from server
	 *
	 * @return string
	 */
	public function read() {
		return $this->response;
	}

	/**
	 * Close the connection to the server
	 *
	 */
	public function close() {
		if(is_resource($this->curl)) {
			curl_close($this->curl);
		}
		$this->curl = $this->host = $this->port = NULL;
		$this->response = NULL;
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_httprequest_adapter_Interface.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_httprequest_adapter_Interface.php']);
}
