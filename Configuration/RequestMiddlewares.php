<?php

/**
 * define a psr-15 middleware for all typo3 frontend requests
 */
return [
    'frontend' => [
        'aoe/restler/system/dispatcher' => [
            'target' => \Aoe\Restler\System\Dispatcher::class,
            'after' => [
                'typo3/cms-frontend/site'
            ]
        ],
    ],
];