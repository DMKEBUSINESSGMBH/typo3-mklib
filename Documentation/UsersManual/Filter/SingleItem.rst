.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

tx_mklib_filter_SingleItem
==========================

Filter for single datasets. Just derive from this class and implement the abstract methods.
You can use this filter for a list action. You need just a new subpart in the template.
This way you can use the ###...EMPTYLIST### marker if the element could not be found.

.. code-block:: php

   require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
   tx_rnbase::load('tx_mklib_filter_SingleItem');
    
   class Tx_Mkexample_Filter_ShowCompany extends tx_mklib_filter_SingleItem {
    
      /**
       * @return string
       */
      protected function getParameterName() {
         return 'company';
      }
    
      /**
       * @return string
       */
      protected function getSearchAlias() {
         return 'COMPANY';
      }
   }