Show Single Item
================

This abstract base class offers the possibility to have a rn\_base single view on base of the mklib abstract repository class to display a single database records. The following methods have to be implemented in deriving actions.

getSingleItemRepository
-----------------------

Returns the repository for the database record. It hast to derive from tx\_mklib\_repository\_Abstract.

The following methods can be overwritten.

getSingleItemUidParameterKey
----------------------------

The parameter key with the uid to show.

Defaults to "uid" in the qualifier namespace, for example myext[uid]

Can be configured with TypoScript in the path "plugin.tx\_myext.myActionConfId.uidParameterKey"

getPageTitle
------------

The value with that the page title is substituted. It is not abstract so it has not necessarily to be provided. Only if "plugin.tx\_myext.myActionConfId.substitutePageTitle" is enabled.

No default value. So take care of it when "plugin.tx\_myext.myActionConfId.substitutePageTitle" is enabled.

deriving class example
----------------------

~~~~ {.sourceCode .php}
class tx_mkdemo_action_ShowDataset extends tx_mklib_action_ShowSingeItem {

   /**
    * @return string
    */
   public function getTemplateName() {
        return 'showDataset';
   }

   /**
    * @return tx_mklib_repository_Abstract
    */
   protected function getSingleItemRepository() {
       return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("tx_mkdemo_repository_Model");
   }

   /**
    * @return string
    */
   protected function getPageTitle() {
        return $this->getConfigurations()->getViewData()->offsetGet('item')->getTitle();
   }
}
~~~~

TypoScript example configuration
--------------------------------

~~~~ {.sourceCode .ts}
showDataset{
   template{
       ### is used as TypoScript path and HTML marker prefix
       ### default is item
       itempath = dataset
       markerclass = Tx_Myext_Marker_Dataset
       ### alternative HTML marker subpart.
       ### if not configured the action configuration ID is used. In this
       ### case ###SHOWDATASET###
       #subpart = ###SHOWANOTHERDATASET###
   }
   ### the TypoScript configuration for every dataset
   ### is configured with template.itempath
   dataset{
   }

   uidParameterKey = myOwnParameterKey

   ### shouldn't 404 typo3 handling be triggered if item not found
   #disable404ExceptionIfNoItemFound = 1

   ### should the page title be substituted?
   ### if so the method getPageTitle has to provide the page title
   substitutePageTitle = 1

   ### you can also configure a uid to show.
   ### this is preferred over parameters
   #uid = 123
}
~~~~

Example temlpate
----------------

~~~~ {.sourceCode .html}
###SHOWDATASET###
   ###ITEM_UID###
###SHOWDATASET###
~~~~
