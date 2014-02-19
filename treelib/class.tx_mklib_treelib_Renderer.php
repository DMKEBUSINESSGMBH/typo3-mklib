<?php
/**
 *  @package tx_mklib
 *  @subpackage tx_mklib_treelib
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@das-medienkombinat.de>
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
 ***************************************************************/
require_once (t3lib_extMgm::extPath('rn_base').'class.tx_rnbase.php');

/**
 * Rendert die TreeViews
 * 
 * @package tx_mklib
 * @subpackage tx_mklib_treelib
 * @author Michael Wagner
 */
class tx_mklib_treelib_Renderer {
	/**
	 * 
	 * @var t3lib_TCEforms
	 */
	private $oTceForm = null;
	
	/**
	 * Liefert eine Instans des Treeviews
	 * 
	 * @param 	array 			$PA
	 * @param 	t3lib_TCEforms 	$fObj
	 * @return 	tx_mklib_treelib_Renderer
	 */
	public static function makeInstance($PA, &$fObj){
		$oTreeView = tx_rnbase::makeInstance('tx_mklib_treelib_Renderer', $PA, $fObj);
		return $oTreeView;
	}
	
	/**
	 * Initialisiert den Treeview
	 *
	 * @param 	array 			$PA
	 * @param 	t3lib_TCEforms 	$fObj
	 * @return 	string
	 * 
	 * @return	void
	 */
	function tx_mklib_treelib_Renderer($PA, &$fObj)	{
		$this->oTceForm = &$PA['pObj'];
		$this->PA = &$PA;
	}
	
	/**
	 * Erzeugt den Baum und gibt das HTML zurück.
	 * 
	 * @param 	tx_mklib_treelib_TreeView 	$oTreeView
	 * @return 	string
	 */
	public function getBrowsableTree(&$oTreeView) {
		
		$content  = '';
		$content .= '<span id="'. $this->treeName . '">';
		$content .= $oTreeView->getBrowsableTree();
		$content .= '<span>';
		$content .= $oTreeView->getHiddenField();

		// unnötige umbrüche und leerzeichen entfernen
		$needle = array ('/[\r\n\t]/', '/> +?</' );
		$replace = array ('', '><' );
		$content = preg_replace($needle, $replace, $content);
						
		return $content;
	}
	
	/**
	 * Erzeugt Das Selectfeld mit der Baumstruktur.
	 * 
	 * @param 	tx_mklib_treelib_TreeView 	$oTreeView
	 * @return 	string
	 */
	public function renderTreeView(&$oTreeView, &$oTtce=null){

		$oConfig = $oTreeView->getConfig();
		
		$iMaxItems = $oConfig->getMaxItems();
		
		$content  = $this->getBrowsableTree($oTreeView);
		
		$divStyle = $oConfig->getTreeWrapStyle();
		$content  = '<div  name="' . $this->PA['itemFormElName'] . '_selTree" id="'.$oTreeView->treeName.'-tree-div" style="'.htmlspecialchars($divStyle).'">'.
						$content.'</div>';
		
		$sSelectedListStyle = $oConfig->getSelectedListStyle();
		$sSelectedListStyle = $sSelectedListStyle ? ' style="'.$sSelectedListStyle.'"' : '';
		$params = array(
			'size' => $oConfig->getSize(),
			'autoSizeMax' => $oConfig->getAutoSizeMax(),
			'style' => $sSelectedListStyle,
			'dontShowMoveIcons' => ($iMaxItems<=1),
			'maxitems' => $iMaxItems,
			'info' => '',
			'headers' => array(
				'selector' => $this->oTceForm->getLL('l_selected').':<br />',
				'items' => $this->oTceForm->getLL('l_items').':<br />'
			),
			'noBrowser' => true,
//			'readOnly' => $disabled,
			'thumbnails' => $content
		);

		$content = $this->oTceForm->dbFileIcons(
						$this->PA['itemFormElName'],
						$oConfig->get('internal_type'),
						$oConfig->get('allowed'),
						$this->getItemArray($oTreeView),
						'',
						$params,
						$this->PA['onFocus']
					);
		
		// Wizards:
		$content = $this->renderWissards($oTreeView, $content);
		
		$content = $this->addJs($oTreeView, $content, $oTtce);
		
		return $content;
	}
	
