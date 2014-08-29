<?php

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
 * Class tx_storelocator_datamap
 */
class tx_storelocator_datamap {

	/**
	 * @param $incomingFieldArray
	 * @param $table
	 * @param $id
	 * @param $pObject
	 */
	function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, $pObject)  {
		if ('tx_storelocator_domain_model_store' === $table) {
			$address = array();
			if ($incomingFieldArray['street']) $address[] = $incomingFieldArray['street'];
			if ($incomingFieldArray['zipcode']) $address[] = $incomingFieldArray['zipcode'];
			if ($incomingFieldArray['city']) $address[] = $incomingFieldArray['city'];
			$incomingFieldArray['address'] = implode(', ', $address);
		}
	}

}

?>