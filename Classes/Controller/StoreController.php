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

		#echo $this->test('51.332268', '6.830757');die();
		#echo $this->test('30', '12', '5000');die();

		$stores = $this->storeRepository->findAll();
		$this->view->assign('stores', $stores);
	}

	/**
	 * action show
	 *
	 * @param \Aijko\StoreLocator\Domain\Model\Store $store
	 * @return void
	 */
	public function showAction(\Aijko\StoreLocator\Domain\Model\Store $store) {
		$this->view->assign('store', $store);
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


		//TODO exlude in repo

		// Search the rows in the markers table
		$query = sprintf("SELECT uid, address, name, latitude, longitude, ( 3959 * acos( cos( radians('%s') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( latitude ) ) ) ) AS distance FROM tx_storelocator_domain_model_store HAVING distance < '%s' ORDER BY distance LIMIT 0 , 20",
			mysql_real_escape_string($latitude),
			mysql_real_escape_string($longitude),
			mysql_real_escape_string($latitude),
			mysql_real_escape_string($radius));
		$result = $GLOBALS['TYPO3_DB']->sql_query($query);

		if (!$result) {
			die("Invalid query: " . mysql_error());
		}

		// Iterate through the rows, adding XML nodes for each
		$locations = array();
		$sidebarItems = array();
		while ($row = @mysql_fetch_assoc($result)){

			$locations[] = $row;
			$sidebarItems[] = '
				<address class="address">
					<strong>RONAL Vertrieb Schweiz</strong><br>
					Lerchenbühl 3<br>
					CH-4624 Härkingen<br>
					Telefon +41 62 389 06 06<br>
					Telefax +41 62 389 05 11
				</address>
				<div class="address-footer"><a href="#">verkauf@ronal.ch<br></a><a href="#">www.ronal.ch</a></div>
				';

		}

		$data = json_encode(array(
			'sidebarItems' => $sidebarItems,
			'locations' => $locations
		));
		echo $data;

		die();

		// TODO als eID/typeNum auslagern ohne layout schnickschnack
	}

}
?>