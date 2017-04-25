<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Recordlist\\RecordList\\DatabaseRecordList'] = array(
   'className' => 'JambageCom\\DbList\\RecordList\\DatabaseRecordList'
);


