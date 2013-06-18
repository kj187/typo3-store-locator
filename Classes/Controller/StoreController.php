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
	 * @see parent::initialView
	 */
	protected function initializeView(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view) {
		if (count($this->settings['javascript']['load']) > 0) {
			foreach ($this->settings['javascript']['load'] as $key => $value) {
				if ($value['enable']) {
					$this->response->addAdditionalHeaderData($this->wrapJavascriptFile($value['src']));
				}
			}
		}
	}

	/**
	 * Wrap js files inside <script> tag
	 *
	 * @param string $file Path to file
	 * @return string <script.. string ready for <head> part
	 */
	public function wrapJavascriptFile($file) {
		if (substr($file, 0, 4) == 'EXT:') {
			list($extKey, $local) = explode('/', substr($file, 4), 2);
			if (strcmp($extKey, '') && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extKey) && strcmp($local, '')) {
				$file = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($extKey) . $local;
			}
		}

		$file = \TYPO3\CMS\Core\Utility\GeneralUtility::resolveBackPath($file);
		$file = \TYPO3\CMS\Core\Utility\GeneralUtility::createVersionNumberedFilename($file);
		return '<script src="' . htmlspecialchars($file) . '" type="text/javascript"></script>';
	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		if ($this->settings['region']['htmlTag_langKey']) {
			$region = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('_', $this->settings['region']['htmlTag_langKey']);
			$region = $region[1];
		} else {
			$region = $this->settings['region']['default'];
		}

		$this->view->assign('region', $region);
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
	 * @dontvalidate $latitude
	 * @dontvalidate $longitude
	 * @dontvalidate $radius
	 *
	 * @return string
	 */
	public function getStoresAction($latitude, $longitude, $radius = 50) {
		$stores = $this->storeRepository->findStores($latitude, $longitude, $radius, $this->settings);
		return $this->outputStoreData($stores);
	}

	/**
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

	/**
	 * @param $id
	 * @return NULL|string
	 */
	protected function translate($id) {
		return  \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($id, $this->request->getControllerExtensionKey());
	}

}

?>