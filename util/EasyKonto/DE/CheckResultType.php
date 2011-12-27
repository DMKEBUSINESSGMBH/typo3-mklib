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
 * Check result type enums.
 *
 * @package EasyKonto_DE
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 */
final class EasyKonto_DE_CheckResultType {

    /**
     * Valid bank account.
     *
     * @var string
     */
    const VALID = 'VALID';

    /**
     * Invalid bank account.
     *
     * @var string
     */
    const INVALID = 'INVALID';

    /**
     * Bank has not implemented a check algorithm for their account numbers -
     * valid or invalid bank account.
     *
     * @var string
     */
    const NOT_CHECKABLE = 'NOT_CHECKABLE';

    /**
     * Bank code has been deleted recently - invalid bank account
     *
     * @var string
     */
    const DELETED_BANK_CODE = 'DELETED_BANK_CODE';

    /**
     * Unknown bank code - invalid bank account.
     *
     * @var string
     */
    const UNKNOWN_BANK_CODE = 'UNKNOWN_BANK_CODE';

    private function __construct() {
    }

}
