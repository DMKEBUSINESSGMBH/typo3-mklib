<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_tests_DBTestCaseSkeleton');
tx_rnbase::load('tx_mklib_util_DAM');

/**
 * DB util tests
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 *
 * @group integration
 */
class tx_mklib_tests_util_DAM_testcase extends tx_mklib_tests_DBTestCaseSkeleton {
	protected $importExtensions = array('dam','mklib');
	protected $importDependencies = true;
	protected $importDataSet = array(
		'EXT:mklib/tests/fixtures/db/dam.xml'
	);

	/**
	 * @var string
	 */
	protected $sTempFolder;

	/**
	 * @var string
	 */
	protected $sRelativeImagePath;

	/**
	 * @var string
	 */
	protected $sAbsoluteImagePath;

	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_tests_DBTestCaseSkeleton::setUp()
	 */
	public function setUp() {
		if (!t3lib_extMgm::isLoaded('dam')) {
			$this->markTestSkipped('DAM ist nicht installiert');
		}
		parent::setUp();

		$this->sTempFolder = t3lib_extMgm::extPath('mklib').'tests/typo3temp';
		t3lib_div::mkdir($this->sTempFolder);
		$sImageFile = 'test.jpg';
		$this->sAbsoluteImagePath = $this->sTempFolder.'/'.$sImageFile;
		touch($this->sAbsoluteImagePath);
		$this->sRelativeImagePath = 'typo3conf/ext/mklib/tests/typo3temp/'.$sImageFile;
	}

	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_tests_DBTestCaseSkeleton::tearDown()
	 */
	public function tearDown() {
		t3lib_div::rmdir($this->sTempFolder,true);
	}

	/**
	 * @group integration
	 */
	public function testDamRecordHasReferencesReturnsFalseIfDamIsNotLoaded(){
		global $TYPO3_LOADED_EXT;
		$aBackUp = $TYPO3_LOADED_EXT['dam'];
		unset($TYPO3_LOADED_EXT['dam']);
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/dam_ref.xml'));

		$this->assertFalse(
			tx_mklib_util_DAM::damRecordHasReferences(1),
			'Es wurde nicht false zurück geben obwohl DAM nicht geladen ist.');

		//damit DAM in den nächsten Tests wieder geladen ist
		$TYPO3_LOADED_EXT['dam'] = $aBackUp;
	}

	/**
	 * @group integration
	 */
	public function testDamRecordHasReferencesReturnsTrueIfReferencesFound(){
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/dam_ref.xml'));
		$this->assertTrue(
			tx_mklib_util_DAM::damRecordHasReferences(1),
			'Es wurde nicht das richtige Array zurückgegeben.');
	}

	/**
	 * die referenz existiert nicht und daher darf auch nicht der dam record zurückgegeben werden
	 * @group integration
	 */
	public function testDamRecordHasReferencesReturnsFalseIfNoReferencesFound(){
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/dam_ref.xml'));
		$this->assertFalse(
			tx_mklib_util_DAM::damRecordHasReferences(1,'tx_does_not_matter'),
			'Es wurde kein leeres Array zurück geben.');
	}

	/**
	 *
	 * @group integration
	 */
	public function testDeleteDamRecordReturnsFalseIfGivenDamRecordIsEmpty() {
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/dam_ref.xml'));
		$aDamRecords = array('rows' => array(),'files' => array());
		$this->assertFalse(tx_mklib_util_DAM::deleteDamRecord($aDamRecords),'es wurde nicht false zurück gegeben obwohl der DAM Record leer ist.');
	}

	/**
	 * nichts machen da es noch referenzen gibt
	 * @group integration
	 */
	public function testDeleteDamRecordWorksCorrectWithDefaultSettingsAndExistingReferences() {
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/dam_ref.xml'));
		$aDamRecords = array('rows' => array(1 => array()),'files' => array(1 => $this->sRelativeImagePath));
		$this->assertTrue(tx_mklib_util_DAM::deleteDamRecord($aDamRecords),'es wurde nicht false zurück gegeben obwohl der DAM Record leer ist.');

		//dam record nicht auf hidden und deleted da noch referenzen da?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam',array('enablefieldsoff' => true));
		$this->assertEquals(1,count($res),'Es wurde nicht die korrekte Anzahl von DAM Einträgen gefunden!');
		$this->assertEquals(1,$res[0]['uid'],'Es wurde scheinbar eine falscher DAM Eintrag gelöscht!!');
		$this->assertEquals(0,$res[0]['hidden'],'hidden falsch!');
		$this->assertEquals(0,$res[0]['deleted'],'deleted falsch!');

		//bild noch da?
		$this->assertFileExists($this->sAbsoluteImagePath,'Das Bild wurde doch gelöscht!');
	}

