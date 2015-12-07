<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_srv
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2010-2015 DMK-EBUSINESS GmbH <dev@dmk-ebusiness.de>
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


/**
 * Service für alles rund um Finanzen
 *
 * @package tx_mklib
 * @subpackage tx_mklib_srv
 * @author Michael Wagner
 */
class tx_mklib_srv_Finance extends Tx_Rnbase_Service_Base {

	/**
	 * @return 	tx_mklib_model_Currency
	 */
	public function getCurrency(){
		tx_rnbase::load('tx_mklib_model_Currency');
		//@TODO: aktuellen Code auslesen und übergeben,
//		return tx_mklib_model_Currency::getByCountry();
		return tx_mklib_model_Currency::getByCurrencyCode();
	}

	/**
	 * Formatiert einen Wert anhand der Wärung.
	 *
	 * @deprecated: direkt $this->getCurrency()->getFormatted($value) aufrufen,
	 * 				ansonsten bekommen wir bei späterer erweiterung des currencies wahrscheinlich probleme!
	 *
	 * @param 	double 		$value
	 * @param 	boolean 	$htmlEntities
	 * @return 	string
	 */
	public function getFormattedCurrency($value, $htmlEntities=true){
		return $this->getCurrency()->getFormatted($value, $htmlEntities);
	}

	/**
	 * Berechnet den Nettopreis anhand des Bruttopreises und des Steuersatzes
	 *
	 * @param doubleval $gross
	 * @param int $tax
	 * @return double
	 */
	public function getNetPriceByGrossPriceAndTax($gross, $tax) {
		//Rechnen wir mit Double?
		if(is_double($gross))
			return $this->getDoubleByInt($this->getIntByDouble($gross) / ((100 + $tax) / 100));
		else
			return $gross / ((100 + $tax) / 100);
	}

	/**
	 * Berechnet den Bruttopreis anhand des Nettopreises und des Steuersatzes
	 *
	 * @param doubleval $gross
	 * @param int $tax
	 * @return double
	 */
	public function getGrossPriceByNetPriceAndTax($net, $tax) {
		//Rechnen wir mit Double?
		if(is_double($net))
			return $this->getDoubleByInt($this->getIntByDouble($net) * (1 + $tax / 100));
		else
			return $net * (1 + $tax / 100);
	}

	/**
	 * Berechnet den Bruttopreis anhand des Nettopreises und des Steuersatzes
	 *
	 * @param doubleval $gross
	 * @param int $tax
	 * @return double
	 */
	public function getTaxAmountByNetPriceAndTax($net, $tax) {
		if(is_double($net))
			return $this->getDoubleByInt(
						$this->getIntByDouble($net) * ($tax / 100)
					);
		else
			return $net * ($tax / 100);
	}

	/**
	 * Multipliziert den Preis mit einem Wert (Anzahl Produkte)
	 *
	 * @param doubleval $price
	 * @param int $quantity
	 * @param boolean $formatted Gibt an ob der Preis Formatiert ausgegeben werde soll.
	 * @return double
	 */
	public function getSumPriceByPriceAndQuantity($price, $quantity, $formatted=false) {
		$sum = $this->getDoubleByInt(
					$this->getIntByDouble($price) * $quantity
				);
		return $formatted ? $this->getFormattedCurrency($sum) : $sum;
	}
	/**
	 * @see 		self::getSumPriceByPriceAndQuantity
	 * @deprecated 	Die getSumPriceByPriceAndQuantity hatte einen Tippfehler.
	 * 				Wenn nicht mehr verwendet, entfernen!
	 */
	public function getSumPriceByPriceAndtQuantity($price, $quantity, $formatted=false) {
		return $this->getSumPriceByPriceAndQuantity($price, $quantity, $formatted);
	}

	/**
	 * Berechnet den Bruttopreis anhand des Nettopreises und des Steuersatzes
	 *
	 * @param doubleval $net
	 * @param doubleval $gross
	 */
	public function getTaxAmountByNetAndGrossPrice($net, $gross) {
		return $this->getDoubleByInt(
					$this->getIntByDouble($gross) - $this->getIntByDouble($net)
				);
	}

	/**
	 * Wandelt einen Doubole-Wert für berechnungen in einen Integer-Wert um
	 *
	 * Wir wandeln den Wert für die Berechnung in einen Integer
	 * @see  http://javathreads.de/2009/03/niemals-mit-den-datentypen-float-oder-double-geldbetraege-berechnen/
	 *
	 * @param double $double
	 * @param int $digits
	 * @return int
	 */
	public function getIntByDouble($double, $digits = 4) {
		$digits = intval('1'.str_repeat('0',$digits));
		// erst zu String, danach zu Integer!
		// (int) 40.05 = 4004
		// (int) (string) 40.05 = 4005
		return (int) (string) ( $double * $digits );
	}

