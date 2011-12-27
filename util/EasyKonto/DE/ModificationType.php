<?php
/**
 * PHP SDK for the easyKonto web service.
 *
 * @category EasyKonto
 * @package EasyKonto_DE
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 * @since 2.0
 */

/**
 * Modification type enum.
 *
 * @package EasyKonto_DE
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 */
final class EasyKonto_DE_ModificationType {

    /**
     * The record has been added in this version.
     *
     * @var string
     */
    const ADDED = 'ADDED';

    /**
     * The record has been modified in this version.
     *
     * @var string
     */
    const MODIFIED = 'MODIFIED';

    /**
     * The record has not been changed in this version.
     *
     * @var string
     */
    const UNCHANGED = 'UNCHANGED';

    /**
     * The record has been deleted in this version.
     *
     * @var string
     */
    const DELETED = 'DELETED';

    private function __construct() {
    }

}
