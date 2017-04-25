<?php
if (!defined ('TYPO3_MODE'))	die ('Access denied.');

if (!defined ('PATH_BE_dblist')) {
	define('PATH_BE_dblist', t3lib_extMgm::extPath($_EXTKEY));
}

$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['typo3/db_list.php'] = PATH_BE_dblist . 'xclass/class.ux_sc_db_list.php';

$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_recordlist.php'] = PATH_BE_dblist . 'xclass/class.ux_t3lib_recordlist.php';

$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_transl8tools.php'] = PATH_BE_dblist . 'xclass/class.ux_t3lib_transl8tools.php';

$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tcemain.php'] = PATH_BE_dblist . 'xclass/class.ux_t3lib_tcemain.php';

$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tceforms_inline.php'] = PATH_BE_dblist . 'xclass/class.ux_t3lib_tceforms_inline.php';

?>