	/**
	 * auf hidden setzen
	 * @group integration
	 */
	public function testDeleteDamRecordWorksCorrectWithDefaultSettingsAndNoneExistingReferences() {
		$aDamRecords = array('rows' => array(1 => array()),'files' => array(1 => $this->sRelativeImagePath));
		$this->assertTrue(tx_mklib_util_DAM::deleteDamRecord($aDamRecords),'es wurde nicht false zurück gegeben obwohl der DAM Record leer ist.');

		//dam record nicht auf hidden und deleted da noch referenzen da?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam',array('enablefieldsoff' => true));
		$this->assertEquals(1,count($res),'Es wurde nicht die korrekte Anzahl von DAM Einträgen gefunden!');
		$this->assertEquals(1,$res[0]['uid'],'Es wurde scheinbar eine falscher DAM Eintrag gelöscht!!');
		$this->assertEquals(1,$res[0]['hidden'],'hidden falsch!');
		$this->assertEquals(0,$res[0]['deleted'],'deleted falsch!');

		//bild noch da?
		$this->assertFileExists($this->sAbsoluteImagePath,'Das Bild wurde doch gelöscht!');
	}

	/**
	 * auf deleted setzen
	 * @group integration
	 */
	public function testDeleteDamRecordWorksCorrectWithMode1AndNoneExistingReferences() {
		$aDamRecords = array('rows' => array(1 => array()),'files' => array(1 => $this->sRelativeImagePath));
		$this->assertTrue(tx_mklib_util_DAM::deleteDamRecord($aDamRecords,1),'es wurde nicht false zurück gegeben obwohl der DAM Record leer ist.');

		//dam record nicht auf hidden und deleted da noch referenzen da?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam',array('enablefieldsoff' => true));
		$this->assertEquals(1,count($res),'Es wurde nicht die korrekte Anzahl von DAM Einträgen gefunden!');
		$this->assertEquals(1,$res[0]['uid'],'Es wurde scheinbar eine falscher DAM Eintrag gelöscht!!');
		$this->assertEquals(0,$res[0]['hidden'],'hidden falsch!');
		$this->assertEquals(1,$res[0]['deleted'],'deleted falsch!');

		//bild noch da?
		$this->assertFileExists($this->sAbsoluteImagePath,'Das Bild wurde doch gelöscht!');
	}

	/**
	 * ganz löschen
	 * @group integration
	 */
	public function testDeleteDamRecordWorksCorrectWithMode2AndNoneExistingReferences() {
		$aDamRecords = array('rows' => array(1 => array()),'files' => array(1 => $this->sRelativeImagePath));
		$this->assertTrue(tx_mklib_util_DAM::deleteDamRecord($aDamRecords,2),'es wurde nicht false zurück gegeben obwohl der DAM Record leer ist.');

		//dam record nicht auf hidden und deleted da noch referenzen da?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam',array('enablefieldsoff' => true));
		$this->assertEmpty($res,'Die DAM Einträge wurden nicht gelöscht!');

		//bild noch da?
		$this->assertFileExists($this->sAbsoluteImagePath,'Das Bild wurde doch gelöscht!');
	}

	/**
	 * ganz löschen und bild löschen
	 * @group integration
	 */
	public function testDeleteDamRecordWorksCorrectWithDeletingImageAndNoneExistingReferences() {
		$aDamRecords = array('rows' => array(1 => array()),'files' => array(1 => $this->sRelativeImagePath));
		$this->assertTrue(tx_mklib_util_DAM::deleteDamRecord($aDamRecords,2,true),'es wurde nicht false zurück gegeben obwohl der DAM Record leer ist.');

		//dam record nicht auf hidden und deleted da noch referenzen da?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam',array('enablefieldsoff' => true));
		$this->assertEmpty($res,'Die DAM Einträge wurden nicht gelöscht!');

		//bild gelöscht?
		$this->assertFileNotExists($this->sAbsoluteImagePath,'Das Bild wurde nicht gelöscht!');
	}

	/**
	 * nichts machen da es noch referenzen gibt
	 * @group integration
	 */
	public function testDeleteDamRecordWorksCorrectWithDeletingImageAndExistingReferences() {
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/dam_ref.xml'));
		$aDamRecords = array('rows' => array(1 => array()),'files' => array(1 => $this->sRelativeImagePath));
		$this->assertTrue(tx_mklib_util_DAM::deleteDamRecord($aDamRecords,2,true),'es wurde nicht false zurück gegeben obwohl der DAM Record leer ist.');

		//dam record nicht auf hidden und deleted da noch referenzen da?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam',array('enablefieldsoff' => true));
		$this->assertEquals(1,count($res),'Es wurde nicht die korrekte Anzahl von DAM Einträgen gefunden!');
		$this->assertEquals(1,$res[0]['uid'],'Es wurde scheinbar eine falscher DAM Eintrag gelöscht!!');
		$this->assertEquals(0,$res[0]['hidden'],'hidden falsch!');
		$this->assertEquals(0,$res[0]['deleted'],'deleted falsch!');

		//bild noch da?
		$this->assertFileExists($this->sAbsoluteImagePath,'Das Bild wurde nicht gelöscht!');
	}

