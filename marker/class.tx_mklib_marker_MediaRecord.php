<?php
/**
 * @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
 ***************************************************************/

/**
 * Diese Klasse ist für die Erstellung von Markerarrays der Section.
 *
 * Entweder für DAM oder für FAL
 *
 * @author Michael Wagner
 */
class tx_mklib_marker_MediaRecord extends \Sys25\RnBase\Frontend\Marker\BaseMarker
{
    /**
     * @return tx_mklib_marker_MediaRecord
     */
    public static function getInstance()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_marker_MediaRecord');
    }

    /**
     * erzeugt eine Liste von Dateien.
     *
     * @param string                        $template
     * @param tx_mkdownloads_model_Download $item
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil     $formatter
     * @param string                        $confId
     * @param string                        $marker
     *
     * @return string
     */
    public static function buildList($aRecords, $template, &$formatter, $confId, $marker)
    {
        if (!self::containsMarker($template, $marker.'S')) {
            return $template;
        }
        $listBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Sys25\RnBase\Frontend\Marker\ListBuilder::class);
        $template = $listBuilder->render(
            $aRecords,
            $formatter->getConfigurations()->getViewData(),
            $template,
            'tx_mklib_marker_MediaRecord',
            $confId,
            $marker,
            $formatter
        );

        return $template;
    }

    /**
     * Daten im Record:
     *  uid, pid, title, media_type, tstamp, crdate, cruser_id,
     *  deleted, sys_language_uid, l18n_parent, hidden, starttime, endtime, fe_group,
     *  file_name, file_dl_name, file_path, file_size, file_type, file_ctime, file_mtime,
     *  file_hash, file_mime_type, file_mime_subtype, file_status, index_type, parent_id.
     *
     * @param tx_mklib_model_Media      $item
     * @param array                     $record
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil $formatter
     * @param string                    $confId
     * @param string                    $marker
     *
     * @return string
     */
    public function parseTemplate($template, &$item, &$formatter, $confId, $marker = 'FILE')
    {
        if (!is_object($item)) {
            $item = self::getEmptyInstance('tx_mklib_model_Media');
        }

        $item->fillPath('file_path_name');

        if ($this->containsMarker($template, $marker.'_FILE_WEBPATH')) {
            $item->fillPath('webpath');
        }

        if ($this->containsMarker($template, $marker.'_FILE_SERVERPATH')) {
            $item->fillPath('serverpath');
        }

        if ($this->containsMarker($template, $marker.'_FILE_RELPATH')) {
            $item->fillPath('relpath');
        }

        $template = $this->addIcon($template, $item, $formatter, $confId, $marker);

        // Fill marker array with data
        $record = $item->getProperty();
        $ignore = self::findUnusedCols($record, $template, $marker);
        $item->setProperty($record);
        $markerArray = $formatter->getItemMarkerArrayWrapped($item->getProperty(), $confId, $ignore, $marker.'_', $item->getColumnNames());
        $wrappedSubpartArray = [];
        $subpartArray = [];

        $this->prepareLinks($item, $marker, $markerArray, $subpartArray, $wrappedSubpartArray, $confId, $formatter, $template);

        $out = \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);

        return $out;
    }

    /**
     * Icon für den Typ hinzufügen.
     *
     * @param string                    $template
     * @param tx_mklib_model_Media      $item
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil $formatter
     * @param string                    $confId
     * @param string                    $marker
     *
     * @return string
     */
    private function addIcon($template, &$item, &$formatter, $confId, $marker)
    {
        if (!$this->containsMarker($template, $marker.'_ICON')) {
            return $template;
        }
        $item->setProperty('icon', '');

        return $template;
        // @TODO: implement if needet
        /*
            ###TS

            icon = IMAGE
            icon {
                ### welches feld soll für das mapping genutzt werden? (file_mime_type, file_mime_subtype, file_type )
                field = file_type
                fileext = gif
                ## wenn kein mapping zutrifft
                default = unknown
                ### mapping der Felder
                ###     wenn im field docx und in fileext gif steht wird doc.gif ausgegeben
                ###     ACHTUNG: in der kommaseparierten liste dürfen keine leerzeichen sein!
                ###              doc, docx wäre falsch und würde nicht funktionieren.
                mapping {
                    doc = doc,docx
                    jpg = jpg,jpeg
                    dwg = dwg
                    dxf = dxf
                    pdf = pdf
                    tiff = tiff
                    xls = xls
                    zip = zip
                    video =
                }
            }
            icon.file {
                import = EXT:mkdownloads/res/fileicons/
                import.field = icon
            }
         */
        $configuration = $formatter->getConfigurations();

        $field = $configuration->get($confId.'icon.field');
        $field = $field ? $field : 'fiel_type';

        $mapping = $configuration->get($confId.'icon.mapping');
        $type = $item->getProperty($field);

        $default = $configuration->get($confId.'icon.default');
        $default = $default ? $default : $type;

        $fileExt = $configuration->get($confId.'icon.fileext');
        $fileExt = $fileExt ? $fileExt : 'gif';

        $icon = $default.'.'.$fileExt;
        if (is_array($mapping)) {
            foreach ($mapping as $key => $value) {
                if (\Sys25\RnBase\Utility\Strings::inList($value, $type)) {
                    $icon = $key.'.'.$fileExt;
                    break;
                }
            }
        }
        $item->setProperty('icon', $icon);
    }

    /**
     * Links vorbereiten.
     *
     * @param tx_mklib_model_Media      $item
     * @param string                    $marker
     * @param array                     $markerArray
     * @param array                     $wrappedSubpartArray
     * @param string                    $confId
     * @param \Sys25\RnBase\Frontend\Marker\FormatUtil $formatter
     */
    private function prepareLinks(&$item, $marker, &$markerArray, &$subpartArray, &$wrappedSubpartArray, $confId, &$formatter, $template)
    {
        $configurations = $formatter->getConfigurations();

        // @TODO Downloadlink integrieren?!
        // index.php?id=download&$mklib[damref]=2
        // hätte einige vorteile

        // Der direkte Link zur Datei ( $confId.'link.' )
        $linkMarker = $marker.'LINK';
        $makeLink = $this->containsMarker($template, $linkMarker);
        $makeUrl = $this->containsMarker($template, $linkMarker.'URL');
        if ($makeLink || $makeUrl) {
            // fill the relative path of the file (dam and fal comform!)
            $url = $item->fillPath('relpath')->getFileRelpath();
            if ($url) { // link erzeugen, wenn gesetzt
                $token = self::getToken();
                $linkObj = $configurations->createLink();
                $linkObj->label($token);
                $linkObj->initByTS($configurations, $confId.'link.', []);
                $linkObj->destination($url);
                // extTarget setzen, wenn im TS. rnbase macht das leider nicht.
                if ($extTarget = $configurations->get($confId.'link.extTarget')) {
                    $linkObj->externalTargetAttribute($extTarget);
                }
                if ($makeLink) {
                    $wrappedSubpartArray['###'.$linkMarker.'###'] = explode($token, $linkObj->makeTag());
                }
                if ($makeUrl) {
                    $markerArray['###'.$linkMarker.'URL###'] = $linkObj->makeUrl(false);
                }
            } else {
                $remove = $configurations->getBool($confId.'link.removeIfDisabled', false, true);
                $this->disableLink($markerArray, $subpartArray, $wrappedSubpartArray, $linkMarker, $remove);
            }
        }
    }
}
