.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

Clear Cache
===========

Just needed for TYPO3 lower 6.x. In 6.x you can just delete the folder typotemp/Cache.

Call it like:

typo3/cli_dispatch.phpsh mklib -fc

or

typo3/cli_dispatch.phpsh mklib --flush-cache