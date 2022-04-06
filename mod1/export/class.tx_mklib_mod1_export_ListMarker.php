<?php

/**
 * Base class for Markers.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_mod1_export_ListMarker extends \Sys25\RnBase\Frontend\Marker\ListMarker
{
    /**
     * Callback function for next item.
     *
     * @param object $data
     */
    public function renderNext($data)
    {
        $data->setProperty('roll', $this->rowRollCnt);
        $data->setProperty('line', $this->i); // Marker für aktuelle Zeilenummer
        $data->setProperty('totalline', $this->i + $this->totalLineStart + $this->offset); // Marker für aktuelle Zeilenummer der Gesamtliste
        $this->handleVisitors($data);
        $part = $this->entryMarker->parseTemplate($this->getInfo()->getTemplate($data), $data, $this->getFormatter(), $this->confId, $this->marker);

        tx_mklib_mod1_export_Util::doOutPut($part);

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
        if (!is_array($this->getVisitors())) {
            return;
        }
        foreach ($this->getVisitors() as $visitor) {
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
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil $formatter
     * @param mixed                     $markerParams
     * @param int                       $offset
     *
     * @return array
     */
    public function render($dataArr, $template, $markerClassname, $confId, $marker, &$formatter, $markerParams = false, $offset = 0)
    {
        $out = parent::render($dataArr, $template, $markerClassname, $confId, $marker, $formatter, $markerParams, $offset);
        tx_mklib_mod1_export_Util::doOutPut($out);

        return '';
    }
}