	/**
	 * Wandelt einen Integer-Wert für berechnungen in einen Dounbe-Wert um
	 *
	 * Wir wandeln den Wert für die Berechnung in einen Integer
	 * @see  http://javathreads.de/2009/03/niemals-mit-den-datentypen-float-oder-double-geldbetraege-berechnen/
	 *
	 * @param int $double
	 * @param int $digits
	 * @param bool $format | soll die double Zahl formatiert werden?
	 * 	Bsp: $int=8 --> ohne Format:8 mit Format:8.0000
	 * @param string $delimiter
	 * @return double
	 */
	public function getDoubleByInt($int, $digits = 4, $format = true, $delimiter = '.') {
		$baseInt = intval('1'.str_repeat('0',$digits));
		$doubleVal = doubleval( doubleval($int) / $baseInt );
		//@TODO: hierfür sollte das currency Objekt genutzt werden,
		// das beinhaltet digits, delemiter, etc.
		// das hier ist nur für die berechnung!
		return ($format) ? number_format($doubleVal, $digits, $delimiter, '') : $doubleVal;
	}

	/**
	 * Rundet einen Double Wert auf die gegeben Stellen nach dem Komma AUF
	 *
	 * @param doubleval $doubleValue
	 * @param int $digits
	 * @param bool $format
	 * @param string $delimiter
	 *
	 * @return doubleval
	 */
	public function roundUpDouble($doubleValue, $digits = 4, $format = true, $delimiter = '.') {
		$baseInt = intval('1'.str_repeat('0',$digits));
		$roundedDoubleValue = ($doubleValue * $baseInt);
		//durch einen Bug wird z.B. die Zahl 2.2000 auf 2.21 gerundet. Damit
		//das nicht passiert prüfen wir ob die Zahl eine Kommastelle enthält
		//und runden nur dann weil wir sonst schon eine ganze Zahl haben
		if(strpos($roundedDoubleValue,'.'))//Ist der $intValue schon eine ganze Zahl?
			$roundedDoubleValue = ceil($roundedDoubleValue) / $baseInt;
		else
			$roundedDoubleValue = $roundedDoubleValue / $baseInt;
		//@TODO: hierfür sollte das currency Objekt genutzt werden,
		// das beinhaltet digits, delemiter, etc.
		// das hier ist nur für die berechnung!
		return ($format) ? number_format($roundedDoubleValue, $digits, $delimiter, '') : $roundedDoubleValue;
	}




	/**
	 * Validate vatregno
	 *
	 * @param string $country cn_iso_2 value of country DE, CH etc...
	 * @param string $vatregno
	 */
	public function validateVatRegNo($country, $vatregno) {
		// if there is a uid, so get from database.
		if((string) (int)$country === (string) $country) {
			$country = tx_mklib_util_ServiceRegistry::getStaticCountriesService()->get($country);
		}
		// get iso from model
		if ($country instanceof tx_mklib_model_StaticCountry) {
			$country = $country->getCnIso_2();
		}

		$result = true;
		switch(strtoupper($country)) {
			// Hier jetzt die einzelnen Regeln implementieren.
			case 'DE':
				$result = preg_match('/^DE[0-9]{9}$/', $vatregno) > 0;
				break;
			case 'PL':
				$result = preg_match('/^PL[0-9]{10}$/', $vatregno) > 0;
				break;
			case 'FR':
				$result = preg_match('/^FR[A-Za-z0-9]{2} [0-9]{9}$/', $vatregno) > 0;
				break;
			case 'LU':
				$result = preg_match('/^LU[0-9]{8}$/', $vatregno) > 0;
				break;
			case 'BE':
				$result = preg_match('/^BE[0-9]{10}$/', $vatregno) > 0;
				break;
			case 'NL':
				$result = preg_match('/^NL[A-Za-z0-9]{10}$/', $vatregno) > 0;
				break;
			case 'DK':
				$result = preg_match('/^DK[0-9]{2} [0-9]{2} [0-9]{2} [0-9]{2}$/', $vatregno) > 0;
				break;
			case 'CZ':
				$result = preg_match('/^CZ[0-9]{8,10}$/', $vatregno) > 0;
				break;
			case 'AT':
				$result = preg_match('/^ATU[A-Za-z0-9]{8}$/', $vatregno) > 0;
				break;
		}
		return $result;
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Finance.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Finance.php']);
}
