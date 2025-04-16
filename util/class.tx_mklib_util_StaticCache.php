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
 * Class to handle static caches.
 *
 * can store data for an request to use in all views
 */
class tx_mklib_util_StaticCache
{
    /**
     * stores static cache data.
     */
    private static array $staticCache = [];

    /**
     * Set static cache value.
     *
     * @param string $key
     * @param string $value
     */
    public static function set($key, $value, $extKey = 'mklib'): void
    {
        if (!is_array(self::$staticCache[$extKey])) {
            self::$staticCache[$extKey] = [];
        }

        self::$staticCache[$extKey][$key] = $value;
    }

    /**
     * get static cache value.
     *
     * @param string $key
     */
    public static function get($key, $extKey = 'mklib')
    {
        if (!is_array(self::$staticCache[$extKey])) {
            self::$staticCache[$extKey] = [];
        }

        return self::$staticCache[$extKey][$key] ?? null;
    }

    /**
     * get static cache value.
     *
     * @param string $key
     */
    public static function has($key, $extKey = 'mklib'): bool
    {
        return is_array(self::$staticCache[$extKey]) && array_key_exists($key, self::$staticCache[$extKey]);
    }

    /**
     * remove static cache value.
     *
     * @param string $key
     */
    public static function remove($key, $extKey = 'mklib'): void
    {
        unset(self::$staticCache[$extKey][$key]);
    }
}
