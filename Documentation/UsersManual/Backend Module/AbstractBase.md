Abstract module class
=====================

Provides methods to search in a backend module.

Sorting
-------

In the array for the columns provided by getDecoratorColumns you can set sortable to TRUE. The field than has to contain the alias, which is used for searching.

Select a date range
-------------------

For that the method tx\_mklib\_mod1\_util\_Selector::showDateRangeSelector exists.

The output are two fields with from and until with a calendar.

Example:

~~~~ {.sourceCode .php}
$this->dateRange = $selector->showDateRangeSelector(
   $data['daterange'],
   $this->getSearcherId().'DateRange'
);
~~~~
