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

		// Start XML file, create parent node
		$dom = new \DOMDocument("1.0");
		$node = $dom->createElement("markers");
		$parnode = $dom->appendChild($node);


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

		#header("Content-type: text/xml");

		// Iterate through the rows, adding XML nodes for each
		while ($row = @mysql_fetch_assoc($result)){
			$node = $dom->createElement("marker");
			$newnode = $parnode->appendChild($node);
			$newnode->setAttribute("uid", $row['uid']);
			$newnode->setAttribute("name", $row['name']);
			$newnode->setAttribute("address", $row['address']);
			$newnode->setAttribute("latitude", $row['latitude']);
			$newnode->setAttribute("longitude", $row['longitude']);
			$newnode->setAttribute("distance", $row['distance']);
		}

		#echo $dom->saveXML();


		$data = json_encode(array(
			'xmlMarker' => $dom->saveXML(),
			'test' => 'OK'
		));
		echo $data;

		die();

		// TODO als eID/typeNum auslagern ohne layout schnickschnack
	}

}
?>