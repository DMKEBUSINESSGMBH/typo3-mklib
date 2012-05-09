<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_mod1_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 das MedienKombinat <kontakt@das-medienkombinat.de>
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

require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_mklib_mod1_decorator_Base');
tx_rnbase::load('tx_mklib_tests_mod1_Util');

/**
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests_mod1_util
 * @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 */
class tx_mklib_tests_mod1_decorator_Base_testcase extends tx_phpunit_testcase {

	/**
	 * @var tx_mklib_mod1_decorator_Base
	 */
	protected $oDecorator;

	protected $beUserAdminState;

	/**
	 * @var tx_rnbase_model_base
	 */
	protected $oModel;

	public function setUp() {
		$oMod = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_DummyMod');
		$this->oDecorator = tx_rnbase::makeInstance('tx_mklib_mod1_decorator_Base',$oMod);
		$this->oModel = tx_rnbase::makeInstance('tx_rnbase_model_base', array('uid' => 0));
		$this->oModel->uid = 1;

		//zurücksetzen aus anderen Tests
		tx_mklib_tests_mod1_Util::unsetSorting($oMod);

		$this->beUserAdminState = $GLOBALS['BE_USER']->user['admin'];
	}

	public function tearDown() {
		$GLOBALS['BE_USER']->user['admin'] = $this->beUserAdminState;
	}

	public function testFormatWithUidColumn() {
		//base model liefert als table name immer 0
		//also setzen wir die columns für die tabelle 0
		$GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = 'disable';

		$record = array('uid' => 1, 'disable' => 0);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format(1,'uid',$record,$this->oModel);
		// Achtung: Das Ergebnis ist Multiline. Leider wird der Modifikator nicht akzeptiert...
		$this->assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert.');


		//jetzt mit versteckt und record fallback
		$record = array('uid' => 1, 'disable' => 1);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format(1,'uid',$record,$this->oModel);
		$this->assertRegExp('/<strike>1<\/strike>/', $result, 'Deaktivierter Datensatz wird falsch geliefert.');

		//jetzt mit versteckt und ohne record fallback
		$record = array('uid' => 1, 'disable' => 1);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format(1,'uid',$record,$this->oModel);
		$this->assertRegExp('/<strike>1<\/strike>/', $result, 'es wurde nicht der korrekte Wert zurück geliefert');
	}

	public function testFormatWithUidColumnAndNoEnableColumnsConfig() {
		//es sollte hidden genommen werden da nicht in enablecolumns konfiguriert
		$GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = null;//konfig löschen

		$record = array('uid' => 1, 'disable' => 0);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format(1,'uid',$record,$this->oModel);
		$this->assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert.');

		//jetzt mit versteckt im falschen feld
		$record = array('uid' => 1, 'disable' => 1);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format(1,'uid',$record,$this->oModel);
		$this->assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert. 2');

		//jetzt mit versteckt im fallback feld
		$record = array('uid' => 1, 'hidden' => 0);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format(1,'uid',$record,$this->oModel);
		$this->assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert. 3');

		$record = array('uid' => 1, 'hidden' => 1);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format(1,'uid',$record,$this->oModel);
		$this->assertRegExp('/<strike>1<\/strike>/', $result, 'Deaktivierter Datensatz wird falsch geliefert.');
	}

	public function testFormatWithActionsColumnBeingAdminDoesReturnDeleteLink() {
		$GLOBALS['BE_USER']->user['admin']=1;
		//base model liefert als table name immer 0
		//also setzen wir die columns für die tabelle 0
		$GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = 'disable';

		$record = array('uid' => 1, 'disable' => 0);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format('','actions',$record,$this->oModel);
		$sExpected = '<a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][disable]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="Hide UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[0][1][delete]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/deletedok.gif" width="16" height="16" title="Delete UID: 1" border="0" alt="" /></a>';
		tx_mklib_tests_mod1_Util::replaceForCli($sExpected);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($sExpected);
		$this->assertEquals($sExpected, $result, 'es wurde nicht der korrekte Wert zurück geliefert. 1');

		//schon versteckt
		$record = array('uid' => 1, 'disable' => 1);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format('','actions',$record,$this->oModel);
		$sExpected = '<a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][disable]=0\');" href="#"><img src="sysext/t3skin/icons/gfx/button_hide.gif" width="16" height="16" title="Show UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[0][1][delete]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/deletedok.gif" width="16" height="16" title="Delete UID: 1" border="0" alt="" /></a>';
		tx_mklib_tests_mod1_Util::replaceForCli($sExpected);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($sExpected);
		$this->assertEquals($sExpected, $result, 'es wurde nicht der korrekte Wert zurück geliefert. 2');
	}

