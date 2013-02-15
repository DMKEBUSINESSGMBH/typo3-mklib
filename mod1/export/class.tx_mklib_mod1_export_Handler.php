<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_mod1
 *
 *  Copyright notice
 *
 *  (c) 2012 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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

require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');

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
						label = Excel
						spriteIcon = mimetypes-excel
						template {
							subpart =
							itempath
							markerclass
						}
					}
					zip {
						label = Zip
						spriteIcon = mimetypes-compressed
					}
					pdf {
						label = PDF
						spriteIcon = mimetypes-pdf
					}
					csv {
						label = CSV
						spriteIcon = mimetypes-text-csv
					}
					html {
						label = HTML
						spriteIcon = mimetypes-text-html
					}
					text {
						label = Text
						spriteIcon = mimetypes-text-text
					}
				}
			}
		}
	}
 *
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mklib_mod1_export_Handler {

	private $modFunc = NULL;

	public function tx_mklib_mod1_export_Handler(
		tx_mklib_mod1_export_IModFunc $modFunc
	) {
		$this->modFunc = $modFunc;
	}

	/**
	 * Prüft, ob ein Export durchgeführt werden soll und führt diesen durch.
	 */
	public function handleExport() {
		$parameters = t3lib_div::_GPmerged('mklib');
		if (empty($parameters['export'])) {
			return ;
		}

		// den Typ des Exports auslesen;
		$type = reset(array_keys($parameters['export']));
		$types = $this->getExportTypes();

		if (!in_array($type,$types)) {
			return ;
		}

		$template = $this->getExportTemplate($type);

		echo '<pre>'.var_export(array(
				$type,
				$this->getExportTemplate($type),
				'DEBUG: '.__FILE__.'&'.__METHOD__.' Line: '.__LINE__
		),true).'</pre>'; // @TODO: remove me
		return;

		/*
		 * @TODO: rendern der einzelnen items.
		 * das ganze allerdings nicht wie hier in den rückgabewert $tamplate,
		 * sondern idealerweise direkt ausgeben um den speicher gleich wieder freizuräumen!
		 */

		/* @var $provider tx_rnbase_util_ListProvider */
		$provider = tx_rnbase::makeInstance('tx_rnbase_util_ListProvider');
		$provider->initBySearch(array($service, 'search'), $fields, $options);


		/* @var $listBuilder tx_rnbase_util_ListBuilder */
		$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
		$template = $listBuilder->renderEach(
			$provider, $viewData,
			$template, 'tx_mkhoga_marker_ApplicationJobOfferImmeditate',
			$confId.'applicationjobofferimmeditate.', 'APPLICATIONJOBOFFERIMMEDITATE', $formatter
		);



		// @TODO: vorher muss noch das Template ausgelesen werden.
		// die cbExportItem sollte nicht hier,
		// sondern in einem extra handler existieren!

		$this->getModFunc()->getSearcher()
			->searchForExport(array($this, 'cbExportItem'));
	}

	/**
	 * Erzeugt Marker für das Module Template,
	 * um die Ausgabe der Export funktionen zu implementieren.
	 * Folgende Marker werden erzeugt:
	 * ###EXPORT_BUTTONS###
	 */
	public function parseTemplate($template) {
		tx_rnbase::load('tx_rnbase_util_BaseMarker');
		if (tx_rnbase_util_BaseMarker::containsMarker($template, $markerPrefix)) {

		}

		$configuration = $this->getModule()->getConfigurations();
		$confId = $this->getModFunc()->getConfId().'export.types.';

		$buttons = '';
		foreach ($this->getExportTypes() as $type) {
			$sprite = $configuration->get($confId.$type.'.spriteIcon');
			$button = $this->getModule()->getFormTool()->createSubmit(
				'mklib[export]['.$type.']',
				$configuration->get($confId.$type.'.label')
			);
			if ($sprite) {
				$button = tx_rnbase_mod_Util::getSpriteIcon($sprite) . $button;
			}
			$buttons .= '<span class="imgsubmit">'.$button.'</span>';
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
	 * @return array
	 */
	private function getExportTypes() {
		return $this->getModule()->getConfigurations()->getKeyNames(
			$this->getModFunc()->getConfId().'export.types.'
		);
	}

	/**
	 *
	 * @param tx_rnbase_model_base $Item
	 */
	public function cbExportItem(
		tx_rnbase_model_base $Item
	) {

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
	private function getExportTemplate($type) {
		$configuration = $this->getModule()->getConfigurations();
		$confId = $this->getModFunc()->getConfId().'export.types.'.$type.'.template.';

		// template laden
		$sAbsPath = t3lib_div::getFileAbsFileName( $configuration->get($confId.'template') );
		$templateCode = t3lib_div::getURL($sAbsPath);
		if(!$templateCode) {
			$this->getModule()->addMessage(
				'Could not find the template "'.$sAbsPath.'"  defined under '.$confId.'subpart'.'.',
				'Template not found', 2
			);
			return FALSE;
		}
		// subpart auslesen
		$subpart = t3lib_div::getFileAbsFileName( $configuration->get($confId.'subpart') );
		tx_rnbase::load('tx_rnbase_util_Templates');
		$template = tx_rnbase_util_Templates::getSubpart($templateCode, $subpart);
		if(!$template) {
			$this->getModule()->addMessage(
				'Could not find the the subpart "'.$subpart.'" in template "'.$sAbsPath.'".',
				'Subpart not found', 2
			);
			return FALSE;
		}
		return $template;
	}

	/**
	 * Liefert die styles der Buttons
	 *
	 * @return string
	 */
	private function getButtonStyles() {
		return '<style type="text/css">
		.imgsubmit {
			position: relative;
			margin: 0 5px;
			float: left;
		}
		.imgsubmit span {
			left: 8px;
			margin: 0;
			position: absolute;
			top: 2px;
		}
		.imgsubmit input[type="submit"] {
			float: none;
			padding-left: 24px;
		}
		</style>';
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_Handler.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_Handler.php']);
}
