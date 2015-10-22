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

require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');

/**
 * HttpRequest
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_HttpRequest {

	/**
	 * HTTP request methods
	 */
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';

	/**
	 * The adapter used to perform the actual connection to the server
	 *
	 * @var tx_mklib_util_httprequest_adapter_Interface
	 */
	protected $adapter = NULL;

	/**
	 * Request URI
	 *
	 * @var string
	 */
	protected $uri = '';

	/**
	 * HTTP request method
	 *
	 * @var string
	 */
	protected $method = self::METHOD_GET;

	/**
	 * Associative array of request headers
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * Associative array of request headers
	 *
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * HTTP Authentication settings
	 *
	 * Expected to be an associative array with this structure:
	 * $this->auth = array('user' => 'username', 'password' => 'password', 'type' => 'basic')
	 *
	 * If null, no authentication will be used.
	 *
	 * @var array|null
	 */
	protected $auth = NULL;

	/**
	 * Configuration array, set using the constructor or using ::setConfig()
	 *
	 * @var array
	 */
	protected $config = array(
		'useragent' => 'tx_mklib_util_HttpRequest',
		'timeout' => 10,
		'adapter' => 'tx_mklib_util_httprequest_adapter_Curl',
		'keepalive' => FALSE,
		'strict' => TRUE,
		'rfc3986_strict' => FALSE,
		'sslcert' => NULL,
		'sslpassphrase' => NULL,
	);

	/**
	 * Constructor method. Will create a new HTTP client. Accepts the target
	 * URL and optionally configuration array.
	 *
	 * @param string $uri
	 * @param array $config Configuration key-value pairs.
	 */
	public function __construct($uri, $config = NULL) {
		$this->uri = $uri;
		if (is_array($config)) {
			$this->setConfig($config);
		}
	}


	/**
	 * Set configuration parameters for this HTTP client
	 *
	 * @param array $config
	 * @return tx_mklib_util_HttpRequest
	 */
	public function setConfig(array $config = array()) {

		foreach ($config as $k => $v) {
			$this->config[strtolower($k)] = $v;
		}

		// Pass configuration options to the adapter if it exists
		if ($this->adapter instanceof tx_mklib_util_httprequest_adapter_Interface) {
			$this->adapter->setConfig($config);
		}

		return $this;
	}


	/**
	 * Load the connection adapter
	 *
	 * While this method is not called more than one for a client, it is
	 * seperated from ->request() to preserve logic and readability
	 *
	 * @param string $adapter
	 * @return tx_mklib_util_HttpRequest
	 */
	public function setAdapter($adapter) {
		if (is_string($adapter)) {
			$adapter = tx_rnbase::makeInstance($adapter);
		}

		if (!$adapter instanceof tx_mklib_util_httprequest_adapter_Interface) {
			throw new Exception('Passed adapter is not a HTTP connection adapter');
		}

		$this->adapter = $adapter;
		$config = $this->config;
		unset($config['adapter']);
		$this->adapter->setConfig($config);

		return $this;
	}

	/**
	 * Set HTTP authentication parameters
	 *
	 * @param string|false $user User name or false disable authentication
	 * @param string $password Password
	 * @return tx_mklib_util_HttpRequest
	 */
	public function setAuth($user, $password = '') {
		// If we got false or null, disable authentication
		if ($user === false || $user === NULL) {
			$this->auth = NULL;

			// Else, set up authentication
		} else {
			$this->auth = array(
				'user' => (string) $user,
				'password' => (string) $password,
				'type' => 'basic'
			);
		}

		return $this;
	}

	/**
	 * Set one or more request headers
	 *
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return tx_mklib_util_HttpRequest
	 */
	public function setHeader($name, $value = NULL) {

		// Make sure the name is valid if we are in strict mode
		if ($this->config['strict'] && (! preg_match('/^[a-zA-Z0-9-]+$/', $name))) {
			throw new Exception("{$name} is not a valid HTTP header name");
		}

		$normalized_name = strtolower($name);

		// If $value is null or false, unset the header
		if ($value === NULL || $value === false) {
			unset($this->headers[$normalized_name]);

		// set the header
		} else {
			if (is_string($value)) {
				$value = trim($value);
			}
			$this->headers[$normalized_name] = array($name, $value);
		}

		return $this;
	}

	/**
	 * Sets a Parameter for the Request
	 *
	 * @param string $name
	 * @param string|null $value
	 * @return null
	 */
	public function addParameter($name, $value = NULL) {
		if ($value === NULL) {
			if (isset($this->parameters[$name])) unset($this->parameters[$name]);
		} else {
			$this->parameters[$name] = $value;
		}
	}

	/**
	 * Gets Parameters
	 *
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * Set the next request's method
	 *
	 * Validated the passed method and sets it. If we have files set for
	 * POST requests, and the new method is not POST, the files are silently
	 * dropped.
	 *
	 * @param string $method
	 * @throws tx_mklib_util_HttpRequest
	 */
	public function setMethod($method = self::METHOD_GET) {
		$method = strtoupper($method);

		if(!defined('self::METHOD_' . $method)) {
			throw new Exception($method . ' is not a valid HTTP request method.');
		}

		$this->method = $method;

		return $this;
	}

	/**
	 * Send the HTTP request and return an HTTP response object
	 *
	 * @param string $method
	 * @return tx_mklib_util_httprequest_Response
	 */
	public function request($method = NULL) {
		if (empty($this->uri)) {
			throw new Exception('No valid URI has been passed to the client');
		}

		if ($method) {
			$this->setMethod($method);
		}

		$response = NULL;

		// Make sure the adapter is loaded
		if (!$this->adapter instanceof tx_mklib_util_httprequest_adapter_Interface) {
			$this->setAdapter($this->config['adapter']);
		}

		// Clone the URI and add the additional GET parameters to it
		$uri = parse_url($this->uri);

		if (!empty($this->parameters) && $this->method == self::METHOD_GET) {
			$query = http_build_query($this->parameters, NULL, '&');
			if ($this->config['rfc3986_strict']) {
				$query = str_replace('+', '%20', $query);
			}
			$uri['query']  = empty($uri['query']) ? '' : $uri['query'] . '&';
			$uri['query'] .= $query;
		}

		$body = $this->prepareBody();
		$headers = $this->prepareHeaders();

		// Open the connection, send the request and read the response
		$this->adapter->connect($uri['host'], $uri['port'],	($uri['scheme'] == 'https' ? true : false));

		tx_rnbase::load('tx_mklib_util_File');
		$this->adapter->write($this->method, tx_mklib_util_File::parseUrlFromParts($uri), $headers, $body);

		$response = $this->adapter->read();
		if (!$response) {
			throw new Exception('Unable to read response, or response is empty');
		}

		tx_rnbase::load('tx_mklib_util_httprequest_Response');
		$response = tx_mklib_util_httprequest_Response::fromString($response);

		// @TODO: redirect prüfen.
		//$response->isRedirect()

		return $response;
	}

	/**
	 * Prepare the request headers
	 *
	 * @return array
	 */
	protected function prepareHeaders() {
		$headers = array();

		// Set the connection header
		if (!isset($this->headers['connection'])) {
			if (! $this->config['keepalive']) {
				$headers[] = "Connection: close";
			}
		}

		if (!isset($this->headers['accept-encoding'])) {
			if (function_exists('gzinflate')) {
				$headers[] = 'Accept-encoding: gzip, deflate';
			} else {
				$headers[] = 'Accept-encoding: identity';
			}
		}

		// Set the Content-Type header
		if ($this->method == self::METHOD_POST
			&& !isset($this->headers['content-type'])) {
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		}

		// Set the user agent header
		if (!isset($this->headers['user-agent']) && isset($this->config['useragent'])) {
			$headers[] = 'User-Agent: ' . $this->config['useragent'];
		}

		// Set HTTP authentication if needed
		if (is_array($this->auth)) {
			$headers[] = 'Authorization: Basic '
				. base64_encode($this->auth['user']
				. ':' . $this->auth['password']);
		}

		// Add all other user defined headers
		foreach ($this->headers as $header) {
			list($name, $value) = $header;
			if (is_array($value)) {
				$value = implode(', ', $value);
			}

			$headers[] = $name . ': ' . $value;
		}

		return $headers;
	}

	/**
	 * Prepare the request body (for POST and PUT requests)
	 *
	 * @return string
	 */
	protected function prepareBody() {
		$body = '';

		// If we have POST parameters, add them to the body
		if (count($this->parameters) > 0 && $this->method == self::METHOD_POST) {
			// Encode body as application/x-www-form-urlencoded
			$this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
			$body = http_build_query($this->parameters, '', '&');
		}

		// Set the Content-Length if we have a body or if request is POST
		if ($body || $this->method == self::METHOD_POST) {
			$this->setHeader('Content-Length', strlen($body));
		}

		return $body;
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_HttpRequest.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_HttpRequest.php']);
}
