<?php

// DO NOT CHANGE THIS FILE! It is automatically generated by extdeveval::buildAutoloadRegistry.
// This file was generated on 2016-07-01 20:06

if (class_exists('TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility', true)) {
    $extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mklib');
} else {
    $extensionPath = t3lib_extMgm::extPath('mklib');
}
$extensionClassesPath = $extensionPath.'Classes/';

return array(
    'tx_mklib_abstract_observablet3service' => $extensionPath.'abstract/class.tx_mklib_abstract_ObservableT3Service.php',
    'tx_mklib_abstract_observer' => $extensionPath.'abstract/class.tx_mklib_abstract_Observer.php',
    'tx_mklib_abstract_soapclientwrapper' => $extensionPath.'abstract/class.tx_mklib_abstract_SoapClientWrapper.php',
    'tx_mklib_action_abstractlist' => $extensionPath.'action/class.tx_mklib_action_AbstractList.php',
    'tx_mklib_action_genericlist' => $extensionPath.'action/class.tx_mklib_action_GenericList.php',
    'tx_mklib_action_listbase' => $extensionPath.'action/class.tx_mklib_action_ListBase.php',
    'tx_mklib_action_listpages' => $extensionPath.'action/class.tx_mklib_action_ListPages.php',
    'tx_mklib_action_showsingeitem' => $extensionPath.'action/class.tx_mklib_action_ShowSingeItem.php',
    'tx_mklib_cli_main' => $extensionPath.'cli/class.tx_mklib_cli_main.php',
    'tx_mklib_database_connection' => $extensionClassesPath.'Database/Connection.php',
    'tx_mklib_database_connectiondatabasetest' => $extensionPath.'tests/Classes/Database/ConnectionDatabaseTest.php',
    'tx_mklib_database_connectiondatabasetestdevlog' => $extensionPath.'tests/Classes/Database/ConnectionDatabaseTest.php',
    'tx_mklib_database_connectionmock' => $extensionPath.'tests/util/class.tx_mklib_tests_util_DB_testcase.php',
    'tx_mklib_database_connectiontest' => $extensionPath.'tests/Classes/Database/ConnectionTest.php',
    'tx_mklib_domain_model_iso_base' => $extensionClassesPath.'Domain/Model/Iso/Base.php',
    'tx_mklib_domain_model_iso_iban' => $extensionClassesPath.'Domain/Model/Iso/Iban.php',
    'tx_mklib_domain_model_iso_ibantest' => $extensionPath.'tests/Classes/Domain/Model/Iso/IbanTest.php',
    'tx_mklib_domain_model_iso_swiftbic' => $extensionClassesPath.'Domain/Model/Iso/SwiftBic.php',
    'tx_mklib_domain_model_iso_swiftbictest' => $extensionPath.'tests/Classes/Domain/Model/Iso/SwiftBicTest.php',
    'tx_mklib_exception_invalidconfiguration' => $extensionPath.'exception/class.tx_mklib_exception_InvalidConfiguration.php',
    'tx_mklib_exception_nobeuser' => $extensionPath.'exception/class.tx_mklib_exception_NoBeUser.php',
    'tx_mklib_exception_nofeuser' => $extensionPath.'exception/class.tx_mklib_exception_NoFeUser.php',
    'tx_mklib_filter_singleitem' => $extensionPath.'filter/class.tx_mklib_filter_SingleItem.php',
    'tx_mklib_filter_sorter' => $extensionPath.'filter/class.tx_mklib_filter_Sorter.php',
    'tx_mklib_hooks_t3lib_tceforms_getsinglefieldclass' => $extensionPath.'hooks/class.tx_mklib_hooks_t3lib_tceforms_getSingleFieldClass.php',
    'tx_mklib_interface_iobservable' => $extensionPath.'interface/class.tx_mklib_interface_IObservable.php',
    'tx_mklib_interface_iobserver' => $extensionPath.'interface/class.tx_mklib_interface_IObserver.php',
    'tx_mklib_interface_izipcountry' => $extensionPath.'interface/class.tx_mklib_interface_IZipCountry.php',
    'tx_mklib_interface_repository' => $extensionPath.'interface/class.tx_mklib_interface_Repository.php',
    'tx_mklib_marker_damrecord' => $extensionPath.'marker/class.tx_mklib_marker_DAMRecord.php',
    'tx_mklib_marker_mediarecord' => $extensionPath.'marker/class.tx_mklib_marker_MediaRecord.php',
    'tx_mklib_mod1_decorator_base' => $extensionPath.'mod1/decorator/class.tx_mklib_mod1_decorator_Base.php',
    'tx_mklib_mod1_export_handler' => $extensionPath.'mod1/export/class.tx_mklib_mod1_export_Handler.php',
    'tx_mklib_mod1_export_iinjecthandler' => $extensionPath.'mod1/export/class.tx_mklib_mod1_export_IInjectHandler.php',
    'tx_mklib_mod1_export_imodfunc' => $extensionPath.'mod1/export/class.tx_mklib_mod1_export_IModFunc.php',
    'tx_mklib_mod1_export_isearcher' => $extensionPath.'mod1/export/class.tx_mklib_mod1_export_ISearcher.php',
    'tx_mklib_mod1_export_listbuilder' => $extensionPath.'mod1/export/class.tx_mklib_mod1_export_ListBuilder.php',
    'tx_mklib_mod1_export_listmarker' => $extensionPath.'mod1/export/class.tx_mklib_mod1_export_ListMarker.php',
    'tx_mklib_mod1_export_util' => $extensionPath.'mod1/export/class.tx_mklib_mod1_export_Util.php',
    'tx_mklib_mod1_linker_base' => $extensionPath.'mod1/linker/class.tx_mklib_mod1_linker_Base.php',
    'tx_mklib_mod1_linker_showdetails' => $extensionPath.'mod1/linker/class.tx_mklib_mod1_linker_ShowDetails.php',
    'tx_mklib_mod1_searcher_abstractbase' => $extensionPath.'mod1/searcher/class.tx_mklib_mod1_searcher_abstractBase.php',
    'tx_mklib_mod1_searcher_base' => $extensionPath.'mod1/searcher/class.tx_mklib_mod1_searcher_Base.php',
    'tx_mklib_mod1_util_helper' => $extensionPath.'mod1/util/class.tx_mklib_mod1_util_Helper.php',
    'tx_mklib_mod1_util_language' => $extensionPath.'mod1/util/class.tx_mklib_mod1_util_Language.php',
    'tx_mklib_mod1_util_searchbuilder' => $extensionPath.'mod1/util/class.tx_mklib_mod1_util_SearchBuilder.php',
    'tx_mklib_mod1_util_selector' => $extensionPath.'mod1/util/class.tx_mklib_mod1_util_Selector.php',
    'tx_mklib_model_constant' => $extensionPath.'model/class.tx_mklib_model_Constant.php',
    'tx_mklib_model_currency' => $extensionPath.'model/class.tx_mklib_model_Currency.php',
    'tx_mklib_model_dam' => $extensionPath.'model/class.tx_mklib_model_Dam.php',
    'tx_mklib_model_media' => $extensionPath.'model/class.tx_mklib_model_Media.php',
    'tx_mklib_model_page' => $extensionPath.'model/class.tx_mklib_model_Page.php',
    'tx_mklib_model_staticcountry' => $extensionPath.'model/class.tx_mklib_model_StaticCountry.php',
    'tx_mklib_model_staticcountryzone' => $extensionPath.'model/class.tx_mklib_model_StaticCountryZone.php',
    'tx_mklib_model_ttaddress' => $extensionPath.'model/class.tx_mklib_model_TtAddress.php',
    'tx_mklib_model_ttnews' => $extensionPath.'model/class.tx_mklib_model_TtNews.php',
    'tx_mklib_model_wordlistentry' => $extensionPath.'model/class.tx_mklib_model_WordlistEntry.php',
    'tx_mklib_repository_abstract' => $extensionPath.'repository/class.tx_mklib_repository_Abstract.php',
    'tx_mklib_repository_pages' => $extensionPath.'repository/class.tx_mklib_repository_Pages.php',
    'tx_mklib_scheduler_cleanuptempfiles' => $extensionPath.'scheduler/class.tx_mklib_scheduler_cleanupTempFiles.php',
    'tx_mklib_scheduler_cleanuptempfilesfieldprovider' => $extensionPath.'scheduler/class.tx_mklib_scheduler_cleanupTempFilesFieldProvider.php',
    'tx_mklib_scheduler_deletefromdatabase' => $extensionPath.'scheduler/class.tx_mklib_scheduler_DeleteFromDatabase.php',
    'tx_mklib_scheduler_deletefromdatabasefieldprovider' => $extensionPath.'scheduler/class.tx_mklib_scheduler_DeleteFromDatabaseFieldProvider.php',
    'tx_mklib_scheduler_emailfieldprovider' => $extensionPath.'scheduler/class.tx_mklib_scheduler_EmailFieldProvider.php',
    'tx_mklib_scheduler_generic' => $extensionPath.'scheduler/class.tx_mklib_scheduler_Generic.php',
    'tx_mklib_scheduler_genericfieldprovider' => $extensionPath.'scheduler/class.tx_mklib_scheduler_GenericFieldProvider.php',
    'tx_mklib_scheduler_schedulertaskfaildetection' => $extensionPath.'scheduler/class.tx_mklib_scheduler_SchedulerTaskFailDetection.php',
    'tx_mklib_scheduler_schedulertaskfaildetectionfieldprovider' => $extensionPath.'scheduler/class.tx_mklib_scheduler_SchedulerTaskFailDetectionFieldProvider.php',
    'tx_mklib_scheduler_schedulertaskfreezedetection' => $extensionPath.'scheduler/class.tx_mklib_scheduler_SchedulerTaskFreezeDetection.php',
    'tx_mklib_scheduler_schedulertaskfreezedetectionfieldprovider' => $extensionPath.'scheduler/class.tx_mklib_scheduler_SchedulerTaskFreezeDetectionFieldProvider.php',
    'tx_mklib_search_constant' => $extensionPath.'search/class.tx_mklib_search_Constant.php',
    'tx_mklib_search_staticcountries' => $extensionPath.'search/class.tx_mklib_search_StaticCountries.php',
    'tx_mklib_search_staticcountryzones' => $extensionPath.'search/class.tx_mklib_search_StaticCountryZones.php',
    'tx_mklib_search_wordlist' => $extensionPath.'search/class.tx_mklib_search_Wordlist.php',
    'tx_mklib_soap_clientwrapper' => $extensionPath.'soap/class.tx_mklib_soap_ClientWrapper.php',
    'tx_mklib_srv_base' => $extensionPath.'srv/class.tx_mklib_srv_Base.php',
    'tx_mklib_srv_constant' => $extensionPath.'srv/class.tx_mklib_srv_Constant.php',
    'tx_mklib_srv_finance' => $extensionPath.'srv/class.tx_mklib_srv_Finance.php',
    'tx_mklib_srv_staticcountries' => $extensionPath.'srv/class.tx_mklib_srv_StaticCountries.php',
    'tx_mklib_srv_staticcountryzones' => $extensionPath.'srv/class.tx_mklib_srv_StaticCountryZones.php',
    'tx_mklib_srv_wordlist' => $extensionPath.'srv/class.tx_mklib_srv_Wordlist.php',
    'tx_mklib_tca_eval_isodate' => $extensionPath.'tca/eval/class.tx_mklib_tca_eval_isoDate.php',
    'tx_mklib_tca_eval_pricedecimalseperator' => $extensionPath.'tca/eval/class.tx_mklib_tca_eval_priceDecimalSeperator.php',
    'tx_mklib_tests_abstract_observablet3service_testcase' => $extensionPath.'tests/abstract/class.tx_mklib_abstract_ObservableT3Service_testcase.php',
    'tx_mklib_tests_action_listbase_testcase' => $extensionPath.'tests/action/class.tx_mklib_tests_action_ListBase_testcase.php',
    'tx_mklib_tests_action_showsingeitem_testcase' => $extensionPath.'tests/action/class.tx_mklib_tests_action_ShowSingeItem_testcase.php',
    'tx_mklib_tests_dbtestcaseskeleton' => $extensionPath.'tests/class.tx_mklib_tests_DBTestCaseSkeleton.php',
    'tx_mklib_tests_filter_singleitem_testcase' => $extensionPath.'tests/filter/class.tx_mklib_tests_filter_SingleItem_testcase.php',
    'tx_mklib_tests_filter_sorter_testcase' => $extensionPath.'tests/filter/class.tx_mklib_tests_filter_Sorter_testcase.php',
    'tx_mklib_tests_fixtures_classes_dummy' => $extensionPath.'tests/fixtures/classes/class.tx_mklib_tests_fixtures_classes_Dummy.php',
    'tx_mklib_tests_fixtures_classes_dummyfilter' => $extensionPath.'tests/fixtures/classes/class.tx_mklib_tests_fixtures_classes_DummyFilter.php',
    'tx_mklib_tests_fixtures_classes_dummyfilterwithreturnfalse' => $extensionPath.'tests/fixtures/classes/class.tx_mklib_tests_fixtures_classes_DummyFilterWithReturnFalse.php',
    'tx_mklib_tests_fixtures_classes_dummylinker' => $extensionPath.'tests/fixtures/classes/class.tx_mklib_tests_fixtures_classes_DummyLinker.php',
    'tx_mklib_tests_fixtures_classes_dummymod' => $extensionPath.'tests/fixtures/classes/class.tx_mklib_tests_fixtures_classes_DummyMod.php',
    'tx_mklib_tests_fixtures_classes_dummysearcher' => $extensionPath.'tests/fixtures/classes/class.tx_mklib_tests_fixtures_classes_DummySearcher.php',
    'tx_mklib_tests_fixtures_classes_firstobserver' => $extensionPath.'tests/fixtures/classes/class.tx_mklib_tests_fixtures_classes_FirstObserver.php',
    'tx_mklib_tests_fixtures_classes_observableinterface' => $extensionPath.'tests/fixtures/classes/class.tx_mklib_tests_fixtures_classes_ObservableInterface.php',
    'tx_mklib_tests_fixtures_classes_observablet3service' => $extensionPath.'tests/fixtures/classes/class.tx_mklib_tests_fixtures_classes_ObservableT3Service.php',
    'tx_mklib_tests_fixtures_classes_secondobserver' => $extensionPath.'tests/fixtures/classes/class.tx_mklib_tests_fixtures_classes_SecondObserver.php',
    'tx_mklib_tests_fixtures_classes_sorterfilter' => $extensionPath.'tests/fixtures/classes/class.tx_mklib_tests_fixtures_classes_SorterFilter.php',
    'tx_mklib_tests_hooks_t3lib_tceforms_getsinglefieldclass_testcase' => $extensionPath.'tests/hooks/class.tx_mklib_tests_hooks_t3lib_tceforms_getSingleFieldClass_testcase.php',
    'tx_mklib_tests_markertestcase' => $extensionPath.'tests/class.tx_mklib_tests_MarkerTestcase.php',
    'tx_mklib_tests_mod1_decorator_base_testcase' => $extensionPath.'tests/mod1/decorator/class.tx_mklib_tests_mod1_decorator_Base_testcase.php',
    'tx_mklib_tests_mod1_linker_base_testcase' => $extensionPath.'tests/mod1/linker/class.tx_mklib_tests_mod1_linker_Base_testcase.php',
    'tx_mklib_tests_mod1_searcher_abstractbase_testcase' => $extensionPath.'tests/mod1/searcher/class.tx_mklib_tests_mod1_searcher_abstractBase_testcase.php',
    'tx_mklib_tests_mod1_util' => $extensionPath.'tests/mod1/class.tx_mklib_tests_mod1_Util.php',
    'tx_mklib_tests_mod1_util_searchbuilder_testcase' => $extensionPath.'tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilder_testcase.php',
    'tx_mklib_tests_mod1_util_selector_testcase' => $extensionPath.'tests/mod1/util/class.tx_mklib_tests_mod1_util_Selector_testcase.php',
    'tx_mklib_tests_repository_abstract_testcase' => $extensionPath.'tests/repository/class.tx_mklib_tests_repository_Abstract_testcase.php',
    'tx_mklib_tests_repository_pages_testcase' => $extensionPath.'tests/repository/class.tx_mklib_tests_repository_Pages_testcase.php',
    'tx_mklib_tests_scheduler_deletefromdatabase_testcase' => $extensionPath.'tests/scheduler/class.tx_mklib_tests_scheduler_DeleteFromDatabase_testcase.php',
    'tx_mklib_tests_scheduler_schedulertaskfaildetection_testcase' => $extensionPath.'tests/scheduler/class.tx_mklib_tests_scheduler_SchedulerTaskFailDetection_testcase.php',
    'tx_mklib_tests_soap_clientwrapper_testcase' => $extensionPath.'tests/soap/class.tx_mklib_tests_soap_ClientWrapper_testcase.php',
    'tx_mklib_tests_srv_finance_testcase' => $extensionPath.'tests/srv/class.tx_mklib_tests_srv_Finance_testcase.php',
    'tx_mklib_tests_srv_staticcountryzones_testcase' => $extensionPath.'tests/srv/class.tx_mklib_tests_srv_StaticCountryZones_testcase.php',
    'tx_mklib_tests_srv_wordlist_testcase' => $extensionPath.'tests/srv/class.tx_mklib_tests_srv_Wordlist_testcase.php',
    'tx_mklib_tests_tca_testcase' => $extensionPath.'tests/class.tx_mklib_tests_TCA_testcase.php',
    'tx_mklib_tests_util' => $extensionPath.'tests/class.tx_mklib_tests_Util.php',
    'tx_mklib_tests_util_array_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_Array_testcase.php',
    'tx_mklib_tests_util_date_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_Date_testcase.php',
    'tx_mklib_tests_util_db_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_DB_testcase.php',
    'tx_mklib_tests_util_encoding_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_Encoding_testcase.php',
    'tx_mklib_tests_util_extensionconfiguration_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_ExtensionConfiguration_testcase.php',
    'tx_mklib_tests_util_file_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_File_testcase.php',
    'tx_mklib_tests_util_flexform_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_FlexForm_testcase.php',
    'tx_mklib_tests_util_httprequest_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_HttpRequest_testcase.php',
    'tx_mklib_tests_util_misctools_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_MiscTools_testcase.php',
    'tx_mklib_tests_util_model_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_Model_testcase.php',
    'tx_mklib_tests_util_number_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_Number_testcase.php',
    'tx_mklib_tests_util_rtfgenerator_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_RTFGenerator_testcase.php',
    'tx_mklib_tests_util_rtfparser_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_RTFParser_testcase.php',
    'tx_mklib_tests_util_scheduler_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_Scheduler_testcase.php',
    'tx_mklib_tests_util_searchsorting_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_SearchSorting_testcase.php',
    'tx_mklib_tests_util_session_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_Session_testcase.php',
    'tx_mklib_tests_util_string_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_String_testcase.php',
    'tx_mklib_tests_util_tca_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_TCA_testcase.php',
    'tx_mklib_tests_util_ts_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_TS_testcase.php',
    'tx_mklib_tests_util_var_testcase' => $extensionPath.'tests/util/class.tx_mklib_tests_util_Var_testcase.php',
    'tx_mklib_tests_validator_wordlist_testcase' => $extensionPath.'tests/validator/class.tx_mklib_tests_validator_WordList_testcase.php',
    'tx_mklib_tests_validator_zipcode_testcase' => $extensionPath.'tests/validator/class.tx_mklib_tests_validator_ZipCode_testcase.php',
    'tx_mklib_treelib_basetreeview' => $extensionPath.'treelib/class.tx_mklib_treelib_TreeView.php',
    'tx_mklib_treelib_config' => $extensionPath.'treelib/class.tx_mklib_treelib_Config.php',
    'tx_mklib_treelib_renderer' => $extensionPath.'treelib/class.tx_mklib_treelib_Renderer.php',
    'tx_mklib_treelib_tce' => $extensionPath.'treelib/class.tx_mklib_treelib_TCE.php',
    'tx_mklib_treelib_treeview' => $extensionPath.'treelib/class.tx_mklib_treelib_TreeView.php',
    'tx_mklib_util_array' => $extensionPath.'util/class.tx_mklib_util_Array.php',
    'tx_mklib_util_csv' => $extensionPath.'util/class.tx_mklib_util_Csv.php',
    'tx_mklib_util_csv_reader' => $extensionPath.'util/csv/class.tx_mklib_util_csv_reader.php',
    'tx_mklib_util_csv_writer' => $extensionPath.'util/csv/class.tx_mklib_util_csv_writer.php',
    'tx_mklib_util_date' => $extensionPath.'util/class.tx_mklib_util_Date.php',
    'tx_mklib_util_db' => $extensionPath.'util/class.tx_mklib_util_DB.php',
    'tx_mklib_util_debug' => $extensionPath.'util/class.tx_mklib_util_Debug.php',
    'tx_mklib_util_encoding' => $extensionPath.'util/class.tx_mklib_util_Encoding.php',
    'tx_mklib_util_extensionconfiguration' => $extensionPath.'util/class.tx_mklib_util_ExtensionConfiguration.php',
    'tx_mklib_util_extensionconfigurationtest' => $extensionPath.'tests/util/class.tx_mklib_tests_util_ExtensionConfiguration_testcase.php',
    'tx_mklib_util_file' => $extensionPath.'util/class.tx_mklib_util_File.php',
    'tx_mklib_util_flexform' => $extensionPath.'util/class.tx_mklib_util_FlexForm.php',
    'tx_mklib_util_httprequest' => $extensionPath.'util/class.tx_mklib_util_HttpRequest.php',
    'tx_mklib_util_httprequest_adapter_curl' => $extensionPath.'util/httprequest/adapter/class.tx_mklib_util_httprequest_adapter_Curl.php',
    'tx_mklib_util_httprequest_adapter_interface' => $extensionPath.'util/httprequest/adapter/class.tx_mklib_util_httprequest_adapter_Interface.php',
    'tx_mklib_util_httprequest_response' => $extensionPath.'util/httprequest/class.tx_mklib_util_httprequest_Response.php',
    'tx_mklib_util_list_builder' => $extensionPath.'util/list/class.tx_mklib_util_list_Builder.php',
    'tx_mklib_util_list_marker' => $extensionPath.'util/list/class.tx_mklib_util_list_Marker.php',
    'tx_mklib_util_list_output_buffer' => $extensionPath.'util/list/output/class.tx_mklib_util_list_output_Buffer.php',
    'tx_mklib_util_list_output_file' => $extensionPath.'util/list/output/class.tx_mklib_util_list_output_File.php',
    'tx_mklib_util_list_output_interface' => $extensionPath.'util/list/output/class.tx_mklib_util_list_output_Interface.php',
    'tx_mklib_util_logger' => $extensionPath.'util/class.tx_mklib_util_Logger.php',
    'tx_mklib_util_misctools' => $extensionPath.'util/class.tx_mklib_util_MiscTools.php',
    'tx_mklib_util_model' => $extensionPath.'util/class.tx_mklib_util_Model.php',
    'tx_mklib_util_number' => $extensionPath.'util/class.tx_mklib_util_Number.php',
    'tx_mklib_util_rtfgenerator' => $extensionPath.'util/class.tx_mklib_util_RTFGenerator.php',
    'tx_mklib_util_rtfparser' => $extensionPath.'util/class.tx_mklib_util_RTFParser.php',
    'tx_mklib_util_scheduler' => $extensionPath.'util/class.tx_mklib_util_Scheduler.php',
    'tx_mklib_util_searchsorting' => $extensionPath.'util/class.tx_mklib_util_SearchSorting.php',
    'tx_mklib_util_serviceregistry' => $extensionPath.'util/class.tx_mklib_util_ServiceRegistry.php',
    'tx_mklib_util_session' => $extensionPath.'util/class.tx_mklib_util_Session.php',
    'tx_mklib_util_staticcache' => $extensionPath.'util/class.tx_mklib_util_StaticCache.php',
    'tx_mklib_util_string' => $extensionPath.'util/class.tx_mklib_util_String.php',
    'tx_mklib_util_tca' => $extensionPath.'util/class.tx_mklib_util_TCA.php',
    'tx_mklib_util_testsearchsorting' => $extensionPath.'tests/util/class.tx_mklib_tests_util_SearchSorting_testcase.php',
    'tx_mklib_util_ts' => $extensionPath.'util/class.tx_mklib_util_TS.php',
    'tx_mklib_util_var' => $extensionPath.'util/class.tx_mklib_util_Var.php',
    'tx_mklib_util_wizicon' => $extensionPath.'util/class.tx_mklib_util_WizIcon.php',
    'tx_mklib_util_xml_element' => $extensionPath.'util/xml/class.tx_mklib_util_xml_Element.php',
    'tx_mklib_validator_wordlist' => $extensionPath.'validator/class.tx_mklib_validator_WordList.php',
    'tx_mklib_validator_zipcode' => $extensionPath.'validator/class.tx_mklib_validator_ZipCode.php',
    'tx_mklib_view_genericlist' => $extensionPath.'view/class.tx_mklib_view_GenericList.php',
);
