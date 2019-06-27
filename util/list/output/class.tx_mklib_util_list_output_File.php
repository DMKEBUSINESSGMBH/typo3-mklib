<?php

/**
 * Interface für Ausgaben des Listbuilders.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_list_output_File implements tx_mklib_util_list_output_Interface
{
    /**
     * @var string
     */
    private $filename = '';
    /**
     * @var string ressource
     */
    private $fileHandler = null;

    public function __construct($data)
    {
        if (is_array($data) && array_key_exists('file', $data)) {
            $this->filename = $data['file'];
        }
        if ('' != $this->filename) {
            $this->fileHandler = fopen($this->filename, 'wb');
        }
    }

    public function __destruct()
    {
        if ($this->fileHandler) {
            fclose($this->fileHandler);
        }
    }

    public function handleOutput($output = '')
    {
        if ($this->fileHandler && '' != $output) {
            fwrite($this->fileHandler, $output);
        }
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/list/output/class.tx_mklib_util_list_output_File.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/list/output/class.tx_mklib_util_list_output_File.php'];
}
