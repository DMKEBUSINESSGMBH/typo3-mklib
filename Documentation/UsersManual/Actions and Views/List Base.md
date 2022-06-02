List Base
=========

This abstract base class offers the possibility to have a rn\_base list view on base of the mklib base service class to display database records. The following methods have to be implemented in deriving actions.

getTemplateName
---------------

Returns the name of the default Template. Is furthermore used to build the TypoScript configuration ID for the action. For that it is just appended with a dot.

getService
----------

Returns the service class with implements the search method for the rn\_base search framework.

getTsPathPageBrowser
--------------------

The TypoScript path to the rn\_base pagebrowser configuration going out from the configuration ID of the action.

deriving class example
----------------------

~~~~ {.sourceCode .php}
class tx_mkdemo_action_ListDatasets extends tx_mklib_action_ListBase {

   /**
    * @return string
    */
   public function getTemplateName() {
            return 'listdatasets';
        }

   protected function getService() {
       return tx_mkdemo_util_ServiceRegistry::getDatasetService();
   }

   /**
    * @return string
    */
   protected function getTsPathPageBrowser() {
       return 'dataset.pagebrowser';
   }
}
~~~~

TypoScript example configuration
--------------------------------

~~~~ {.sourceCode .ts}
listDatasets{
   template{
       ### is used as TypoScript path and HTML marker prefix
       ### default is item
       itempath = dataset
       markerclass = Tx_Myext_Marker_Dataset
       ### alternative HTML marker subpart. 
       ### if not configured the action configuration ID is used. In this
       ### case ###LISTDATASETS###
       #subpart = ###PROJECTLIST###
   }
   ### the TypoScript configuration for every dataset
   ### is configured with template.itempath 
   dataset{
   }
}
~~~~

Example temlpate
----------------

~~~~ {.sourceCode .html}
###LISTDATASETS###
   ###DATASETS###
      ###DATASET###
         ###DATASET_UID###
      ###DATASET###
   ###DATASETS###
###LISTDATASETS###
~~~~
