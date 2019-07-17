<?php

/**
 * Numeric Util Tests.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_tests_util_NumberTest extends tx_rnbase_tests_BaseTestCase
{
    private $oldLocal = null;

    public function setUp()
    {
        parent::setUp();
        $this->oldLocal = setlocale(LC_ALL, 0);
    }

    public function tearDown()
    {
        parent::tearDown();
        setlocale(LC_ALL, $this->oldLocal);
    }

    /**
     * @dataProvider providerFloatVal
     */
    public function testFloatVal($expected, $actual, $config)
    {
        if (!is_array($config)) {
            $config = array();
        }

        // bei einem normalen float sollte nun eine Kommazahl herauskommen.

        self::assertEquals($expected, tx_mklib_util_Number::floatVal($actual, $config));
    }

    /**
     * @dataProvider providerFloatVal
     */
    public function testFloatValLcDe($expected, $actual, $config)
    {
        // Locale auf deutsch stellen.
        // Damit sind Beispielsweise die Dezimaltrennzeichen falsch (,anstatt.)
        setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'deu_deu', 'de', 'ge');

        $this->testFloatVal($expected, $actual, $config);
    }

    public function providerFloatVal()
    {
        return array(
            // über die parseFloat, sollte genau das herauskommen, was wir benötigen
            // ein Float mit einem Punkt als Dezimaltrennzeichen.
            'Line:'.__LINE__ => array('5.43', '5.43', array()),
            'Line:'.__LINE__ => array('-5.43', '-5.43', array()),
            'Line:'.__LINE__ => array('5.43', '5,43', array()),
            'Line:'.__LINE__ => array('-5.43', '-5,43', array()),
            // hierzu muss erst der Todo aus parseFloat abgearbeidet werden.
            // 'Line:'.__LINE__ => array('5435.55', '5.435,55', array()),
            // 'Line:'.__LINE__ => array('5435.55', '5,435.55', array()),
            // Jetzt wollen wir eine Pipe als Dezimaltrennzeichen, nur so zum Spaß ;)
            'Line:'.__LINE__ => array('5|43', '5.43', array('decimal_point' => '|')),
        );
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_NumberTest.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_NumberTest.php'];
}
