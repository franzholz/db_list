<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2014 Kasper Skårhøj (kasperYYYY@typo3.com)
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
class ux_t3lib_TCEmain extends t3lib_TCEmain {

	/**
	 * Returns data for inline fields.
	 *
	 * @param	array		Current value array
	 * @param	array		TCA field config
	 * @param	integer		Record id
	 * @param	string		Status string ('update' or 'new')
	 * @param	string		Table name, needs to be passed to t3lib_loadDBGroup
	 * @param	string		The current field the values are modified for
	 * @return	string		Modified values
	 */
	protected function checkValue_inline_processDBdata($valueArray, $tcaFieldConf, $id, $status, $table, $field) {
		$newValue = '';
		$foreignTable = $tcaFieldConf['foreign_table'];

		/*
		 * Fetch the related child records by using t3lib_loadDBGroup:
		 * @var $dbAnalysis t3lib_loadDBGroup
		 */
		$dbAnalysis = t3lib_div::makeInstance('t3lib_loadDBGroup');
		$dbAnalysis->start(implode(',', $valueArray), $foreignTable, '', 0, $table, $tcaFieldConf);
			// If the localizationMode is set to 'keep', the children for the localized parent are kept as in the original untranslated record:
		$localizationMode = t3lib_BEfunc::getInlineLocalizationMode($table, $tcaFieldConf);
		if ($localizationMode == 'keep' && $status == 'update') {
				// Fetch the current record and determine the original record:
			$row = t3lib_BEfunc::getRecordWSOL($table, $id);
			if (is_array($row)) {
				$language = intval($row[$GLOBALS['TCA'][$table]['ctrl']['languageField']]);
				$transOrigPointer = intval($row[$GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField']]);
					// If language is set (e.g. 1) and also transOrigPointer (e.g. 123), use transOrigPointer as uid:
				if ($language > 0 && $transOrigPointer) {
// FHO Anfang
					if ($GLOBALS['TCA'][$table]['ctrl']['transOrigPointerTable'] == $table) {
						$id = $transOrigPointer;
					}
// FHO Ende
						// If we're in active localizationMode 'keep', prevent from writing data to the field of the parent record:
						// (on removing the localized parent, the original (untranslated) children would then also be removed)
					$keepTranslation = TRUE;
				}
			}
		}

		// IRRE with a pointer field (database normalization):
		if ($tcaFieldConf['foreign_field']) {
				// if the record was imported, sorting was also imported, so skip this
			$skipSorting = ($this->callFromImpExp ? TRUE : FALSE);
				// update record in intermediate table (sorting & pointer uid to parent record)
			$dbAnalysis->writeForeignField($tcaFieldConf, $id, 0, $skipSorting);
			$newValue = ($keepTranslation ? 0 : $dbAnalysis->countItems(FALSE));
				// IRRE with MM relation:
		} else {
			if ($this->getInlineFieldType($tcaFieldConf) == 'mm') {
					// in order to fully support all the MM stuff, directly call checkValue_group_select_processDBdata instead of repeating the needed code here
				$valueArray = $this->checkValue_group_select_processDBdata($valueArray, $tcaFieldConf, $id, $status, 'select', $table, $field);
				$newValue = ($keepTranslation ? 0 : $valueArray[0]);
					// IRRE with comma separated values:
			} else {
				$valueArray = $dbAnalysis->getValueArray();
					// Checking that the number of items is correct:
				$valueArray = $this->checkValue_checkMax($tcaFieldConf, $valueArray);
					// If a valid translation of the 'keep' mode is active, update relations in the original(!) record:
				if ($keepTranslation) {
					$this->updateDB($table, $transOrigPointer, array($field => implode(',', $valueArray)));
				} else {
					$newValue = implode(',', $valueArray);
				}
			}
		}

		return $newValue;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/db_list/xclass/class.ux_t3lib_tcemain.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/db_list/xclass/class.ux_t3lib_tcemain.php']);
}


?>