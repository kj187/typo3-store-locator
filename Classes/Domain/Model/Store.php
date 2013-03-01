<?php
namespace Aijko\StoreLocator\Domain\Model;

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
class Store extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * name
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $name;

	/**
	 * Address
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $address;

	/**
	 * City
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $city;

	/**
	 * ismainstore
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $ismainstore;

	/**
	 * Street
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $street;

	/**
	 * state
	 *
	 * @var \string
	 */
	protected $state;

	/**
	 * Zipcode
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $zipcode;

	/**
	 * Country
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $country;

	/**
	 * Latitude
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $latitude;

	/**
	 * Longitude
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $longitude;

	/**
	 * URL
	 *
	 * @var \string
	 */
	protected $url;

	/**
	 * Description
	 *
	 * @var \string
	 */
	protected $description;

	/**
	 * E-Mail
	 *
	 * @var \string
	 */
	protected $email;

	/**
	 * Telephone
	 *
	 * @var \string
	 */
	protected $phone;

	/**
	 * Fax
	 *
	 * @var \string
	 */
	protected $fax;

	/**
	 * Logo
	 *
	 * @var \string
	 */
	protected $logo;

	/**
	 * Returns the name
	 *
	 * @return \string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name
	 *
	 * @param \string $name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Returns the address
	 *
	 * @return \string $address
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * Sets the address
	 *
	 * @param \string $address
	 * @return void
	 */
	public function setAddress($address) {
		$this->address = $address;
	}

	/**
	 * Returns the city
	 *
	 * @return \string $city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Sets the city
	 *
	 * @param \string $city
	 * @return void
	 */
	public function setCity($city) {
		$this->city = $city;
	}

	/**
	 * Returns true if it is a mainstore
	 *
	 * @return \boolean $ismainstore
	 */
	public function getIsmainstore() {
		return $this->ismainstore;
	}

	/**
	 * Sets the ismainstore
	 *
	 * @param \boolean $ismainstore
	 * @return void
	 */
	public function setIsmainstore($ismainstore) {
		$this->ismainstore = $ismainstore;
	}

	/**
	 * Returns the street
	 *
	 * @return \string $street
	 */
	public function getStreet() {
		return $this->street;
	}

	/**
	 * Sets the street
	 *
	 * @param \string $street
	 * @return void
	 */
	public function setStreet($street) {
		$this->street = $street;
	}

	/**
	 * Returns the state
	 *
	 * @return \string $state
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * Sets the state
	 *
	 * @param \string $state
	 * @return void
	 */
	public function setState($state) {
		$this->state = $state;
	}

	/**
	 * Returns the zipcode
	 *
	 * @return \string $zipcode
	 */
	public function getZipcode() {
		return $this->zipcode;
	}

	/**
	 * Sets the zipcode
	 *
	 * @param \string $zipcode
	 * @return void
	 */
	public function setZipcode($zipcode) {
		$this->zipcode = $zipcode;
	}

	/**
	 * Returns the country
	 *
	 * @return \string $country
	 */
	public function getCountry() {
		return $this->country;
	}

	/**
	 * Sets the country
	 *
	 * @param \string $country
	 * @return void
	 */
	public function setCountry($country) {
		$this->country = $country;
	}

	/**
	 * Returns the latitude
	 *
	 * @return \string $latitude
	 */
	public function getLatitude() {
		return $this->latitude;
	}

	/**
	 * Sets the latitude
	 *
	 * @param \string $latitude
	 * @return void
	 */
	public function setLatitude($latitude) {
		$this->latitude = $latitude;
	}

	/**
	 * Returns the longitude
	 *
	 * @return \string $longitude
	 */
	public function getLongitude() {
		return $this->longitude;
	}

	/**
	 * Sets the longitude
	 *
	 * @param \string $longitude
	 * @return void
	 */
	public function setLongitude($longitude) {
		$this->longitude = $longitude;
	}

	/**
	 * Returns the url
	 *
	 * @return \string $url
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * Sets the url
	 *
	 * @param \string $url
	 * @return void
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * Returns the description
	 *
	 * @return \string $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets the description
	 *
	 * @param \string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * Returns the email
	 *
	 * @return \string $email
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Sets the email
	 *
	 * @param \string $email
	 * @return void
	 */
	public function setEmail($email) {
		$this->email = $email;
	}

	/**
	 * Returns the phone
	 *
	 * @return \string $phone
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * Sets the phone
	 *
	 * @param \string $phone
	 * @return void
	 */
	public function setPhone($phone) {
		$this->phone = $phone;
	}

	/**
	 * Returns the fax
	 *
	 * @return \string $fax
	 */
	public function getFax() {
		return $this->fax;
	}

	/**
	 * Sets the fax
	 *
	 * @param \string $fax
	 * @return void
	 */
	public function setFax($fax) {
		$this->fax = $fax;
	}

	/**
	 * Returns the logo
	 *
	 * @return \string $logo
	 */
	public function getLogo() {
		return $this->logo;
	}

	/**
	 * Sets the logo
	 *
	 * @param \string $logo
	 * @return void
	 */
	public function setLogo($logo) {
		$this->logo = $logo;
	}

}
?>