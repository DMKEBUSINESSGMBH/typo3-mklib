Sorting of database searches
============================

Registers a hook for rn\_base to enhance SQL queries with a default sorting.

It's usefull when the result should always be sorted by title or the output should always use the sorting column,

Example
-------

~~~~ {.sourceCode .php}
tx_mklib_util_SearchSorting::registerSortingAliases(array('TABLEALIAS1.tablename', 'TABLEALIAS2.tablename'=>'title'));
~~~~

This call registers the hook if not done already and adds the default sorting for SQL queries with rn\_base. If the alias TABLEALIAS1 occurs in the \$fields array, ORDER BY TABLEALIAS1.sorting ASC is automatically set for the SQL statement. If the alias TABLEALIAS2 occurs ORDER BY TABLEALIAS2.title ASC is appended. But this is only the case if the alias is registered for the mapped table. If you use TABLEALIAS1 for tablename2 the orderby is not set.

Classes:

-   tx\_mklib\_util\_SearchSorting

