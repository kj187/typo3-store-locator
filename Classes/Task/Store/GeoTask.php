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
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
		$this->storeRepository = $objectManager->get('Aijko\\StoreLocator\\Domain\\Repository\\StoreRepository');
		$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['store_locator']);

		$stores = $this->storeRepository->findAllStoresWithoutLatLong($this->storagePid);
		foreach ($stores as $store) {
			$data = \Aijko\StoreLocator\Utility\GoogleUtility::getLatLongFromAddress($store->getAddress(), $extensionConfiguration['googleApiKey']);
			$store->setLatitude($data['latitude']);
			$store->setLongitude($data['longitude']);
			$this->storeRepository->update($store);
			sleep(2); // Usage limits exceeded - https://developers.google.com/maps/documentation/business/articles/usage_limits
		}

		$this->persistenceManager->persistAll();
		return TRUE;
	}

}