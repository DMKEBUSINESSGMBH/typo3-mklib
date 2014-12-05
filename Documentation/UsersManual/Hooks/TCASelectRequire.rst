.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _users-manual:

require for select fields in the TCA
====================================

Provides the the require eval function for select fields in the TCA.

.. code-block:: php

   'config' => array (
       'type' => 'select',
       'items' => array (
            array('please choose', ""),
       ),
       'minitems' => 1,
   )