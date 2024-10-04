<?php
/**
 * Copyright notice.
 *
 * (c) 2015 DMK E-Business GmbH <dev@dmk-ebusiness.de>
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
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 *
 * @todo This class should be dropped or needs refactoring as the table sys_language
 * doesn't exists anymore since TYPO3 12.
 */
class tx_mklib_mod1_util_Language
{
    /**
     * cached language records.
     *
     * @var array
     */
    private static $sysLanguageRecords = [];

    /**
     * @param int $uid
     *
     * @return array
     */
    public static function getLangRecord($uid)
    {
        $uid = (int) $uid;
        if (empty(static::$sysLanguageRecords[$uid])) {
            static::$sysLanguageRecords[$uid] = \Sys25\RnBase\Database\Connection::getInstance()->getRecord(
                'sys_language',
                $uid
            );
        }

        return static::$sysLanguageRecords[$uid];
    }

    /**
     * @param int $pageId
     *
     * @return array
     */
    public static function getLangRecords($pageId)
    {
        static $sysLanguageRecordAll = false;
        if (!$sysLanguageRecordAll) {
            $sysLanguageRecordAll = true;
            $records = \Sys25\RnBase\Database\Connection::getInstance()->doSelect('*', 'sys_language', []);
            foreach ($records as $record) {
                static::$sysLanguageRecords[(int) $record['uid']] = $record;
            }
        }
        $records = static::$sysLanguageRecords;

        if ($pageId) {
            // check all page overlays to get all available languages for the page
            $available = \Sys25\RnBase\Database\Connection::getInstance()->doSelect(
                'sys_language.uid',
                ['sys_language,pages_language_overlay', 'sys_language'],
                [
                    'where' => 'pages_language_overlay.sys_language_uid=sys_language.uid'
                    .' AND pages_language_overlay.pid='.(int) $pageId
                    .' AND pages_language_overlay.deleted=0',
                    'orderby' => 'sys_language.title ASC',
                ]
            );
            $records = [];
            foreach ($available as $langRow) {
                $langUid = (int) $langRow['uid'];
                $records[$langUid] = self::getLangRecord($langUid);
            }
        }

        return $records;
    }

    /**
     * returns the sprite icon for the given sys language record.
     *
     * @param array|int $recordOrUid
     *
     * @return Ambigous <string, multitype:>
     */
    public static function getLangSpriteIcon($recordOrUid, $options = null)
    {
        $options = \Sys25\RnBase\Domain\Model\DataModel::getInstance($options);

        if (!is_array($recordOrUid)) {
            $langUid = (int) $recordOrUid;
            $record = self::getLangRecord($recordOrUid);
        } else {
            $langUid = (int) $recordOrUid['uid'];
            $record = $recordOrUid;
        }
        $spriteIconName = 'flags-multiple';
        if (!empty($record)) {
            $spriteIconName = \Sys25\RnBase\Backend\Utility\Icons::mapRecordTypeToSpriteIconName(
                'sys_language',
                $record
            );
        }
        $out = \Sys25\RnBase\Backend\Utility\ModuleUtility::getSpriteIcon(
            $spriteIconName
        );
        // add title per default (typo3 equivalent)!
        if (false !== $options->getShowTitle()) {
            $langTitle = 'N/A';
            if (-1 === $langUid) {
                $langTitle = 'LLL:EXT:lang/locallang_general.xml:LGL.allLanguages';
            } elseif (0 === $langUid) {
                $langTitle = 'LLL:EXT:lang/locallang_general.xml:LGL.default_value';
            } elseif (!empty($record['title'])) {
                $langTitle = $record['title'];
            }

            $out .= '&nbsp;'.htmlspecialchars($GLOBALS['LANG']->sL($langTitle));
        }

        return $out;
    }

    public static function getAddLocalizationLinks(
        \Sys25\RnBase\Domain\Model\RecordInterface $item,
        ?\Sys25\RnBase\Backend\Module\BaseModule $mod = null
    ) {
        if (// the item already are an translated item!
            $item->getUid() != $item->getProperty('uid')
            || 0 !== $item->getSysLanguageUid()
        ) {
            return '';
        }

        $out = '';
        foreach (self::getLangRecords($item->getPid()) as $lang) {
            // skip, if the be user hase no access to for the language!
            if (!$GLOBALS['BE_USER']->checkLanguageAccess($lang['uid'])) {
                continue;
            }

            // skip, if a overlay for this language allready exists
            $parentField = \Sys25\RnBase\Backend\Utility\TCA::getTransOrigPointerFieldForTable($item->getTableName());
            $sysLanguageUidField = \Sys25\RnBase\Backend\Utility\TCA::getLanguageFieldForTable($item->getTableName());
            $overlays = \Sys25\RnBase\Database\Connection::getInstance()->doSelect(
                'uid',
                $item->getTableName(),
                [
                    'where' => implode(
                        ' AND ',
                        [
                            $parentField.'='.$item->getUid(),
                            $sysLanguageUidField.'='.(int) $lang['uid'],
                        ]
                    ),
                    'limit' => 1,
                ]
            );
            if (!empty($overlays)) {
                continue;
            }

            /* @var $mod \Sys25\RnBase\Backend\Module\BaseModule */
            if (!$mod instanceof \Sys25\RnBase\Backend\Module\BaseModule) {
                return '';
            }

            $onclick = $mod->issueCommand(
                '&cmd['.$item->getTableName().']['.$item->getUid().'][localize]='.$lang['uid']
            );
            $onclick = 'window.location.href=\''.$onclick.'\'; return false;';

            $out .= sprintf(
                '<a href="#" onclick="%1$s">%2$s</a>',
                htmlspecialchars(
                    $onclick
                ),
                self::getLangSpriteIcon(
                    $lang,
                    ['show_title' => false]
                )
            );
        }

        return $out;
    }
}
