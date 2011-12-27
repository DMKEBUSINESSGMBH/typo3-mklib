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
final class EasyKonto_ConnectionConfiguration {

    /**
     * The type of connection.
     *
     * @var integer
     * @see EasyKonto_ConnectionType
     */
    private $connectionType;

    /**
     * Username for web service authentication.
     *
     * @var string
     */
    private $username;

    /**
     * Password for web service authentication.
     *
     * @var string
     */
    private $password;

    /**
     * The configured connect timeout in seconds.
     *
     * @var integer
     */
    private $connectTimeout = 5;

    /**
     * The configured read timeout in seconds.
     *
     * @var integer
     */
    private $readTimeout = 5;

    /**
     * The number of retry attempts when a communication error occurs.
     *
     * @var integer
     */
    private $retryAttempts = 2;

    /**
     * The configured interval in seconds between retries when a
     * communication errors occurs.
     *
     * @var integer
     */
    private $retryInterval = 3;

    /**
     * The proxy hostname or ip address.
     *
     * @var string
     */
    private $proxyHost;

    /**
     * The proxy port.
     *
     * @var integer
     */
    private $proxyPort;

    /**
     * Username for proxy authentication.
     *
     * @var string
     */
    private $proxyUsername;

    /**
     * Password for proxy authentication.
     *
     * @var string
     */
    private $proxyPassword;

    /**
     * The URLs (easyKonto web service endpoints) to be used.
     *
     * @var array
     */
    private $serviceEndpoints;

    /**
     * Initiliaze this service with the provided connection type (see ConnectionType class
     * for possible values).
     *
     * @param integer $connectionType the type of connection
     * @param string $username the username used for authentication
     * @param string $password the password used for authentication
     * @throws InvalidArgumentException if an invalid ConnectionType was specified
     * @see EasyKonto_ConnectionType
     */
    public function __construct($connectionType = null, $username = null, $password = null) {
        if (!is_null($connectionType)) {
            $this->setConnectionType($connectionType);
        }
        if (!is_null($username)) {
            $this->setUsername($username);
        }
        if (!is_null($password)) {
            $this->setPassword($password);
        }
    }

    /**
     * Gets the type of connection.
     *
     * @return integer the type of connection
     * @see EasyKonto_ConnectionType
     */
    public function getConnectionType() {
        return $this->connectionType;
    }

    /**
     * Sets the type of connection.
     *
     * @param integer $connectionType the type of connection
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     * @throws InvalidArgumentException if an invalid ConnectionType was specified
     * @see EasyKonto_ConnectionType
     */
    public function setConnectionType($connectionType) {
        switch ($connectionType) {
            case EasyKonto_ConnectionType::SINGLE_NODE:
                $this->configureSingleNode();
                break;
            case EasyKonto_ConnectionType::HA_CLUSTER:
                $this->configureHACluster();
                break;
            default:
                throw new InvalidArgumentException("Unknown connectionType specified: $connectionType");
        }
        $this->connectionType = $connectionType;
        return $this;
    }

    /**
     * Gets the username for authentication.
     *
     * @return string the username for authentication
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Sets the username for authentication.
     *
     * @param string $username the username for authentication
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

    /**
     * Gets the password for authentication.
     *
     * @return string the password for authentication
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Sets the password for authentication.
     *
     * @param string $password the password for authentication
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    /**
     * Gets the connect timeout in seconds.
     *
     * @return integer the connect timeout in seconds
     */
    public function getConnectTimeout() {
        return $this->connectTimeout;
    }

    /**
     * Sets the connect timeout in seconds.
     *
     * @param integer $connectTimeout the connect timeout in seconds
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setConnectTimeout($connectTimeout) {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    /**
     * Gets the read timeout in seconds.
     *
     * @return integer the read timeout in seconds
     */
    public function getReadTimeout() {
        return $this->readTimeout;
    }

