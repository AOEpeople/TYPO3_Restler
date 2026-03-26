/**
 * The response of this REST-endpoint will be cached by TYPO3-caching-framework, because:
 *  - it's a GET-request/method
 *  - annotation 'restler_typo3cache_expires' (define seconds, after cache is expired; '0' means cache will never expire) is set
 *  - annotation 'restler_typo3cache_tags' (comma-separated list of cache-tags) is set
 *
 * The cache is stored in this TYPO3-tables:
 *  - cache_restler
 *  - cache_restler_tags
 *
 * @url GET my-rest-endpoint-which-should-be-cached
 *
 * @restler_typo3cache_expires 180
 * @restler_typo3cache_tags typo3cache_examples,typo3cache_example_car
 */
public function myRestEndpointWhichShouldBeCached() {

}
