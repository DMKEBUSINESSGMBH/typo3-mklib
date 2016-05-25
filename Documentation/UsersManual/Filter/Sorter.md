tx\_mklib\_filter\_Sorter
=========================

Offers links for sorting. Can be configured as filter class for the list actions.

TypoScript configuration
------------------------

~~~~ {.sourceCode .ts}
plugin.tx_mkexample {
  listProjects{
    filter{
      class = Tx_Mkexample_Filter_Projects
      ### the fields allowed for sorting
      sort{
         fields = title,year
         default {
            field = title
            sortOrder = asc
         }
         link{
            pid = ...
            ...
         }
      }
    }
}
~~~~

HTML template
-------------

~~~~ {.sourceCode .html}
###SORT_TITLE_LINK###title###SORT_TITLE_LINK###
###SORT_YEAR_LINK###year###SORT_YEAR_LINK###
~~~~

Example filter class
--------------------

~~~~ {.sourceCode .ts}
tx_rnbase::load('tx_mklib_filter_Sorter');

class Tx_Mkexample_Filter_Projects extends tx_mklib_filter_Sorter {

   /**
    * (non-PHPdoc)
    * @see tx_rnbase_filter_BaseFilter::initFilter()
    */
   protected function initFilter(&$fields, &$options, &$parameters, &$configurations, $confId) {
      if(!$this->initSorting()) {
         return true;
      }
      $sortBy = $this->getSortBy();
      $sortOrder = $this->getSortOrder();

      $options['orderby'] = array('PROJECT.' . $sortBy => $sortOrder);

      return true;
   }
}
~~~~
