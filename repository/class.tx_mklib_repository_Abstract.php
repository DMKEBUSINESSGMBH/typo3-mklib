<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2014 DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
 * All rights reserved
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
 * Abstracte Repository Klasse.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class tx_mklib_repository_Abstract implements \Sys25\RnBase\Domain\Repository\SearchInterface, \TYPO3\CMS\Core\SingletonInterface
{
    // 0: Hide record; 1: Soft-delete (via "deleted" field) record; 2: Really DELETE
    public const DELETION_MODE_HIDE = 0;
    public const DELETION_MODE_SOFTDELETE = 1;
    public const DELETION_MODE_REALLYDELETE = 2;

    /**
     * Liefert den Namen der Suchklasse.
     *
     * @return string
     */
    abstract protected function getSearchClass();

    /**
     * Liefert den Searcher.
     *
     * @return \Sys25\RnBase\Search\SearchBase
     */
    protected function getSearcher()
    {
        $searcher = \Sys25\RnBase\Search\SearchBase::getInstance($this->getSearchClass());
        if (!$searcher instanceof \Sys25\RnBase\Search\SearchBase) {
            throw new Exception(get_class($this).'->getSearchClass() has to return a classname'.' of class which extends \Sys25\RnBase\Search\SearchBase!');
        }

        return $searcher;
    }

    /**
     * Liefert die Model Klasse.
     *
     * @return string
     */
    protected function getWrapperClass()
    {
        return $this->getSearcher()->getWrapperClass();
    }

    /**
     * Holt einen bestimmten Datensatz aus dem Repo.
     *
     * @param int|array $rowOrUid
     *
     * @return \Sys25\RnBase\Domain\Model\RecordInterface|null
     *
     * @TODO use handleEnableFieldsOptions to get hidden records in BE
     */
    public function findByUid($rowOrUid)
    {
        /* @var $model \Sys25\RnBase\Domain\Model\RecordInterface */
        $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            $this->getWrapperClass(),
            $rowOrUid
        );

        return $model->isPersisted() && $model->isValid() ? $model : null;
    }

    /**
     * @return array[\Sys25\RnBase\Domain\Model\RecordInterface]
     */
    public function findAll()
    {
        return $this->search([], []);
    }

    /**
     * Search database.
     *
     * @param array $fields
     * @param array $options
     *
     * @return array[\Sys25\RnBase\Domain\Model\RecordInterface]
     */
    public function search(array $fields, array $options)
    {
        $this->prepareFieldsAndOptions($fields, $options);
        $items = $this->getSearcher()->search($fields, $options);

        return $this->prepareItems($items, $options);
    }

    /**
     * Search database.
     *
     * @param array $fields
     * @param array $options
     *
     * @return \Sys25\RnBase\Domain\Model\RecordInterface
     */
    public function searchSingle(array $fields = [], array $options = [])
    {
        $options['limit'] = 1;
        $items = $this->search($fields, $options);

        return !empty($items[0]) ? $items[0] : null;
    }

    /**
     * On default, return hidden and deleted fields in backend.
     *
     * @param array &$fields
     * @param array &$options
     */
    protected function prepareFieldsAndOptions(&$fields, &$options)
    {
        $this->handleEnableFieldsOptions($fields, $options);
        $this->handleLanguageOptions($fields, $options);
    }

    /**
     * On default, return hidden and deleted fields in backend.
     *
     * @param array &$fields
     * @param array &$options
     */
    protected function handleEnableFieldsOptions(&$fields, &$options)
    {
        if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof \Psr\Http\Message\ServerRequestInterface &&
            \TYPO3\CMS\Core\Http\ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend() &&
            !isset($options['enablefieldsoff']) &&
            !isset($options['enablefieldsbe']) &&
            !isset($options['enablefieldsfe'])
        ) {
            $options['enablefieldsbe'] = true;
        }
    }

    /**
     * Setzt eventuelle Sprachparameter,
     * damit nur valide Daten für die aktuelle Sprache ausgelesen werden.
     *
     * @param array &$fields
     * @param array &$options
     */
    protected function handleLanguageOptions(&$fields, &$options)
    {
        if (!isset($options['i18n'])
            && !isset($options['ignorei18n'])
            && !isset($options['enablefieldsoff'])
        ) {
            $tableName = $this->getEmptyModel()->getTableName();
            $languageField = tx_mklib_util_TCA::getLanguageField($tableName);
            // Die Sprache prüfen wir nur, wenn ein Sprachfeld gesetzt ist.
            if (!empty($languageField)) {
                $tsfe = \Sys25\RnBase\Utility\TYPO3::getTSFE();
                $languages = [];
                if (isset($options['additionali18n'])) {
                    $languages = \Sys25\RnBase\Utility\Strings::trimExplode(',', $options['additionali18n'], true);
                }
                $languages[] = '-1'; // for all languages
                // Wenn eine bestimmte Sprache gesetzt ist,
                // laden wir diese ebenfalls.
                if (is_object($tsfe) && \Sys25\RnBase\Utility\FrontendControllerUtility::getLanguageContentId($tsfe)) {
                    $languages[] = \Sys25\RnBase\Utility\FrontendControllerUtility::getLanguageContentId($tsfe);
                } // andernfalls nutzen wir die default sprache
                else {
                    $languages[] = '0'; // default language
                }
                $options['i18n'] = implode(',', array_unique($languages, SORT_NUMERIC));
            }
        }
    }

    /**
     * Modifiziert die Ergebisliste.
     *
     * @param array $items
     * @param array $options
     *
     * @return array[\Sys25\RnBase\Domain\Model\RecordInterface]
     */
    protected function prepareItems($items, $options)
    {
        if (!is_array($items)) {
            return $items;
        }
        $items = $this->uniqueItems($items, $options);

        return $items;
    }

    /**
     * Entfernt alle doppelten Datensatze, wenn die Option distinct gesetzt ist.
     * Dabei werden die Sprachoverlays bevorzugt.
     *
     * @param array        $items
     * @param unknown_type $options
     *
     * @return array[\Sys25\RnBase\Domain\Model\RecordInterface]
     */
    protected function uniqueItems(array $items, $options)
    {
        // uniqueue, if there are models and the distinct option
        if (reset($items) instanceof \Sys25\RnBase\Domain\Model\RecordInterface
            && isset($options['distinct'])
            && $options['distinct']
        ) {
            // seperate master and overlays
            $master = $overlay = [];
            /* @var $item \Sys25\RnBase\Domain\Model\RecordInterface */
            foreach ($items as $item) {
                $uid = (int) $item->getUid();
                $realUid = (int) $item->getProperty('uid');
                if ($uid === $realUid) {
                    $master[$uid] = $item;
                } else {
                    $overlay[$uid] = $item;
                }
            }
            // merge master and overlays and keep the order!
            $new = [];
            // uniquemode can be master or overlay!
            $preferOverlay = empty($options['uniquemode']) || 'master' !== strtolower($options['uniquemode']);
            foreach ($items as $item) {
                $uid = (int) $item->getUid();
                $new[$uid] = !empty($overlay[$uid]) && $preferOverlay ? $overlay[$uid] : $master[$uid];
            }
            $items = array_values($new);
        }

        return $items;
    }

    /************************
     * Manipulation methods *
     ************************/

    /**
     * Return an instantiated dummy model without any content.
     *
     * This is used only to access several model info methods like
     * getTableName(), getColumnNames() etc.
     *
     * @return \Sys25\RnBase\Domain\Model\RecordInterface
     */
    public function getEmptyModel()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($this->getWrapperClass());
    }

    /**
     * Liefert die PageId für diese Tabelle.
     * Dies kann überschrieben werden, um individuelle pid's zu setzen.
     *
     * @return int
     */
    protected function getPid()
    {
        return tx_mklib_util_MiscTools::getPortalPageId();
    }

    /**
     * Create a new record.
     *
     * Note that the PID derived from the EXT:mklib constant "portalPageId"
     * is inserted.
     *
     * @TODO: should be protected, not public. handleCreation is public!
     *
     * @param array  $data
     * @param string $table
     *
     * @return int UID of just created record
     */
    public function create(array $data)
    {
        $model = $this->getEmptyModel();
        $table = $model->getTableName();

        $data = \Sys25\RnBase\Backend\Utility\TCA::eleminateNonTcaColumns($model, $data);
        $data = $this->secureFromCrossSiteScripting($model, $data);

        // setzen wir nur, wenn noch nicht gesetzt!
        // Achtung: wird durch eleminateNonTcaColumns entfernt,
        // wenn pid nicht in der tca steht.
        if (empty($data['pid'])) {
            $data['pid'] = $this->getPid();
        }

        $uid = $this->getDatabaseUtility()->doInsert($table, $data/* , 1 */);

        return $uid;
    }

    /**
     * Save model with new data.
     *
     * Overwrite this method to specify a specialised method signature,
     * and just call THIS method via parent::handleUpdate().
     * Additionally, the deriving implementation may perform further checks etc.
     *
     * @param \Sys25\RnBase\Domain\Model\RecordInterface $model         this model is being updated
     * @param array                                  $data          New data
     * @param string                                 $where         Override default restriction by defining an explicite where clause
     * @param int                                    $debug         Set to 1 to debug sql-String
     * @param mixed                                  $noQuoteFields Array or commaseparated string with fieldnames
     *
     * @return \Sys25\RnBase\Domain\Model\RecordInterface Updated model
     */
    public function handleUpdate(
        \Sys25\RnBase\Domain\Model\RecordInterface $model,
        array $data,
        $where = '',
        $debug = 0,
        $noQuoteFields = ''
    ) {
        $db = $this->getDatabaseUtility();
        $table = $model->getTableName();
        $uid = $model->getUid();

        if (!$where) {
            $where = sprintf(
                '1=1 AND `%s`.`uid`=%s',
                $table,
                $db->fullQuoteStr($uid, $table)
            );
        }

        // remove uid if exists
        if (array_key_exists('uid', $data)) {
            unset($data['uid']);
        }

        // Eleminate columns not in TCA
        $data = \Sys25\RnBase\Backend\Utility\TCA::eleminateNonTcaColumns($model, $data);
        $data = $this->secureFromCrossSiteScripting($model, $data);

        $db->doUpdate($table, $where, $data, $debug, $noQuoteFields);

        $model->reset();

        return $model;
    }

    /**
     * @return Tx_Mklib_Database_Connection
     */
    protected function getDatabaseUtility()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Mklib_Database_Connection');
    }

    /**
     * Wrapper for actual deletion.
     *
     * Delete records according to given ready-constructed "where" condition and deletion mode
     *
     * @TODO: use tx_mklib_util_TCA::getEnableColumn to get enablecolumns!
     * @TODO: should be protected, not public. handleDelete is public!
     *
     * @param string $table
     * @param string $where Ready-to-use where condition containing uid restriction
     * @param int    $mode  @see self::handleDelete()
     *
     * @return int anzahl der betroffenen zeilen
     */
    public static function delete($table, $where, $mode)
    {
        // @TODO: could not call $this->getDatabaseUtility in static context.
        return Tx_Mklib_Database_Connection::getInstance()->delete($table, $where, $mode);
    }

    /**
     * Delete one model.
     *
     * Overwrite this method to specify a specialised method signature,
     * and just call THIS method via parent::handleDelete().
     * Additionally, the deriving implementation may perform further checks etc.
     *
     * @param \Sys25\RnBase\Domain\Model\RecordInterface $model this model is being updated
     * @param string                                 $where Override default restriction by defining an explicite where clause
     * @param int                                    $mode  deletion mode with the following options: 0: Hide record; 1: Soft-delete (via "deleted" field) record; 2: Really DELETE record
     * @param int                                    $table Wenn eine Tabelle angegeben wird, wird die des Models missachtet (wichtig für temp anzeigen)
     *
     * @return \Sys25\RnBase\Domain\Model\RecordInterface updated (on success actually empty) model
     */
    public function handleDelete(\Sys25\RnBase\Domain\Model\RecordInterface $model, $where = '', $mode = 0, $table = null)
    {
        if (empty($table)) {
            $table = $model->getTableName();
        }

        $uid = $model->getUid();

        if (!$where) {
            $where = sprintf(
                '1=1 AND `%s`.`uid`=%s',
                $table,
                $this->getDatabaseUtility()->fullQuoteStr($uid, $table)
            );
        }

        $this->delete($table, $where, $mode);

        $model->reset();

        return $model;
    }

    /**
     * Einen Datensatz in der DB anlegen.
     *
     * Diese Methode kann in Child-Klassen einfach überschrieben werden um zusätzliche Funktionen
     * zu implementieren. Dann natürlich nicht vergessen diese Methode via parent::handleCreation()
     * aufzurufen ;)
     *
     * @param array $data
     *
     * @return \Sys25\RnBase\Domain\Model\RecordInterface created model
     */
    public function handleCreation(array $data)
    {
        // datensatz anlegen and model holen
        $model = $this->findByUid(
            $this->create($data)
        );

        return $model;
    }

    /**
     * Clears the complete table.
     */
    public function truncate()
    {
        $table = $this->getEmptyModel()->getTableName();

        return $this->getDatabaseUtility()->doQuery('TRUNCATE TABLE '.$table);
    }

    /**
     * Schützt die Felder vor Cross-Site-Scripting.
     *
     * @TODO: model has to implement interface!
     *
     * @param \Sys25\RnBase\Domain\Model\RecordInterface $model
     * @param array                                  $data
     *
     * @return array
     */
    protected function secureFromCrossSiteScripting($model, array $data)
    {
        if (!method_exists($model, 'getFieldsToBeStripped')) {
            return $data;
        }
        $tags = method_exists($model, 'getTagsToBeIgnoredFromStripping') ? $model->getTagsToBeIgnoredFromStripping() : null;
        foreach ($model->getFieldsToBeStripped() as $field) {
            if (isset($data[$field])) {
                $data[$field] = strip_tags($data[$field], $tags);
            }
        }

        return $data;
    }
}
