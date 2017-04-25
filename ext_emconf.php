<?php

########################################################################
# Extension Manager/Repository config file for ext: "db_list"
#
# Auto generated 12-08-2009 09:41
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Enhancements of the list module',
	'description' => 'This implements hooks and copied classes from the records list also used in the list module.',
	'category' => 'be',
	'author' => 'Franz Holzinger',
	'author_email' => 'franz@ttproducts.de',
	'shy' => '',
	'dependencies' => 'div2007,recordlist',
	'conflicts' => '',
	'priority' => '',
	'suggests' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'jambage.com',
	'version' => '0.2.0',
	'constraints' => array(
		'depends' => array(
			'php' => '5.1.2-5.5.99',
			'typo3' => '4.7.0-4.7.99',
			'div2007' => '0.2.1-0.0.0',
			'recordlist' => '4.7.0-0.0.0'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:19:{s:9:"ChangeLog";s:4:"1cad";s:35:"class.tx_dblist_localrecordlist.php";s:4:"be9d";s:30:"class.tx_dblist_recordlist.php";s:4:"006a";s:26:"class.tx_dblist_script.php";s:4:"1c93";s:12:"ext_icon.gif";s:4:"488d";s:17:"ext_localconf.php";s:4:"f770";s:31:"lang/locallang_mod_web_list.xml";s:4:"f1c6";s:30:"xclass/class.ux_sc_db_list.php";s:4:"6944";s:36:"xclass/class.ux_t3lib_recordlist.php";s:4:"b98f";s:38:"xclass/class.ux_t3lib_transl8tools.php";s:4:"f469";s:14:"doc/manual.sxw";s:4:"fe8b";s:21:"gfx/control_first.gif";s:4:"ec47";s:30:"gfx/control_first_disabled.gif";s:4:"d0ee";s:20:"gfx/control_last.gif";s:4:"5da1";s:29:"gfx/control_last_disabled.gif";s:4:"1e27";s:20:"gfx/control_next.gif";s:4:"c1b0";s:29:"gfx/control_next_disabled.gif";s:4:"2f56";s:24:"gfx/control_previous.gif";s:4:"dcd8";s:33:"gfx/control_previous_disabled.gif";s:4:"7d6c";}'
);

?>