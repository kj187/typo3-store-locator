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
 * @package store_locator
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class StoreRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * Find all main stores (for default view)
	 *
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findAllMainStores() {
		$query = $this->createQuery();
		return $query->matching($query->equals('ismainstore', TRUE))->execute();
	}

	/**
	 * @param $latitude
	 * @param $longitude
	 * @param int $radius
	 * @return array|null|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findStores($latitude, $longitude, $radius = 50) {
		$query = $this->createQuery();
		$settings = $query->getQuerySettings();
		$storagePageIds = $settings->getStoragePageIds();

		// Using the query statement is not an option. Unfortunately.
		$result = $GLOBALS['TYPO3_DB']->sql_query(
			sprintf(
				"SELECT uid, address, name, latitude, longitude, ( 3959 * acos( cos( radians('%s') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( latitude ) ) ) ) AS distance FROM tx_storelocator_domain_model_store WHERE pid = %s " .  $GLOBALS['TSFE']->sys_page->enableFields('tx_storelocator_domain_model_store') . " HAVING distance < '%s'  ORDER BY distance LIMIT 0 , 20",
				mysql_real_escape_string($latitude),
				mysql_real_escape_string($longitude),
				mysql_real_escape_string($latitude),
				$storagePageIds[0],
				mysql_real_escape_string($radius)
			)
		);

		$uids = array();
		while ($store = mysql_fetch_assoc($result)) {
			$uids[] = $store['uid'];
		}

		if (count($uids) > '0') {
			$query = $this->createQuery();
			return $query->matching($query->in('uid', $uids))->execute();
		}

		return NULL;
	}
}

?>