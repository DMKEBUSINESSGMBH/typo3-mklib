<?php

tx_rnbase::load('tx_rnbase_util_ListMarker');

/**
 * Base class for Markers.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_list_Marker extends tx_rnbase_util_ListMarker
{
    public $output = null;

    /**
     * @param ListMarkerInfo                      $listMarkerInfo
     * @param tx_mklib_util_list_output_Interface $output
     */
    public function __construct(ListMarkerInfo $listMarkerInfo = null, tx_mklib_util_list_output_Interface $output)
    {
        $this->output = $output;
        parent::__construct($listMarkerInfo);
    }

    /**
     * Callback function for next item.
     *
     * @param object $data
     */
    public function renderNext($data)
    {
        $data->record['roll'] = $this->rowRollCnt;
        $data->record['line'] = $this->i; // Marker für aktuelle Zeilenummer
        $data->record['totalline'] = $this->i + $this->totalLineStart + $this->offset; // Marker für aktuelle Zeilenummer der Gesamtliste
        $this->handleVisitors($data);
        $part = $this->entryMarker->parseTemplate($this->info->getTemplate($data), $data, $this->formatter, $this->confId, $this->marker);

        $this->handleOutput($part);
        $this->rowRollCnt = ($this->rowRollCnt >= $this->rowRoll) ? 0 : $this->rowRollCnt + 1;
        ++$this->i;
    }

    /**
     * Call all visitors for an item.
     *
     * @param object $data
     */
    protected function handleVisitors($data)
    {
        if (!is_array($this->visitors)) {
            return;
        }
        foreach ($this->visitors as $visitor) {
            call_user_func($visitor, $data);
        }
    }

    /**
     * Render an array of objects.
     *
     * @param array                     $dataArr
     * @param string                    $template
     * @param string                    $markerClassname
     * @param string                    $confId
     * @param string                    $marker
     * @param tx_rnbase_util_FormatUtil $formatter
     * @param mixed                     $markerParams
     * @param int                       $offset
     *
     * @return array
     */
    public function render($dataArr, $template, $markerClassname, $confId, $marker, &$formatter, $markerParams = false, $offset = 0)
    {
        $out = parent::render($dataArr, $template, $markerClassname, $confId, $marker, $formatter, $markerParams, $offset);
        $this->handleOutput($out);
    }

    /**
     * Handle output.
     *
     * @param string $out
     *
     * @return bool
     */
    private function handleOutput($out)
    {
        if ($this->output instanceof tx_mklib_util_list_output_Interface) {
            $this->output->handleOutput($out);

            return true;
        }

        return false;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/list/class.tx_mklib_util_list_Marker.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/list/class.tx_mklib_util_list_Marker.php'];
}
