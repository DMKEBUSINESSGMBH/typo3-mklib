<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 */

/**
 * benötigte Klassen einbinden.
 */

/**
 * Basisklasse für Suchfunktionen in BE-Modulen.
 */
class tx_mklib_mod1_searcher_Base
{
    private $mod;

    protected $selector;

    protected $options;
    protected $formTool;

    protected $uid;

    /**
     * Constructor.
     *
     * @param tx_rnbase_mod_IModule $mod
     * @param unknown_type          $options
     * @param string                $sSelector
     *
     * @return unknown_type
     */
    public function __construct(tx_rnbase_mod_IModule $mod, $options = [], $sSelector = 'tx_mklib_mod1_util_Selector')
    {
        $this->init($mod, $options);
    }

    /**
     * Init object.
     *
     * @param tx_rnbase_mod_IModule $mod
     * @param array                 $options
     * @param string                $sSelector
     */
    protected function init(tx_rnbase_mod_IModule $mod, $options, $sSelector = 'tx_mklib_mod1_util_Selector')
    {
        $this->options = $options;
        $this->mod = $mod;
        $this->formTool = $mod->getFormTool();

        $this->selector = tx_rnbase::makeInstance($sSelector);
        $this->selector->init($mod);
    }

    /**
     * @param unknown_type $srv
     * @param unknown_type $fields
     * @param unknown_type $options
     */
    public function getCount($srv, $fields, $options)
    {
        // Get counted data
        $options['count'] = 1;

        return $srv->search($fields, $options);
    }

    /**
     * Returns an instance of tx_mkhoga_beutil_Selector.
     *
     * @return tx_mkhoga_beutil_Selector
     */
    protected function getSelector()
    {
        return $this->selector;
    }

    /**
     * Returns an instance of tx_rnbase_mod_IModule.
     *
     * @return tx_rnbase_mod_IModule
     */
    public function getModule()
    {
        return $this->mod;
    }

    /**
     * Returns an instance of tx_rnbase_mod_IModule.
     *
     * @return tx_rnbase_mod_IModule
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns an instance of tx_rnbase_mod_IModule.
     *
     * @return tx_rnbase_mod_IModule
     */
    public function getFormTool()
    {
        return $this->formTool;
    }

    /**
     * Liefert die Funktions-Id.
     */
    public function getFuncId()
    {
        return '';
    }

    /**
     * Setzte die Uid des Objekts.
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Gibt die Uid des Objekts.
     */
    public function getUid()
    {
        return $this->uid;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/searcher/class.tx_mklib_mod1_searcher_Base.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/searcher/class.tx_mklib_mod1_searcher_Base.php'];
}
