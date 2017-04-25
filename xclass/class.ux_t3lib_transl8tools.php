<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2011 Kasper Skårhøj (kasperYYYY@typo3.com)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * Contains translation tools
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */










/**
 * Contains translation tools
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 */
class ux_t3lib_transl8tools	{

	/**
	 * Returns array of system languages
	 * Since TYPO3 4.5 the flagIcon is not returned as a filename in "gfx/flags/*" anymore,
	 * but as a string <flags-xx>. The calling party should call
	 * t3lib_iconWorks::getSpriteIcon(<flags-xx>) to get an HTML which will represent
	 * the flag of this language.
	 *
	 * @param	integer		page id (only used to get TSconfig configuration setting flag and label for default language)
	 * @param	string		Backpath for flags
	 * @return	array		Array with languages (title, uid, flagIcon)
	 */
	function getSystemLanguages($page_id = 0, $backPath = '') {
		global $TCA,$LANG;

			// Icons and language titles:
		t3lib_div::loadTCA('sys_language');
		$flagAbsPath = t3lib_div::getFileAbsFileName($TCA['sys_language']['columns']['flag']['config']['fileFolder']);
		$flagIconPath = $backPath.'../'.substr($flagAbsPath, strlen(PATH_site));

		$modSharedTSconfig = t3lib_BEfunc::getModTSconfig($page_id, 'mod.SHARED');
		$languageIconTitles = array();

			// fallback "old iconstyles"
		if (preg_match('/\.gif$/', $modSharedTSconfig['properties']['defaultLanguageFlag'])) {
			$modSharedTSconfig['properties']['defaultLanguageFlag'] = str_replace('.gif', '', $modSharedTSconfig['properties']['defaultLanguageFlag']);
		}
		$languageIconTitles[0] = array(
			'uid' => 0,
			'title' => strlen($modSharedTSconfig['properties']['defaultLanguageLabel']) ? $modSharedTSconfig['properties']['defaultLanguageLabel'] . ' (' . $GLOBALS['LANG']->sl('LLL:EXT:lang/locallang_mod_web_list.xml:defaultLanguage') . ')' : $GLOBALS['LANG']->sl('LLL:EXT:lang/locallang_mod_web_list.xml:defaultLanguage'),
			'ISOcode' => 'DEF',
			'flagIcon' => strlen($modSharedTSconfig['properties']['defaultLanguageFlag']) ? 'flags-' . $modSharedTSconfig['properties']['defaultLanguageFlag'] : 'empty-empty',
		);

			// Set "All" language:
		$languageIconTitles[-1] = array(
			'uid' => -1,
			'title' => $GLOBALS['LANG']->getLL('multipleLanguages'),
			'ISOcode' => 'DEF',
			'flagIcon' => 'flags-multiple',
		);

			// Find all system languages:
		$sys_languages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'sys_language',
			''
		);
		foreach ($sys_languages as $row) {
			$languageIconTitles[$row['uid']] = $row;

			if ($row['static_lang_isocode'] && t3lib_extMgm::isLoaded('static_info_tables'))	{
				$staticLangRow = t3lib_BEfunc::getRecord('static_languages', $row['static_lang_isocode'], 'lg_iso_2');
				if ($staticLangRow['lg_iso_2']) {
					$languageIconTitles[$row['uid']]['ISOcode'] = $staticLangRow['lg_iso_2'];
				}
			}
			if (strlen($row['flag'])) {
				$languageIconTitles[$row['uid']]['flagIcon'] = t3lib_iconWorks::mapRecordTypeToSpriteIconName('sys_language', $row);
			}
		}

