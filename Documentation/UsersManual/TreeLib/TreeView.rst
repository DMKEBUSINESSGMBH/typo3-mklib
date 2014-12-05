.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

Tree View in the TCA
====================

Load the TCA class in ext_tables.php

.. code-block:: php

   if (TYPO3_MODE == 'BE')	{	
      tx_rnbase::load('tx_mklib_treelib_TCE');
   }
   
In the 'ctrl' section of the TCA for the table, which entries should be displayed
as a tree, the following has to be inserted.

.. code-block:: php

   // treeParentField: the field with the identifier of the parent record
   $TCA['tx_mkexample_categories']['ctrl']['treeParentField'] = 'parent';
   // treeParentMM: if treeParentField is not the UID of the parent
   // and the entries are found with a MM table, this has to be configuired
   $TCA['tx_mkexample_categories']['ctrl']['treeParentMM'] = array(
         'MM' => 'tx_mkexample_categories_mm',   
         'MM_match_fields' => array('tablenames' => 'tx_mkexample_categories'),   
         'MM_insert_fields' => array('tablenames' => 'tx_mkexample_categories'),
   );
   
The field configuration has to changed to display the tree view

.. code-block:: php

   $TCA['tx_mkexample_downloads']['columns']['category']['config'] = array(
      'form_type' => 'user',
      'userFunc' => 'tx_mklib_treelib_TCE->getSelectTree',
      'treeConfig' => array(
         // not just the label but also the label_alt as title (@see t3lib_befunc::getRecordTitle)
         'parseRecordTitle' => true,
         // use ajax for expand/collapse
         // Defaut is true
         // extension xajax is needed
         'useAjax' => false,
                   // if the parent records should not be selectable 
                   // default: false
                   'dontLinkParentRecords' => true
      ),
      'autoSizeMax' => 30,
      'treeView' => 1,
   );
   
Known problems
--------------
Normal backend users sometimes have trouble with rights for the root mount.
You can avoid this by setting forceAdminRootRecord so root records are displayed
for non admin users.