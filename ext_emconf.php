<?php
/***************************************************************
 * Extension Manager/Repository config file for ext "restler".
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'restler',
    'description' => 'This extension integrates the REST-API-framework "restler" in TYPO3',
    'category' => 'fe',
    'author' => 'AOE GmbH',
    'author_company' => 'AOE GmbH',
    'author_email' => 'dev@aoe.com',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => 'typo3temp/tx_restler',
    'clearCacheOnLoad' => 0,
    'version' => '1.5.7',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.2.0-7.6.99',
            'php' => '5.3.4',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
    '_md5_values_when_last_written' => '',
);
