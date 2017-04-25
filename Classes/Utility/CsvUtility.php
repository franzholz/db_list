<?php
namespace JambageCom\DbList\Utility;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CSV Utility
 */
class CsvUtility implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    public $objectManager;

    /**
     * @var \TYPO3\CMS\Extensionmanager\Utility\DatabaseUtility
     */
    protected $databaseUtility;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * @param \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
     */
    public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \TYPO3\CMS\Extensionmanager\Utility\DatabaseUtility $databaseUtility
     */
    public function injectDatabaseUtility(\TYPO3\CMS\Extensionmanager\Utility\DatabaseUtility $databaseUtility)
    {
        $this->databaseUtility = $databaseUtility;
    }

    /**
     * @param \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
     */
    public function injectSignalSlotDispatcher(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    /**
     * Helper function to install an extension
     * also processes db updates and clears the cache if the extension asks for it
     *
     * @param string $extensionKey
     * @throws ExtensionManagerException
     * @return void
     */
    public function install($extensionKey)
    {
        $extension = $this->enrichExtensionWithDetails($extensionKey, false);
        $this->loadExtension($extensionKey);
        if (!empty($extension['clearcacheonload']) || !empty($extension['clearCacheOnLoad'])) {
            $this->cacheManager->flushCaches();
        } else {
            $this->cacheManager->flushCachesInGroup('system');
        }
        $this->reloadCaches();
        $this->processExtensionSetup($extensionKey);

        $this->emitAfterExtensionInstallSignal($extensionKey);
    }

    /**
     * Emits a signal after extension files were imported
     *
     * @param string $destinationAbsolutePath
     */
    protected function emitAfterExtensionFileImportSignal($destinationAbsolutePath)
    {
        $this->signalSlotDispatcher->dispatch(__CLASS__, 'afterExtensionFileImport', [$destinationAbsolutePath, $this]);
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
