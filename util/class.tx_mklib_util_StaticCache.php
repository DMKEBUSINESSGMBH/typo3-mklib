<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2010 Michael Wagner <michael.wagner@das-medienkombinat.de>
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
 */

/**
 * Class to handle static caches.
 *
 * can store data for an request to use in all views
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_StaticCache {

	/**
	 * stores static cache data
	 *
	 * @var array
	 */
	private static $staticCache = array();

	/**
	 * Set static cache value
	 *
	 * @param string $key
	 * @param string $value
	 * @param $extKey
	 */
	public function set($key, $value, $extKey = 'mklib'){
		if(!is_array(self::$staticCache[$extKey]))
			self::$staticCache[$extKey] = array();
		self::$staticCache[$extKey][$key] = $value;
	}

	/**
	 * get static cache value
	 *
	 * @param string $key
	 * @param $extKey
	 * @return mixed
	 */
	public function get($key, $extKey = 'mklib'){
		if(!is_array(self::$staticCache[$extKey]))
			self::$staticCache[$extKey] = array();
		return array_key_exists($key, self::$staticCache[$extKey]) ? self::$staticCache[$extKey][$key] : null;
	}

	/**
	 * get static cache value
	 *
	 * @param string $key
	 * @param $extKey
	 * @return mixed
	 */
	public function has($key, $extKey = 'mklib'){
		return is_array(self::$staticCache[$extKey]) && array_key_exists($key, self::$staticCache[$extKey]);
	}

	/**
	 * remove static cache value
	 *
	 * @param string $key
	 * @param $extKey
	 * @return mixed
	 */
	public function remove($key, $extKey = 'mklib'){
		unset(self::$staticCache[$extKey][$key]);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_StaticCache.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_StaticCache.php']);
}
