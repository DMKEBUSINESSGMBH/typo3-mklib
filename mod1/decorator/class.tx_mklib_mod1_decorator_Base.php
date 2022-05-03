<?php
/**
 * Copyright notice.
 *
 * (c) 2011 - 2015 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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
 * Diese Klasse ist für die Darstellung von Elementen im Backend verantwortlich.
 *
 * @author Hannes Bochmann
 * @author Michael Wagner
 */
class tx_mklib_mod1_decorator_Base implements \Sys25\RnBase\Backend\Decorator\InterfaceDecorator
{
    /**
     * @var \Sys25\RnBase\Backend\Module\IModule
     */
    private $mod = null;
    /**
     * @var array
     */
    private $options = null;

    /**
     * @param \Sys25\RnBase\Backend\Module\IModule $mod
     */
    public function __construct(\Sys25\RnBase\Backend\Module\IModule $mod, array $options = [])
    {
        $this->mod = $mod;
        $this->options = $options;
    }

    /**
     * @param string                                 $value
     * @param string                                 $colName
     * @param array                                  $record
     * @param \Sys25\RnBase\Domain\Model\DataInterface $item
     */
    public function format($value, $colName, $record, \Sys25\RnBase\Domain\Model\DataInterface $item)
    {
        $ret = $value;
        switch ($colName) {
            case 'uid':
                $ret = $this->getUidColumn($item);
                break;

            case 'label':
                $ret = $this->getLabelColumn($item);
                break;

            case 'crdate':
            case 'tstamp':
                $ret = strftime('%d.%m.%y %H:%M:%S', (int) $ret);
                break;

            case 'sys_language_uid':
                $ret = $this->getSysLanguageColumn($item);
                break;

            case 'actions':
                $ret .= $this->getActions($item, $this->getActionOptions($item));
                break;

            default:
                break;
        }

        return $this->wrapValue($ret, $value, $colName, $record, $item);
    }

    /**
     * renders the uid column.
     *
     * @param \Sys25\RnBase\Domain\Model\DataInterface $item
     *
     * @return string
     */
    protected function getUidColumn(\Sys25\RnBase\Domain\Model\DataInterface $item)
    {
        $wrap = $item->isHidden() ? ['<del>', '</del>'] : ['', ''];
        $ret = $wrap[0].$item->getProperty('uid').$wrap[1];
        $dates = [];
        $dates['crdate'] = $item->hasProperty('crdate') ? strftime('%d.%m.%y %H:%M:%S', intval($item->getProperty('crdate'))) : '-';
        $dates['tstamp'] = $item->hasProperty('tstamp') ? strftime('%d.%m.%y %H:%M:%S', intval($item->getProperty('tstamp'))) : '-';

        return '<span title="Creation: '.$dates['crdate']." \nLast Change: ".$dates['tstamp'].' ">'.$ret.'</span>';
    }

    /**
     * renders the label column.
     *
     * @param \Sys25\RnBase\Domain\Model\DataInterface $item
     *
     * @return string
     */
    protected function getLabelColumn(\Sys25\RnBase\Domain\Model\DataInterface $item)
    {
        $lastModifyDateTime = $item->getLastModifyDateTime();
        $creationDateTime = $item->getCreationDateTime();

        return sprintf(
            '<span title="UID: %3$d %1$sLabel: %2$s %1$sCreation: %4$s %1$sLast Change: %5$s">%2$s</span>',
            CRLF,
            $item->getTcaLabel(),
            $item->getProperty('uid'),
            $creationDateTime ? $creationDateTime->format(DateTime::ATOM) : '-',
            $lastModifyDateTime ? $lastModifyDateTime->format(DateTime::ATOM) : '-'
        );
    }

    /**
     * Renders the language column.
     * Renders the flag and the title of the sys language record.
     * Renders some links to create the overlay too.
     *
     * @param \Sys25\RnBase\Domain\Model\DataInterface $item
     *
     * @return string
     */
    protected function getSysLanguageColumn(\Sys25\RnBase\Domain\Model\DataInterface $item)
    {
        if ($item->getTableName()) {
            $ret = tx_mklib_mod1_util_Language::getLangSpriteIcon(
                $item->getSysLanguageUid(),
                ['show_title' => true]
            );
            $new = tx_mklib_mod1_util_Language::getAddLocalizationLinks(
                $item,
                $this->getModule()
            );

            if (!empty($new)) {
                $fileExt = 'xlf';
                $ret .= ' ('
                    .$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.'.$fileExt.':Localize')
                    .' '.$new
                    .')';
            }
        }

        return empty($ret) ? false : $ret;
    }

