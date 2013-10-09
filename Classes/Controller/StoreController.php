<?php
namespace Aijko\StoreLocator\Controller;

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
 *
 *
 * @package store_locator
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class StoreController extends \Aijko\StoreLocator\Controller\AbstractController {

	/**
	 * storeRepository
	 *
	 * @var \Aijko\StoreLocator\Domain\Repository\StoreRepository
	 * @inject
	 */
	protected $storeRepository;

	/**
	 * countryRepository
	 *
	 * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
	 * @inject
	 */
	protected $countryRepository;

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$this->settings['filter']['default']['radius'] = $this->prepareDefaultRadius();
		$this->view->assign('settings', $this->settings);

		if ($this->settings['region']['htmlTag_langKey']) {
			$region = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('_', $this->settings['region']['htmlTag_langKey']);
			$region = $region[1];
		} else {
			$region = $this->settings['region']['default'];
		}

		$this->view->assign('region', $region);
		$this->view->assign('countries', $this->getOnlyCountriesWhereStoresAvailable());
		$this->view->assign('preSelectedCountry', $this->countryRepository->findOneByIsoCodeA2($region));
	}

	/**
	 * @return array
	 */
	protected function getOnlyCountriesWhereStoresAvailable() {
		$stores = $this->storeRepository->findAll();
		$countries = array();
		foreach ($stores as $store) {
			$countries[$store->getCountry()->getUid()] = $store->getCountry();
		}

		return $countries;
	}

	/**
	 * Get all main stores (for default view)
	 * @return string
	 */
	public function getMainStoresAction() {
		$stores = $this->storeRepository->findAllMainStores();
		return $this->outputStoreData($stores);
	}

	/**
	 * @param float $latitude
	 * @param float $longitude
	 * @param int $radius
	 * @param int $country
	 *
	 * @dontvalidate $latitude
	 * @dontvalidate $longitude
	 * @dontvalidate $radius
	 * @dontvalidate $country
	 *
	 * @return string
	 */
	public function getStoresAction($latitude, $longitude, $radius = 50, $country = 0) {
		$stores = $this->storeRepository->findStores($latitude, $longitude, $radius, $country, $this->settings);
		return $this->outputStoreData($stores);
	}

	/**
	 * @param $stores
	 * @return string
	 */
	protected function outputStoreData($stores) {
		$locations = array();
		$sidebarItems = array();
		$markerContent = array();

		if (NULL !== $stores) {
			$stores = $stores->toArray();

			foreach ($stores as $store) {
				$locations[] = $store->toArray();
				$sidebarItems[] = $this->getSidebarItems($store->toArray());
				$markerContent[] = $this->getMarkerContent($store->toArray());
			}

			$data = array(
				'sidebarItems' => $sidebarItems,
				'markerContent' => $markerContent,
				'locations' => $locations
			);

		} else {
			$data = array(
				'locations' => array(),
				'notification' => $this->translate('locations.empty')
			);
		}

		return json_encode($data);
	}

	/**
	 * @param array $store
	 * @return string
	 */
	protected function getSidebarItems(array $store) {
		return $this->getStandaloneView(array('store' => $store, 'settings' => $this->settings), 'Store/Ajax/Item.html')->render();
	}

	/**
	 * @param array $store
	 * @return string
	 */
	protected function getMarkerContent(array $store) {
		return $this->getStandaloneView(array('store' => $store, 'settings' => $this->settings), 'Store/Ajax/MarkerContent.html')->render();
	}

	/**
	 * @return array
	 */
	protected function prepareDefaultRadius() {
		$radiusArrayTemp = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['filter']['default']['radius']);
		$radiusArray = array();
		foreach ($radiusArrayTemp as $value) {
			$radiusArray[$value] = $value;
		}

		return $radiusArray;
	}

}

?>