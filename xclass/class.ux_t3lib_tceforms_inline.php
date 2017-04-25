<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2011 Oliver Hader <oliver@typo3.org>
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
 * The Inline-Relational-Record-Editing (IRRE) functions as part of the TCEforms.
 *
 * @author	Oliver Hader <oliver@typo3.org>
 */
class ux_t3lib_TCEforms_inline extends t3lib_TCEforms_inline {

	/**
	 * Handle AJAX calls to show a new inline-record of the given table.
	 * Normally this method is never called from inside TYPO3. Always from outside by AJAX.
	 *
	 * @param	string		$domObjectId: The calling object in hierarchy, that requested a new record.
	 * @param	string		$foreignUid: If set, the new record should be inserted after that one.
	 * @return	array		An array to be used for JSON
	 */
	function createNewRecord($domObjectId, $foreignUid = 0) {
			// the current table - for this table we should add/import records
		$current = $this->inlineStructure['unstable'];
			// the parent table - this table embeds the current table
		$parent = $this->getStructureLevel(-1);
			// get TCA 'config' of the parent table
		if (!$this->checkConfiguration($parent['config'])) {
			return $this->getErrorMessageForAJAX('Wrong configuration in table ' . $parent['table']);
		}
		$config = $parent['config'];
		$collapseAll = (isset($config['appearance']['collapseAll']) && $config['appearance']['collapseAll']);
		$expandSingle = (isset($config['appearance']['expandSingle']) && $config['appearance']['expandSingle']);

			// Put the current level also to the dynNestedStack of TCEforms:
		$this->fObj->pushToDynNestedStack('inline', $this->inlineNames['object']);
			// dynamically create a new record using t3lib_transferData
		if (!$foreignUid || !t3lib_utility_Math::canBeInterpretedAsInteger($foreignUid) || $config['foreign_selector']) {
			$record = $this->getNewRecord($this->inlineFirstPid, $current['table']);
				// Set language of new child record to the language of the parent record:
			if ($parent['localizationMode'] == 'select') { // FHO: fix bug #57063
				$parentRecord = $this->getRecord(0, $parent['table'], $parent['uid']);
				$parentLanguageField = $GLOBALS['TCA'][$parent['table']]['ctrl']['languageField'];
				$childLanguageField = $GLOBALS['TCA'][$current['table']]['ctrl']['languageField'];
				if ($parentRecord[$parentLanguageField] > 0) {
					$record[$childLanguageField] = $parentRecord[$parentLanguageField];
				}

				// Anfang FHO
				if (
					$config['foreign_table'] != '' &&
					$config['foreign_field'] != '' &&
					$config['foreign_table_field'] != ''
				) {
					if (!$record[$config['foreign_table_field']]) {
						$record[$config['foreign_table_field']] = $parent['table'];
						$record[$config['foreign_field']] = $parentRecord['uid'];
					}
				}
				// Ende FHO
			}
				// dynamically import an existing record (this could be a call from a select box)
		} else {
			$record = $this->getRecord($this->inlineFirstPid, $current['table'], $foreignUid);
		}

			// now there is a foreign_selector, so there is a new record on the intermediate table, but
			// this intermediate table holds a field, which is responsible for the foreign_selector, so
			// we have to set this field to the uid we get - or if none, to a new uid
		if ($config['foreign_selector'] && $foreignUid) {
			$selConfig = $this->getPossibleRecordsSelectorConfig($config, $config['foreign_selector']);
				// For a selector of type group/db, prepend the tablename (<tablename>_<uid>):
			$record[$config['foreign_selector']] = $selConfig['type'] != 'groupdb' ? '' : $selConfig['table'] . '_';
			$record[$config['foreign_selector']] .= $foreignUid;
		}

			// the HTML-object-id's prefix of the dynamically created record
		$objectPrefix = $this->inlineNames['object'] . self::Structure_Separator . $current['table'];
		$objectId = $objectPrefix . self::Structure_Separator . $record['uid'];

			// render the foreign record that should passed back to browser
		$item = $this->renderForeignRecord($parent['uid'], $record, $config);
		if ($item === FALSE) {
			return $this->getErrorMessageForAJAX('Access denied');
		}

		if (!$current['uid']) {
			$jsonArray = array(
				'data' => $item,
				'scriptCall' => array(
					"inline.domAddNewRecord('bottom','" . $this->inlineNames['object'] . "_records','$objectPrefix',json.data);",
					"inline.memorizeAddRecord('$objectPrefix','" . $record['uid'] . "',null,'$foreignUid');"
				)
			);

			// append the HTML data after an existing record in the container
		} else {
			$jsonArray = array(
				'data' => $item,
				'scriptCall' => array(
					"inline.domAddNewRecord('after','" . $domObjectId . '_div' . "','$objectPrefix',json.data);",
					"inline.memorizeAddRecord('$objectPrefix','" . $record['uid'] . "','" . $current['uid'] . "','$foreignUid');"
				)
			);
		}
		$this->getCommonScriptCalls($jsonArray, $config);
			// Collapse all other records if requested:
		if (!$collapseAll && $expandSingle) {
			$jsonArray['scriptCall'][] = "inline.collapseAllRecords('$objectId', '$objectPrefix', '" . $record['uid'] . "');";
		}
			// tell the browser to scroll to the newly created record
		$jsonArray['scriptCall'][] = "Element.scrollTo('" . $objectId . "_div');";
			// fade out and fade in the new record in the browser view to catch the user's eye
		$jsonArray['scriptCall'][] = "inline.fadeOutFadeIn('" . $objectId . "_div');";

			// Remove the current level also from the dynNestedStack of TCEforms:
		$this->fObj->popFromDynNestedStack();
			// Return the JSON array:
		return $jsonArray;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/db_list/xclass/class.ux_class.t3lib_tceforms_inline.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/db_list/xclass/class.ux_class.t3lib_tceforms_inline.php']);
}


?>