	public function testFormatWithActionsColumnBeingNoAdminDoesNotReturnDeleteLink() {
		$GLOBALS['BE_USER']->user['admin']=0;
		//base model liefert als table name immer 0
		//also setzen wir die columns für die tabelle 0
		$GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = 'disable';

		$record = array('uid' => 1, 'disable' => 0);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format('','actions',$record,$this->oModel);
		$sExpected = '<a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][disable]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="Hide UID: 1" border="0" alt="" /></a>';
		tx_mklib_tests_mod1_Util::replaceForCli($sExpected);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($sExpected);
		$this->assertEquals($sExpected, $result, 'es wurde nicht der korrekte Wert zurück geliefert. 1');

		//schon versteckt
		$record = array('uid' => 1, 'disable' => 1);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format('','actions',$record,$this->oModel);
		$sExpected = '<a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][disable]=0\');" href="#"><img src="sysext/t3skin/icons/gfx/button_hide.gif" width="16" height="16" title="Show UID: 1" border="0" alt="" /></a>';
		tx_mklib_tests_mod1_Util::replaceForCli($sExpected);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($sExpected);
		$this->assertEquals($sExpected, $result, 'es wurde nicht der korrekte Wert zurück geliefert. 2');
	}

	public function testFormatWithActionsColumnAndNoEnableColumnConfigBeingAdmin() {
		$GLOBALS['BE_USER']->user['admin']=1;
		//base model liefert als table name immer 0
		//also setzen wir die columns für die tabelle 0
		$GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = null;//konfig löschen

		$record = array('uid' => 1, 'hidden' => 0);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format('','actions',$record,$this->oModel);
		$sExpected = '<a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][hidden]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="Hide UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[0][1][delete]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/deletedok.gif" width="16" height="16" title="Delete UID: 1" border="0" alt="" /></a>';
		tx_mklib_tests_mod1_Util::replaceForCli($sExpected);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($sExpected);
		$this->assertEquals($sExpected, $result, 'es wurde nicht der korrekte Wert zurück geliefert. 1');

		//schon versteckt
		$record = array('uid' => 1, 'hidden' => 1);
		$this->oModel->record = $record;
		$result = $this->oDecorator->format('','actions',$record,$this->oModel);
		$sExpected = '<a href="#" onclick="window.location.href=\'alt_doc.php?returnUrl=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;edit[0][1]=edit\'; return false;"><img src="sysext/t3skin/icons/gfx/edit2.gif" width="16" height="16" title="Edit UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;data[0][1][hidden]=0\');" href="#"><img src="sysext/t3skin/icons/gfx/button_hide.gif" width="16" height="16" title="Show UID: 1" border="0" alt="" /></a><a onclick="return jumpToUrl(\'tce_db.php?redirect=%2Ftypo3%2Fmod.php%3FM%3Dtools_txphpunitbeM1&amp;cmd[0][1][delete]=1\');" href="#"><img src="sysext/t3skin/icons/gfx/deletedok.gif" width="16" height="16" title="Delete UID: 1" border="0" alt="" /></a>';
		tx_mklib_tests_mod1_Util::replaceForCli($sExpected);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($result);
		tx_mklib_tests_mod1_Util::removeVcAndFormToken($sExpected);
		$this->assertEquals($sExpected, $result, 'es wurde nicht der korrekte Wert zurück geliefert. 2');
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php']);
}