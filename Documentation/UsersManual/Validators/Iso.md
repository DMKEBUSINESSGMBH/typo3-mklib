IBAN
====

The IBAN model and validator is based on Jan Schädlich IBAN-Library. <https://github.com/jschaedl/Iban>

Validating an IBAN:

~~~~ {.sourceCode .php}
$model = Tx_Mklib_Domain_Model_Iso_Iban::getInstance($iban);
return $model->validate();
~~~~

Swift/Bic
=========

Validating an Swift/Bic:

~~~~ {.sourceCode .php}
$model = Tx_Mklib_Domain_Model_Iso_SwiftBic::getInstance($swift);
return $model->validate();
~~~~
