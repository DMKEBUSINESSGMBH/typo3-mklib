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
 * This class provides accessors for all information that are provided for a
 * bank.
 * <p>
 * Please note, that if isDeleted() returns true, this bank record /
 * bank code <b>must not be used anymore</b>. It will be removed in the next
 * version. It only remains available in this version for information purposes.
 * Banks may specify a follow-up bank code - see getFollowUpBankCode()
 * for further details.
 *
 * @package EasyKonto_DE
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 */
final class EasyKonto_DE_BankRecord {

    /**
     * The bank code (german: Bankleitzahl / BLZ) of this bank record.
     *
     * @var integer
     */
    private $bankCode;

    /**
     * If this bank record is the main branch for the specified
     * bank code.
     *
     * @var boolean
     */
    private $mainBranch;

    /**
     * The name of the bank/branch.
     *
     * @var string
     */
    private $description;

    /**
     * The zip code of the bank/branch.
     *
     * @var string
     */
    private $zipCode;

    /**
     * The city of the bank/branch.
     *
     * @var string
     */
    private $city;

    /**
     * The short name of the branch with its location.
     *
     * @var string
     */
    private $briefDescription;

    /**
     * The primary account number.
     *
     * @var string
     */
    private $pan;

    /**
     * The bank identifier code.
     *
     * @var string
     */
    private $bic;

    /**
     * The check method algorithm identifier of the bank.
     *
     * @var string
     */
    private $checkMethod;

    /**
     * The unique bank record number.
     *
     * @var integer
     */
    private $recordId;

    /**
     * The type of modification since the last version.
     *
     * @var string
     */
    private $modificationType;

    /**
     * If a deletion of this bank records bank code is intended.
     *
     * @var boolean
     */
    private $deletionIntended;

    /**
     * The new (follow-up) bank code that is to be used instead of this bank
     * records bank code, or null if no follow-up bank code is specified.
     *
     * @var integer
     */
    private $followUpBankCode;

    /**
     * Constructs an immutable instance of this class (for internal use only).
     *
     * @param integer $bankCode
     *            the bank code (german: Bankleitzahl / BLZ) of this bank record
     *            (mandatory)
     * @param boolean $mainBranch
     *            true, if this bank record is the main branch for the
     *            specified bank code, false otherwise
     * @param string $description
     *            the name of the bank/branch (mandatory)
     * @param string $zipCode
     *            the zip code of the bank/branch (mandatory)
     * @param string $city
     *            the city of the bank/branch (mandatory)
     * @param string $briefDescription
     *            the short name of the branch with its location (mandatory)
     * @param string $pan
     *            the primary account number (optional)
     * @param string $bic
     *            the bank identifier code (optional)
     * @param string $checkMethod
     *            the check method algorithm identifier of the bank (mandatory)
     * @param integer $recordId
     *            the unique bank record number (mandatory)
     * @param string $modificationType
     *            the type of modification since the last version (mandatory)
     * @param boolean $deletionIntended
     *            true if a deletion of this bank records bank code is
     *            intended, false otherwise
     * @param integer $followUpBankCode
     *            the new (follow-up) bank code that is to be used instead of
     *            this bank records bank code, or null, if no follow-up
     *            bank code is specified
     */
    public function __construct($bankCode, $mainBranch, $description, $zipCode, $city, $briefDescription, $pan, $bic,
            $checkMethod, $recordId, $modificationType, $deletionIntended, $followUpBankCode) {
        $this->bankCode = $bankCode;
        $this->mainBranch = $mainBranch;
        $this->description = $description;
        $this->zipCode = $zipCode;
        $this->city = $city;
        $this->briefDescription = $briefDescription;
        $this->pan = $pan;
        $this->bic = $bic;
        $this->checkMethod = $checkMethod;
        $this->recordId = $recordId;
        $this->modificationType = $modificationType;
        $this->deletionIntended = $deletionIntended;
        $this->followUpBankCode = $followUpBankCode;
    }

    /**
     * Gets the bank code (german: Bankleitzahl / BLZ) of this bank record.
     *
     * @return integer the bank code
     */
    public function getBankCode() {
        return $this->bankCode;
    }

    /**
     * Different branches of a bank can share a single bank code, but only one
     * branch of a bank is specified to be the main branch (german:
     * bankleitzahlführender Zahlungsdienstleister). Only main branches are used
     * for payment transactions, so usually you can ignore all non main
     * branches.
     *
     * @return boolean true, if this bank record is the main branch for the
     *         specified bank code, false otherwise
     */
    public function isMainBranch() {
        return $this->mainBranch;
    }

