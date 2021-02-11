<?php
/**
 * Copyright notice.
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

/**
 * Schreibt CSV Dateien.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_csv_writer
{
    /**
     * @var string
     */
    private $file = '';
    /**
     * @var ressource
     */
    private $handle = false;
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
     * @var int
     */
    private $rowCount = 0;

    /**
     * Die Sapletn, welche in die CSV geschrieben werden.
     *
     * @var array
     */
    private $columns = [];

    /**
     * Gibt an ob letzter Zeilenumbruch geschrieben werden soll.
     *
     * @var bool
     */
    private $trailingLineBreak = true;

    /**
     * @param string $file
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     *
     * @throws Exception
     */
    public function __construct(
        $file,
        $delimiter = ';',
        $enclosure = '"',
        $escape = '\\'
    ) {
        $this->handle = @fopen($file, 'w');
        if (false === $this->handle) {
            throw new Exception('Could not open file for csv writer. File: '.$file);
        }
        $this->file = $file;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape; // @TODO: wird noch nicht verwendet
    }

    public function __destruct()
    {
        if ($this->handle) {
            $this->removeTrailingLineBreak();
            @fclose($this->handle);
        }
        if (!$this->rowCount && is_file($this->file)) {
            @unlink($this->file);
        }
    }

    /**
     * Setzt die zu schreibenden Spalten.
     *
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->columns = array_values($columns);
    }

    /**
     * Schreibt die Spaltenüberschriften in die Datei.
     *
     * @return int returns the length of the written string or FALSE on failure
     */
    public function writeHeader()
    {
        return $this->putRow($this->columns);
    }

    /**
     * Fügt einen Datensatz zur CSV-Datei hinzu.
     *
     * @param array $record
     *
     * @return int returns the length of the written string or FALSE on failure
     */
    public function addRow(array $row)
    {
        $fields = [];
        foreach ($this->columns as $field) {
            $fields[$field] = empty($row[$field]) ? '' : $row[$field];
        }
        ++$this->rowCount;

        return $this->putRow($fields);
    }

    /**
     * Schreibt eine zeile in die CSV-Datei.
     *
     * @param array $row
     *
     * @return int returns the length of the written string or FALSE on failure
     */
    protected function putRow(array $row)
    {
        return fputcsv(
            $this->handle,
            $row,
            $this->delimiter,
            $this->enclosure
        );
    }

    /**
     * Setzt Option um letzten Zeilenumbruch zu schreiben.
     *
     * @param bool $removal
     */
    public function setTrailingLineBreakRemoval($removal = false)
    {
        $this->trailingLineBreak = (!$removal);
    }

    /**
     * Entfernt letzten Zeilenumbruch, der durch fputcsv automatisch generiert wird.
     */
    protected function removeTrailingLineBreak()
    {
        if (!$this->trailingLineBreak &&
            $this->rowCount > 0 &&
            is_file($this->file)
        ) {
            @ftruncate($this->handle, filesize($this->file) - 1);
        }
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_csv_writer.php']) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_csv_writer.php'];
}
