.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

Sorting of database searches
============================
Registers a hook for rn_base to enhance SQL queries with a default sorting.

It's usefull when the result should always be sorted by title or the output
should always use the sorting column,

Example
-------
.. code-block:: php

    tx_mklib_util_SearchSorting::registerSortingAliases(array('TABLEALIAS1', 'TABLEALIAS2'=>'title'));
   
This call registers the hook if not done already and adds the default sorting for SQL queries
with rn_base. If the alias TABLEALIAS1 occurs in the $fields array,
ORDER BY TABLEALIAS1.sorting ASC is automatically set for the SQL statement. If the alias
TABLEALIAS2 occurs ORDER BY TABLEALIAS2.title ASC si appended.


Classes:

* tx_mklib_util_SearchSorting