<?php

/**
 * Testfälle für tx_mklib_validator_ZipCode.
 *
 * @author   Michael Wagner <michael.wagner@dmk-ebusiness.de>
 *
 * @group integration
 */
class tx_mklib_tests_validator_ZipCodeTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        if (!tx_rnbase_util_Extensions::isLoaded('static_info_tables')) {
            $this->markTestSkipped('static_info_tables nicht installiert');
        }

        // zur Sicherheit die Zip Code Rules einfügen
        $sqlFilename = tx_rnbase_util_Files::getFileAbsFileName(
            tx_rnbase_util_Extensions::extPath(
                'mklib',
                'ext_tables_static_update.sql'
            )
        );
        if (@is_file($sqlFilename)) {
            try {
                //alle statements importieren
                \DMK\Mklib\Utility\Tests::queryDB($sqlFilename, false, true);
            } catch (RuntimeException $e) {
                $this->markTestSkipped('ext_tables_static_update failed.');
            }
        }
    }

    /**
     * @group integration
     */
    public function testValidateGermanZips()
    {
        self::checkStaticCountries();

        $country = self::getStaticCountryModel(54 /*DE*/);

        self::assertTrue(is_object($country), 'No model given.');
        self::assertTrue($country->isValid(), 'No valid model given.');
        self::assertEquals('DE', $country->getISO2(), 'No or wrong iso 2 given.');
        self::assertEquals(5, $country->getZipLength(), 'No or wrong  zip length given.');
        self::assertEquals(4, $country->getZipRule(), 'No or wrong  zip rule given.');

        $validator = tx_mklib_validator_ZipCode::getInstance();

        $zips = ['09113', '14482'];
        foreach ($zips as $zip) {
            self::assertTrue(
                $validator->validate($country, $zip),
                $zip.' -> '.$validator->getFormatInfo($country)
            );
        }
        $zips = ['9120', 'O9113'];
        foreach ($zips as $zip) {
            self::assertFalse(
                $validator->validate($country, $zip),
                $zip.' -> '.$validator->getFormatInfo($country)
            );
        }
    }

    /**
     * @dataProvider providerValidatorRules
     *
     * @param string $zip
     * @param int    $countryUid
     * @param bool   $result
     *
     * @group integration
     */
    public function testValidatorRules($zip, $countryUid, $result)
    {
        self::checkStaticCountries();
        $country = self::getStaticCountryModel($countryUid);
        $validator = tx_mklib_validator_ZipCode::getInstance();
        self::assertEquals(
            $result,
            $validator->validate($country, $zip),
            $zip.' -> '.$validator->getFormatInfo($country)
        );
    }

    /**
     * @return multitype:multitype:string number boolean
     */
    public function providerValidatorRules()
    {
        $return = [];
        foreach ([
                         // array($iZip, $country, $result),
                __LINE__ => ['09113', (54 /*DE*/), true],
                __LINE__ => ['9120', (54 /*DE*/), false],
                __LINE__ => ['föllig egal, keine rules!', (130 /*MA*/), true],
                __LINE__ => ['70014', (85 /*GR*/), false],
                __LINE__ => ['700 14', (85 /*GR*/), true],
                __LINE__ => ['irgendwasabermax9', (220 /*US*/), false],
                __LINE__ => ['99825', (220 /*US*/), true],
                __LINE__ => ['4526', (13 /*AT*/), true],
                __LINE__ => ['45267', (13 /*AT*/), false],
                __LINE__ => ['452', (13 /*AT*/), false],
                __LINE__ => ['A0A 0A0', (36 /*CA*/), true],
                __LINE__ => ['9120', (36 /*CA*/), false],
                __LINE__ => ['irgendwas', (36 /*CA*/), false],

                __LINE__ => ['333', (103 /*IS*/), true],
                __LINE__ => ['33', (103 /*IS*/), false],
                __LINE__ => ['3333', (103 /*IS*/), false],
                __LINE__ => ['4444', (20 /*BE*/), true],
                __LINE__ => ['444', (20 /*BE*/), false],
                __LINE__ => ['44444', (20 /*BE*/), false],
                __LINE__ => ['55555', (72 /*FR*/), true],
                __LINE__ => ['5555', (72 /*FR*/), false],
                __LINE__ => ['555555', (72 /*FR*/), false],
                __LINE__ => ['666666', (46 /*CN*/), true],
                __LINE__ => ['66666', (46 /*CN*/), false],
                __LINE__ => ['6666666', (46 /*CN*/), false],
            ] as $key => $row) {
            $key = 'Line:'.strtolower($key).' Zip:'.$row[0].' Country:'.intval($row[1]).' Return:'.intval($row[2]);
            $return[$key] = $row;
        }

        return $return;
    }

    /**
     * Wurden die ZipRules geladen?
     */
    private static function checkStaticCountries()
    {
        $cnt = tx_rnbase_util_DB::doSelect('COUNT(uid) as cnt', 'static_countries', ['enablefieldsoff' => 1, /*'debug'=>1,*/ 'where' => 'zipcode_rule > 0']);
        $loaded = intval($cnt[0]['cnt']) > 0;

        if (!$loaded) {
            // zur Sicherheit die Zip Code Rules einfügen
            $sqlFilename = tx_rnbase_util_Files::getFileAbsFileName(tx_rnbase_util_Extensions::extPath('mklib', 'ext_tables_static_update.sql'));
            if (@is_file($sqlFilename)) {
                \DMK\Mklib\Utility\Tests::queryDB($sqlFilename, false, true); //alle statements importieren
            }
        }
    }

    /**
     * @param mixed $rowOrUid
     *
     * @return tx_mklib_model_StaticCountry
     */
    private static function getStaticCountryModel($rowOrUid)
    {
        return tx_rnbase::makeInstance('tx_mklib_model_StaticCountry', $rowOrUid);
    }
}
