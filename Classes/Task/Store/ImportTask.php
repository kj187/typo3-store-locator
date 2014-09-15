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
 * Class ImportTask
 *
 * @package Aijko\StoreLocator\Task\Store
 */
class ImportTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

	/**
	 * @var \Aijko\StoreLocator\Domain\Repository\StoreRepository
	 */
	protected $storeRepository;

	/**
	 * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
	 */
	protected $countryRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 */
	protected $persistenceManager;

	/**
	 * @return bool
	 */
	public function execute() {
		$csvData = $this->getCsvData($this->csvPath);
		if (count($csvData) > 0) {
			$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
			$propertyMapper = $objectManager->get('TYPO3\\CMS\\Extbase\\Property\\PropertyMapper');
			$this->persistenceManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
			$this->storeRepository = $objectManager->get('Aijko\\StoreLocator\\Domain\\Repository\\StoreRepository');
			$this->countryRepository = $objectManager->get('SJBR\\StaticInfoTables\\Domain\\Repository\\CountryRepository');

			if ($this->truncate) {
				// Remove all stores
				$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_storelocator_domain_model_store', 'pid = ' . $this->storagePid);
			}

			foreach ($csvData as $row) {
				$setCountry = FALSE;
				if (array_key_exists('country', $row)) {
					$country = $this->countryRepository->findOneByIsoCodeA2($row['country']);
					$setCountry = TRUE;
					unset($row['country']);
				}

				$store = $propertyMapper->convert($row, 'Aijko\\StoreLocator\\Domain\\Model\\Store');
				$address = \Aijko\StoreLocator\Utility\GoogleUtility::getFullAddressFromUserData($row);
				$data = \Aijko\StoreLocator\Utility\GoogleUtility::getLatLongFromAddress($address);
				$store->setLatitude($data['latitude']);
				$store->setLongitude($data['longitude']);
				$store->setAddress($address);
				$store->setPid($this->storagePid);

				if (TRUE === $setCountry) {
					$store->setCountry($country);
				}
				$this->storeRepository->add($store);
			}

			$this->persistenceManager->persistAll();
		}

		return TRUE;
	}

	/**
	 * @param string $file
	 * @return array
	 * @throws \Exception
	 */
	protected function getCsvData($file) {
		$file = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($file);
		if (!file_exists($file)) {
			throw new \Exception('File does not exist: ' . $file, 1402826996);
		}

		ini_set('auto_detect_line_endings', TRUE);
		if (($handle = fopen($file, 'r')) === FALSE) {
			throw new \Exception('Cant open file: ' . $file, 1402827088);
		}

		$data = array();
		$firstIteration = TRUE;
		while (($rows = fgetcsv($handle, 0, ';')) !== FALSE) {
			if (TRUE == $firstIteration) {
				$columns = $rows;
				$firstIteration = FALSE;
			} else {
				$row = array();
				foreach ($rows as $key => $value) {
					$row[$columns[$key]] = $this->convertStringChartsetToUtf8($value);
				}
				$data[] = $row;
			}
		}
		fclose($handle);
		ini_set('auto_detect_line_endings', FALSE);

		return $data;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	protected function convertStringChartsetToUtf8($value) {
		//$value = iconv('CP1251', 'UTF-8', $value)
		$value = iconv('macintosh', 'UTF-8', $value);
		return $value;
	}

}