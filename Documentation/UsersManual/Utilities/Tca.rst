.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.






TCA
===

Preallocate field with the uid of the parent
--------------------------------------------

When a child element has a field for the parent and this child should
be created inside the parent it is useful wo have the parent preallocated
in the child.

.. code-block:: php
    
   $TCA['db_table']['columns']['my_column']['config']['default'] = tx_mklib_util_TCA::getParentUidFromReturnUrl();

Shorten label
-------------

Sometimes a text field is used as label. In the list view the label is shortened.
In selectboxes etc. nothing is shortened.

.. code-block:: php
    
   $feldName['config']['itemsProcFunc'] = 'tx_mklib_util_TCA->cropLabels'; 
   
The label length is configured in $tcaTableInformation['config']['labelLength']

German states field
-------------------

A method to retrieve a TCA field a german state selection.

Classes:

* tx_mklib_util_TCA