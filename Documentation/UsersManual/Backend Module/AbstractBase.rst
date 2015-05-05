.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.



Abstract module class
=====================

Provides methods to search in a backend module.

Sorting
-------
In the array for the columns provided by getDecoratorColumns you can set
sortable to TRUE. The field than has to contain the alias, which is used for searching.

Select a date range
-------------------
For that the method tx_mklib_mod1_util_Selector::showDateRangeSelector exists.

The output are two fields with from and until with a calendar.

Example:

.. code-block:: php

   $this->dateRange = $selector->showDateRangeSelector(
      $data['daterange'],
      $this->getSearcherId().'DateRange'
   );
         
      