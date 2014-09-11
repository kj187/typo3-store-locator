<?php
namespace Aijko\StoreLocator\Hooks;

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
 * Class AutoFillFields
 *
 * @package Aijko\StoreLocator\Hooks
 */
class AutoFillFields {

	/**
	 * @param array $incomingFieldArray
	 * @param string $table
	 * @param int $id
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
	 */
	public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, $table, $id,  \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler)  {
		if ('tx_storelocator_domain_model_store' !== $table) {
			return;
		}

		$incomingFieldArray['address'] = \Aijko\StoreLocator\Utility\GoogleUtility::getFullAddressFromUserData($incomingFieldArray);
	}

	/**
	 * @param string $status
	 * @param string $table
	 * @param int $id
	 * @param array $fieldArray
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
	 */
	public function processDatamap_postProcessFieldArray($status, $table, $id, array &$fieldArray, \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler) {
		if ('tx_storelocator_domain_model_store' !== $table || !isset($fieldArray['address'])) {
			return;
		}

		$data = \Aijko\StoreLocator\Utility\GoogleUtility::getLatLongFromAddress($fieldArray['address']);
		$fieldArray['latitude'] = $data['latitude'];
		$fieldArray['longitude'] = $data['longitude'];
	}

}