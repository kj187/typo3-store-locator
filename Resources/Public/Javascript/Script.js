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
		'useCustomInfoBox': false
	},

	init: function(options) {
		this._initializeOptions(options);
		this._initializeMap();
		this._initializeMarkers();
		this._attachEvents();
		this._initializeInfoWindow();
		this._initializeToggleMap();

		// default view
		this.searchLocations();
	},

	/**
	 * @private
	 */
	_initializeOptions: function(options) {
		this.options = $.extend({}, this.defaultOptions, options);
	},

	/**
	 * @private
	 */
	_initializeMap: function() {
		var mapOptions = {
			center: new google.maps.LatLng(51, 6),
			zoom: 8,
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
	 * @private
	 */
	_attachEvents: function() {
		var $body = $(document.body);
		$body.on('click', '#searchButton', $.proxy(this, '_startSearch'));
	},

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
		this._clearAllLocations();
		var address = $('#location').val();
		var radius = $('#location_radius').val();

		var self = this;
		if (address != '') {
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode({address: address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					var center = results[0].geometry.location;
					self._findLocations(center.lat(), center.lng(), radius);
				} else {
					alert(address + ' not found');
				}
			});
		} else {
			self._findMainStoreLocations();
		}
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
	_findLocations: function(lat, lng, radius) {
		this._clearAllLocations();

		var self = this;
		var getStoresUri = this.options.getStoresUri;

		getStoresUri = getStoresUri.replace('_LATITUDE_', lat);
		getStoresUri = getStoresUri.replace('_LONGITUD_', lng);
		getStoresUri = getStoresUri.replace('_RADIUS_', radius);

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
		var sidebar = $('#sidebar').get(0);

		sidebar.innerHTML = '';
		if (locations.length > 0) {
			for (var i = 0; i < locations.length; i++) {

				//var distance = parseFloat(locations[i]['distance']);
				var latlng = new google.maps.LatLng(parseFloat(locations[i]['latitude']), parseFloat(locations[i]['longitude']));

				var sidebarEntry = self._createSidebarItem(sidebarItems[i], locations[i]);
				sidebar.appendChild(sidebarEntry);

				self._createLocationMarker(markerContent[i], locations[i], latlng);
				bounds.extend(latlng);
			}

			self.map.fitBounds(bounds);
		} else {
			sidebar.innerHTML = data.notification;
		}
	},

	/**
	 * @param uid
	 * @param location
	 * @return {*}
	 * @private
	 */
	_createSidebarItem: function(sidebarItem, location) {
		var list = document.createElement('li');
		list.innerHTML = sidebarItem;
		return list;
	},

	/**
	 * @param uniqueName
	 * @param latlng
	 * @param name
	 * @param address
	 * @private
	 */
	_createLocationMarker: function(markerContent, location, latlng) {
		var self = this;

		if (self.options.markerIcon != '') {
			var icon = self.options.markerIcon;
		}

		var marker = new google.maps.Marker({
			map: self.map,
			position: latlng,
			icon: icon,
			animation: google.maps.Animation.DROP
		});

		var html = markerContent;

		google.maps.event.addListener(marker, 'click', function() {
			self.infoWindow.setContent(html);
			self.infoWindow.open(self.map, marker);
		});

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
	_clearAllLocations: function() {
		$('#sidebar').html('');
		this.infoWindow.close();
		for (var i = 0; i < this.markers.length; i++) {
			this.markers[i].setMap(null);
		}
		this.markers.length = 0;
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
	}

}