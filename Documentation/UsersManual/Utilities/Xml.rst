.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.






XML
===
Classes to handle XML strings.

Element
-------

A child class of SimpleXMLElement with more features.

.. code-block:: xml
    
   <?xml version="1.0" encoding="utf-8"?>
   <customers>
      <customer>
         <id>54</id>
         <email>john@doe.com</email>
         <birthday>01.01.1970</birthday>
         <shippingaddress
            firstname="John"
            lastname="Doe"
         >
            <company><![CDATA[Doe ltd.]]></company>
         </shippingaddress>
      </customer>
   </customers>
   
.. code-block:: php
    
   // the XML from above
   $xml = '<customers />';
   
   $reader = new XMLReader();
   $reader->XML($xml, NULL, 0);
   // jump to first customer node
   while ($reader->read() && $reader->name !== 'customer');
   // a DOM Document to import the XML
   $doc = new DOMDocument();
   
   while ($reader->name === 'customer') {
      // create current customer node
      $customerNode = simplexml_import_dom(
         $doc->importNode($reader->expand(), true),
         'tx_mklib_util_xml_Element'
      );
      // now we can use the From Path method:
      $data = array(
         'extid' => $customerNode->getIntFromPath('id'),
         'mail' => $customerNode->getValueFromPath('email'),
         'birthday' => $customerNode->getDateTimeFromPath('birthday')->format('Y-m-d'),
         'firstname' => $customerNode->getValueFromPath('shippingaddress.firstname'),
         'lastname' => $customerNode->getValueFromPath('shippingaddress.lastname'),
         'company' => $customerNode->getValueFromPath('shippingaddress.company'),
      );
      $reader->next('customer');
   }
   $reader->close();


Classes:

* tx_mklib_util_xml_Element