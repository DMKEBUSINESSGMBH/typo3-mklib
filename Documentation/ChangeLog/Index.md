ChangeLog
=========

The following is a very high level overview of the changes in this extension.

| Version                                                                                                 | Changes                                                                                                              |
|---------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------|
| 11.0.0                                                                                                  | added support for TYPO3 10.4 and 11.5 and PHP 7.4 and 8.0 only                                                       |
| 10.1.0                                                                                                  | Require rn_base >= 1.15.0                                                    |
| 10.0.0                                                                                                  | added support for TYPO3 9.5 and 10.4 only                                                        |
| 9.5.0                                                                                                   | added support for TYPO3 9.5                                                                                          |
| 3.0.12                                                                                                  | bugfixes                                                                                                             |
| 3.0.11                                                                                                  | bugfixes                                                                                                             |
| 3.0.10                                                                                                  | allow Tx_Rnbase_Domain_Repository_AbstractRepository in tx_mklib_action_ShowSingeItem                                |
| 3.0.8                                                                                                   | bugfixes                                                                                                             |
| 3.0.7                                                                                                   | bugfixes                                                                                                             |
| 3.0.6                                                                                                   | bugfixes                                                                                                             |
|| new option to disable sourceopt in development TypoScript                                               |
| 3.0.5                                                                                                   | bugfixes                                                                                                             |
| 3.0.4                                                                                                   | bugfixes                                                                                                             |
|| moved tests utility to \DMK\Mklib\Utility\Tests                                                         |
| 3.0.3                                                                                                   | minor bugfixes                                                                                                       |
|| fixed clearing cache in TYPO3 7.6 via CLI                                                               |
| 3.0.2                                                                                                   | fixed PHP 7 depreactions                                                                                             |
| 3.0.1                                                                                                   | clean up temp files scheduler is able to handle files in fileadmin                                                   |
| 3.0.0                                                                                                   | Initial TYPO3 8.7 LTS Support                                                                                        |
| 2.0.8                                                                                                   | bugfix for tx_mklib_util_File::getDocumentRoot() on CLI                                                              |
| 2.0.6                                                                                                   | make subparts for be export templates optionaly                                                                      |
|| add support for module markers in be export handlers                                                    |
| 2.0.5                                                                                                   | bugfix for TreeView of non-admins                                                                                    |
|| bugfix for sortable lists ans arrayobjects in be modules                                                |
| 2.0.4                                                                                                   | add missing classmap folders in composer.json                                                                        |
|| converted documentation to markdown                                                                     |
|| some minor cleanup                                                                                      |
| 2.0.3                                                                                                   | new model fpr tt_news                                                                                                |
|| move up and down links in BE modules improved                                                           |
|| bugfix for move up and down sorting in BE modules                                                       |
| 2.0.2                                                                                                   | compatibility to TYPO3 7.6                                                                                           |
|| added autoloading support for composer environments                                                     |
|| fixed CLI clear cache command for \>= TYPO3 6.x                                                         |
|| removed some deprecated methods and classes                                                             |
|| throwItemNotFound404Exception configurable in TS for ShowSingleItemAction                               |
|| move up and down support for records in be module added.                                                |
|| don't check deactivated scheduler tasks for failures                                                    |
|| new prepareRequest for abstract list action, for childclass usage                                       |
|| new IBAN model, swiftbic model and validator added                                                      |
|| new showSelectorByModels method for backend module selector                                             |
|| add switch for adding BOM in export handler output                                                      |
|| new output handler 'buffer' for listbuilder                                                             |
|| first ArrayObject / Traversable support for be modules                                                  |
|| Heads Up Grid updated and moved to footer                                                               |
|| new method tx\_mklib\_tests\_Util::removeCreationDateFromPdfContent                                     |
| 1.0.10                                                                                                  | bugfix in tx\_mklib\_mod1\_searcher\_abstractBase for TYPO3 4.x                                                      |
| 1.0.9                                                                                                   | existing locallang lables are not longer overwritten in BE modules which use tx\_mklib\_mod1\_searcher\_abstractBase |
|| added possibility to use full TypoScript support for export headers in the export handler of BE modules |
| 1.0.8                                                                                                   | some cleanup                                                                                                         |
| 1.0.7                                                                                                   | bugfix for a typo                                                                                                    |
| 1.0.5                                                                                                   | new vat registration number validation in tx\_mklib\_srv\_Finance                                                    |
|| [BUGFIX] load tx\_rnbase\_mod\_Util in tx\_mklib\_mod1\_export\_Handler                                 |
|| [TASK] refactoring of generic scheduler field provider to support different TYPO3 versions              |
| 1.0.0                                                                                                   | [TASK] make getEmptyModel public                                                                                     |
| 0.9.93                                                                                                  | [TASK] classification in documentation fixed                                                                         |
|| [TASK] lang for sys language translates in decorator fixed                                              |
| 0.9.89                                                                                                  | [TASK] file path name in media model for fal fixed                                                                   |