	/**
	 * Fügt die Wizzards hinzu.
	 * 
	 * @param 	tx_mklib_treelib_TreeView 	$oTreeView
	 * @param 	string 						$sContent
	 * @return 	string
	 */
	private function renderWissards($oTreeView, $sContent){
		$altItem = '<input type="hidden" name="' . $this->PA['itemFormElName'] . '" value="' . htmlspecialchars($this->PA['itemFormElName']) . '" />';
		$sContent = $this->oTceForm->renderWizards(
							array($sContent, $altItem),
							$oTreeView->getConfig()->getWizards(),
							$oTreeView->table,
							$oTreeView->row,
							$oTreeView->parentField,
							$this->PA,
							$this->PA['itemFormElName'],
							$specConf
						);
		return $sContent;
	}
	
	/**
	 * Fügt benötigtes Javascript hinzu.
	 * 
	 * @param 	tx_mklib_treelib_TreeView 	$oTreeView
	 * @param 	string 						$content
	 * @param 	tx_mklib_treelib_TCE 		$oTtce
	 * @return 	string
	 */
	private function addJs(&$oTreeView, $content, &$oTtce=null){
			
		//@todo ajax funktionalitäten von typo3 nutzen wenn möglich
		//damit nicht extra die xajax Extension installiert werden muss
		if($oTreeView->useAjax() && is_object($oTtce)) {
//		require_once(t3lib_extMgm::extPath( 'xajax', 'class.tx_xajax.php'));
			$xajax = tx_rnbase::makeInstance('tx_xajax');
			$xajax->setWrapperPrefix($oTreeView->treeName.'_');
			$xajax->registerFunction(array('sendXajaxResponse', &$oTtce, 'sendXajaxResponse'));
			$js .= $xajax->getJavascript ( '../' . t3lib_extMgm::siteRelPath ( 'xajax' ) );
			$xajax->processRequests ();
			$content .= $js;
		}
		
		return $content;
		
		if($this->oTceForm->additionalJS_pre['tx_mklib_tree_'.$oTreeView->treeName]) {
			return ;
		}
		// add add js
		$js ='			function getFormValueSelected(fName)	{	//
							var formObj = setFormValue_getFObj(fName)
								if (formObj)	{
							var result = "";
							var localArray_V = new Array();
							var fObjSel = formObj[fName+"_list"];
							var l=fObjSel.length;
							var c=0;
							for (a=0;a<l;a++)	{
								if (fObjSel.options[a].selected==1)	{
									localArray_V[c++]=fObjSel.options[a].value;
								}
							}
						}
						result = localArray_V.join("_");
						return result;	
				}';
		$needle = array ('/ {2,}/','/\}\r\n/','/\t{2,}/');
		$replace = array (' ','}',' ');
		$js = preg_replace($needle, $replace, $js);
		$this->oTceForm->additionalJS_pre['tx_mklib_tree_'.$oTreeView->treeName] = $js;
	}
	
	/**
	 * Liefert bereits selektierte Elemente.
	 * @param 	tx_mklib_treelib_TreeView 	$oTreeView
	 * @return 	array
	 */
	private function getItemArray(&$oTreeView) {
		$itemArrayProcessed = array();
		foreach($oTreeView->getItemArray() as $tk => $tv ) {
			$tvP = explode('|', $tv, 2);
			$tvP[1] = rawurlencode( $this->oTceForm->sL( rawurldecode( $tvP[1] ) ) );
			$itemArrayProcessed[$tk] = implode('|', $tvP);
		}
		return $itemArrayProcessed;
	}
			
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/treelib/class.tx_mklib_treelib_Renderer.php'])	{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/treelib/class.tx_mklib_treelib_Renderer.php']);
}