<?php

/**
 * benötigte Klassen einbinden.
 */

/**
 * Base service class.
 *
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 *
 * @deprecated use tx_mklib_repository_Abstract instead
 */
abstract class tx_mklib_srv_Base extends Tx_Rnbase_Service_Base
{
    // 0: Hide record; 1: Soft-delete (via "deleted" field) record; 2: Really DELETE
    const DELETION_MODE_HIDE = 0;
    const DELETION_MODE_SOFTDELETE = 1;
    const DELETION_MODE_REALLYDELETE = 2;

    /**
     * Return name of search class.
     *
     * @return string
     */
    abstract public function getSearchClass();

    /**
     * @return tx_rnbase_util_SearchBase
     */
    protected function getSearcher()
    {
        return tx_rnbase_util_SearchBase::getInstance($this->getSearchClass());
    }

    /**
     * @TODO:   Achtung,
     *          tx_rnbase_util_SearchBase::getWrapperClass() ist eigentlich protected!
     *
     * @return string
     */
    protected function getWrapperClass()
    {
        return $this->getSearcher()->getWrapperClass();
    }

    /**
     * Search database.
     *
     * @param array $fields
     * @param array $options
     *
     * @return array[Tx_Rnbase_Domain_Model_RecordInterface]
     */
    public function search($fields, $options)
    {
        $this->handleEnableFieldsOptions($fields, $options);
        $this->handleLanguageOptions($fields, $options);
        $items = $this->getSearcher()->search($fields, $options);

        return $this->prepareItems($items, $options);
    }

