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
tx_rnbase::load('tx_mklib_util_HttpRequest');

/**
 * Http Request Object Tests
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_HttpRequest_testcase extends tx_phpunit_testcase {


	/**
	 *
	 */
	public function testHttpRequestWithCurlAndSllAndServerAuth(){
		$time = time();

		$url = 'https://mwagner.project.dmknet.de/tests/httprequest.php?method=POST';
		#$url = 'https://api.broadmail.de/';
		$config = array(
			'sslcainfo' => tx_mklib_tests_Util::getFixturePath('project.dmknet.de.crt'),
			#'sslcainfo' => tx_mklib_tests_Util::getFixturePath('broadmail.crt'),
			#'curloptions' => array(
			#	CURLOPT_SSL_VERIFYPEER => FALSE,
			#),
		);


		$request = new tx_mklib_util_HttpRequest($url, $config);

		$request->addParameter('httprequest', array('time' => $time, 'return' => 'time'));
		$request->setAuth('mwagner', 'mk17');
		$request->setMethod($request::METHOD_POST);
		$response = $request->request();

		$this->assertEquals(200, $response->getStatus());
		$this->assertEquals('OK', $response->getMessage());
		$this->assertEquals($time, $response->getBody());

	}

}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_HttpRequest_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_HttpRequest_testcase.php']);
}