.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.






IBAN
====

The IBAN model and validator is based on Jan Schädlich IBAN-Library. https://github.com/jschaedl/Iban

Validating an IBAN:


.. code-block:: php


    tx_rnbase::load('Tx_Mklib_Domain_Model_Iso_Iban');
    $model = Tx_Mklib_Domain_Model_Iso_Iban::getInstance($iban);
    return $model->validate();


Swift/Bic
=========

Validating an Swift/Bic:


.. code-block:: php


    tx_rnbase::load('Tx_Mklib_Domain_Model_Iso_SwiftBic');
    $model = Tx_Mklib_Domain_Model_Iso_SwiftBic::getInstance($swift);
    return $model->validate();

