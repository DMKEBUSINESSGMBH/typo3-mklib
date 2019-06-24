tx\_mklib\_filter\_SingleItem
=============================

Filter for single datasets. Just derive from this class and implement the abstract methods. You can use this filter for a list action. You need just a new subpart in the template. This way you can use the \#\#\#...EMPTYLIST\#\#\# marker if the element could not be found.

~~~~ {.sourceCode .php}

class Tx_Mkexample_Filter_ShowCompany extends tx_mklib_filter_SingleItem {

   /**
    * @return string
    */
   protected function getParameterName() {
      return 'company';
   }

   /**
    * @return string
    */
   protected function getSearchAlias() {
      return 'COMPANY';
   }
}
~~~~
