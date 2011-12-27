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
 * This interface specifies easyKonto's web service API.
 *
 * @package EasyKonto_DE
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 */
interface EasyKonto_DE_ServiceInterface {

    /**
     * Checks the provided bank code / account number combination for validity.
     *
     * @param integer $bankCode the bank code (german: Bankleitzahl / BLZ)
     * @param integer $accountNumber account number (german: Kontonummer)
     * @throws EasyKonto_Exception if a web service communication error occurs.
     * @return EasyKonto_DE_CheckResult the check result for the provided account
     *         information, never null.
     */
    public function checkAccount($bankCode, $accountNumber);

    /**
     * Finds the main branches bank record by its bank code. See
     * findAllBankRecordsByBankCode() if you want to find all
     * existing branches for a given bank code.
     *
     * @param integer $bankCode the bank code (german: Bankleitzahl / BLZ)
     * @throws EasyKonto_Exception if a web service communication error occurs.
     * @return EasyKonto_DE_BankRecord the bank record found by the provided bank code, null otherwise
     */
    public function findBankRecordByBankCode($bankCode);

    /**
     * Finds a bank record by its unique bank identifier code (BIC).
     *
     * @param string $bic the Bank Identifier Code (aka BIC-Code, SWIFT-Code)
     * @throws EasyKonto_Exception if a web service communication error occurs.
     * @return EasyKonto_DE_BankRecord the bank record found by the provided BIC, null otherwise
     */
    public function findBankRecordByBIC($bic);

    /**
     * Finds a bank record by its unique primary account number (PAN).
     *
     * @param string $pan the Primary Account Number
     * @throws EasyKonto_Exception if a web service communication error occurs.
     * @return EasyKonto_DE_BankRecord the bank record found by the provided PAN, null otherwise
     */
    public function findBankRecordByPAN($pan);

    /**
     * Finds all bank records by the provided bank code. This method returns the
     * main branch and all non main branches of the bank found. Use
     * findBankRecordByBankCode() if you only want the get the main
     * branch. You should usually do not need this method.
     *
     * @param integer $bankCode the bank code (german: Bankleitzahl / BLZ)
     * @throws EasyKonto_Exception if a web service communication error occurs.
     * @return array all bank records found by the the provided bank code, an empty array otherwise (never null)
     */
    public function findAllBankRecordsByBankCode($bankCode);

}
