<?php
/**
 * Copyright notice.
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

/**
 * HttpRequest.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
interface tx_mklib_util_httprequest_adapter_Interface
{
    /**
     * Set the configuration array for the adapter.
     *
     * @param array $config
     */
    public function setConfig(array $config = []);

    /**
     * Connect to the remote server.
     *
     * @param string $host
     * @param int    $port
     * @param bool   $secure
     */
    public function connect($host, $port = 80, $secure = false);

    /**
     * Send request to the remote server.
     *
     * @param string $method
     * @param string $url
     * @param array  $headers
     * @param string $body
     *
     * @return string Request as text
     */
    public function write($method, $url, $headers = [], $body = '');

    /**
     * Read response from server.
     *
     * @return string
     */
    public function read();

    /**
     * Close the connection to the server.
     */
    public function close();
}
