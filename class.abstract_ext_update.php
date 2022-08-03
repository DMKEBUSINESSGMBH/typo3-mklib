<?php

/**
 * Class for updating the db.
 *
 * @author   Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
abstract class abstract_ext_update
{
    /**
     * @var \TYPO3\CMS\Core\Charset\CharsetConverter or t3lib_cs
     */
    private $csconv = false;

    /**
     * Main function, returning the HTML content of the update module.
     *
     * @return string HTML
     */
    public function main()
    {
        $fieldsets = [];
        $fieldsets['Character encoding'] = $this->getDestEncodingSelect();
        $fieldsets['Update Static Info Tables'] = $this->handleUpdateStaticInfoTables();

        $content = '';
        $content .= '<form action="'.htmlspecialchars(\Sys25\RnBase\Utility\Link::linkThisScript()).'" method="post">';
        foreach ($fieldsets as $legend => $fieldset) {
            $content .= '<fieldset>';
            if ($legend && !is_numeric($legend)) {
                $content .= '<legend><strong>&nbsp;'.$legend.'&nbsp;</strong></legend>';
            }
            $content .= $fieldset;
            $content .= '</fieldset>';
            $content .= '<p><br /></p>';
        }

        $content .= '<p><input type="submit" /></p>';
        $content .= '</form>';

        return $content;
    }

    /**
     * Erzeugt die Selectbox für das encoding.
     *
     * @return string
     */
    private function getDestEncodingSelect()
    {
        require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('static_info_tables', 'class.tx_staticinfotables_encoding.php');
        $content = '';
        $content .= '<label>Destination character encoding:</label>';
        $content .= tx_staticinfotables_encoding::getEncodingSelect('dest_encoding', '', 'utf-8');
        $content .= '<p>(The character encoding must match the encoding of the existing tables data. By default this is UTF-8.)</p>';
        if ($destEncoding = $this->getDestEncoding()) {
            $content .= '<p>Current encoding: '.htmlspecialchars($destEncoding).'</p>';
        }

        return $content;
    }

    /**
     * @return string
     */
    private function getDestEncoding()
    {
        return \Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('dest_encoding');
    }

    /**
     * @TODO prüfen ob der import bereits durchgeführt wurde.
     *
     * @return string
     */
    private function handleUpdateStaticInfoTables()
    {
        $updateKey = $this->getStatementKey();

        $content = '';
        $content .= $this->getInfoMsg();

        if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $content .= '<p><strong>The extension static_info_tables needs to be installed first!</strong></p>';
        } else {
            if (\Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter($updateKey)) {
                if (true === ($ret = $this->queryDB($updateKey))) {
                    $content .= $this->getSuccessMsg();
                } else {
                    $content .= '<p><big><strong>'.$ret.'</strong></big></p>';
                }
            } else {
                $content .= '<p><br /><input type="checkbox" name="'.$updateKey.'" id="'.$updateKey.'" /> <label for="'.$updateKey.'">'.$this->getCheckboxLabel().'</label></p>';
            }
        }

        return $content;
    }

    /**
     * Liefert das Label für die Checkbox
     * Enter description here ...
     */
    protected function getCheckboxLabel()
    {
        return 'import static info tables';
    }

    private function queryDB($updateKey)
    {
        $file = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->getExtensionName(), $this->getSqlFileName());
        $fileContent = explode("\n", \Sys25\RnBase\Utility\Network::getUrl($file));
        if (!$fileContent) {
            return $this->getSqlFileName().' not found! ('.$file.')';
        }

        $destEncoding = $this->getDestEncoding();
        $querys = [];
        $keyQuery = 0;
        foreach ($fileContent as $line) {
            $line = trim($line);
            // nach dem ende des update keys suchen
            if ($keyQuery && \Sys25\RnBase\Utility\Strings::isFirstPartOfStr($line, '#'.$updateKey)) {
                $keyQuery = 2;
                break; // alle satements gefunden schleife nicht mehr durchlaufen
            }
            // nach dem anfang des update keys suchen
            if (!$keyQuery && \Sys25\RnBase\Utility\Strings::isFirstPartOfStr($line, '#'.$updateKey)) {
                $keyQuery = 1; // key gefunden, jetzt folgen die statements
                continue;
            }
            // der update key wurde noch nicht erreicht
            if (!$keyQuery) {
                continue;
            }
            if ($line && \Sys25\RnBase\Utility\Strings::isFirstPartOfStr($line, $this->getSqlMode())) {
                // ggf. das encoding ändern
                $querys[] = $this->getUpdateEncoded($line, $destEncoding);
            }
        }

        switch ($keyQuery) {
            case 0:
                return 'No '.strtolower($this->getSqlMode()).' key not found. ('.$updateKey.')';
            case 1:
                return 'End key not found. ('.$updateKey.')';
            case 2:
            // alles ok
            }

        if (0 === count($querys)) {
            return 'No queries found. ('.$updateKey.')';
        }
        foreach ($querys as $query) {
            \Sys25\RnBase\Database\Connection::getInstance()->doQuery($query);
        }

        return true;
    }

    /**
     * Sollen Updates, Inserts ausgeführt werden?
     *
     * @return string
     */
    protected function getSqlMode()
    {
        return 'UPDATE';
    }

    /**
     * @return \TYPO3\CMS\Core\Charset\CharsetConverter or t3lib_cs
     */
    private function getCharsetsConversion()
    {
        if (!$this->csconv) {
            $charsetConverterClass = '\TYPO3\CMS\Core\Charset\CharsetConverter';
            $this->csconv = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($charsetConverterClass);
        }

        return $this->csconv;
    }

    /**
     * Convert the values of a SQL update statement to a different encoding than UTF-8.
     *
     * @param string $query        Update statement like: UPDATE static_countries SET zipcode_rule='2', zipcode_length='5' WHERE cn_iso_2='DE';
     * @param string $destEncoding Destination encoding
     *
     * @return string Converted update statement
     */
    private function getUpdateEncoded($query, $destEncoding)
    {
        if (!('utf-8' === $destEncoding)) {
            $queryElements = explode('WHERE', $query);
            $where = preg_replace('#;$#', '', trim($queryElements[1]));
            $queryElements = explode('SET', $queryElements[0]);
            $queryFields = $queryElements[1];

            $queryElements = \Sys25\RnBase\Utility\Strings::trimExplode('UPDATE', $queryElements[0], 1);
            $table = $queryElements[0];

            $fields_values = [];
            $queryFieldsArray = \Sys25\RnBase\Utility\Strings::trimExplode(',', $queryFields, 1);
            foreach ($queryFieldsArray as $fieldsSet) {
                $col = \Sys25\RnBase\Utility\Strings::trimExplode('=', $fieldsSet, 1);
                $value = stripslashes(substr($col[1], 1, strlen($col[1]) - 2));
                $value = $this->getCharsetsConversion()->conv($value, 'utf-8', $destEncoding);
                $fields_values[$col[0]] = $value;
            }
            $query = \Sys25\RnBase\Database\Connection::getInstance()->doUpdate(
                $table,
                $where,
                $fields_values,
                ['sqlonly' => true]
            );
        }

        return $query;
    }

    public function access()
    {
        return true;
    }

    /**
     * Liefert den Namen der Datei, welche die Update Statements beinhaltet.
     *
     * @return string
     */
    protected function getSqlFileName()
    {
        return 'ext_tables_static_update.sql';
    }

    /**
     * @return string
     */
    protected function getStatementKey()
    {
        return 'importStaticInfoTables';
    }

    /**
     * Liefert den Namen der Extension für die.
     *
     * @return string
     */
    abstract protected function getExtensionName();

    /**
     * Liefert die Nachricht, was gemacht werden soll.
     *
     * @return string
     */
    abstract protected function getInfoMsg();

    /**
     * Liefert die Nachricht für den Erfolgsfall.
     *
     * @return string
     */
    abstract protected function getSuccessMsg();
}
