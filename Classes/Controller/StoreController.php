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
class StoreController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * storeRepository
	 *
	 * @var \Aijko\StoreLocator\Domain\Repository\StoreRepository
	 * @inject
	 */
	protected $storeRepository;

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$stores = $this->storeRepository->findAll();
		$this->view->assign('stores', $stores);
	}

	/**
	 * Get all main stores (for default view)
	 * @return void
	 */
	public function getMainStoresAction() {
		$stores = $this->storeRepository->findAllMainStores();
		$this->outputStoreData($stores);
	}

	/**
	 * @param float $latitude
	 * @param float $longitude
	 * @param int $radius
	 * @dontvalidate $latitude
	 * @dontvalidate $longitude
	 * @dontvalidate $radius
	 *
	 * @return xml
	 */
	public function getStoresAction($latitude, $longitude, $radius = 50) {
		$stores = $this->storeRepository->findStores($latitude, $longitude, $radius);
		$this->outputStoreData($stores);
	}

	/**
	 * TODO als eID/typeNum auslagern
	 * @param $stores
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

			$data = json_encode(array(
				'sidebarItems' => $sidebarItems,
				'markerContent' => $markerContent,
				'locations' => $locations
			));
			echo $data;
		}

		die();
	}

	/**
	 * @param array $store
	 * @return string
	 */
	protected function getSidebarItems(array $store) {
		return $this->getStandaloneView(array('store' => $store), 'Store/Ajax/Item.html')->render();
	}

	/**
	 * @param array $store
	 * @return string
	 */
	protected function getMarkerContent(array $store) {
		return $this->getStandaloneView(array('store' => $store), 'Store/Ajax/MarkerContent.html')->render();
	}

	/**
	 * @param array $store
	 * @return string
	 */
	protected function getStandaloneView(array $variables, $template) {
		$viewObject = $this->objectManager->create('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$viewObject->setFormat('html');
		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
		$templatePathAndFilename = $templateRootPath . $template;
		$viewObject->setTemplatePathAndFilename($templatePathAndFilename);
		$viewObject->assignMultiple($variables);
		return $viewObject;
	}



}
?>