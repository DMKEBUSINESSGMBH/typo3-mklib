<?php
/**
 * Copyright notice
 *
 * (c) 2014 DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
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
 * Liest CSV Dateien aus
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_csv_reader
	implements Iterator {

	/**
	 *
	 * @var ressource
	 */
	private $handle = FALSE;
	/**
	 * @var string
	 */
	private $delimiter = ';';
	/**
	 * @var string
	 */
	private $enclosure = '"';
	/**
	 * @var string
	 */
	private $escape = '\\';
	/**
	 * Ignoriert die erste Zeile der CSV.
	 * Dies ist der Fall, wenn ein Header im CSV enthalten ist.
	 *
	 * @var bool
	 */
	private $ignoreFirst = TRUE;

	/**
	 *
	 * @param string $file
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param string $escape
	 * @throws Exception
	 */
	public function tx_mklib_util_csv_reader (
		$file, $delimiter = ';', $enclosure = '"', $escape = '\\'
	) {
		$this->handle = @fopen($file, 'r');
		if ($this->handle === FALSE) {
			throw new Exception(
				'Could not open file for csv reader. File: ' . $file
			);
		}
		$this->delimiter = $delimiter;
		$this->enclosure = $enclosure;
		$this->escape = $escape;
	}

	public function __destruct() {
		if ($this->handle)
			@fclose($this->handle);
	}

	public function setIgnoreFirstLine($ignoreFirst = TRUE) {
		$this->ignoreFirst = $ignoreFirst;
	}

	/**
	 * This method resets the file pointer.
	 */
	public function rewind() {
		fseek($this->handle, 0, SEEK_SET);
		if ($this->ignoreFirst) {
			$this->current();
		}
	}

    /**
     * This method returns the current csv row as a 2 dimensional array
     *
     * @return array The current csv row as a 2 dimensional array
     */
	public function current() {
		return fgetcsv(
			$this->handle,
			NULL,
			$this->delimiter,
			$this->enclosure,
			$this->escape
		);
	}

	/**
	 * This method returns the current row number.
	 *
	 * @return int The current row number
	 */
	public function key() {
		return ftell($this->handle);
	}

	/**
	 * This method checks if the end of file is reached.
	 *
	 * @return boolean Returns true on EOF reached, false otherwise.
	 */
	public function next() {
		return !feof($this->handle);
	}

	/**
	 * This method checks if the next row is a valid row.
	 *
	 * @return boolean If the next row is a valid row.
	 */
	public function valid() {
        if (!$this->next()) {
        	// nicht schließen, wenn mehrfach über die csv itteriert werden soll gibts probleme!
//             fclose($this->handle);
            return false;
        }
        return true;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_csv_reader.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_csv_readere.php']);
}