	/**
	 * prüfen ob alles gelöscht wird, auch die bilder da sie keine
	 * verwendung mehr haben
	 * @group integration
	 */
	public function testHandleDeleteWithDefaultSettings() {
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/dam_ref.xml'));
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/wordlist.xml'));
		//wir müssen nachträglich noch die richtige pid in die dam datensätze einfügen damit
		//diese auch gefunden werden über tx_mklib_util_DAM::getRecords
		require_once(PATH_txdam.'lib/class.tx_dam_db.php');
		tx_rnbase_util_DB::doUpdate('tx_dam', '', array('pid' => tx_dam_db::getPidList()));

		$result = tx_mklib_util_DAM::handleDelete('tx_mklib_wordlist', 1, 'blacklisted');

		//richtige Anzahl gelöscht?
		$this->assertEquals(1,$result['deletedReferences'],'deletedReferences ist falsch!');
		$this->assertEquals(1,$result['deletedRecords'],'deletedRecords ist falsch!');

		//bild nicht gelöscht?
		$this->assertFileExists($this->sAbsoluteImagePath,'Das Bild der tempoäreren Anzeigen 4 und 20 wurde nicht gelöscht!');

		//eintrag in dam auf hidden gesetzt?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam',array('enablefieldsoff' => true));
		$this->assertEquals(1,count($res),'Es wurde nicht die korrekte Anzahl von DAM Einträgen gefunden!');
		$this->assertEquals(1,$res[0]['uid'],'Es wurde scheinbar eine falscher DAM Eintrag gelöscht!!');
		$this->assertEquals(1,$res[0]['hidden'],'hidden falsch!');
		$this->assertEquals(0,$res[0]['deleted'],'deleted falsch!');

		//dam referenz gelöscht?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam_mm_ref',array('enablefieldsoff' => true));
		$this->assertEmpty($res,'Die DAM Referenzen wurden nicht gelöscht!');
	}

	/**
	 * prüfen ob alles gelöscht wird, auch die bilder da sie keine
	 * verwendung mehr haben
	 * @group integration
	 */
	public function testHandleDeleteWithMode1() {
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/dam_ref.xml'));
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/wordlist.xml'));
		//wir müssen nachträglich noch die richtige pid in die dam datensätze einfügen damit
		//diese auch gefunden werden über tx_mklib_util_DAM::getRecords
		require_once(PATH_txdam.'lib/class.tx_dam_db.php');
		tx_rnbase_util_DB::doUpdate('tx_dam', '', array('pid' => tx_dam_db::getPidList()));

		$result = tx_mklib_util_DAM::handleDelete('tx_mklib_wordlist', 1, 'blacklisted',1);

		//richtige Anzahl gelöscht?
		$this->assertEquals(1,$result['deletedReferences'],'deletedReferences ist falsch!');
		$this->assertEquals(1,$result['deletedRecords'],'deletedRecords ist falsch!');

		//bild nicht gelöscht?
		$this->assertFileExists($this->sAbsoluteImagePath,'Das Bild der tempoäreren Anzeigen 4 und 20 wurde nicht gelöscht!');

		//eintrag in dam auf hidden gesetzt?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam',array('enablefieldsoff' => true));
		$this->assertEquals(1,count($res),'Es wurde nicht die korrekte Anzahl von DAM Einträgen gefunden!');
		$this->assertEquals(1,$res[0]['uid'],'Es wurde scheinbar eine falscher DAM Eintrag gelöscht!!');
		$this->assertEquals(0,$res[0]['hidden'],'hidden falsch!');
		$this->assertEquals(1,$res[0]['deleted'],'deleted falsch!');

		//dam referenz gelöscht?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam_mm_ref',array('enablefieldsoff' => true));
		$this->assertEmpty($res,'Die DAM Referenzen wurden nicht gelöscht!');
	}

