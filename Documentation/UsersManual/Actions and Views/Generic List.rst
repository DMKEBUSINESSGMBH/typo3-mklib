.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.



Generic List
============

This view provides a simple dataset list with the listbuilder of rn_base.
It is completely configured with TypoScript.
In the plugin configuration you should configure the extended configuration ID,
so you can have several outputs with this view. The default ID is "default".

Example
-------

Display all pages with pid = 1. The extended configuration ID was set to "pages". The join
on "PAGEPARENT" is just a demo.

.. code-block:: ts

   plugin.tx_mklib {
      genericlist {
         ### is mostly set in the plugin flexform
         extendedConfId = pages
         ### standard configuration for the view
         pages = < lib.mklib.genericlist.default
         pages {
            ### template configuration
            template {
               path = EXT:myext/Ressources/Private/Html/Ext/mklib/genericlist.pages.html
               subpart = ###LISTPAGE###
               itempath = page
               markerclass = tx_rnbase_util_SimpleMarker
            }
            ### filter configuration
            filter {
               ### alternativ filter class
               #class = tx_myext_filter_pages
               ### where conditions for the rn_base search framework
               fields {
                  PAGESPARENT.uid.OP_EQ_INT = 1
               }
               options {
                  ###debug = 1
                  ### configure searcher (tx_rnbase_util_SearchGeneric)
                  searchdef {
                     usealias = 1
                     basetable = pages
                     basetablealias = PAGES
                     wrapperclass = Tx_Rnbase_Domain_Model_Base
                     alias {
                        PAGES.table = pages
                        PAGESPARENT.table = pages
                        PAGESPARENT.join = JOIN pages AS PAGESPARENT ON PAGES.pid = PAGESPARENT.uid
                     }
                  }
               }
            }
            ### configuration of a page entry
            page {
               links {
               }
               title.wrap = <h3>|</h3>
            }
         }
      }
   }

The beloning HTML template in Template: EXT:myext/Ressources/Private/Html/Ext/mklib/genericlist.pages.html

.. code-block:: html

   <!-- ###LISTPAGE### START -->
      <!-- ###PAGES### START -->
         <ul>
         <!-- ###PAGE### START -->
            <li>
               ###PAGE_TITLE###
               <p>ID: ###PAGE_UID###</p>
            </li>
         <!-- ###PAGE### END -->
         </ul>
      <!-- ###PAGES### END -->
   <!-- ###LISTPAGE### END -->
