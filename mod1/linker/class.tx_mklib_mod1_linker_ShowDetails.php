<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 * Generischer Linker für eine Detailseite.
 *
 * // Linker Instanz
 * $linker = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
 *			'tx_mklib_mod1_linker_ShowDetails',
 *			'couponGroup'
 *		)
 * // einen Button, welcher auf die Detailseite zeigt erstellen.
 * $linker->makeLink($item, $mod->getFormTool());
 * // einen Button, welcher wieder auf die Übersichtsseite zeigt erstellen.
 * $linker->makeClearLink($item, $mod->getFormTool());
 *
 * // Liefert die Uid des Datensatzes für die Detailseite, falls vorhanden?
 * // Die ID wird aus den Parametern oder aud den Modul-Daten geholt
 * // Dabei wird gleichzeitig das Clear Event geprüft!
 * $linker->getCurrentUid($mod);
 *
 * @TODO: UnitTests!!!
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_mod1_linker_ShowDetails
{
    private $identifier = null;

    /**
     * @throws InvalidArgumentException
     *
     * @param string $identifier Model or tablename
     *                           Wird zum Speichern in den Moduldaten,
     *                           zum erzeugen der buttons und
     *                           zum auslesend er Parameter verwendet
     */
    public function __construct($identifier)
    {
        if (empty($identifier)) {
            throw new InvalidArgumentException('Constructor needs a valid identifier');
        }
        $this->identifier = $identifier;
    }

    /**
     * @param \Sys25\RnBase\Domain\Model\RecordInterface $item
     * @param \Sys25\RnBase\Backend\Form\ToolBox                $formTool
     * @param array                                  $options
     *
     * @return string
     */
    public function makeLink(
        \Sys25\RnBase\Domain\Model\RecordInterface $item,
        \Sys25\RnBase\Backend\Form\ToolBox $formTool,
        $options = []
    ) {
        $out = $formTool->createSubmit(
            'showDetails['.$this->identifier.']['.$item->getUid().']',
            isset($options['label']) ? $options['label'] : '###LABEL_SHOW_DETAILS###',
            isset($options['confirm']) ? $options['confirm'] : '',
            $options
        );

        return $out;
    }

    /**
     * @param \Sys25\RnBase\Backend\Form\ToolBox $formTool
     * @param array                   $options
     *
     * @return string
     */
    public function makeClearLink(
        // wird eigentlich nicht benötigt.
        \Sys25\RnBase\Domain\Model\RecordInterface $item,
        \Sys25\RnBase\Backend\Form\ToolBox $formTool,
        $options = []
    ) {
        $out = $formTool->createSubmit(
            'showDetails['.$this->identifier.'][clear]',
            isset($options['label']) ? $options['label'] : '###LABEL_BTN_NEWSEARCH###',
            isset($options['confirm']) ? $options['confirm'] : '',
            $options
        );

        return $out;
    }

    /**
     * @param \Sys25\RnBase\Backend\Module\IModule $mod
     */
    public function getCurrentUid(
        \Sys25\RnBase\Backend\Module\IModule $mod
    ) {
        $modSettings = [
            $this->identifier => '0',
        ];

        $params = \Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('showDetails');
        $params = is_array($params) ? $params : [];
        $model = key($params);
        $uid = current($params);
        if (is_array($uid)) {
            $uid = current($uid);
        }

        if (
            !empty($uid)
            && 'clear' === $uid
        ) {
            \Sys25\RnBase\Backend\Utility\BackendUtility::getModuleData(
                $modSettings,
                $modSettings,
                $mod->getName()
            );

            return 0;
        }
        // else

        $uid = intval($uid);
        $data = \Sys25\RnBase\Backend\Utility\BackendUtility::getModuleData(
            $modSettings,
            $uid
                ? [
                    $this->identifier => $uid,
                ] : [],
            $mod->getName()
        );

        return intval($data[$this->identifier]);
    }
}
