.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

Database
========

Enhances the database utility of rn_base with logging functionality and tools for handling mm relations. To use this function
you need to activate logDbHandler option in the mklib extension configuration.
Furthermore you should configure the minLogLevel of the devlog extension to at least Notice.

Tables which should be not logged have to be configured in logDbIgnoreTables option
in the mklib extension configuration. A comma separated list is expected.

The following data is logged:

* method
* fe user
* be user
* tablename
* where clause
* record


Classes:

* tx_mklib_util_DB