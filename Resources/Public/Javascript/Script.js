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


StoreLocator = {

	defaultOptions: {
		'getStoresUri': '',
		'markerIcon': '',
		'useCustomInfoBox': false,
		'maxResultItemsOriginal': 10,
		'maxRadius': 50000
	},

	/**
	 * @param options
	 */
	init: function(options) {
		this._initializeOptions(options);
		this._initializeMap();
		this._initializeRadius();
		this._initializeMarkers();
		this._attachEvents();
		this._initializeInfoWindow();
		this._initializeToggleMap();

		if (this.options.displayMode == 'storeSearch') {
			this._initializeMapPosition();
			this.searchLocations();
		}
		if (this.options.displayMode == 'directionsService') {
			this._initializeDefaultLocation();
			this._initializeDirectionService();
		}
	},

	/**
	 * @private
	 */
	_initializeRadius: function() {
		if ($('#location_radius').is('select')) {
			this.radius = parseInt($('#location_radius option:selected').val());
		} else {
			this.radius = parseInt($('#location_radius').val());
		}
	},

	/****************************************************************************************
	 * Store Search
	 */

	/**
	 * @private
	 * @return {Boolean}
	 */
	_startSearch: function(e) {
		this.searchLocations();
		e.preventDefault();
		return false;
	},

	/**
	 * StoreLocator
	 */
	searchLocations: function() {
		var address = $('#location').val();
		var country = ($('#location_country').length ? $('#location_country').val() : 0);

		if (address != '') {
			if (this.userLocation) { // Performance improvement, avoid OVER_QUERY_LIMIT
				this._findLocations(this.userLocation.lat(), this.userLocation.lng(), country);
			} else {
				this._firstSearchWithoutUserLocationData(address, country);
			}
		} else {
			if (this.options.activate.mainstore) {
				this._findMainStoreLocations();
			}
		}
	},

	/**
	 * @param address
	 * @param country
	 * @private
	 */
	_firstSearchWithoutUserLocationData: function(address, country) {
		var geocoder = new google.maps.Geocoder();
		var self = this;

		geocoder.geocode({address: address, region: this.options.region}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				self.userLocation = results[0].geometry.location;
				self._findLocations(self.userLocation.lat(), self.userLocation.lng(), country);
			} else {
				self._noResultsFound(address);
			}
		});
	},

	/**
	 * @private
	 */
	_findMainStoreLocations: function() {
		this._clearAllLocations();

		var self = this;
		var getDefaultStoresUri = this.options.getDefaultStoresUri;

		$.ajax({
			type: 'GET',
			url: getDefaultStoresUri,
			dataType: 'json',
			success: function(data) {
				self._initializeLocations(data);
			}
		});
	},

	/**
	 * @private
	 */
	_findLocations: function(lat, lng, country) {

		var self = this;
		var getStoresUri = this.options.getStoresUri;

		getStoresUri = getStoresUri.replace('_LATITUDE_', lat);
		getStoresUri = getStoresUri.replace('_LONGITUD_', lng);
		getStoresUri = getStoresUri.replace('_RADIUS_', this.radius);
		getStoresUri = getStoresUri.replace('_COUNTRY_', country);

		$.ajax({
			type: 'GET',
			url: getStoresUri,
			dataType: 'json',
			success: function(data) {
				self._initializeLocations(data);
			}
		});
	},

	/**
	 * @param data
	 * @private
	 */
	_initializeLocations: function(data) {
		var self = this;
		var locations = data.locations;
		var sidebarItems = data.sidebarItems;
		var markerContent = data.markerContent;
		var bounds = new google.maps.LatLngBounds();
		var sidebar = $('#sidebar').eq(0);
		var moreButton = $('#more-button');
		var address = $('#location').val();
		var country = ($('#location_country').length ? $('#location_country').val() : 0);

		sidebar.innerHTML = '';
		if (locations.length > 0) {
			if (this.options.activate.automaticellyIncreaseRadius) {
				if (locations.length < this.options.automaticallyIncreaseRadiusMaxResultItems && this.radius < this.options.maxRadius) {
					// mind. Anzahl nicht erreicht, weiter suchen
					self._increaseRadius();
					return;
				}
			}

			if (locations.length > this.options.maxResultItems) {
				moreButton.show();
			} else {
				moreButton.hide();
			}

			for (var i = 0; i < locations.length; i++) {
				if (i > (this.options.maxResultItems-1)) break;
				var latlng = new google.maps.LatLng(parseFloat(locations[i]['latitude']), parseFloat(locations[i]['longitude']));
				this._setDistance(locations[i].uid, address, country, latlng);

				var sidebarEntry = self._createSidebarItem($(sidebarItems[i]), locations[i]);
				sidebar.append(sidebarEntry);

				self._createLocationMarker(markerContent[i], locations[i], latlng);
				bounds.extend(latlng);
			}

			self.map.fitBounds(bounds);
		} else {
			if (this.options.activate.automaticellyIncreaseRadius && this.radius < this.options.maxRadius) {
				self._increaseRadius();
			} else {
				self._noResultsFound(address);
			}
		}
	},

	/**
	 * @param storeUid
	 * @param address
	 * @param country
	 * @param latlng
	 * @private
	 */
	_setDistance: function(storeUid, address, country, latlng) {
		var distanceMatrixCallback = function(response, status) {
			if (status == google.maps.DistanceMatrixStatus.OK) {
				var distance = response.rows[0].elements[0].distance.text;
				var duration = response.rows[0].elements[0].duration.text;

				$('#distance_' + this.storeUid).html(distance);
				$('#duration_' + this.storeUid).html(duration);
			}
		}

		var service = new google.maps.DistanceMatrixService()
		service.storeUid = storeUid;
		service.getDistanceMatrix({
				origins: [address, country],
				destinations: [latlng],
				travelMode: google.maps.TravelMode.DRIVING,
				avoidHighways: false,
				avoidTolls: false
			}, distanceMatrixCallback.bind(service)
		);
	},

	/**
	 * Increase radius automatically
	 *
	 * @private
	 */
	_increaseRadius: function() {
		this.radius = (this.radius + parseInt(this.options.defaultRadius));
		this.searchLocations();
	},

	/**
	 * @param uid
	 * @param location
	 * @return {*}
	 * @private
	 */
	_createSidebarItem: function(sidebarItem, location) {
		var list = $('<li/>');
		list.append(sidebarItem);
		return list;
	},

	/**
	 * @private
	 */
	_clearAllLocations: function() {
		$('#sidebar').html('');
		this.infoWindow.close();
		for (var i = 0; i < this.markers.length; i++) {
			this.markers[i].setMap(null);
		}
		this.markers.length = 0;
	},


	/****************************************************************************************
	 * Directions Service
	 */

	/**
	 * @private
	 */
	_calculateDirectionRoute: function(e) {
		var self = this;
		var originStreet = $('#from-street').val();
		var originZip = $('#from-zip').val();
		var originCity = $('#from-city').val();

		if (originStreet || originZip || originCity) {
			this.infoWindow.close();
			var request = {
				origin: originStreet + ',' +  originZip + ', ' + originCity + ', ' + this.options.region,
				destination: this.options.defaultStore.locations[0].address,
				travelMode: google.maps.TravelMode.DRIVING,
				unitSystem: google.maps.UnitSystem.METRIC
			};
			this.directionsService.route(request, function(response, status) {
				if (status == google.maps.DirectionsStatus.OK) {
					self.directionsDisplay.setDirections(response);
				} else {
					self._noResultsFound(originZip + ' ' + originCity + ' ' + originStreet);
				}
			});
		}

		e.preventDefault();
		return false;
	},

	/**
	 *
	 * @private
	 */
	_initializeDefaultLocation: function() {
		var locations = this.options.defaultStore.locations;
		var markerContent = this.options.defaultStore.markerContent;
		var latlng = new google.maps.LatLng(parseFloat(locations[0]['latitude']), parseFloat(locations[0]['longitude']));

		this._createLocationMarker(markerContent[0], locations[0], latlng, true);
		this.map.setCenter(latlng);
		this.map.setZoom(15);
	},

	/**
	 * @private
	 */
	_initializeDirectionService: function() {
		this.directionsService = new google.maps.DirectionsService();
		this.directionsDisplay = new google.maps.DirectionsRenderer();
		this.directionsDisplay.setMap(this.map);
		this.directionsDisplay.setPanel($('#directions-panel').get(0));
	},

	/****************************************************************************************
	 * General
	 */

	/**
	 * @private
	 */
	_initializeOptions: function(options) {
		this.options = $.extend({}, this.defaultOptions, options);
		this.options.maxResultItemsOriginal = this.options.maxResultItems;
	},

	/**
	 * @returns {*}
	 * @private
	 */
	_initializeMap: function() {
		var mapOptions = {
			maxZoom: 15,
			panControl: true,
			zoomControl: true,
			scaleControl: false,
			disableDefaultUI: false,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			mapTypeControl: true,
			mapTypeControlOptions: {
				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
			}
		}

		this.map = new google.maps.Map($('#map').get(0), mapOptions);
	},

	/**
	 * @private
	 */
	_initializeMapPosition: function() {
		var self = this;
		var geo = new google.maps.Geocoder;
		var zoom = (this.options.region == 'europa' ? 3 : 6);

		geo.geocode({'address': this.options.region, 'region':this.options.region}, function(results, status){
			if (status == google.maps.GeocoderStatus.OK) {
				self.map.setCenter(results[0].geometry.location);
				self.map.setZoom(zoom);
			}
		});
	},

	/**
	 * @private
	 */
	_initializeMarkers: function() {
		this.markers = [];
	},

	/**
	 * @private
	 */
	_initializeInfoWindow: function() {
		this.infoWindow = new StoreLocatorInfoWindow(this.options.useCustomInfoBox);
	},

	/**
	 * @param markerContent
	 * @param location
	 * @param latlng
	 * @param infoWindowIsOpen
	 * @private
	 */
	_createLocationMarker: function(markerContent, location, latlng, infoWindowIsOpen = false) {
		var self = this;

		if (self.options.markerIcon != '') {
			var icon = self.options.markerIcon;
		}

		var marker = new google.maps.Marker({
			map: self.map,
			position: latlng,
			icon: icon,
			animation: google.maps.Animation.DROP,
		});

		var html = markerContent;

		google.maps.event.addListener(marker, 'click', function() {
			self.infoWindow.setContent(html);
			self.infoWindow.open(self.map, marker);
		});

		if (infoWindowIsOpen) {
			self.infoWindow.setContent(html);
			self.infoWindow.open(self.map, marker);
		}

		// register click event to open layer from external
		$('#storeLocatorMarker_' + location['uid']).bind('click', function() {
			self.infoWindow.setContent(html);
			self.infoWindow.open(self.map, marker);

			return false;
		});

		self.markers.push(marker);
	},

	/**
	 * @private
	 */
	_attachEvents: function() {
		var $body = $(document.body);
		$body.on('change', '.storeSearch #location_country', $.proxy(function(e) {
			this._clearAllLocations();
			this._initializeRadius();
			this._startSearch(e);
			this._clearNotification();
		}, this));
		$body.on('click', '.storeSearch #searchButton', $.proxy(function(e) {
			this.options.maxResultItems = this.options.maxResultItemsOriginal;
			this._clearAllLocations();
			this._initializeRadius();
			this._startSearch(e);
			this._clearNotification();
		}, this));
		$body.on('click', '.directionsService #searchButton', $.proxy(function(e) {
			this._calculateDirectionRoute(e);
			this._clearNotification();
		}, this));
		$body.on('click', '.storeSearch #more-button', $.proxy(function(e) {
			this.options.maxResultItems = (this.options.maxResultItems + this.options.maxResultItems);
			this._startSearch(e);
			this._initializeRadius();
			this._clearNotification();
		}, this));
	},

	/**
	 *
	 * @private
	 */
	_initializeToggleMap: function() {

		var $el = $('#retailer_search'),
			$mapHolder = $el.find('.google-map'),
			self = this,
			mapHeight = $mapHolder.height(),
			$btn = $el.find('.js-toggle-map'),
			$btnInner = $btn.find('span'),
			textClose = $btnInner.text(),
			textOpen = $btn.attr('data-text-open');

		$btn.bind('click', function() {

			if (!$mapHolder.is(':animated')) {
				if ($btn.hasClass('closed')) {
					$mapHolder.animate({height: mapHeight}, 500);
					$btnInner.html(textClose);
				}
				else {
					$mapHolder.animate({height: 0}, 500);
					$btnInner.html(textOpen);
				}

				$btn.toggleClass('closed');
			}

			return false;
		});
	},

	/**
	 * @private
	 */
	_noResultsFound: function(address) {
		var notificationText = this.options.labels.notificationNoDealerFound.replace('_KEYWORD_', address);
		$('#notification').html(notificationText);
	},

	/**
	 * @private
	 */
	_clearNotification: function() {
		$('#notification').html('');
	},

}
