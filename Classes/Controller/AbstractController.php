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
 * Abstract Controller
 *
 * @package store_locator
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *
 */
class AbstractController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObject;

	/**
	 * @var array
	 */
	protected $contentObjectData = array();

	/**
	 * @var string
	 */
	protected $region;

	/**
	 * Initializes the view before invoking an action method.
	 *
	 * Override this method to solve assign variables common for all actions
	 * or prepare the view in another way before the action is called.
	 *
	 * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view The view to be initialized
	 * @return void
	 * @api
	 */
	protected function initializeView(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view) {
		$this->contentObject = $this->configurationManager->getContentObject();
		$this->contentObjectData = $this->contentObject->data;
		$this->data['parent'] = $this->contentObjectData;
		$view->assign('data', $this->data);

		if ($this->settings['region']['htmlTag_langKey']) {
			$this->region = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('_', $this->settings['region']['htmlTag_langKey']);
			$this->region = $this->region[1];
		} else {
			$this->region = $this->settings['region']['default'];
		}
		$view->assign('region', $this->region);

		parent::initializeView($view);

		if (count($this->settings['javascript']['load']) > 0) {
			foreach ($this->settings['javascript']['load'] as $key => $value) {
				if ($value['enable']) {
					$src = $value['src'];
					if ($key == 'googleMapsApi') {
						$src .= '&language=' . ($this->settings['region']['htmlTag_langKey'] ? $this->settings['region']['htmlTag_langKey'] : $this->settings['region']['default']);
					}
					if ($key == 'googleMapsApi' && '' != $this->settings['general']['google']['apikey']) {
						$src .= '&key=' . $this->settings['general']['google']['apikey'];
					}
					$this->response->addAdditionalHeaderData($this->wrapJavascriptFile($src));
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
	 * @param array $variables
	 * @param $template
	 *
	 * @return object
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
