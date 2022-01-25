<?php

/**
 * Der Listbuilder erzeugt die ausgabe für den export.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_list_Builder extends \Sys25\RnBase\Frontend\Marker\ListBuilder
{
    // die ist leider private und muss überschrieben werden
    private $callbacks = [];

    /**
     * @var tx_mklib_util_list_output_Interface
     */
    private $output = null;

    /**
     * @param tx_mklib_util_list_output_Interface $outputHandler
     * @param array                               $data
     */
    public function __construct(tx_mklib_util_list_output_Interface $outputHandler, $data = [])
    {
        $this->output = $outputHandler;

        $info = (is_array($data) && array_key_exists('info', $data)) ? $data['info'] : null;
        parent::__construct($info);
    }

    /**
    /**
     * Add a visitor callback. It is called for each item before rendering.
     *
     * @param array $callback
     */
    public function addVisitor(array $callback)
    {
        $this->callbacks[] = $callback;
    }

    public function renderEach(
        \Sys25\RnBase\Frontend\Marker\IListProvider $provider,
        $viewData,
        $template,
        $markerClassname,
        $confId,
        $marker,
        $formatter,
        $markerParams = null
    ) {
        $outerMarker = $this->getOuterMarker($marker, $template);

        // wir teilen das Template, da der erste teil direkt ausgegeben werden muss!
        list($header, $footer) = $this->getWrapForSubpart($template, $outerMarker.'S');

        $this->handleOutput($header);

        /* @var $listMarker tx_mklib_util_list_Marker */
        $listMarker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'tx_mklib_util_list_Marker',
            $this->info->getListMarkerInfo(),
            $this->output
        );

        $templateList = \Sys25\RnBase\Frontend\Marker\Templates::getSubpart($template, '###'.$outerMarker.'S###');
        list($listHeader, $listFooter) = $this->getWrapForSubpart($templateList, $marker);
        $templateEntry = \Sys25\RnBase\Frontend\Marker\Templates::getSubpart($templateList, '###'.$marker.'###');

        $this->handleOutput($listHeader);

        $listMarker->addVisitors($this->callbacks);
        $listMarker->renderEach(
            $provider,
            $templateEntry,
            $markerClassname,
            $confId,
            $marker,
            $formatter,
            $markerParams,
            $offset
        );

        $this->handleOutput($listFooter);
        $this->handleOutput($footer);

        return true;
    }

    protected function getWrapForSubpart($template, $marker, $required = true)
    {
        // wir teilen das Template, da der erste teil direkt ausgegeben werden muss!
        $token = md5(time()).md5(get_class());
        $wrap = \Sys25\RnBase\Frontend\Marker\Templates::substituteSubpart(
            $template,
            '###'.$marker.'###',
            $token,
            0
        );
        $wrap = explode($token, $wrap);

        if ($required && 2 != count($wrap)) {
            // es ist etwas schiefgelaufen, wir sollten immer 2 teile haben
            // einmal header und einmal footer
            throw new Exception('Marker '.$marker.' not fount in Template', 1361171589);
        }

        return $wrap;
    }

    public function render(&$dataArr, $viewData, $template, $markerClassname, $confId, $marker, $formatter, $markerParams = null)
    {
        $out = parent::render($dataArr, $viewData, $template, $markerClassname, $confId, $marker, $formatter);
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
        $this->output->handleOutput($out);

        return true;
    }
}
