<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_mod1_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 DMK E-BUSINESS GmbH  <dev@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_mklib_mod1_decorator_Base');
tx_rnbase::load('tx_mklib_tests_mod1_Util');

/**
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests_mod1_util
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_tests_mod1_decorator_Base_testcase
	extends tx_rnbase_tests_BaseTestCase {

	protected $backup = array();

	public function setUp() {
		$this->backup['iconsAvailable'] = $GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'];
		if (!is_array($GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'])) {
			$GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] = array();
		}
		$this->backup['beUserAdminState'] = $GLOBALS['BE_USER']->user['admin'];

		$GLOBALS['LANG']->lang = 'default';
	}

	public function tearDown() {
		$GLOBALS['BE_USER']->user['admin'] = $this->backup['beUserAdminState'];
		$GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] = $this->backup['iconsAvailable'];
	}

	public function testFormatWithUidColumn() {
		//base model liefert als table name immer 0
		//also setzen wir die columns für die tabelle 0
		$GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = 'disable';

		$record = array('uid' => 1, 'disable' => 0);
		$result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
		// Achtung: Das Ergebnis ist Multiline. Leider wird der Modifikator nicht akzeptiert...
		self::assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert.');


		//jetzt mit versteckt und record fallback
		$record = array('uid' => 1, 'disable' => 1);
		$result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
		self::assertRegExp('/<del>1<\/del>/', $result, 'Deaktivierter Datensatz wird falsch geliefert.');

		//jetzt mit versteckt und ohne record fallback
		$record = array('uid' => 1, 'disable' => 1);
		$result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
		self::assertRegExp('/<del>1<\/del>/', $result, 'es wurde nicht der korrekte Wert zurück geliefert');
	}

	public function testFormatWithUidColumnAndNoEnableColumnsConfig() {
		//es sollte hidden genommen werden da nicht in enablecolumns konfiguriert
		$GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = null;//konfig löschen

		$record = array('uid' => 1, 'disable' => 0);
		$result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
		self::assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert.');

		//jetzt mit versteckt im falschen feld
		$record = array('uid' => 1, 'disable' => 1);
		$result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
		self::assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert. 2');

		//jetzt mit versteckt im fallback feld
		$record = array('uid' => 1, 'hidden' => 0);
		$result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
		self::assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert. 3');

		$record = array('uid' => 1, 'hidden' => 1);
		$result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
		self::assertRegExp('/<del>1<\/del>/', $result, 'Deaktivierter Datensatz wird falsch geliefert.');
	}

	public function testFormatWithLabelColumn() {
		$model = $this->getModel(
			array(
				'uid' => 57,
				'header' => 'Home',
				'crdate' => 1433158465,
				'tstamp' => 1434158465,
			)
		)->setTablename('tt_content');
		$result = $this->getDecoratorMock()->format('Content', 'label', $model->getRecord(), $model);

		self::assertContains('>Home</span>', $result, 'Falsches Label erzeugt');
		self::assertContains('<span title="UID: 57', $result, 'UID fehlt.');
		self::assertContains('Creation: 2015-06-01T11:34:25+00:00', $result, 'CRDATE fehlt.');
		self::assertContains('Last Change: 2015-06-13T01:21:05+00:00', $result, 'TSTAMP fehlt.');
	}

	public function testFormatWithSysLanguageUidColumn() {
		$GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'][] = 'flags-multiple';
		$model = $this->getModel(
			array(
				'uid' => 57,
				'header' => 'Home',
				'sys_language_uid' => '0',
			)
		)->setTablename('tt_content');

		$result = $this->getDecoratorMock()->format('0', 'sys_language_uid', $model->getRecord(), $model);

		self::assertContains(
			'<span class="t3-icon t3-icon-flags t3-icon-flags-multiple t3-icon-multiple">&nbsp;</span>&nbsp;Default',
			$result,
			'Falsches oder fehlendes Icon erzeugt.'
		);

		$model->setProperty('sys_language_uid', '-1');
		$result = $this->getDecoratorMock()->format('0', 'sys_language_uid', $model->getRecord(), $model);

		self::assertContains(
			'<span class="t3-icon t3-icon-flags t3-icon-flags-multiple t3-icon-multiple">&nbsp;</span>&nbsp;[All]',
			$result,
			'Falsches oder fehlendes Icon erzeugt.'
		);

		// @TODO: write test for syslang uid > 0 !!!
	}

	public function testFormatWithActionsColumnBeingAdminDoesReturnDeleteLink() {
		$GLOBALS['BE_USER']->user['admin']=1;
		//base model liefert als table name immer 0
		//also setzen wir die columns für die tabelle 0
		$GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = 'disable';

		$record = array('uid' => 1, 'disable' => 0);
		$result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));
		$sExpected = '<a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 1" border="0" alt="Edit" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][disable]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="Hide UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[0][1][delete]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/deletedok.gif" width="16" height="16" title="Delete UID: 1" border="0" alt="" /></a>';
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);

		self::assertEquals(
			$this->replaceForCliAndremoveVcAndFormToken($sExpected),
			$result,
			'es wurde nicht der korrekte Wert zurück geliefert. 1'
		);

		//schon versteckt
		$record = array('uid' => 1, 'disable' => 1);
		$result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'<img src="sysext/t3skin/icons/gfx/edit2.gif"'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][disable]=0'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'<img src="sysext/t3skin/icons/gfx/button_hide.gif"'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[0][1][delete]=1'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'<img src="sysext/t3skin/icons/gfx/deletedok.gif"'
			),
			$result
		);

	}

	public function testFormatWithActionsColumnBeingNoAdminDoesNotReturnDeleteLink() {
		$GLOBALS['BE_USER']->user['admin']=0;
		//base model liefert als table name immer 0
		//also setzen wir die columns für die tabelle 0
		$GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = 'disable';

		$record = array('uid' => 1, 'disable' => 0);
		$result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));
		$sExpected = '<a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 1" border="0" alt="Edit" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][disable]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="Hide UID: 1" border="0" alt="" /></a>';
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);
		self::assertEquals(
			$this->replaceForCliAndremoveVcAndFormToken($sExpected),
			$result,
			'es wurde nicht der korrekte Wert zurück geliefert. 1'
		);

		//schon versteckt
		$record = array('uid' => 1, 'disable' => 1);
		$result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'<img src="sysext/t3skin/icons/gfx/edit2.gif"'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][disable]=0'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'<img src="sysext/t3skin/icons/gfx/button_hide.gif"'
			),
			$result
		);
	}

	public function testFormatWithActionsColumnAndNoEnableColumnConfigBeingAdmin() {
		$GLOBALS['BE_USER']->user['admin']=1;
		//base model liefert als table name immer 0
		//also setzen wir die columns für die tabelle 0
		$GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = null;//konfig löschen

		$record = array('uid' => 1, 'hidden' => 0);
		$result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'<img src="sysext/t3skin/icons/gfx/edit2.gif"'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][hidden]=1'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'<img src="sysext/t3skin/icons/gfx/button_unhide.gif"'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[0][1][delete]=1'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'<img src="sysext/t3skin/icons/gfx/deletedok.gif"'
			),
			$result
		);

		//schon versteckt
		$record = array('uid' => 1, 'hidden' => 1);
		$result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'<img src="sysext/t3skin/icons/gfx/edit2.gif"'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][hidden]=0'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'<img src="sysext/t3skin/icons/gfx/button_hide.gif"'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[0][1][delete]=1'
			),
			$result
		);
		self::assertContains(
			$this->replaceForCliAndremoveVcAndFormToken(
				'<img src="sysext/t3skin/icons/gfx/deletedok.gif"'
			),
			$result
		);
	}

	/**
	 * @return tx_mklib_mod1_decorator_Base
	 */
	protected function getDecoratorMock() {
		$mod = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_DummyMod');
		//zurücksetzen aus anderen Tests
		tx_mklib_tests_mod1_Util::unsetSorting($mod);
		return tx_rnbase::makeInstance('tx_mklib_mod1_decorator_Base', $mod);
	}

	protected function replaceForCliAndremoveVcAndFormToken($string) {
		tx_rnbase::load('tx_mklib_tests_mod1_Util');
		tx_mklib_tests_mod1_Util::replaceForCli($string);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($string);
		return $string;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']);
}