    /**
     * Gets the name of the bank/branch (max. 58 characters long).
     *
     * @return string the name of the bank/branch (never null)
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Gets the zip code of the bank/branch (5 characters long).
     *
     * @return string the zip code of the bank/branch (never null)
     */
    public function getZipCode() {
        return $this->zipCode;
    }

    /**
     * Gets the city of the bank/branch (max. 35 characters long).
     *
     * @return string the city of the bank/branch (never null)
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * Gets the short name of the branch with its location (max. 27 characters
     * long).
     *
     * @return string the short name of the branch with its location (never
     *         null)
     */
    public function getBriefDescription() {
        return $this->briefDescription;
    }

    /**
     * Gets the primary account number (PAN) of the bank/branch (5 characters
     * long).
     *
     * @return string the primary account number (null, if not specified)
     */
    public function getPAN() {
        return $this->pan;
    }

    /**
     * Gets the bank identifier code (BIC) of the bank (11 characters long).
     *
     * @return string the bank identifier code (null, if not specified)
     */
    public function getBIC() {
        return $this->bic;
    }

    /**
     * Every bank defines an algorithm that is used to check the validity of
     * their account numbers. This method returns the identifier of the used
     * check algorithm.
     *
     * @return string the check method algorithm identifier of the bank (2 characters
     *         long) (never null)
     */
    public function getCheckMethod() {
        return $this->checkMethod;
    }

    /**
     * Gets the unique record number used by the Deutsche Bundesbank to identify
     * this bank record.
     *
     * @return integer the unique bank record number
     */
    public function getRecordId() {
        return $this->recordId;
    }

    /**
     * Gets the type of modification on this record since the last version.
     *
     * @return string the type of modification since the last version
     * @see EasyKonto_DE_ModificationType
     * @see isDeleted()
     */
    public function getModificationType() {
        return $this->modificationType;
    }

    /**
     * If a bank intend to delete a bank code in the future, they might announce
     * that.
     *
     * @return boolean true, if a deletion of this bank records bank code is
     *         intended, false otherwise
     */
    public function isDeletionIntended() {
        return $this->deletionIntended;
    }

    /**
     * If this bank records bank code has been deleted (see isDeleted())
     * or a deletion is intended (see isDeletionIntended()), the bank
     * may specify a follow-up bank code that is to be used instead of this
     * records bank code.
     *
     * @return integer a new (follow-up) bank code that is to be used instead of this
     *         bank records bank code, or null, if no follow-up bank code
     *         is specified
     */
    public function getFollowUpBankCode() {
        return $this->followUpBankCode;
    }

    /**
     * Convenience method that returns true if
     * getModificationType() is EasyKonto_DE_ModificationType::DELETED
     * . A follow-up bank code might be specified - see
     * getFollowUpBankCode().
     *
     * @return boolean true, if this bank records bank code has been deleted,
     *         false otherwise
     * @see getModificationType()
     * @see getFollowUpBankCode()
     */
    public function isDeleted() {
        return $this->modificationType == EasyKonto_DE_ModificationType::DELETED;
    }

    public function __toString() {
        return
            'bank code: ' . $this->bankCode . ', ' .
            'main branch: ' . $this->mainBranch . ', ' .
            'description: ' . $this->description . ', ' .
            'zip code: ' . $this->zipCode . ', ' .
            'city: ' . $this->city . ', ' .
            'brief description: ' . $this->briefDescription . ', ' .
            'PAN: ' . $this->pan . ', ' .
            'BIC: ' . $this->bic . ', ' .
            'check method: ' . $this->checkMethod . ', ' .
            'record id: ' . $this->recordId . ', ' .
            'modification type: ' . $this->modificationType . ', ' .
            'deletion intended: ' . $this->deletionIntended . ', ' .
            'follow-up bank code: ' . $this->followUpBankCode;
    }

    /**
     * Returns the array representation of the object.
     *
     * @return array the array representation of the object
     */
    public function toArray() {
        return array(
            'bankCode'         => $this->bankCode,
            'mainBranch'       => $this->mainBranch,
            'description'      => $this->description,
            'zipCode'          => $this->zipCode,
            'city'             => $this->city,
            'briefDescription' => $this->briefDescription,
            'PAN'              => $this->pan,
            'BIC'              => $this->bic,
            'checkMethod'      => $this->checkMethod,
            'recordId'         => $this->recordId,
            'modificationType' => $this->modificationType,
            'deletionIntended' => $this->deletionIntended,
            'followUpBankCode' => $this->followUpBankCode
        );
    }

}
