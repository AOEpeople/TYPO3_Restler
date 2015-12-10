<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "restler".
 *
 * Auto generated 17-08-2015 10:54
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
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
	'version' => '1.1.13',
	'constraints' => 
	array (
		'depends' => 
		array (
			'typo3' => '6.2.0-6.2.99',
			'php' => '5.3.4',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
	'_md5_values_when_last_written' => '',
);
