<?php

/**
 * benötigte Klassen einbinden.
 */

/*
 * @FIXME: ist das nötig? es wird auch exceptions hageln,
 * wenn eine extension diese abstrakte klasse implementiert, installiert wird,
 * mklib nocht nicht geladen ist. selbst wenn sie als depends mklib enthält.
 * das sollte unbedingt umgestellt werden!
 * auf jeden fall sollte das umgestellt werden, das hier ist nur eine quick and dirty lösung!
 */
// wenn mklib installiert wird, funktioniert der aufruf extPath natürlich nicht und wirft eine exception
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('mklib')) {
    require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mklib', 'class.abstract_ext_update.php');
} // ist de pfad bereits gesetzt?
elseif (isset($GLOBALS['absPath'])) {
    require_once $GLOBALS['absPath'].'class.abstract_ext_update.php';
} // ist de pfad bereits gesetzt?
elseif (isset($absPath)) {
    require_once $absPath.'class.abstract_ext_update.php';
} // weitere ausführung abbrechen
else {
    // klasse mus erstellt. access liefert false um weitere aufrufe zu verhindern
    class ext_update
    {
        public function access()
        {
            return false;
        }
    }

    return '';
}

/**
 * Class for updating the db.
 *
 * @author   Michael Wagner <michael.wagner@dmk-ebusiness.de>
 * @author   Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class ext_update extends abstract_ext_update
{
    /**
     * Liefert den Namen der Extension für die.
     *
     * @return string
     */
    protected function getExtensionName()
    {
        return 'mklib';
    }

    /**
     * Liefert die Nachricht, was gemacht werden soll.
     *
     * @return string
     */
    protected function getInfoMsg()
    {
        return '<p>Update the Static Info Tables with new zip code rules.<br /></p>';
    }

    /**
     * Liefert die Nachricht, was gemacht werden soll.
     *
     * @return string
     */
    protected function getSuccessMsg()
    {
        return '<p><big><strong>Import done.</strong></big></p>';
    }
}
