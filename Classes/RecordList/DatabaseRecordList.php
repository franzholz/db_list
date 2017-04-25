<?php
namespace JambageCom\DbList\RecordList;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */



/**
 * Class for rendering of Web>List module
 */
class DatabaseRecordList extends \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList
{
    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher;

    protected $extensionKey = 'db_list';

    /**
     * Constructor
     */
    public function __construct ()
    {
        parent::__construct();
        $this->signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    }

    /**
     * Add header line with field names as CSV line
     *
     * @return void
     */
    protected function addHeaderRowToCSV()
    {
        // Add header row, control fields will be reduced inside addToCSV()
        $this->addToCSV(
            array_combine($this->fieldArray, $this->fieldArray),
            true
        );
    }

    /**
     * Adds selected columns of one table row as CSV line.
     *
     * @param mixed[] $row Record array, from which the values of fields found in $this->fieldArray will be listed in the CSV output.
     * @param boolean $header true, if it is the header row
     * @return void
     */
    protected function addToCSV(array $row = [], $header = false)
    {
        $rowReducedByControlFields = self::removeControlFieldsFromFieldRow($row);
        // Get an field array without control fields but in the expected order
        $fieldArray = array_intersect_key(array_flip($this->fieldArray), $rowReducedByControlFields);
        // Overwrite fieldArray to keep the order with an array of needed fields
        $rowReducedToSelectedColumns = array_replace($fieldArray, array_intersect_key($rowReducedByControlFields, $fieldArray));
        $this->setCsvRow($rowReducedToSelectedColumns, $header);
    }

    /**
     * Adds input row of values to the internal csvLines array as a CSV formatted line
     *
     * @param mixed[] $csvRow Array with values to be listed.
     * @param boolean $header true, if it is the header row
     * @return void
     */
    public function setCsvRow ($csvRow, $header = false)
    {
        $slotResult = $this->signalSlotDispatcher->dispatch(__CLASS__, 'beforeSetCsvRow', array($this->table, $csvRow, $header));

        if (
            isset($slotResult) &&
            is_array($slotResult) &&
            !empty($slotResult) &&
            isset($slotResult['1'])
        ) {
            $newCsvRow = $slotResult['1'];

            if (
                isset($newCsvRow) &&
                is_array($newCsvRow)
            ) {
                $csvRow = $newCsvRow;
            }
        }

        parent::setCsvRow($csvRow);
    }
}

