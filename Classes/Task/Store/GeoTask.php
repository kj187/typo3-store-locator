<?php
namespace Aijko\StoreLocator\Task\Store;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Julian Kleinhans <julian.kleinhans@aijko.de>, aijko GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class GeoTask
 *
 * @package Aijko\StoreLocator\Task\Store
 */
class GeoTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

	/**
	 * @var \Aijko\StoreLocator\Domain\Repository\StoreRepository
	 */
	protected $storeRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 */
	protected $persistenceManager;

	/**
	 * @return bool
	 */
	public function execute() {
		$zeroResults = array();
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
		$this->storeRepository = $objectManager->get('Aijko\\StoreLocator\\Domain\\Repository\\StoreRepository');
		$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['store_locator']);

		$stores = $this->storeRepository->findAllStoresWithoutLatLong($this->storagePid);
		foreach ($stores as $store) {
			try {
				$data = \Aijko\StoreLocator\Utility\GoogleUtility::getLatLongFromAddress($store->getAddress(), $extensionConfiguration['googleApiKey']);
				if (isset($data['ZERO_RESULTS'])) {
					$zeroResults[] = $data['ZERO_RESULTS'];
					$store->setHidden(TRUE);
				} else {
					$store->setLatitude($data['latitude']);
					$store->setLongitude($data['longitude']);
				}

				$this->storeRepository->update($store);
				sleep(2); // Usage limits exceeded - https://developers.google.com/maps/documentation/business/articles/usage_limits

			} catch (\Aijko\StoreLocator\Task\Store\GoogleException $e) {
				$this->addMessage($e->getMessage(), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				return FALSE;
			}
		}

		$this->addMessage('Bei folgenden Adressen wurde der Datensatz deaktiviert da keine Lat/Long dazu vorhanden sind:<br> - ' . implode('<br> - ', $zeroResults), \TYPO3\CMS\Core\Messaging\FlashMessage::INFO);

		$this->persistenceManager->persistAll();
		return TRUE;
	}

	/**
	 * This method is used to add a message to the internal queue
	 *
	 * @param string $message The message itself
	 * @param integer $severity Message level (according to \TYPO3\CMS\Core\Messaging\FlashMessage class constants)
	 * @return void
	 */
	public function addMessage($message, $severity = \TYPO3\CMS\Core\Messaging\FlashMessage::OK) {
		$flashMessage = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage', $message, '', $severity);
		/** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
		$flashMessageService = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
		/** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
		$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
		$defaultFlashMessageQueue->enqueue($flashMessage);
	}

}