		return $languageIconTitles;
	}

	/**
	 * Information about translation for an element
	 * Will overlay workspace version of record too!
	 *
	 * @param	string		Table name
	 * @param	integer		Record uid
	 * @param	integer		Language uid. If zero, then all languages are selected.
	 * @return	array		Array with information. Errors will return string with message.
	 */
	function translationInfo($table, $uid, $sys_language_uid = 0, $row = NULL, $selFieldList = '') {
		if ($GLOBALS['TCA'][$table] && $uid) {
			t3lib_div::loadTCA($table);

			if ($row === NULL) {
				$row = t3lib_BEfunc::getRecordWSOL($table, $uid);
			}

			if (is_array($row))	{
				$trTable = $this->getTranslationTable($table);
				if ($trTable) {

					// FHO	if ($trTable !== $table || $row[$GLOBALS['TCA'][$table]['ctrl']['languageField']] <= 0) {
					// FHO	if ($trTable !== $table || $row[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']] == 0) {
							$transOrigPointerField = $GLOBALS['TCA'][$trTable]['ctrl']['transOrigPointerField'];
							$the_sys_language_uid = intval($row[$GLOBALS['TCA'][$table]['ctrl']['languageField']]);
							$where = 'pid=' . intval($table === 'pages' ? $row['uid'] : $row['pid']).	// Making exception for pages of course where the translations will always be ON the page, not on the level above...
								' AND ' . $GLOBALS['TCA'][$trTable]['ctrl']['languageField'] . (!$sys_language_uid ? ' NOT IN (0,' . $the_sys_language_uid . ')' : '=' . intval($sys_language_uid)) .
								' AND ' .
									(($GLOBALS['TCA'][$table]['ctrl']['transOrigPointerTable'] != '' || $the_sys_language_uid > 0) ?
										$transOrigPointerField . '=' . intval($row[$transOrigPointerField]) :
										$transOrigPointerField . '=' . intval($uid)
									) .
								t3lib_BEfunc::deleteClause($trTable) .
								t3lib_BEfunc::versioningPlaceholderClause($trTable);

								// Look for translations of this record, index by language field value:
 							$translationsTemp = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
								($selFieldList ? $selFieldList : 'uid,' . $GLOBALS['TCA'][$trTable]['ctrl']['languageField']),
								$trTable,
								$where
							);

 							$translations = array();
 							$translations_errors = array();
							foreach ($translationsTemp as $r) {
								if (!isset($translations[$r[$GLOBALS['TCA'][$trTable]['ctrl']['languageField']]])) {
									$translations[$r[$GLOBALS['TCA'][$trTable]['ctrl']['languageField']]] = $r;
 								} else {
									$translations_errors[$r[$GLOBALS['TCA'][$trTable]['ctrl']['languageField']]][] = $r;
 								}
 							}
							return array(
 								'table' => $table,
 								'uid' => $uid,
								'CType' => $row['CType'],
								'sys_language_uid' => $row[$GLOBALS['TCA'][$table]['ctrl']['languageField']],
								'translation_table' => $trTable,
								'translations' => $translations,
								'excessive_translations' => $translations_errors
							);
				// FHO		} else {
			// FHO			return 'Record "' . $table . '_' . $uid . '" seems to be a translation already (has a relation to record "' . $row[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']] . '")';
			// FHO		}
			// FHO	} else {
			// FHO		return 'Record "' . $table . '_' . $uid . '" seems to be a translation already (has a language value "' . $row[$GLOBALS['TCA'][$table]['ctrl']['languageField']] . '", relation to record "' . $row[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']] . '")';
			// 		}
				} else {
					return 'Translation is not supported for this table!';
				}
			} else {
				return 'Record "' . $table . '_' . $uid . '" was not found';
			}
		} else {
			return 'No table "' . $table . '" or no UID value';
		}
 	}

	/**
	 * Returns the table in which translations for input table is found.
	 *
	 * @param	[type]		$table: ...
	 * @return	[type]		...
	 */
	function getTranslationTable($table) {
		return $this->isTranslationInOwnTable($table) ? $table : $this->foreignTranslationTable($table);
	}

	/**
	 * Returns TRUE, if the input table has localization enabled and done so with records from the same table
	 *
	 * @param	[type]		$table: ...
	 * @return	[type]		...
	 */
	function isTranslationInOwnTable($table) {
		return $GLOBALS['TCA'][$table]['ctrl']['languageField']
			&& $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'];
			// FHO && !$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerTable'];
	}

	/**
	 * Returns foreign translation table, if any
	 *
	 * @param	[type]		$table: ...
	 * @return	[type]		...
	 */
	function foreignTranslationTable($table) {
		$trTable = $GLOBALS['TCA'][$table]['ctrl']['transForeignTable'];

		if ($trTable && $GLOBALS['TCA'][$trTable] && $GLOBALS['TCA'][$trTable]['ctrl']['languageField']
			&& $GLOBALS['TCA'][$trTable]['ctrl']['transOrigPointerField']
			&& $GLOBALS['TCA'][$trTable]['ctrl']['transOrigPointerTable'] === $table) {
			return $trTable;
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/db_list/xclass/class.ux_t3lib_transl8tools.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/db_list/xclass/class.ux_t3lib_transl8tools.php']);
}


?>