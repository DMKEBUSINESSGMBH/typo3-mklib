.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.






IBAN
====

The IBAN model and validator is based on Jan Schädlich IBAN-Library. https://github.com/jschaedl/Iban

Validating an IBAN:


.. code-block:: php


    tx_rnbase::load('Tx_Mklib_Domain_Model_Iban');
    $model = Tx_Mklib_Domain_Model_Iban::getInstance($iban);
    return $iban->validate();
