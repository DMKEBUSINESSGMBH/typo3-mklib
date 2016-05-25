HTTP requests
=============

A HTTP request object to make HTTP requests. The advance over tx\_rnbase\_util\_Network::getUrl/TYPO3CMSCoreUtilityGeneralUtility::getUrl is that you can have different adapters. So the request is theoretically possible with curl, socket, proxy and so one. Request are also possible wit SSL certificates, keys and pass phrases.

In the moment only the curl adapter is implemented and configured as default one. The base is partly taken from Zend\_Http\_Client.

Here a little example for a request with HTTPS, post parameters and SSL certificate:

~~~~ {.sourceCode .php}
$url = 'https://example.de/tests/';
$config = array(
   'sslcainfo' => tx_rnbase_util_Files::getFileAbsFileName('EXT:mkmyext/Resources/Private/example.de.crt'),
);
$request = tx_rnbase::makeInstance('tx_mklib_util_HttpRequest', $url, $config);
$request->addParameter('_POST', $_POST);
$request->setAuth('user', 'pass');
$request->setMethod($request::METHOD_POST);
$response = $request->request();
if ($response->isSuccessful()) {
   return $response->getBody();
}
// else,  error handling
~~~~

Classes:

-   mklib\_util\_HttpRequest  
    -   Response  
        -   tx\_mklib\_util\_httprequest\_Response

    -   Adapter  
        -   tx\_mklib\_util\_httprequest\_adapter\_Interface
        -   tx\_mklib\_util\_httprequest\_adapter\_Curl


