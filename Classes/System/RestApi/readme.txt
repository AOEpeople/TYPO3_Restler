##########
# Notice #
##########
ALL classes in this directory are ONLY used, when we want to call REST-API-endpoints by using a
PHP-client (without 'leaving' the current PHP-process...this can save us a lot of performance).
This can be useful, if:
 - we want to call REST-API-Endpoints inside another REST-API-Endpoint (to merge/use the result/data of several REST-API-Endpoints)
 - we want to call REST-API-Endpoints inside a TYPO3-extBase-plugin



You can also define, that some ('internal') REST-API-Endpoints are ONLY callable by using a PHP-client. To archive this, you must
implement your own Authentication-class (to protect the 'internal' REST-API-Endpoint) and than check, if a REST-API-Endpoint is
currently running and was called by using the PHP-client:
$restApiClient = GeneralUtility::makeInstance(\Aoe\Restler\System\RestApi\RestApiClient::class);
if ($restApiClient->isExecutingRequest()) {
    if ($restApiClient->isProductionContextSet()) {
        // REST-API-Endpoint is currently running and was called by using the PHP-client (in production-mode)
    } else {
        // REST-API-Endpoint is currently running and was called by using the PHP-client (in none production-mode)
    }
} else {
    // REST-API-Endpoint is currently NOT running
}



If we want to call REST-API-endpoints by using a PHP-client, than the code should look like this:

##################################################
# Example to do GET-request (without GET-params) #
##################################################
$restApiClient = GeneralUtility::makeInstance(\Aoe\Restler\System\RestApi\RestApiClient::class);
$result = $restApiClient->executeRequest('GET', '/api/products/320');


###############################################
# Example to do GET-request (with GET-params) #
###############################################
$getData = ['context' => 'mobile'];
$restApiClient = GeneralUtility::makeInstance(\Aoe\Restler\System\RestApi\RestApiClient::class);
$result = $restApiClient->executeRequest('GET', '/api/products/320', $getData);


##############################
# Example to do POST-request #
##############################
$getData = [];
$postData = [
    'id' => 1,
    'name' => 'Test-Product'
];
$restApiClient = GeneralUtility::makeInstance(\Aoe\Restler\System\RestApi\RestApiClient::class);
$result = $restApiClient->executeRequest('POST', '/api/products', $getData, $postData);
