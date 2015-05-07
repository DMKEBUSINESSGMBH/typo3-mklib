.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.



Show Single Item
================

This abstract base class offers the possibility to have a rn_base single view on base of the
mklib abstract repository class to display a single database records.
The following methods have to be implemented in deriving actions.

getSingleItemRepository
-----------------------

Returns the repository for the database record. It hast to derive from tx_mklib_repository_Abstract.

The following methods can be overwritten.

getItemNotFound404Message
-------------------------

The message when the item could not be found.

Defaults to "Datensatz nicht gefunden."

Can be configured with TypoScript in the path "plugin.tx_myext.myActionConfId.notfound"
or in the loaded locallang with the key "myActionConfId_notfound".

getSingleItemUidParameterKey
----------------------------

The parameter key with the uid to show.

Defaults to "uid" in the qualifier namespace, for example myext[uid]

Can be configured with TypoScript in the path "plugin.tx_myext.myActionConfId.uidParameterKey"

deriving class example
----------------------
.. code-block:: php

   tx_rnbase::load('tx_mklib_action_ShowSingeItem');
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
          return tx_rnbase::makeInstance("tx_mkdemo_repository_Model");
      }
   }
   

   
TypoScript example configuration
--------------------------------

.. code-block:: ts

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
      notfound = Dataset could not be found.
      
      ### you can also configure a uid to show.
      ### this is preferred over parameters
      #uid = 123
   }
   
Example temlpate
----------------

.. code-block:: html

   ###SHOWDATASET###
      ###ITEM_UID###
   ###SHOWDATASET###