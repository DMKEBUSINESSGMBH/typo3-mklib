<?php

/**
 * HttpRequest.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_httprequest_adapter_Curl implements tx_mklib_util_httprequest_adapter_Interface
{
    /**
     * Parameters array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * What host are we connected to?
     *
     * @var string
     */
    protected $host = null;

    /**
     * What port are we connected to?
     *
     * @var int
     */
    protected $port = null;

    /**
     * The curl session handle.
     *
     * @var resource|null
     */
    protected $curl = null;

    /**
     * Response gotten from server.
     *
     * @var string
     */
    protected $response = null;

    /**
     * Adapter constructor.
     *
     * Config is set using setConfig()
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new Exception('cURL extension has to be loaded to use this adapter.');
        }
    }

    /**
     * Set the configuration array for the adapter.
     *
     * @param array $config
     */
    public function setConfig(array $config = [])
    {
        if (isset($config['proxy_user']) && isset($config['proxy_pass'])) {
            $this->setCurlOption(CURLOPT_PROXYUSERPWD, $config['proxy_user'].':'.$config['proxy_pass']);
            unset($config['proxy_user'], $config['proxy_pass']);
        }

        foreach ($config as $k => $v) {
            $option = strtolower($k);
            switch ($option) {
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
     * @param string|int $option
     * @param mixed      $value
     *
     * @return Zend_Http_Adapter_Curl
     */
    protected function setCurlOption($option, $value)
    {
        if (!isset($this->config['curloptions'])) {
            $this->config['curloptions'] = [];
        }
        $this->config['curloptions'][$option] = $value;

        return $this;
    }

    /**
     * Initialize curl.
     *
     * @param string $host
     * @param int    $port
     * @param bool   $secure
     *
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
        if (80 != $port) {
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
            throw new Exception('Unable to Connect to '.$host.':'.$port);
        }

        if (false !== $secure) {
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
     * Send request to the remote server.
     *
     * @param string $method
     * @param string $uri
     * @param float  $http_ver
     * @param array  $headers
     * @param string $body
     *
     * @return string $request
     */
    public function write($method, $uri, $headers = [], $body = '')
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
        curl_setopt($this->curl, $curlMethod, true);

        // ensure headers are also returned
        curl_setopt($this->curl, CURLOPT_HEADER, true);

        // ensure actual response is returned
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        // set additional headers
        $headers['Accept'] = '';
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

        /*
         * Make sure POSTFIELDS is set after $curlMethod is set:
         * @link http://de2.php.net/manual/en/function.curl-setopt.php#81161
         */
        if (tx_mklib_util_HttpRequest::METHOD_POST == $method) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        }

        // set additional curl options
        if (isset($this->config['curloptions'])) {
            foreach ((array) $this->config['curloptions'] as $k => $v) {
                if (false == curl_setopt($this->curl, $k, $v)) {
                    throw new Exception('Unknown or erroreous cURL option "'.$k.'" set');
                }
            }
        }

        // send the request
        $response = curl_exec($this->curl);

        $this->response = $response;

        $request = curl_getinfo($this->curl, CURLINFO_HEADER_OUT);
        $request .= $body;

        if (empty($this->response)) {
            throw new Exception('Error in cURL request: '.curl_error($this->curl));
        }

        // cURL automatically decodes chunked-messages, this means we have to disallow the response to do it again
        if (stripos($this->response, "Transfer-Encoding: chunked\r\n")) {
            $this->response = str_ireplace("Transfer-Encoding: chunked\r\n", '', $this->response);
        }

        // cURL automatically handles Proxy rewrites, remove the "HTTP/1.0 200 Connection established" string:
        if (false !== stripos($this->response, "HTTP/1.0 200 Connection established\r\n\r\n")) {
            $this->response = str_ireplace("HTTP/1.0 200 Connection established\r\n\r\n", '', $this->response);
        }

        return $request;
    }

    /**
     * Return read response from server.
     *
     * @return string
     */
    public function read()
    {
        return $this->response;
    }

    /**
     * Close the connection to the server.
     */
    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
        $this->curl = $this->host = $this->port = null;
        $this->response = null;
    }
}
