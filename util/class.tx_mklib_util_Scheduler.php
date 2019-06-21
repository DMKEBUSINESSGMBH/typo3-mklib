<?php

/**
 * tx_mklib_util_Scheduler.
 *
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_util_Scheduler
{
    /**
     * formatiert die sekunden als eine leserliche ausgabe
     * wie 1 minute 30 sekunden.
     *
     * @param int $seconds
     *
     * @return string
     */
    public static function getFormattedTime($seconds)
    {
        $time = array();
        $time['hours'] = floor($seconds / 3600);
        $time['minutes'] = floor(($seconds - $time['hours'] * 3600) / 60);
        $time['seconds'] = $seconds - $time['hours'] * 3600 - $time['minutes'] * 60;

        $formattedTime = '';
        foreach ($time as $timePart => $value) {
            if ($value < 1) {
                continue; //null wollen wir nicht sehen
            }
            //else
            $labelKey = 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_formattedtime_'.
                        $timePart.'_'.(($value > 1) ? 'plural' : 'singular');
            $formattedTime .= sprintf('%01d', $value).' '.
                                $GLOBALS['LANG']->sL($labelKey).' ';
        }

        return $formattedTime;
    }
}