    /**
     * Liefert die möglichen Optionen für die actions.
     *
     * @param \Sys25\RnBase\Domain\Model\DataInterface $item
     *
     * @return array
     */
    protected function getActionOptions($item = null)
    {
        $cols = [
            'edit' => '',
            'hide' => '',
        ];

        if ($item && \Sys25\RnBase\Backend\Utility\TCA::getSortbyFieldForTable($item->getTableName())) {
            $cols['moveup'] = '';
            $cols['movedown'] = '';
        }

        $userIsAdmin = is_object($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER']->isAdmin() : 0;
        // admins dürfen auch löschen
        if ($userIsAdmin) {
            $cols['remove'] = '';
        }

        return $cols;
    }

    /**
     * @TODO: weitere links integrieren!
     * $options = array('hide'=>'ausblenden,'edit'=>'bearbeiten,'remove'=>'löschen','history'='history','info'=>'info','move'=>'verschieben');
     *
     * @param \Sys25\RnBase\Domain\Model\DataInterface $item
     * @param array                                  $options
     *
     * @return string
     */
    protected function getActions(\Sys25\RnBase\Domain\Model\DataInterface $item, array $options)
    {
        $ret = '';
        $tableName = $item->getTableName();
        // we use the real uid, not the uid of the parent!
        $uid = $item->getProperty('uid');
        foreach ($options as $sLinkId => $bTitle) {
            switch ($sLinkId) {
                case 'edit':
                    $ret .= $this->getFormTool()->createEditLink($tableName, $uid, $bTitle);
                    break;
                case 'hide':
                    $sHiddenColumn = tx_mklib_util_TCA::getEnableColumn($tableName, 'disabled', 'hidden');
                    $ret .= $this->getFormTool()->createHideLink($tableName, $uid, $item->getProperty($sHiddenColumn));
                    break;
                case 'remove':
                    // Es wird immer ein Bestätigungsdialog ausgegeben!!! Dieser steht
                    // in der BE-Modul locallang.xlf der jeweiligen Extension im Schlüssel
                    // 'confirmation_deletion'. (z.B. mkkvbb/mod1/locallang.xlf) Soll kein
                    // Bestätigungsdialog ausgegeben werden, dann einfach 'confirmation_deletion' leer lassen
                    $ret .= $this->getFormTool()->createDeleteLink($tableName, $uid, $bTitle, ['confirm' => $GLOBALS['LANG']->getLL('confirmation_deletion')]);
                    break;
                case 'moveup':
                    $fromUid = $uid;
                    $uidMap = $this->getItemsMap($item);
                    // zwei schritte in der map zurück,
                    // denn wir wollen das aktuelle element vor das vorherige.
                    // typo3 verschiebt aber immer hinter elemente, also muss es hinter das vorvorletzte.
                    // wenn es kein vorvorletztes gibt, verschieben wir das vorletzte element hinter das aktuelle element
                    prev($uidMap);
                    $prevId = key($uidMap);
                    if ($prevId) {
                        prev($uidMap);
                        if (key($uidMap)) {
                            $prevId = key($uidMap);
                        } else {
                            $fromUid = $prevId;
                            $prevId = $uid;
                        }
                    }
                    if ($prevId) {
                        $ret .= $this->getFormTool()->createMoveUpLink(
                            $tableName,
                            $fromUid,
                            $prevId,
                            [
                                'label' => '',
                                'title' => 'Move '.$fromUid.' after '.$prevId,
                            ]
                        );
                    } else {
                        $ret .= \Sys25\RnBase\Backend\Utility\ModuleUtility::getSpriteIcon('empty-icon');
                    }
                    break;
                case 'movedown':
                    $uidMap = $this->getItemsMap($item);
                    // einen schritt in der map nach vorne, denn wir wollen das aktuelle hinter dem nächsten platzieren.
                    next($uidMap);
                    $nextId = key($uidMap);
                    if ($nextId) {
                        $ret .= $this->getFormTool()->createMoveDownLink(
                            $tableName,
                            $uid,
                            $nextId,
                            [
                                'label' => '',
                                'title' => 'Move '.$uid.' after '.$nextId,
                            ]
                        );
                    } else {
                        $ret .= \Sys25\RnBase\Backend\Utility\ModuleUtility::getSpriteIcon('empty-icon');
                    }
                    break;
                default:
                    break;
            }
        }

        return $ret;
    }

    /**
     * @param string $output
     *
     * @return string
     */
    protected function wrapValue(
        $output,
        $value,
        $colName,
        $record,
        \Sys25\RnBase\Domain\Model\DataInterface $item
    ) {
        $stateClass = [];

        if ($item->isHidden()) {
            $stateClass[] = 'ef-hidden';
        }
        if ($item->isDeleted()) {
            $stateClass[] = 'ef-deleted';
        }

        if (!empty($stateClass)) {
            $output = '<div class="'.implode(' ', $stateClass).'">'.$output.'</div>';
        }

        return $output;
    }

    /**
     * liefert die items map und setzten den pointer auf das aktuelle element.
     *
     * @param \Sys25\RnBase\Domain\Model\DataInterface $item
     *
     * @return array
     */
    protected function getItemsMap(\Sys25\RnBase\Domain\Model\DataInterface $item)
    {
        if (empty($this->options['items_map'])) {
            return [];
        }
        $currentId = $item->getUid();
        $map = $this->options['items_map'];

        while (null !== key($map) && key($map) != $currentId) {
            next($map);
        }

        return $map;
    }

    /**
     * Returns the module.
     *
     * @return \Sys25\RnBase\Backend\Module\IModule
     */
    protected function getModule()
    {
        return $this->mod;
    }

    /**
     * Returns an instance of \Sys25\RnBase\Backend\Module\IModule.
     *
     * @return \Sys25\RnBase\Backend\Form\ToolBox
     */
    protected function getFormTool()
    {
        return $this->mod->getFormTool();
    }
}
