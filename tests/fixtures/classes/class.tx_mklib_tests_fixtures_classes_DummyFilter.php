<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Rene Nitzsche
 *  Contact: rene@system25.de
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/

class tx_mklib_tests_fixtures_classes_DummyFilter extends tx_rnbase_filter_BaseFilter
{
    /**
     * Abgeleitete Filter können diese Methode überschreiben und zusätzlich Filter setzen.
     *
     * @param array                    $fields
     * @param array                    $options
     * @param tx_rnbase_parameters     $parameters
     * @param tx_rnbase_configurations $configurations
     * @param string                   $confId
     *
     * @return bool
     */
    protected function initFilter(&$fields, &$options, &$parameters, &$configurations, $confId)
    {
        $fields['test'] = 'value';

        return true;
    }

    public static function handlePageBrowser(&$configurations, $confid, &$viewdata, &$fields, &$options, $cfg = [])
    {
        //damit wir im test sehen ob alles korrekt übergeben wurde
        $viewdata->offsetSet('pageBrowserConfig', [
            'config' => $configurations,
            'confid' => $confid,
            'fields' => $fields,
            'options' => $options,
            'cfg' => $cfg,
        ]);
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/rn_base/filter/class.tx_rnbase_filter_BaseFilter.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/rn_base/filter/class.tx_rnbase_filter_BaseFilter.php'];
}
