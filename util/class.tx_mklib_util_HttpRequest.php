<?php

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
    protected $adapter = null;

    /**
     * Request URI.
     *
     * @var string
     */
    protected $uri = '';

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
    protected $auth = null;

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
    public function __construct($uri, $config = null)
    {
        $this->uri = $uri;
        if (is_array($config)) {
            $this->setConfig($config);
        }
    }

    /**
     * Set configuration parameters for this HTTP client.
     *
     * @param array $config
     *
     * @return tx_mklib_util_HttpRequest
     */
    public function setConfig(array $config = [])
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
     *
     * @return tx_mklib_util_HttpRequest
     */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            $adapter = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($adapter);
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
     *
     * @return tx_mklib_util_HttpRequest
     */
    public function setAuth($user, $password = '')
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
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return tx_mklib_util_HttpRequest
     */
    public function setHeader($name, $value = null)
    {
        // Make sure the name is valid if we are in strict mode
        if ($this->config['strict'] && (!preg_match('/^[a-zA-Z0-9-]+$/', $name))) {
            throw new Exception("{$name} is not a valid HTTP header name");
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
    public function addParameter($name, $value = null)
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
    public function setMethod($method = self::METHOD_GET)
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
     *
     * @return tx_mklib_util_httprequest_Response
     */
    public function request($method = null)
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

        if (!empty($this->parameters) && self::METHOD_GET == $this->method) {
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
        $this->adapter->connect($uri['host'], $uri['port'], ('https' == $uri['scheme'] ? true : false));

        $this->adapter->write($this->method, tx_mklib_util_File::parseUrlFromParts($uri), $headers, $body);

        $response = $this->adapter->read();
        if (!$response) {
            throw new Exception('Unable to read response, or response is empty');
        }

        $response = tx_mklib_util_httprequest_Response::fromString($response);

        // @TODO: redirect prüfen.
        // $response->isRedirect()

        return $response;
    }

    /**
     * Prepare the request headers.
     *
     * @return array
     */
    protected function prepareHeaders()
    {
        $headers = [];

        // Set the connection header
        if (!isset($this->headers['connection'])) {
            if (!$this->config['keepalive']) {
                $headers[] = 'Connection: close';
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
            list($name, $value) = $header;
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $headers[] = $name.': '.$value;
        }

        return $headers;
    }

    /**
     * Prepare the request body (for POST and PUT requests).
     *
     * @return string
     */
    protected function prepareBody()
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
