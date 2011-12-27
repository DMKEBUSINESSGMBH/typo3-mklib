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
 * This class implements the easyKonto web service API.
 *
 * @package EasyKonto_DE
 * @copyright Copyright (C) 2011 Oliver Siegmar
 * @license http://www.easykonto.de/license Proprietary license
 */
final class EasyKonto_DE_Service implements EasyKonto_DE_ServiceInterface {

    /**
     * @var EasyKonto_HTTPClient
     */
    private $httpClient;

    public function __construct(EasyKonto_ConnectionConfiguration $connectionConfiguration) {
        $this->httpClient = new EasyKonto_HTTPClient($connectionConfiguration, 'de', 1);
    }

    public function checkAccount($bankCode, $accountNumber) {
        if (!$this->isValidBankCode($bankCode)) {
            return new EasyKonto_DE_CheckResult(
                EasyKonto_DE_CheckResultType::UNKNOWN_BANK_CODE,
                null
            );
        }

        $parameters = array(
            'bankCode' => $bankCode,
            'accountNumber' => $accountNumber
        );
        $data = $this->request('checkAccount', $parameters);

        return new EasyKonto_DE_CheckResult(
            (string)$data->CheckResultType,
            $this->transformXMLToBankDERecord($data->BankRecord)
        );
    }

    public function findBankRecordByBankCode($bankCode) {
        if (!$this->isValidBankCode($bankCode)) {
            return null;
        }
        $parameters = array('bankCode' => $bankCode);
        $data = $this->request('findBankRecordByBankCode', $parameters);
        return $this->transformXMLToBankDERecord($data);
    }

    public function findBankRecordByBIC($bic) {
        if (!$this->isValidBIC($bic)) {
            return null;
        }
        $parameters = array('BIC' => $bic);
        $data = $this->request('findBankRecordByBIC', $parameters);
        return $this->transformXMLToBankDERecord($data);
    }

    public function findBankRecordByPAN($pan) {
        if (!$this->isValidPAN($pan)) {
            return null;
        }
        $parameters = array('PAN' => $pan);
        $data = $this->request('findBankRecordByPAN', $parameters);
        return $this->transformXMLToBankDERecord($data);
    }

    public function findAllBankRecordsByBankCode($bankCode) {
        if (!$this->isValidBankCode($bankCode)) {
            return array();
        }
        $parameters = array('bankCode' => $bankCode);
        $data = $this->request('findAllBankRecordsByBankCode', $parameters);
        return $this->transformXMLToBankDERecords($data);
    }

    private function isValidBankCode($parameter) {
        return preg_match('/^[1-9][0-9]{7}$/', $parameter);
    }

    private function isValidBIC($parameter) {
        return preg_match('/^[a-zA-Z]{6}[a-zA-Z0-9]{2}([a-zA-Z0-9]{3})?$/', $parameter);
    }

    private function isValidPAN($parameter) {
        return preg_match('/^[0-9]{5}$/', $parameter);
    }

    private function request($method, array $parameters) {
        return new SimpleXMLElement($this->httpClient->request($method, $parameters));
    }

    private function transformXMLToBankDERecord(SimpleXMLElement $xmlData) {
        $ret = $this->transformXMLToBankDERecords($xmlData);
        return count($ret) ? $ret[0] : null;
    }

    private function transformXMLToBankDERecords(SimpleXMLElement $xmlData) {
        $ret = array();
        foreach ($xmlData as $e) {
            $ret[] = new EasyKonto_DE_BankRecord(
                (int)$e->BankCode,
                ($e->MainBranch == 'true'),
                (string)$e->Description,
                (string)$e->ZipCode,
                (string)$e->City,
                (string)$e->BriefDescription,
                isset($e->PAN) ? (string)$e->PAN : null,
                isset($e->BIC) ? (string)$e->BIC : null,
                (string)$e->CheckMethod,
                (int)$e->RecordId,
                (string)$e->ModificationType,
                ($e->DeletionIntended == 'true'),
                isset($e->FollowUpBankCode) ? (int)$e->FollowUpBankCode : null
            );
        }
        return $ret;
    }

}
