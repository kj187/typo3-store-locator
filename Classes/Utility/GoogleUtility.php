<?php
namespace Aijko\StoreLocator\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Julian Kleinhans <julian.kleinhans@aijko.de>, aijko GmbH
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
 * Class GoogleUtility
 *
 * @package Aijko\StoreLocator\Utility
 */
class GoogleUtility {

	/**
	 * @param string $address
	 * @param string $googleApiKey
	 * @return array
	 * @throws \Aijko\StoreLocator\Task\Store\GoogleException
	 */
	public static function getLatLongFromAddress($address, $googleApiKey) {
		$data = array();
		$logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
		$geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=' . $googleApiKey);
		$geoStdClass = json_decode($geocode);

		if ('ZERO_RESULTS' == $geoStdClass->status) {
			$logger->notice($geoStdClass->status, array('error_message' => $geoStdClass->error_message, 'Address' => $address, 'API Key: ' . $googleApiKey));
			$data['ZERO_RESULTS'] = $address;
			return $data;
		}

		if ('OK' !== $geoStdClass->status) { # https://developers.google.com/maps/documentation/geocoding/?hl=de#StatusCodes
			$logger->error($geoStdClass->status, array('error_message' => $geoStdClass->error_message, 'Address' => $address, 'API Key: ' . $googleApiKey));
			throw new \Aijko\StoreLocator\Task\Store\GoogleException($geoStdClass->error_message . '(Status: ' . $geoStdClass->status . ', Requested address: ' . $address . ', API Key: ' . $googleApiKey . ')', 1415868175);
		}

		$data['latitude'] = $geoStdClass->results[0]->geometry->location->lat;
		$data['longitude'] = $geoStdClass->results[0]->geometry->location->lng;

		return $data;
	}


	/**
	 * @param array $userData
	 * @return string
	 */
	public static function getFullAddressFromUserData(array $userData) {
		$address = array();
		if ($userData['street']) {
			$address[] = $userData['street'];
		}

		if ($userData['zipcode']) {
			$address[] = $userData['zipcode'];
		}

		if ($userData['city']) {
			$address[] = $userData['city'];
		}

		return implode(', ', $address);
	}

}
