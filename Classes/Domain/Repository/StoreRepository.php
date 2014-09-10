<?php
namespace Aijko\StoreLocator\Domain\Repository;

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
 * Store repository
 *
 * @package store_locator
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StoreRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 *  Earthradius in km
	 */
	const EARTH_RADIUS = 6371;

	/**
	 * Find all main stores (for default view)
	 *
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findAllMainStores() {
		$query = $this->createQuery();
		return $query->matching($query->equals('ismainstore', TRUE))->execute()->toArray();
	}

	/**
	 * @param $latitude
	 * @param $longitude
	 * @param int $radius
	 * @param int $country
	 * @param bool $retailerLocal
	 * @param bool $retailerOnline
	 * @param array $typoscriptSettings
	 *
	 * @return array
	 */
	public function findStores($latitude, $longitude, $radius, $country, $retailerLocal, $retailerOnline, array $typoscriptSettings) {
		$query = $this->createQuery();
		$settings = $query->getQuerySettings();
		$storagePageIds = $settings->getStoragePageIds();
		$whereClause = array('1=1');
		$formulaToCalculateDistance = $this->getFormulaToCalculateDistance($latitude, $longitude);

		if (!$typoscriptSettings['disableStoragePageId']) {
			$whereClause[] = 'pid = ' . (int)$storagePageIds[0];
		}

		if ('' != $country) {
			$whereClause[] = 'country = ' . (int)$country;
		}

		if (TRUE === $retailerLocal && TRUE !== $retailerOnline) {
			$whereClause[] = 'localretailer = 1';
		}

		if (TRUE === $retailerOnline && TRUE !== $retailerLocal) {
			$whereClause[] = 'onlineretailer = 1';
		}

		if (TRUE === $retailerOnline && TRUE === $retailerLocal) {
			$whereClause[] = '(onlineretailer = 1 OR localretailer = 1)';
		}

		if ($radius) {
			$whereClause[] = $formulaToCalculateDistance . ' <= ' . (int)$radius;
		}

		$queryString = 'SELECT uid, address, name, latitude, longitude, ' . $formulaToCalculateDistance . ' AS distance FROM tx_storelocator_domain_model_store WHERE ' . implode(' AND ', $whereClause) . ' ' . $GLOBALS['TSFE']->sys_page->enableFields('tx_storelocator_domain_model_store') . ' ORDER BY distance';
		$result = $GLOBALS['TYPO3_DB']->sql_query($queryString);

		$returnValue = array();
		while ($data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$store = $this->findByUid($data['uid']);
			$store->setDistance($data['distance']);
			$returnValue[] = $store;
		}
		return $returnValue;
	}

	/**
	 * Create formula to calculate the beeline distance between two locations
	 *
	 * http://www.mamat-online.de/umkreissuche/opengeodb.php
	 *
	 * @param $latitude
	 * @param $longitude
	 *
	 * @return string
	 */
	protected function getFormulaToCalculateDistance($latitude, $longitude) {
		// convert degree measure in radian measure
		$lambda = $longitude * pi() / 180;
		$phi = $latitude * pi() / 180;

		// Convert spherical coordinates in cartesian (square) coordinate system
		$x = self::EARTH_RADIUS * cos($phi) * cos($lambda);
		$y = self::EARTH_RADIUS * cos($phi) * sin($lambda);
		$z = self::EARTH_RADIUS * sin($phi);

		// Calculate beeline distance
		return (2 * self::EARTH_RADIUS) . ' *
			ASIN(
				SQRT(
					POWER(' . $x .' - ' . self::EARTH_RADIUS . ' * COS(latitude * PI() / 180) * COS(longitude * PI() / 180), 2)
					+ POWER(' . $y .' - ' . self::EARTH_RADIUS . ' * COS(latitude * PI() / 180) * SIN(longitude * PI() / 180), 2)
					+ POWER(' . $z .' - ' . self::EARTH_RADIUS . ' * SIN(latitude * PI() / 180), 2)
				) / ' . (2 * self::EARTH_RADIUS) . '
			)
		';
	}
}

?>