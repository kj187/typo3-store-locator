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
 * Store locator controller
 *
 * @package store_locator
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class StoreController extends \Aijko\StoreLocator\Controller\AbstractController {

	/**
	 * @var \Aijko\StoreLocator\Domain\Repository\StoreRepository
	 * @inject
	 */
	protected $storeRepository;

	/**
	 * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
	 * @inject
	 */
	protected $countryRepository;

	/**
	 * Store search
	 *
	 * @return void
	 */
	public function storeSearchAction() {
		$this->view->assign('displayMode', 'storeSearch');
		$this->settings['filter']['default']['radius'] = $this->getDefaultRadiusAsArray();
		$this->view->assign('settings', $this->settings);

		if ($this->settings['filter']['showAllCountries']) {
			$countries = $this->countryRepository->findAllOrderedBy('officialNameEn');
		} else {
			$countries = $this->getOnlyCountriesWhereStoresAvailable();
		}

		$this->view->assign('countries', $countries);
		$this->view->assign('preSelectedCountry', $this->countryRepository->findOneByIsoCodeA2($this->region));
	}

	/**
	 * Direction service
	 *
	 * @return void
	 */
	public function directionsServiceAction() {
		$this->view->assign('displayMode', 'directionsService');
		$store = $this->storeRepository->findByUid($this->settings['directionsservice']['destination']);
		$this->view->assign('defaultStore', $this->outputStoreData(array($store)));
	}

	/**
	 * Get all main stores (for default view)
	 *
	 * @return string
	 */
	public function getMainStoresAction() {
		$stores = $this->storeRepository->findAllMainStores();
		return $this->outputStoreData($stores);
	}

	/**
	 * Get stores (for ajax request)
	 *
	 * @param float $latitude
	 * @param float $longitude
	 * @param int $radius
	 * @param int $country
	 * @param bool $localretailer
	 * @param bool $onlineretailer
	 *
	 * @dontvalidate $latitude
	 * @dontvalidate $longitude
	 * @dontvalidate $radius
	 * @dontvalidate $country
	 * @dontvalidate $localretailer
	 * @dontvalidate $onlineretailer
	 *
	 * @return string
	 */
	public function getStoresAction($latitude, $longitude, $radius, $country = 0, $localretailer, $onlineretailer) {
		$stores = $this->storeRepository->findStores($latitude, $longitude, $radius, $country, $localretailer, $onlineretailer,  $this->settings);
		return $this->outputStoreData($stores);
	}

	/**
	 * Prepare json output for ajax request
	 *
	 * @param array $stores
	 * @return string
	 */
	protected function outputStoreData($stores) {
		$locations = array();
		$sidebarItems = array();
		$markerContent = array();
		$data = array('locations' => array());

		if (count($stores) > 0) {
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
		}

		return json_encode($data);
	}

	/**
	 * Get sidebar items (the address cards below the map)
	 *
	 * @param array $store
	 * @return string
	 */
	protected function getSidebarItems(array $store) {
		return $this->getStandaloneView(array('store' => $store, 'logo' => $this->getLogo($store), 'settings' => $this->settings), 'Store/Ajax/Item.html')->render();
	}

	/**
	 * Get marker content (overlay in the map)
	 *
	 * @param array $store
	 * @return string
	 */
	protected function getMarkerContent(array $store) {
		return $this->getStandaloneView(array('store' => $store, 'settings' => $this->settings), 'Store/Ajax/MarkerContent.html')->render();
	}

	/**
	 * @return array
	 */
	protected function getDefaultRadiusAsArray() {
		$radiusArrayTemp = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['filter']['default']['radius']);
		$radiusArray = array();
		foreach ($radiusArrayTemp as $value) {
			$radiusArray[$value] = $value;
		}

		return $radiusArray;
	}

	/**
	 * @return array
	 */
	protected function getOnlyCountriesWhereStoresAvailable() {
		$stores = $this->storeRepository->findAll();
		$countries = array('0' => $this->translate('select.country.choose'));
		foreach ($stores as $store) {
			if (!$store->getCountry()) {
				continue;
			}
			$countries[$store->getCountry()->getUid()] = $store->getCountry();
		}

		return $countries;
	}

	/**
	 * @param array $store
	 * @return string
	 */
	protected function getLogo(array $store) {
		if (!$store['logo']) {
			return '';
		}

		// Fallback to old imagehandling without FAL
		if (!\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($store['logo'])) {
			return $store['logo'];
		}

		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		$fileObjects = $fileRepository->findByRelation('tx_storelocator_domain_model_store', 'logo', $store['uid']);
		if (is_array($fileObjects)) {
			$fileObjects = array_shift($fileObjects);
		}

		return $fileObjects->getPublicUrl();
	}

}