    /**
     * On default, return hidden fields in backend.
     *
     * @param array $fields
     * @param array $options
     */
    protected function handleEnableFieldsOptions(&$fields, &$options)
    {
        if (TYPO3_MODE == 'BE' &&
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
     * @param array $fields
     * @param array $options
     */
    protected function handleLanguageOptions(&$fields, &$options)
    {
        if (!isset($options['i18n'])
            && !isset($options['ignorei18n'])
            && !isset($options['enablefieldsoff'])
        ) {
            $tableName = $this->getDummyModel()->getTableName();
            $languageField = tx_mklib_util_TCA::getLanguageField($tableName);
            // Die Sprache prüfen wir nur, wenn ein Sprachfeld gesetzt ist.
            if (!empty($languageField)) {
                $tsfe = tx_rnbase_util_TYPO3::getTSFE();
                $languages = [];
                if (isset($options['additionali18n'])) {
                    $languages = tx_rnbase_util_Strings::trimExplode(',', $options['additionali18n'], true);
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
     * @return array[Tx_Rnbase_Domain_Model_RecordInterface]
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
     * @return array[Tx_Rnbase_Domain_Model_RecordInterface]
     */
    protected function uniqueItems(array $items, $options)
    {
        // uniqueue, if there are models and the distinct option
        if (reset($items) instanceof Tx_Rnbase_Domain_Model_RecordInterface
            && isset($options['distinct'])
            && $options['distinct']
        ) {
            $master = $overlay = [];
            /* @var $item Tx_Rnbase_Domain_Model_RecordInterface */
            foreach ($items as $item) {
                $uid = $item->getUid();
                if ($uid === $item->uid) {
                    $master[$uid] = $item;
                } else {
                    $overlay[$uid] = $item;
                }
            }
            // array_merge doesnt work, it resets the numerical keys.
            // so twoe models with the same master uid in both arrays
            // are both existance in the merged array
            // the + array union operator doesnt overide exiting keys
            // and adds only new keys
            $items = ($overlay + $master);
            // we put back the keys
            $items = array_values($items);
        }

        return $items;
    }

    /**
     * @param array $fields
     * @param array $options
     */
    public function searchSingle($fields, $options)
    {
        $options['limit'] = 1;
        $result = $this->search($fields, $options);

        return $result ? $result[0] : null;
    }

    /**
     * Liefert das erste Element aus dem Ergebniss.
     * Es wird eine Warnung in die Devlog geschrieben,
     * wenn mehr als ein Element enthalten ist.
     *
     * @param array $items
     *
     * @return Tx_Rnbase_Domain_Model_RecordInterface
     */
    protected function getFirstResult(array $items)
    {
        $result = $this->limitResults($items, 1);

        return empty($result) ? null : reset($result);
    }

    /**
     * Kürzt die Elemente auf die gewünschte Größe.
     *
     * Es wird eine Warnung in die Devlog geschrieben,
     * wenn mehr als die angegebene Anzahl Element enthalten ist.
     *
     * Wenn keine Warnung erzeugt werden soll,
     * muss bei dem $this->search() Aufruf ein Limit mitgegebenwerden
     * um die Elemente zu beschränken! Oder auch $this->searchSingle aufrufen.
     *
     * @param array        $items
     * @param unknown_type $limit
     * @param array        $options
     *
     * @return array
     */
    protected function limitResults(array $items, $limit, array $options = [])
    {
        // Leer, wir haben nichts zu tun.
        if (empty($items)) {
            return $items;
        }
        $count = count($items);
        // Nur bearbeiten, wenn mehr Elemente vorhanden sind, als notwendig.
        if ($count > $limit) {
            // Wir kürzen die Elemente.
            $items = array_slice($items, 0, $limit);
            // Wir Schreiben einen Log-Eintrag um den Fehler zu melden.
            tx_rnbase_util_Logger::warn(
                'There are more elements('.$count.') for limitResults supplied than expected('.$limit.').',
                'mkhoga'
            );
        }

        return $items;
    }

    /**
     * Search the item for the given uid.
     * liefert immer ein model, auch wenn kein datensatz zur uid existiert.
     *
     * @param int $ct
     *
     * @return Tx_Rnbase_Domain_Model_RecordInterface
     */
    public function get($uid)
    {
        return tx_rnbase::makeInstance($this->getWrapperClass(), $uid);
    }

    /**
     * Holt einen bestimmten Datensatz aus dem Repo.
     *
     * @param int|array $rowOrUid
     *
     * @return Tx_Rnbase_Domain_Model_RecordInterface|null
     */
    public function findByUid($rowOrUid)
    {
        $model = $this->get($rowOrUid);

        return $model->isPersisted() && $model->isValid() ? $model : null;
    }

    /**
     * Find all records.
     *
     * @return array[Tx_Rnbase_Domain_Model_RecordInterface]
     */
    public function findAll()
    {
        return $this->search([], []);
    }

    /************************
     * Manipulation methods *
     ************************/

    /**
     * Dummy model instance.
     *
     * @var Tx_Rnbase_Domain_Model_RecordInterface
     */
    protected $dummyModel;

    /**
     * Return an instantiated dummy model without any content.
     *
     * This is used only to access several model info methods like
     * getTableName(), getColumnNames() etc.
     *
     * @return Tx_Rnbase_Domain_Model_RecordInterface
     */
    protected function getDummyModel()
    {
        if (!$this->dummyModel) {
            $this->dummyModel = tx_rnbase::makeInstance($this->getWrapperClass(), ['uid' => 0]);
        }

        return $this->dummyModel;
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
     * @param array  $data
     * @param string $table
     *
     * @return int UID of just created record
     */
    public function create(array $data)
    {
        $model = $this->getDummyModel();
        $table = $model->getTableName();

        $data = tx_mklib_util_TCA::eleminateNonTcaColumns($model, $data);
        $data = $this->secureFromCrossSiteScripting($model, $data);

        // setzen wir nur, wenn noch nicht gesetzt!
        // Achtung: wird durch eleminateNonTcaColumns entfernt,
        // wenn pid nicht in der tca steht.
        if (empty($data['pid'])) {
            $data['pid'] = $this->getPid();
        }

        $uid = tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doInsert($table, $data/*, 1*/);

        return $uid;
    }

    /**
     * Save model with new data.
     *
     * Overwrite this method to specify a specialised method signature,
     * and just call THIS method via parent::handleUpdate().
     * Additionally, the deriving implementation may perform further checks etc.
     *
     * @param Tx_Rnbase_Domain_Model_RecordInterface $model this model is being updated
     * @param array                                  $data  New data
     * @param string                                 $where Override default restriction by defining an explicite where clause
     *
     * @return Tx_Rnbase_Domain_Model_RecordInterface Updated model
     */
    public function handleUpdate(Tx_Rnbase_Domain_Model_RecordInterface $model, array $data, $where = '')
    {
        $table = $model->getTableName();
        $uid = $model->getUid();

        if (!$where) {
            $where = '1=1 AND `'.$table.'`.`uid`='.\Tx_Rnbase_Database_Connection::getInstance()->fullQuoteStr($uid, $table);
        }

        // remove uid if exists
        if (array_key_exists('uid', $data)) {
            unset($data['uid']);
        }

        // Eleminate columns not in TCA
        $data = tx_mklib_util_TCA::eleminateNonTcaColumns($model, $data);
        $data = $this->secureFromCrossSiteScripting($model, $data);

        tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doUpdate($table, $where, $data);

        $model->reset();

        return $model;
    }

    /**
     * Wrapper for actual deletion.
     *
     * Delete records according to given ready-constructed "where" condition and deletion mode
     *
     * @TODO: use tx_mklib_util_TCA::getEnableColumn to get enablecolumns!
     *
     * @param string $table
     * @param string $where Ready-to-use where condition containing uid restriction
     * @param int    $mode  @see self::handleDelete()
     *
     * @return int anzahl der betroffenen zeilen
     */
    public static function delete($table, $where, $mode)
    {
        return tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->delete($table, $where, $mode);
    }

    /**
     * Delete one model.
     *
     * Overwrite this method to specify a specialised method signature,
     * and just call THIS method via parent::handleDelete().
     * Additionally, the deriving implementation may perform further checks etc.
     *
     * @param Tx_Rnbase_Domain_Model_RecordInterface $model this model is being updated
     * @param string                                 $where Override default restriction by defining an explicite where clause
     * @param int                                    $mode  deletion mode with the following options: 0: Hide record; 1: Soft-delete (via "deleted" field) record; 2: Really DELETE record
     * @param int                                    $table Wenn eine Tabelle angegeben wird, wird die des Models missachtet (wichtig für temp anzeigen)
     *
     * @return Tx_Rnbase_Domain_Model_RecordInterface updated (on success actually empty) model
     */
    public function handleDelete(Tx_Rnbase_Domain_Model_RecordInterface $model, $where = '', $mode = 0, $table = null)
    {
        if (empty($table)) {
            $table = $model->getTableName();
        }

        $uid = $model->getUid();

        if (!$where) {
            $where = '1=1 AND `'.$table.'`.`uid`='.\Tx_Rnbase_Database_Connection::getInstance()->fullQuoteStr($uid, $table);
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
     * @return Tx_Rnbase_Domain_Model_RecordInterface created model
     */
    public function handleCreation(array $data)
    {
        // datensatz anlegen and model holen
        $model = $this->get(
            $this->create($data)
        );

        return $model;
    }

    /**
     * Clears the complete table.
     */
    public function truncate()
    {
        $table = $this->getDummyModel()->getTableName();

        return tx_rnbase::makeInstance('Tx_Mklib_Database_Connection')->doQuery('TRUNCATE TABLE '.$table);
    }

    /**
     * Schützt die Felder vor Cross-Site-Scripting.
     *
     * @TODO: model has to implement interface!
     *
     * @param Tx_Rnbase_Domain_Model_RecordInterface $model
     * @param array                                  $data
     *
     * @return array
     */
    private function secureFromCrossSiteScripting($model, array $data)
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

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Base.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Base.php'];
}
