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
            static::$sysLanguageRecords[$uid] = tx_rnbase_util_DB::getRecord(
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
            $records = tx_rnbase_util_DB::doSelect('*', 'sys_language', []);
            foreach ($records as $record) {
                static::$sysLanguageRecords[(int) $record['uid']] = $record;
            }
        }
        $records = static::$sysLanguageRecords;

        if ($pageId) {
            // check all page overlays to get all available languages for the page
            $available = tx_rnbase_util_DB::doSelect(
                'sys_language.uid',
                ['sys_language,pages_language_overlay', 'sys_language'],
                [
                    'where' => 'pages_language_overlay.sys_language_uid=sys_language.uid'
                    .' AND pages_language_overlay.pid='.(int) $pageId
                    .Tx_Rnbase_Backend_Utility::deleteClause('pages_language_overlay'),
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
        $options = tx_rnbase_model_data::getInstance($options);

        if (!is_array($recordOrUid)) {
            $langUid = (int) $recordOrUid;
            $record = self::getLangRecord($recordOrUid);
        } else {
            $langUid = (int) $recordOrUid['uid'];
            $record = $recordOrUid;
        }
        $spriteIconName = 'flags-multiple';
        if (!empty($record)) {
            $spriteIconName = Tx_Rnbase_Backend_Utility_Icons::mapRecordTypeToSpriteIconName(
                'sys_language',
                $record
            );
        }
        $out = tx_rnbase_mod_Util::getSpriteIcon(
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
        Tx_Rnbase_Domain_Model_RecordInterface $item,
        tx_rnbase_mod_BaseModule $mod = null
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
            $parentField = tx_rnbase_util_TCA::getTransOrigPointerFieldForTable($item->getTableName());
            $sysLanguageUidField = tx_rnbase_util_TCA::getLanguageFieldForTable($item->getTableName());
            $overlays = tx_rnbase_util_DB::doSelect(
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

            /* @var $mod tx_rnbase_mod_BaseModule */
            if (!$mod instanceof tx_rnbase_mod_BaseModule) {
                $mod = $GLOBALS['SOBE'];
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
