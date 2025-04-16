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
 * HttpRequest.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_HttpRequest
{
    /**
     * HTTP request methods.
     */
    public const METHOD_GET = 'GET';

    public const METHOD_POST = 'POST';

    /**
     * The adapter used to perform the actual connection to the server.
     *
     * @var tx_mklib_util_httprequest_adapter_Interface
     */
    protected $adapter;

    /**
     * HTTP request method.
     *
     * @var string
     */
    protected $method = self::METHOD_GET;

    /**
     * Associative array of request headers.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Associative array of request headers.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * HTTP Authentication settings.
     *
     * Expected to be an associative array with this structure:
     * $this->auth = array('user' => 'username', 'password' => 'password', 'type' => 'basic')
     *
     * If null, no authentication will be used.
     *
     * @var array|null
     */
    protected $auth;

    /**
     * Configuration array, set using the constructor or using ::setConfig().
     *
     * @var array
     */
    protected $config = [
        'useragent' => 'tx_mklib_util_HttpRequest',
        'timeout' => 10,
        'adapter' => 'tx_mklib_util_httprequest_adapter_Curl',
        'keepalive' => false,
        'strict' => true,
        'rfc3986_strict' => false,
        'sslcert' => null,
        'sslpassphrase' => null,
    ];

    /**
     * Constructor method. Will create a new HTTP client. Accepts the target
     * URL and optionally configuration array.
     *
     * @param string $uri
     * @param array  $config configuration key-value pairs
     */
    public function __construct(/**
     * Request URI.
     */
        protected $uri, $config = null)
    {
        if (is_array($config)) {
            $this->setConfig($config);
        }
    }

    /**
     * Set configuration parameters for this HTTP client.
     */
    public function setConfig(array $config = []): static
    {
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
     * Load the connection adapter.
     *
     * While this method is not called more than one for a client, it is
     * seperated from ->request() to preserve logic and readability
     *
     * @param string $adapter
     */
    public function setAdapter($adapter): static
    {
        if (is_string($adapter)) {
            $adapter = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($adapter);
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
     * Set HTTP authentication parameters.
     *
     * @param string|false $user     User name or false disable authentication
     * @param string       $password Password
     */
    public function setAuth($user, $password = ''): static
    {
        // If we got false or null, disable authentication
        if (false === $user || null === $user) {
            $this->auth = null;

        // Else, set up authentication
        } else {
            $this->auth = [
                'user' => (string) $user,
                'password' => (string) $password,
                'type' => 'basic',
            ];
        }

        return $this;
    }

    /**
     * Set one or more request headers.
     */
    public function setHeader(string $name, $value = null): static
    {
        // Make sure the name is valid if we are in strict mode
        if ($this->config['strict'] && in_array(preg_match('/^[a-zA-Z0-9-]+$/', $name), [0, false], true)) {
            throw new Exception($name.' is not a valid HTTP header name');
        }

        $normalized_name = strtolower($name);

        // If $value is null or false, unset the header
        if (null === $value || false === $value) {
            unset($this->headers[$normalized_name]);

        // set the header
        } else {
            if (is_string($value)) {
                $value = trim($value);
            }

            $this->headers[$normalized_name] = [$name, $value];
        }

        return $this;
    }

    /**
     * Sets a Parameter for the Request.
     *
     * @param string      $name
     * @param string|null $value
     */
    public function addParameter($name, $value = null): void
    {
        if (null === $value) {
            if (isset($this->parameters[$name])) {
                unset($this->parameters[$name]);
            }
        } else {
            $this->parameters[$name] = $value;
        }
    }

    /**
     * Gets Parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set the next request's method.
     *
     * Validated the passed method and sets it. If we have files set for
     * POST requests, and the new method is not POST, the files are silently
     * dropped.
     *
     * @param string $method
     *
     * @throws tx_mklib_util_HttpRequest
     */
    public function setMethod($method = self::METHOD_GET): static
    {
        $method = strtoupper($method);

        if (!defined('self::METHOD_'.$method)) {
            throw new Exception($method.' is not a valid HTTP request method.');
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Send the HTTP request and return an HTTP response object.
     *
     * @param string $method
     */
    public function request($method = null): tx_mklib_util_httprequest_Response
    {
        if (empty($this->uri)) {
            throw new Exception('No valid URI has been passed to the client');
        }

        if ($method) {
            $this->setMethod($method);
        }

        $response = null;

        // Make sure the adapter is loaded
        if (!$this->adapter instanceof tx_mklib_util_httprequest_adapter_Interface) {
            $this->setAdapter($this->config['adapter']);
        }

        // Clone the URI and add the additional GET parameters to it
        $uri = parse_url($this->uri);

        if ([] !== $this->parameters && self::METHOD_GET == $this->method) {
            $query = http_build_query($this->parameters, null, '&');
            if ($this->config['rfc3986_strict']) {
                $query = str_replace('+', '%20', $query);
            }

            $uri['query'] = empty($uri['query']) ? '' : $uri['query'].'&';
            $uri['query'] .= $query;
        }

        $body = $this->prepareBody();
        $headers = $this->prepareHeaders();

        // Open the connection, send the request and read the response
        $this->adapter->connect($uri['host'], $uri['port'], 'https' === $uri['scheme']);

        $this->adapter->write($this->method, tx_mklib_util_File::parseUrlFromParts($uri), $headers, $body);

        $response = $this->adapter->read();
        if (!$response) {
            throw new Exception('Unable to read response, or response is empty');
        }

        // @TODO: redirect prüfen.
        // $response->isRedirect()

        return tx_mklib_util_httprequest_Response::fromString($response);
    }

    /**
     * Prepare the request headers.
     */
    protected function prepareHeaders(): array
    {
        $headers = [];

        // Set the connection header
        if (!isset($this->headers['connection']) && !$this->config['keepalive']) {
            $headers[] = 'Connection: close';
        }

        if (!isset($this->headers['accept-encoding'])) {
            $headers[] = function_exists('gzinflate') ? 'Accept-encoding: gzip, deflate' : 'Accept-encoding: identity';
        }

        // Set the Content-Type header
        if (self::METHOD_POST == $this->method
            && !isset($this->headers['content-type'])) {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }

        // Set the user agent header
        if (!isset($this->headers['user-agent']) && isset($this->config['useragent'])) {
            $headers[] = 'User-Agent: '.$this->config['useragent'];
        }

        // Set HTTP authentication if needed
        if (is_array($this->auth)) {
            $headers[] = 'Authorization: Basic '
                .base64_encode($this->auth['user']
                .':'.$this->auth['password']);
        }

        // Add all other user defined headers
        foreach ($this->headers as $header) {
            [$name, $value] = $header;
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $headers[] = $name.': '.$value;
        }

        return $headers;
    }

    /**
     * Prepare the request body (for POST and PUT requests).
     */
    protected function prepareBody(): string
    {
        $body = '';

        // If we have POST parameters, add them to the body
        if (count($this->parameters) > 0 && self::METHOD_POST == $this->method) {
            // Encode body as application/x-www-form-urlencoded
            $this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
            $body = http_build_query($this->parameters, '', '&');
        }

        // Set the Content-Length if we have a body or if request is POST
        if ($body || self::METHOD_POST == $this->method) {
            $this->setHeader('Content-Length', strlen($body));
        }

        return $body;
    }
}
