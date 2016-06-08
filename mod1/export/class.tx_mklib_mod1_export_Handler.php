<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_mod1
 *
 *  Copyright notice
 *
 *  (c) 2012 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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


tx_rnbase::load('tx_mklib_mod1_export_Util');
tx_rnbase::load('tx_rnbase_mod_Util');
tx_rnbase::load('tx_rnbase_util_Files');

/**
 * Handelt die über Typoscript definierte Exportfunktionalität.
 *
 * BeispielTS: EXT:mkextension/mod1/pageTSconfig.txt
 *
	mod {
		mkextension {
			funcmodule {
				template = EXT:mkhoga/mod1/templates/funccompanies.html
				export.types {
					excel {
						### Label des Buttons, kann auch: ###LABEL_*
						label = Excel
						### Diese Beschreibung wird als ToolTip beim Hovern über den Button ausgegeben. Kann auch: ###LABEL_*
						description = Firmen mit Hauptkontakt und Anzahl geschaltener Anzeigen
						### ein für Typo3 bekanntes Sprite Icon. Siehe tx_rnbase_mod_Util::debugSprites
						spriteIcon = mimetypes-excel
						### Wenn gesetzt, wird das BOM (Byte Order Mark) an den Anfang der Ausgabe gesetzt.
						### Das ist z.B. bei UTF-8 Ausgaben nötig für eine korrekte Darstellung in MS Excel.
						BOM = 0
						### konfiguration für das template
						template {
							### Pfad zum Template
							template = EXT:mkhoga/mod1/templates/export/data.xls
							### Marker für den Subpart
							subpart = ###DATALIST###
							### Item Pfad, wird für die confid (lowercase) und den markernamen (uppercase) genutzt. Default ist item
							itempath = data
							### Markerklasse, wleche die daten rendert. Default ist tx_rnbase_util_SimpleMarker
							markerclass = tx_mkhoga_marker_Data
						}
						### Header konfiguration. Diese header werden gesendet, wenn ein export statfindet.
						headers {
							### der Dateiname
							filename = companies.xls
							### es kann auch eine USER Func oder sonstiges TypoScript verwendet werden.
							### Bei einer userFunc muss man sich nur selbst darum kümmern die Klasse
							### zu laden da ein filename.includeLibs = EXT:myext/Classes/Utility/Backend.php nicht
							### funktioniert. Das kann mit autoload gemacht werden oder man ruft
							### tx_rnbase::load('Tx_Myext_Utility_Backend') auf, bevor die PageTS Config Datei,
							### welche dieses TypoScript enthält, geladen wird.
							#filename = USER
							#filename.userFunc = Tx_Myext_Utility_Backend->getFileNameForExport
							### der contenttype
							contenttype = application/vnd.ms-excel
							### zusätzliche Headerdaten ($key: $value)
							additional {
								### daraus wird location: http://www.das-medienkombinat.de/
								#location = http://www.das-medienkombinat.de/
							}
						}
					}
					csv {
						label = CSV
						spriteIcon = mimetypes-text-csv
					}
				}
			}
		}
	}
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_mod1_export_Handler {

	/**
	 * @var tx_mklib_mod1_export_IModFunc
	 */
	private $modFunc = NULL;

	/**
	 * Byte Order Mark = BOM
	 * @return void
	 */
	public static function getByteOrderMark() {
		return ( chr(0xEF) . chr(0xBB) . chr(0xBF) );
	}

	/**
	 * @param tx_mklib_mod1_export_IModFunc $modFunc
	 * @return void
	 */
	public function tx_mklib_mod1_export_Handler(
		tx_mklib_mod1_export_IModFunc $modFunc
	) {
		$this->modFunc = $modFunc;
	}

	/**
	 * Prüft, ob ein Export durchgeführt werden soll und führt diesen durch.
	 */
	public function handleExport() {

		// den Typ des Exports auslesen;
		$type = $this->getCurrentExportType();
		if (!$type) {
			return;
		}

		$template = $this->getExportTemplate($type);
		$provider = $this->getListProvider();

		if (!$template || !$provider) {
			return ;
		}

		// Ignoriere Stop-Button
		ignore_user_abort(true);
		// No Time-Limit
		set_time_limit(0);

		$itemPath = $this->getItemPath($type);

		// Der Subpart für Debug-Ausgaben wird am ende ausgegeben
		$debug = tx_rnbase_util_Templates::getSubpart($template, '###DEBUG###');
		if ($debug) {
			$template = tx_rnbase_util_Templates
				::substituteSubpart($template, '###DEBUG###', '');
			$timeStart = microtime(true);
			$memStart = memory_get_usage();
		}

		tx_mklib_mod1_export_Util::sendHeaders($this->getHeaderConfig($type));

		if ($this->isByteOrderMarkRequired($type)) {
			echo self::getByteOrderMark();
		}

		/* @var $listBuilder tx_mklib_mod1_export_ListBuilder */
		$listBuilder = tx_rnbase::makeInstance('tx_mklib_mod1_export_ListBuilder');
		$template = $listBuilder->renderEach(
			$provider, FALSE,
			$template,
			$this->getMarkerClass($type),
			$this->getModFunc()->getConfId().strtolower($itemPath).'.',
			strtoupper($itemPath),
			$this->getConfigurations()->getFormatter()
		);

		$this->parseDebugs($debug, $timeStart, $memStart);

		// done!
		exit();
	}

	/**
	 * Liefert den aktuell angeforderten ExportTyp (string) oder false (boolean)
	 *
	 * @return string|boolean
	 */
	public function getCurrentExportType() {
		$parameters = tx_rnbase_parameters::getPostAndGetParametersMerged('mklib');
		if (empty($parameters['export'])) {
			return FALSE;
		}

		// den Typ des Exports auslesen;
		$type = reset(array_keys($parameters['export']));
		$types = $this->getExportTypes();

		if (!in_array($type,$types)) {
			return FALSE;
		}

		return $type;
	}

	/**
	 * Erzeugt Marker für das Module Template,
	 * um die Ausgabe der Export funktionen zu implementieren.
	 * Folgende Marker werden erzeugt:
	 * ###EXPORT_BUTTONS###
	 */
	public function parseTemplate($template) {
		tx_rnbase::load('tx_rnbase_util_BaseMarker');
		if (!tx_rnbase_util_BaseMarker::containsMarker($template, 'EXPORT_BUTTONS')) {
			return $template;
		}

		$configuration = $this->getConfigurations();
		$confId = $this->getConfId().'types.';

		$buttons = '';
		foreach ($this->getExportTypes() as $type) {
			$buttons .= $this->renderButton($type);
		}

		if (!empty($buttons)){
			$buttons = $this->getButtonStyles() . $buttons;
		}

		$markerArray = array();
		$markerArray['###EXPORT_BUTTONS###'] = $buttons;

		$out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray);
		return $out;
	}

	/**
	 * Rendert einen einzelnen Button inklusive Icons und Beschreibungs-Tooltip.
	 * @param string $type
	 * @return string
	 */
	protected function renderButton($type) {
		static $infoSprite = FALSE;

		if ($infoSprite == FALSE) {
			$infoSprite = tx_rnbase_mod_Util
				::getSpriteIcon('status-dialog-information');
		}

		$configuration = $this->getConfigurations();
		$confId = $this->getConfId().'types.';

		$label = $configuration->get($confId.$type.'.label');
		$sprite = $configuration->get($confId.$type.'.spriteIcon');
		$button = $this->getModule()->getFormTool()->createSubmit(
			'mklib[export]['.$type.']',
			$label
		);
		if ($sprite) {
			$sprite = tx_rnbase_mod_Util::getSpriteIcon($sprite);
		}
		$description = $configuration->get($confId.$type.'.description');
		if ($description) {
			$description = '<span class="bgColor2 info">'
			. $infoSprite . '<strong>' . $label . '</strong><br />'
			. $description . '</span>';
		}
		$button = '<span class="imgbtn">' . $sprite . $button . '</span>';
		return '<span class="mklibexport">' . $button . $description . '</span>';
	}

	/**
	 * @return array
	 */
	private function getExportTypes() {
		return $this->getConfigurations()->getKeyNames(
			$this->getConfId().'types.'
		);
	}

	/**
	 * Returns the module function
	 *
	 * @return tx_mklib_mod1_export_IModFunc
	 */
	protected function getModFunc() {
		return $this->modFunc;
	}

	/**
	 * Liefert den Searcher des Module
	 *
	 * @return tx_mklib_mod1_export_ISearcher
	 */
	protected function getSearcher() {
		$searcher = $this->getModFunc()->getSearcher();
		if (!$searcher instanceof tx_mklib_mod1_export_ISearcher) {
			throw new Exception(
				'The searcher "'.get_class($searcher).'" has to implement'.
				' the interface "tx_mklib_mod1_export_ISearcher"',
				1361174776
			);
		}
		// wir setzen optional den export handler
		if ($searcher instanceof tx_mklib_mod1_export_IInjectHandler) {
			$searcher->setExportHandler($this);
		}
		return $searcher;
	}

	/**
	 * Liefert den Provider für die Listenausgabe.
	 *
	 * @return tx_rnbase_util_IListProvider
	 */
	protected function getListProvider() {
		$provider = $this->getSearcher()->getInitialisedListProvider();
		if (!$provider instanceof tx_rnbase_util_IListProvider) {
			$this->getModule()->addMessage(
				'The provider "'.get_class($provider).'" has to implement'.
				' the interface tx_rnbase_util_IListProvider',
				'Subpart not found', 2
			);
			return FALSE;
		}
		return $provider;
	}

	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 *
	 * @return 	tx_rnbase_mod_IModule
	 */
	protected function getModule() {
		return $this->getModFunc()->getModule();
	}

	/**
	 * Liefert das Template für den Export
	 * eigentlich private, für tests protected
	 * @param string $type
	 * @return string
	 */
	protected function getExportTemplate($type) {
		$configuration = $this->getConfigurations();
		$confId = $this->getConfId().'types.'.$type.'.template.';

		// template laden
		$absPath = tx_rnbase_util_Files::getFileAbsFileName( $configuration->get($confId.'template') );
		$template = tx_rnbase_util_Network::getUrl($absPath);
		if(!$template) {
			$this->getModule()->addMessage(
				'Could not find the template "' . $absPath . '"  defined under ' . $confId . 'template.',
				'Template not found',
				2
			);
			return false;
		}
		// subpart optional auslesen
		$subpart = $configuration->get($confId.'subpart');
		if ($subpart) {
			tx_rnbase::load('tx_rnbase_util_Templates');
			$template = tx_rnbase_util_Templates::getSubpart($template, $subpart);
			if(!$template) {
				$this->getModule()->addMessage(
					'Could not find the the subpart "'.$subpart.'" in template "'.$sAbsPath.'".',
					'Subpart not found', 2
				);
				return false;
			}
		}

		if ($configuration->getBool($confId . 'callModules')) {
			$markerArray = $subpartArray = $wrappedSubpartArray = $params = array();
			tx_rnbase_util_BaseMarker::callModules(
				$template,
				$markerArray,
				$subpartArray,
				$wrappedSubpartArray,
				$params,
				$this->getConfigurations()->getFormatter()
			);
			$template = tx_rnbase_util_Templates::substituteMarkerArrayCached(
				$template,
				$markerArray,
				$subpartArray,
				$wrappedSubpartArray
			);
		}

		return $template;
	}

	/**
	 * @return string
	 */
	protected function getMarkerClass($type) {
		$configuration = $this->getConfigurations();
		$confId = $this->getConfId().'types.'.$type.'.template.';
		$class = $configuration->get($confId.'markerclass');
		$class = $class ? $class : 'tx_rnbase_util_SimpleMarker';
		if (!tx_rnbase::load($class)) {
			$class = 'tx_rnbase_util_SimpleMarker';
		}
		return $class;
	}

	/**
	 * @return string
	 */
	protected function getItemPath($type) {
		$configuration = $this->getConfigurations();
		$confId = $this->getConfId().'types.'.$type.'.template.';
		$class = $configuration->get($confId.'itempath');
		return $class ? $class : 'item';
	}

	/**
	 *
	 * @param string $type
	 * @return array
	 */
	protected function getHeaderConfig($type) {
		$headers = $this->getConfigurations()->get(
			$this->getConfId().'types.'.$type.'.headers.', TRUE
		);
		return is_array($headers) ? $headers : array();
	}

	/**
	 * test if BOM is set in configuration
	 *
	 * @param $type
	 * @return bool
	 */
	protected function isByteOrderMarkRequired($type) {
		$configuration = $this->getConfigurations();
		$confId = $this->getConfId() . 'types.';

		return $configuration->getBool($confId . $type . '.BOM');
	}

	/**
	 * @return tx_rnbase_configurations
	 */
	protected function getConfigurations() {
		return $this->getModule()->getConfigurations();
	}

	/**
	 * @return string
	 */
	protected function getConfId() {
		return $this->getModFunc()->getConfId().'export.';
	}

	/**
	 * Parst den DEBUG Subpart und gibt diesen direkt aus!
	 *
	 * @param string $template
	 * @param int $timeStart
	 * @param int $memStart
	 * @param array $markerArr
	 * @return boolean
	 */
	protected function parseDebugs(
		$template,
		$timeStart = 0, $memStart = 0,
		array $markerArr = array()
	) {
		if (empty($template)) {
			return FALSE;
		}
		tx_rnbase::load('tx_mklib_util_Date');
		$memEnd = memory_get_usage();
		$markerArr['###DEBUG_PARSETIME###'] = (microtime(true) - $timeStart);
		$markerArr['###DEBUG_MEMUSED###'] = ($memEnd - $memStart);
		$markerArr['###DEBUG_MEMSTART###'] = $memStart;
		$markerArr['###DEBUG_MEMEND###'] = $memEnd;
		$markerArr['###DEBUG_DATE###'] = tx_mklib_util_Date::getExecDate(DATE_ATOM);
		$markerArr['###DEBUG_ITEMCOUNT###'] = 'N/A';
		// die anzahl der ausgegebenen Datensätze ermitteln.
		$provider = $this->getListProvider();
		if ($provider instanceof tx_rnbase_util_ListProvider) {
			$params = array($provider->fields, $provider->options);
			$params[1]['count'] = 1;
			$count = call_user_func_array($provider->searchCallback, $params);
			$markerArr['###DEBUG_ITEMCOUNT###'] = $count;
		}
		$out = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArr);
		echo $out;
		return TRUE;
	}

	/**
	 * Liefert die styles der Buttons
	 *
	 * @return string
	 */
	private function getButtonStyles() {
		$css = '<style type="text/css">
		.mklibexport {
			display: block;
			position: relative;
		}
		.mklibexport .imgbtn {
			position: relative;
			margin: 5px;
			float: left;
		}
		.mklibexport .imgbtn span.t3-icon {
			left: 8px;
			margin: 0;
			position: absolute;
			top: 2px;
		}
		.mklibexport span.info {
			display: none;
			position: absolute;
			padding: 5px;
			top: 25px
		}
		.mklibexport:hover span.info {
			display: block;
		}
		.mklibexport input[type="submit"] {
			float: none;
			padding-left: 24px;
		}
		</style>';
		// alle umbrüche und tabs entfernen
		return str_replace(array("\t", "\n", "\r"), '', $css);
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_Handler.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_Handler.php']);
}
