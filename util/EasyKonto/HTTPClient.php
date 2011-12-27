<?php
/**
 * PHP SDK for the easyKonto web service.
 *
 * @category EasyKonto
 * @package EasyKonto
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 * @since 2.0
 */

/**
 * This class implements the easyKonto web service API.
 *
 * @package EasyKonto
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 */
final class EasyKonto_HTTPClient {

    /**
     * The version of this library.
     *
     * @var string
     */
    const VERSION = '2.0.1';

    /**
     * The number of retry attempts when a communication error occurs.
     *
     * @var integer
     */
    private $retryAttempts;

    /**
     * The configured interval in seconds between retries when a
     * communication errors occurs.
     *
     * @var integer
     */
    private $retryInterval;

    /**
     * The URLs (easyKonto web service endpoints) to be used.
     *
     * @var array
     */
    private $serviceEndpoints;

    /**
     * The base URL (containing placeholders for substitution).
     *
     * @var string
     */
    private $baseUrl;

    /**
     * The cURL resource handle.
     *
     * @var resource
     */
    private $curl;

    /**
     * Initialize this http client with the specified configuration parameters.
     *
     * @param EasyKonto_ConnectionConfiguration $connectionConfiguration the configuration object
     * @param string $type the web service type to use
     * @param integer $version the web service version to use
     * @throws InvalidArgumentException when the specified configuration is invalid
     */
    public function __construct(EasyKonto_ConnectionConfiguration $connectionConfiguration, $type, $version) {
        if (is_null($connectionConfiguration)) {
            throw new InvalidArgumentException('ConnectionConfiguration must be specified');
        }
        if (is_null($connectionConfiguration->getConnectionType())) {
            throw new InvalidArgumentException('ConnectionType must be specified');
        }
        if (is_null($connectionConfiguration->getUsername())) {
            throw new InvalidArgumentException('Username must be specified');
        }
        if (is_null($connectionConfiguration->getPassword())) {
            throw new InvalidArgumentException('Password must be specified');
        }

        $this->baseUrl = "%s/satellite/$type/v$version/%s";
        $this->retryAttempts = $connectionConfiguration->getRetryAttempts();
        $this->retryInterval = $connectionConfiguration->getRetryInterval();
        $this->serviceEndpoints = $connectionConfiguration->getServiceEndpoints();

        $curlOptions = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERAGENT => 'easyKonto-PHP/' . self::VERSION,
            CURLOPT_CONNECTTIMEOUT => $connectionConfiguration->getConnectTimeout(),
            CURLOPT_TIMEOUT => $connectionConfiguration->getReadTimeout(),
            CURLOPT_USERPWD => $connectionConfiguration->getUsername() . ':' . $connectionConfiguration->getPassword()
        );

        if (!is_null($connectionConfiguration->getProxyHost())) {
            if (is_null($connectionConfiguration->getProxyPort())) {
                throw new InvalidArgumentException('Proxy host configured without configuring proxy port');
            }
            $curlOptions[CURLOPT_PROXY] = $connectionConfiguration->getProxyHost();
            $curlOptions[CURLOPT_PROXYPORT] = $connectionConfiguration->getProxyPort();

            if (!is_null($connectionConfiguration->getProxyUsername())) {
                if (is_null($connectionConfiguration->getProxyPassword())) {
                    throw new InvalidArgumentException('Proxy username configured without configuring proxy password');
                }
                $curlOptions[CURLOPT_PROXYUSERPWD] = $connectionConfiguration->getProxyUsername() . ':' . $connectionConfiguration->getProxyPassword();
            }
        }

        $this->curl = curl_init();
        curl_setopt_array($this->curl, $curlOptions);
    }

    /**
     * Closes open cURL handle.
     */
    public function __destruct() {
        if (!is_null($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
     * Requests the specified web service method with the given request parameters.
     *
     * @param string $method web service method name
     * @param array $parameters web service request parameters
     * @return string web service response
     * @throws EasyKonto_Exception when an unrecoverable error occurs
     */
    public function request($method, array $parameters) {
        $post_data = http_build_query($parameters);

        $err_str = null;

        for ($retry = 0;;) {
            foreach ($this->serviceEndpoints as $serviceEndpoint) {
                $response = $this->requestUrl($serviceEndpoint, $method, $post_data);
                $status = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

                if (curl_errno($this->curl)) {
                    $err_str = curl_error($this->curl);
                } else {
                    switch ($status) {
                        case 200: // OK
                            return $response;
                        case 401: // UNAUTHORIZED
                        case 403: // FORBIDDEN
                            throw new EasyKonto_Exception("HTTP error: $status");
                        default:
                            $err_str = "HTTP error: $status";
                    }
                }
            }

            if (++$retry > $this->retryAttempts) {
                break;
            }

            sleep($this->retryInterval);
        }
        throw new EasyKonto_Exception("Error connecting webservice: $err_str");
    }

    /**
     * Requests the specified web service endpoint method with the given post data.
     *
     * @param string $serviceEndpoint the service endpoint to use
     * @param string $method the web service method to use
     * @param string $post_data the request parameters in serialized form
     * @return string plain web service response
     */
    private function requestUrl($serviceEndpoint, $method, $post_data) {
        $curlOptions = array(
            CURLOPT_URL => sprintf($this->baseUrl, $serviceEndpoint, $method),
            CURLOPT_HTTPHEADER => array('Content-Length: ' . strlen($post_data)),
            CURLOPT_POSTFIELDS => $post_data
        );

        curl_setopt_array($this->curl, $curlOptions);

        return curl_exec($this->curl);
    }

}
