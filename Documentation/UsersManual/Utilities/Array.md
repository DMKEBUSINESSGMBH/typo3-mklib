Array
=====

This class contains methods to work with arrays.

removeEmptyValues
-----------------

Removes emtpy values from an array. 0, NULL and FALSE are considered as empty.

inArray
-------

Checks if one or more values are in an array.

fieldsToArray
-------------

Creates an array with the values of the columns from models/arrays.

Example:

~~~~ {.sourceCode .php}
$arrays = array(
   array('uid' => 1, 'name' => 'A1'),
   array('uid' => 2, 'name' => 'A2'),
   array('uid' => 3, 'name' => 'A3'),
);
$fields = tx_mklib_util_Array::fieldsToArray($arrays, 'name');
// the result
$fields = array('A1', 'A2', 'A3');
~~~~

fieldsToString
--------------

Like fieldsToArray but the output is a string.

Example:

~~~~ {.sourceCode .php}
$arrays = array(
   array('uid' => 1, 'name' => 'A1'),
   array('uid' => 2, 'name' => 'A2'),
   array('uid' => 3, 'name' => 'A3'),
);
$fields = tx_mklib_util_Array::fieldsToString($arrays, 'name', '|');
// the result
$fields = 'A1|A2|A3';
~~~~

Classes:

-   tx\_mklib\_util\_Array

