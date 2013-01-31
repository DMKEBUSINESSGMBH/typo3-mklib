<?php
/**
 * 	@package TYPO3
 *  @subpackage tx_mklib
 *  @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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

/**
 * @package TYPO3
 * @subpackage tx_mklib
 * @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 */
class tx_mklib_tests_filter_Sorter_testcase extends tx_phpunit_testcase {

	/**
	 * @group unit
	 * @dataProvider getExpectedParsedLinks
	 */
	public function testParseTemplateParsesLinksCorrect(
		$template, $expectedParsedTemplate, $sortBy, $sortOrder
	) {
		$parameters = $this->getParameters();
		$configurations = $this->getConfigurations();
		
		if($sortBy) {
			$parameters->offsetSet('sortBy', $sortBy);
		}
		
		if($sortOrder) {
			$parameters->offsetSet('sortOrder', $sortOrder);
		}
		
		$confId = 'myConfId.filter.';
		$filter = tx_rnbase::makeInstance(
			'tx_mklib_filter_Sorter',
			$parameters,$configurations,$confId
		);
			
		$fields = array();
		$options = array();
		$filter->init($fields,$options);
		
		$formatter = $configurations->getFormatter();
		$parsedTemplate = $filter->parseTemplate($template, &$formatter, $confId);

		$this->assertEquals($expectedParsedTemplate, $parsedTemplate, 'link falsch');
	}
	
	/**
	 * @return array
	 */
	public function getExpectedParsedLinks() {
		return array(
			array(
				'###SORT_FIRSTFIELD_LINK###link###SORT_FIRSTFIELD_LINK###',
				'<a href="?id=1&amp;mklib%5BsortBy%5D=firstField&amp;mklib%5BsortOrder%5D=asc" >link</a>',
				'',
				''
			),
			array(
				'###SORT_FIRSTFIELD_LINK###link###SORT_FIRSTFIELD_LINK###',
				'<a href="?id=1&amp;mklib%5BsortBy%5D=firstField&amp;mklib%5BsortOrder%5D=desc" >link</a>',
				'firstField',
				'asc'
			),
			//normaler Link mit asc wenn anderes sortBy gewählt
			array(
				'###SORT_FIRSTFIELD_LINK###link###SORT_FIRSTFIELD_LINK###',
				'<a href="?id=1&amp;mklib%5BsortBy%5D=firstField&amp;mklib%5BsortOrder%5D=asc" >link</a>',
				'unknownField',
				'asc'
			),
			array(
				'###SORT_SECONDFIELD_LINK###link###SORT_SECONDFIELD_LINK###',
				'<a href="?id=1&amp;mklib%5BsortBy%5D=secondField&amp;mklib%5BsortOrder%5D=asc" >link</a>',
				'',
				''
			),
			array(
				'###SORT_SECONDFIELD_LINK###link###SORT_SECONDFIELD_LINK###',
				'<a href="?id=1&amp;mklib%5BsortBy%5D=secondField&amp;mklib%5BsortOrder%5D=desc" >link</a>',
				'secondField',
				'asc'
			),
			array(
				'###SORT_UNKNOWN_LINK###link###SORT_UNKNOWN_LINK###',
				'###SORT_UNKNOWN_LINK###link###SORT_UNKNOWN_LINK###',
				'',
				''
			),
		);		
	}
	
	/**
	 * @return tx_rnbase_parameters
	 */
	private function getParameters() {
		$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
		$parameters->setQualifier('mklib');
		
		return $parameters;
	}
	
	/**
	 * @return tx_rnbase_configurations
	 */
	private function getConfigurations() {
		tx_rnbase_util_Misc::prepareTSFE();
		
		$configurations = new tx_rnbase_configurations();
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$config = array(
			'myConfId.' => array(
				'filter.' => array(
					'sort.' => array(
						'fields' => 'firstField,secondField',
						'link.' => array(
							'noHash' => 1
						)
					)
				)
			)
		);
	    $configurations->init($config, $cObj, 'mklib', 'mklib');
	    
		return $configurations;
	}
}