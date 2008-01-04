<?php

########################################################################
# Extension Manager/Repository config file for ext: "lumophpinclude"
#
# Auto generated 04-01-2008 12:08
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'LumoNet PHP Include',
	'description' => 'Includes local PHP scripts via eval() or external PHP script via HTTP. Provides options to transfer GET and POST variables to the included script and strip header and footer HTML parts.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author' => 'Thomas Off',
	'author_email' => 'typo3@retiolum.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.0.0-4.1.99',
			'php' => '4.4.0-5.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:15:{s:9:"ChangeLog";s:4:"1299";s:10:"README.txt";s:4:"7559";s:12:"ext_icon.gif";s:4:"e86e";s:17:"ext_localconf.php";s:4:"294e";s:14:"ext_tables.php";s:4:"5b1c";s:13:"locallang.xml";s:4:"f7b9";s:16:"locallang_db.xml";s:4:"8e64";s:14:"doc/manual.sxw";s:4:"d7ba";s:20:"lib/Snoopy.class.php";s:4:"7c1e";s:14:"pi1/ce_wiz.gif";s:4:"4012";s:35:"pi1/class.tx_lumophpinclude_pi1.php";s:4:"1210";s:43:"pi1/class.tx_lumophpinclude_pi1_wizicon.php";s:4:"4871";s:13:"pi1/clear.gif";s:4:"cc11";s:23:"pi1/flexform_ds_pi1.xml";s:4:"8ead";s:17:"pi1/locallang.xml";s:4:"8c9b";}',
);

?>