<?php
/**
 * PHP SDK for the easyKonto web service.
 *
 * @category EasyKonto
 * @package EasyKonto
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 * @since 2.0
 */

/**
 * Connection type enum.
 *
 * @package EasyKonto
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 */
final class EasyKonto_ConnectionType {

    /**
     * This connection type uses a single easyKonto web service instance. The
     * single node configuration is the default connection type - use it, if you
     * don't have ordered the high availibility option.
     *
     * @var integer
     */
    const SINGLE_NODE = 0;

    /**
     * This connection type uses a high availibility easyKonto web service
     * cluster. If you have ordered the high availibility option, use this to
     * achieve high availibility.
     *
     * @var integer
     */
    const HA_CLUSTER = 1;

    private function __construct() {
    }

}
