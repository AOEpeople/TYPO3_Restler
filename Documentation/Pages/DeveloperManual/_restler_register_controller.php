// register API-Controller to Restler
$GLOBALS['TYPO3_Restler']['addApiClass']['<YOUR_ENDPOINT_PATH>'][] =
    yourNamespace\yourRestController::class;
