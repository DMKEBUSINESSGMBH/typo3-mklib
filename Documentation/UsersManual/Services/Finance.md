Finance
=======

Methods for the correct calculation of money prices.

~~~~ {.sourceCode .php}
$financeSrv = tx_mklib_util_ServiceRegistry::getFinanceService();
~~~~

With getCurrency() a curreny model is created, to format the output. Currently only the Euro is supported.