    /**
     * Sets the read timeout in seconds.
     *
     * @param integer $readTimeout the read timeout in seconds
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setReadTimeout($readTimeout) {
        $this->readTimeout = $readTimeout;
        return $this;
    }

    /**
     * Gets the number of retry attempts when a communication error occurs.
     *
     * @return integer the number of retry attempts
     */
    public function getRetryAttempts() {
        return $this->retryAttempts;
    }

    /**
     * Sets the number of retry attempts when a communication error occurs.
     *
     * @param integer $retryAttempts the number of retry attempts
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setRetryAttempts($retryAttempts) {
        $this->retryAttempts = $retryAttempts;
        return $this;
    }

    /**
     * Gets the interval in seconds between retries when a communication
     * errors occurs.
     *
     * @return integer the retry interval in seconds
     */
    public function getRetryInterval() {
        return $this->retryInterval;
    }

    /**
     * Sets the interval in seconds between retries when a communication
     * errors occurs.
     *
     * @param integer $retryInterval the retry interval in seconds
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setRetryInterval($retryInterval) {
        $this->retryInterval = $retryInterval;
        return $this;
    }

    /**
     * Gets the proxy hostname or ip address.
     *
     * @return string the proxy hostname or ip address
     */
    public function getProxyHost() {
        return $this->proxyHost;
    }

    /**
     * Sets the proxy hostname or ip address.
     *
     * @param string the proxy hostname or ip address
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setProxyHost($proxyHost) {
        $this->proxyHost = $proxyHost;
        return $this;
    }

    /**
     * Gets the proxy port.
     *
     * @return integer the proxy port
     */
    public function getProxyPort() {
        return $this->proxyPort;
    }

    /**
     * Sets the proxy port.
     *
     * @param integer $proxyPort the proxy port
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setProxyPort($proxyPort) {
        $this->proxyPort = $proxyPort;
        return $this;
    }

    /**
     * Gets the username for proxy authentication.
     *
     * @return string the username for proxy authentication
     */
    public function getProxyUsername() {
        return $this->proxyUsername;
    }

    /**
     * Sets the username for proxy authentication.
     *
     * @param string $username the username for proxy authentication
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setProxyUsername($proxyUsername) {
        $this->proxyUsername = $proxyUsername;
        return $this;
    }

    /**
     * Gets the password for proxy authentication.
     *
     * @return string the password for proxy authentication
     */
    public function getProxyPassword() {
        return $this->proxyPassword;
    }

    /**
     * Sets the password for proxy authentication.
     *
     * @param string $password the password for proxy authentication
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setProxyPassword($proxyPassword) {
        $this->proxyPassword = $proxyPassword;
        return $this;
    }

    /**
     * Gets the URLs (easyKonto web service endpoints) to be used.
     *
     * @return array the URLs (easyKonto web service endpoints) to be used
     */
    public function getServiceEndpoints() {
        return $this->serviceEndpoints;
    }

    /**
     * Sets the URLs (easyKonto web service endpoints) to be used.
     *
     * @param array $serviceEndpoints the URLs (easyKonto web service endpoints) to be used
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setServiceEndpoints(array $serviceEndpoints) {
        if (!count($serviceEndpoints)) {
            throw new InvalidArgumentException('URLs has to be a non-empty array');
        }
        $this->serviceEndpoints = $serviceEndpoints;
        return $this;
    }

    /**
     * Sets configuration options via key/value array.
     * The keys must use this classes setter names.
     *
     * @param array $options the configuration options to set
     * @return EasyKonto_ConnectionConfiguration provides fluent interface
     */
    public function setOptions(array $options) {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (!is_callable(array($this, $method))) {
                throw new InvalidArgumentException('Unknown option: ' . $key);
            }
            $this->$method($value);
        }
        return $this;
    }

    private function configureSingleNode() {
        $this->serviceEndpoints = array('https://www.easykonto.de');
    }

    private function configureHACluster() {
        $this->serviceEndpoints = array(
            'https://node1.easykonto.de',
            'https://node2.easykonto.de'
        );
    }

}
