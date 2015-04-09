<?php
/**
 *  Copyright notice
 *
 *  (c) 2015 Hannes Bochmann <dev@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_action_BaseIOC');

/**
 *
 * tx_mklib_action_ShowSingeItem
 *
 * @package 		TYPO3
 * @subpackage	 	mklin
 * @author 			Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license 		http://www.gnu.org/licenses/lgpl.html
 * 					GNU Lesser General Public License, version 3 or later
 */
abstract class tx_mklib_action_ShowSingeItem extends tx_rnbase_action_BaseIOC {

	/**
	 * Do the magic!
	 *
	 * @param tx_rnbase_IParameters &$parameters
	 * @param tx_rnbase_configurations &$configurations
	 * @param ArrayObject &$viewdata
	 * @return string Errorstring or NULL
	 */
	protected function handleRequest(&$parameters, &$configurations, &$viewdata) {
		$itemParameterKey = $this->getSingleItemUidParameterKey();
		if (!($itemUid = $parameters->getInt($itemParameterKey))) {
			$this->throwItemNotFound404Exception();
		}

		$singleItemRepository = $this->getSingleItemRepository();
		if (!$singleItemRepository instanceof tx_mklib_repository_Abstract) {
			throw new Exception(
				'Das Repository, welches von getSingleItemRepository() geliefert ' .
				'wird, muss von tx_mklib_repository_Abstract erben!'
			);
		}

		if (!($item = $singleItemRepository->findByUid($itemUid))) {
			$this->throwItemNotFound404Exception();
		}

		$viewdata->offsetSet('item', $item);

		return NULL;
	}

	/**
	 * der Parameter Name für die Item UID.
	 * default ist uid, bei Bedarf überschreiben!
	 *
	 * @return string
	 */
	protected function getSingleItemUidParameterKey() {
		return 'uid';
	}

	/**
	 * @return tx_mklib_repository_Abstract
	 */
	abstract protected function getSingleItemRepository();

	/**
	 * @throws tx_rnbase_exception_ItemNotFound404
	 */
	protected function throwItemNotFound404Exception() {
		throw tx_rnbase::makeInstance(
			'tx_rnbase_exception_ItemNotFound404',
			$this->getItemNotFound404Message()
		);
	}

	/**
	 * The message cann be stored at
	 * typoscript: "plugin.tx_myext.myaction.notfound"
	 * or locallang: "myaction_notfound"
	 *
	 * @return string
	 */
	protected function getItemNotFound404Message() {
		$msg = $this->getConfigurations()->getCfgOrLL(
			$this->getConfId() . 'notfound'
		);
		return empty($msg) ? 'Datensatz nicht gefunden.' : $msg;
	}

	/**
	 * Liefert den Namen der View-Klasse
	 *
	 * @return string
	 */
	protected function getViewClassName() {
		return 'tx_rnbase_view_Single';
	}
}