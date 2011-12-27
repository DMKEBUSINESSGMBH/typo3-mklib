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
 * Check result for bank account checks.
 *
 * @package EasyKonto_DE
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 */
final class EasyKonto_DE_CheckResult {

    /**
     * The check result type for the checked account.
     *
     * @var string
     * @see EasyKonto_DE_CheckResultType
     */
    private $checkResultType;

    /**
     * The bank record for the checked account.
     *
     * @var EasyKonto_DE_BankRecord
     */
    private $bankRecord;

    /**
     * Constructs an immutable instance of this class (for internal use only).
     *
     * @param string $checkResultType the check result type for the checked account
     * @param EasyKonto_DE_BankRecord $bankRecord the bank record for the checked account
     */
    public function __construct($checkResultType, $bankRecord) {
        $this->checkResultType = $checkResultType;
        $this->bankRecord = $bankRecord;
    }

    /**
     * Gets the check result type for the checked account.
     *
     * @return string the check result type for the checked account, never null.
     * @see EasyKonto_DE_CheckResultType
     * @see isValid()
     */
    public function getCheckResultType() {
        return $this->checkResultType;
    }

    /**
     * Convenience method that returns true if getCheckResultType() returns
     * either EasyKonto_DE_CheckResultType::VALID or
     * EasyKonto_DE_CheckResultType::NOT_CHECKABLE.
     *
     * @return boolean true, if the checked account is valid.
     * @see getCheckResultType()
     */
    public function isValid() {
        return
            $this->checkResultType == EasyKonto_DE_CheckResultType::VALID ||
            $this->checkResultType == EasyKonto_DE_CheckResultType::NOT_CHECKABLE;
    }

    /**
     * Gets the bank record for the checked account.
     *
     * @return EasyKonto_DE_BankRecord the bank record for the checked account,
     *         or null, if the bank code does not exist.
     */
    public function getBankRecord() {
        return $this->bankRecord;
    }

}
