.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.



Find unused locallang labels
============================

Lists all locallang labels, that can't be found in the given folders except the locallang file itself.

Call it like:

typo3/cli_dispatch.phpsh mklib_find_unused_locallang_labels --locallangFile=/some/ext/locallang.xml --locallangFile=/other/ext/locallang.xml --searchFolder=/some/path1,/some/path2 --searchFolder=/some/path3