	/**
	 * prüfen ob alles gelöscht wird, auch die bilder da sie keine
	 * verwendung mehr haben
	 * @group integration
	 */
	public function testHandleDeleteWithMode2() {
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/dam_ref.xml'));
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/wordlist.xml'));
		//wir müssen nachträglich noch die richtige pid in die dam datensätze einfügen damit
		//diese auch gefunden werden über tx_mklib_util_DAM::getRecords
		require_once(PATH_txdam.'lib/class.tx_dam_db.php');
		tx_rnbase_util_DB::doUpdate('tx_dam', '', array('pid' => tx_dam_db::getPidList()));

		$result = tx_mklib_util_DAM::handleDelete('tx_mklib_wordlist', 1, 'blacklisted',2);

		//richtige Anzahl gelöscht?
		$this->assertEquals(1,$result['deletedReferences'],'deletedReferences ist falsch!');
		$this->assertEquals(1,$result['deletedRecords'],'deletedRecords ist falsch!');

		//bild nicht gelöscht?
		$this->assertFileExists($this->sAbsoluteImagePath,'Das Bild der tempoäreren Anzeigen 4 und 20 wurde nicht gelöscht!');

		//eintrag in dam auf hidden gesetzt?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam',array('enablefieldsoff' => true));
		$this->assertEmpty($res,'Die DAM Einträge wurden nicht gelöscht!');

		//dam referenz gelöscht?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam_mm_ref',array('enablefieldsoff' => true));
		$this->assertEmpty($res,'Die DAM Referenzen wurden nicht gelöscht!');
	}

	/**
	 * prüfen ob alles gelöscht wird, auch die bilder da sie keine
	 * verwendung mehr haben
	 * @group integration
	 */
	public function testHandleDeleteWithMode2AndDeleteImage() {
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/dam_ref.xml'));
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/wordlist.xml'));
		//wir müssen nachträglich noch die richtige pid in die dam datensätze einfügen damit
		//diese auch gefunden werden über tx_mklib_util_DAM::getRecords
		require_once(PATH_txdam.'lib/class.tx_dam_db.php');
		tx_rnbase_util_DB::doUpdate('tx_dam', '', array('pid' => tx_dam_db::getPidList()));

		$result = tx_mklib_util_DAM::handleDelete('tx_mklib_wordlist', 1, 'blacklisted',2, true);

		//richtige Anzahl gelöscht?
		$this->assertEquals(1,$result['deletedReferences'],'deletedReferences ist falsch!');
		$this->assertEquals(1,$result['deletedRecords'],'deletedRecords ist falsch!');

		//bild nicht gelöscht?
		$this->assertFileNotExists($this->sAbsoluteImagePath,'Das Bild der tempoäreren Anzeigen 4 und 20 wurde nicht gelöscht!');

		//eintrag in dam auf hidden gesetzt?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam',array('enablefieldsoff' => true));
		$this->assertEmpty($res,'Die DAM Einträge wurden nicht gelöscht!');

		//dam referenz gelöscht?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam_mm_ref',array('enablefieldsoff' => true));
		$this->assertEmpty($res,'Die DAM Referenzen wurden nicht gelöscht!');
	}

	/**
	 * prüfen ob alles gelöscht wird, auch die bilder da sie keine
	 * verwendung mehr haben
	 * @group integration
	 */
	public function testHandleDeleteWhenNoReference() {
		$this->importDataSet(tx_mklib_tests_Util::getFixturePath('db/wordlist.xml'));
		//wir müssen nachträglich noch die richtige pid in die dam datensätze einfügen damit
		//diese auch gefunden werden über tx_mklib_util_DAM::getRecords
		require_once(PATH_txdam.'lib/class.tx_dam_db.php');
		tx_rnbase_util_DB::doUpdate('tx_dam', '', array('pid' => tx_dam_db::getPidList()));

		$result = tx_mklib_util_DAM::handleDelete('tx_mklib_wordlist', 2, 'blacklisted',1, true);

		//richtige Anzahl gelöscht?
		$this->assertEquals(0,$result['deletedReferences'],'deletedReferences ist falsch!');
		$this->assertEquals(0,$result['deletedRecords'],'deletedRecords ist falsch!');

		//bild nicht gelöscht?
		$this->assertFileExists($this->sAbsoluteImagePath,'Das Bild der tempoäreren Anzeigen 4 und 20 wurde nicht gelöscht!');

		//eintrag in dam auf hidden gesetzt?
		$res = tx_rnbase_util_DB::doSelect('*', 'tx_dam',array('enablefieldsoff' => true));
		$this->assertEquals(1,count($res),'Es wurde nicht die korrekte Anzahl von DAM Einträgen gefunden!');
		$this->assertEquals(1,$res[0]['uid'],'Es wurde scheinbar eine falscher DAM Eintrag gelöscht!!');
		$this->assertEquals(0,$res[0]['hidden'],'hidden falsch!');
		$this->assertEquals(0,$res[0]['deleted'],'deleted falsch!');
	}
}