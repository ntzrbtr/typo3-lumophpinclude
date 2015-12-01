<?php

########################################################################
# Extension Manager/Repository config file for ext: "lumophpinclude"
#
# Auto generated 26-06-2014 22:53
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'LumoNet PHP Include',
	'description' => 'Includes local PHP scripts via eval() or external PHP script via HTTP. Provides options to transfer GET and POST variables to the included script and strip header and footer HTML parts.',
	'category' => 'plugin',
	'author' => 'Thomas Off',
	'author_email' => 'retiolum@googlemail.com',
	'state' => 'stable',
	'uploadfolder' => TRUE,
	'clearCacheOnLoad' => FALSE,
	'version' => '1.2.2',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.0.0-4.7.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);
