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

	initialized: false,
	clientData: {},
	defaultOptions: {
		'getStoresUri': '',
		'markerIcon': '',
		'useCustomInfoBox': false,
		'maxResultItemsOriginal': 10,
		'maxRadius': 2000
	},

	/**
	 * Run store locator
	 *
	 * @param options
	 */
	run: function(options) {
		if (this.initialized) {
			return;
		}
		this.initialized = true;
		this.root = $('.storeSearch');

		this._initializeOptions(options);
		this._initializeMap();
		this._initializeRadius();
		this._initializeMarkers();
		this._attachEvents();
		this._initializeInfoWindow();
		this._initializeToggleMap();

		if (this.options.displayMode == 'storeSearch') {
			this._initializeMapPosition();
			this._searchLocations(true);
		}
		if (this.options.displayMode == 'directionsService') {
			this._initializeDefaultLocation();
			this._initializeDirectionService();
		}


	},

	/**
	 * Initialize radius
	 *
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
	 * Start search
	 *
	 * @private
	 * @return false
	 */
	_startSearch: function(e) {
		this._searchLocations();
		e.preventDefault();
		return false;
	},

	/**
	 * @private
	 */
	_searchLocations: function(initializingSearch) {
		var address = $('#location').val();
		var country = ($('#location_country').length ? $('#location_country').val() : 0);
    var self = this;

		if (address != '' || !initializingSearch) {
			this._showIndicator();
			if (this.userLocation && this.lastQueryAddress == address) { // Performance improvement, avoid OVER_QUERY_LIMIT
				this._loadLocations(this.userLocation.lat(), this.userLocation.lng(), country);
			} else {
				this._firstSearchWithoutUserLocationData(address, country);
			}
		} else {
			if (this.options.activate.mainstore) {
				this._findMainStoreLocations();
			} else {
				if (this.options.activate.clientPosition) {
					this._loadClientPositionData(function(data, status, errorMessage) {
            self._firstSearchWithoutUserLocationData(address, country);
          });
				} else {
          this._firstSearchWithoutUserLocationData(address, country);
				}
			}
		}
	},

	/**
	 * Load the client data
	 *
	 * Required EXT:aijko_geoip
	 * https://bitbucket.org/aijko/aijko_geoip
	 *
	 * @private
	 */
	_loadClientPositionData: function(fallback) {
		if (!geoIpClientMetaDataUrlForLocator) {
			this._hideIndicator();
			return;
		}

		if (Object.keys(this.clientData).length !== 0) {
			this._loadClientPosition(this.clientData);
			return;
		}

		var self = this;
		$.ajax({
			type: 'GET',
			url: geoIpClientMetaDataUrlForLocator,
			dataType: 'json',
			success: function(data) {
				self._loadClientPosition(data);
				self.clientData = data;
			},
			error: function(data, status, errorMessage) {
        if (typeof fallback == 'function') {
          fallback(data, status, errorMessage);
        }
        else {
          self._hideIndicator();
          console.log(status, errorMessage);
        }
			}
		});
	},

	/**
	 * Load the client position
	 *
	 * @private
	 */
	_loadClientPosition: function(data) {
		if (data.errorMessage) {
			this._hideIndicator();
			return;
		}

		var country = data.country.staticInfoTableUid;
		if (!country) {
			this._hideIndicator();
			return;
		}

		if ($("#location_country option[value='" + country + "']").val() === undefined) {
			this._hideIndicator();
			return;
		}

		$('#location_country').val(country).change();
		if (data.city.name) {
			$('#location').val(data.city.name);
		}

		this._loadLocations(data.latitude, data.longitude, country);
	},

	/**
	 * Find user location and store it a variable to avoid an geocode OVER_QUERY_LIMIT
	 *
	 * @param address
	 * @param country
	 * @private
	 */
	_firstSearchWithoutUserLocationData: function(address, country) {
		var geocoder = new google.maps.Geocoder();
		var self = this;
		this.lastQueryAddress = address;

		geocoder.geocode({address: address, region: this.options.region}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				self.userLocation = results[0].geometry.location;
				self._loadLocations(self.userLocation.lat(), self.userLocation.lng(), country);
			} else {
				self._noResultsFound(address);
				$('[data-showOnResponse]').show();
        self.root.trigger('domupdate');
			}
		});
	},

	/**
	 * Find main store locations (if activated, should be displayed on the first page load)
	 *
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
				self._initializeAndOutputLocations(data);
			},
			error: function(data, status, errorMessage) {
				console.log(status, errorMessage);
			}
		});
	},

	/**
	 * Load locations
	 *
	 * @private
	 */
	_loadLocations: function(lat, lng, country, keepBounds) {

		var self = this;
		var getStoresUri = this.options.getStoresUri;
		var retailer = {
			online: ($('#retailer-online').is(':checked') ? 1 : 0),
			local: ($('#retailer-local').is(':checked') ? 1 : 0)
		};

		google.maps.event.removeListener(self.listenerDragend);
		google.maps.event.removeListener(self.listenerZoomChanged);

		getStoresUri = getStoresUri.replace('_LATITUDE_', lat);
		getStoresUri = getStoresUri.replace('_LONGITUD_', lng);
		getStoresUri = getStoresUri.replace('_RADIUS_', this.radius);
		getStoresUri = getStoresUri.replace('_COUNTRY_', country);
		getStoresUri = getStoresUri.replace('_ONLINERETAILER_', retailer.online);
		getStoresUri = getStoresUri.replace('_LOCALRETAILER_', retailer.local);

		self._showIndicator();

		$.ajax({
			type: 'GET',
			url: getStoresUri,
			dataType: 'json',
			success: function(data) {
				self._initializeAndOutputLocations(data, keepBounds);
			},
			error: function(data, status, errorMessage) {
				console.log(status, errorMessage);
			}
		});
	},

	/**
	 * Update location pins depending on current viewport
	 */
	_updateByMapBounds: function() {
		var self = this;
		var bounds = self.map.getBounds();

		if (bounds) {
			var country = 0;
			var center = self.map.getCenter();
			var swPoint = bounds.getSouthWest();
			var nePoint = bounds.getNorthEast();
			this.radius = Math.round(google.maps.geometry.spherical.computeDistanceBetween(swPoint, nePoint) / 1000 / 2);
			self._loadLocations(center.lat(), center.lng(), country, true);
		};
		
	},

	/**
	 * Initialize and output locations
	 *
	 * @param data
	 * @private
	 */
	_initializeAndOutputLocations: function(data, keepBounds) {
		var self = this;
		var locations = data.locations;
		var sidebarItems = data.sidebarItems;
		var markerContent = data.markerContent;
		var bounds = new google.maps.LatLngBounds();
		var sidebar = $('#sidebar').eq(0);
		var address = $('#location').val();

		keepBounds = !!keepBounds;

		sidebar.innerHTML = '';
		this._clearAllLocations();
		if (locations.length > 0) {
			if (this.options.activate.automaticellyIncreaseRadius) {
				if (locations.length < this.options.automaticallyIncreaseRadiusMaxResultItems && this.radius < this.options.maxRadius) {
					self._increaseRadius();
					return;
				}
			}

			if (locations.length > this.options.maxResultItems) {
				self._showMoreButton();
			} else {
				self._hideMoreButton();
			}

			for (var i = 0; i < locations.length; i++) {
				if (i > (this.options.maxResultItems-1)) break;
				var latlng = new google.maps.LatLng(parseFloat(locations[i]['latitude']), parseFloat(locations[i]['longitude']));
				sidebar.append($(sidebarItems[i]));
				self._createLocationMarker(markerContent[i], locations[i], latlng, false);
				bounds.extend(latlng);
			}

			!keepBounds && self.map.fitBounds(bounds);

			self.listenerDragend = google.maps.event.addListener(this.map, 'dragend', function() {
				self._updateByMapBounds();
			});

			self.listenerZoomChanged = google.maps.event.addListener(this.map, 'zoom_changed', function() {
				self._updateByMapBounds();
			});

			this._hideIndicator();
			$('[data-showOnSuccess]').show();
		} else {
			if (!keepBounds && this.options.activate.automaticellyIncreaseRadius && this.radius < this.options.maxRadius) {
				self._increaseRadius();
			} else {
				self._noResultsFound(address);
			}
		}

		$('[data-showOnResponse]').show();
		this.root.trigger('domupdate');
	},

	/**
	 * Increase radius automatically
	 *
	 * @private
	 */
	_increaseRadius: function() {
		if ($('#location_radius').length) {
			this.radius = (this.radius + this.radius);
		} else {
			this.radius = (this.radius + parseInt(this.options.defaultRadius));
		}

		this._searchLocations();
	},

	/**
	 * Clear locations
	 *
	 * @private
	 */
	_clearAllLocations: function() {
		$('[data-showOnSuccess]').hide();
		$('[data-queryresult]').remove();
		this.root.trigger('domupdate');

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
	 * Calculate direction route to a specific store
	 *
	 * @private
	 */
	_calculateDirectionRoute: function(e) {
		var self = this;
		var originStreet = $('#from-street').val();
		var originZip = $('#from-zip').val();
		var originCity = $('#from-city').val();
		var headline = $('#directions-headline');

		if (originStreet || originZip || originCity) {
			$('#directions-panel').html('');
			headline.hide();
			this._showIndicator();
			this.infoWindow.close();
			var request = {
				origin: originStreet + ',' +  originZip + ', ' + originCity + ', ' + this.options.region,
				destination: this.options.defaultStore.locations[0].address,
				travelMode: google.maps.TravelMode.DRIVING,
				unitSystem: google.maps.UnitSystem.METRIC
			};
			this.directionsService.route(request, function(response, status) {
				if (status == google.maps.DirectionsStatus.OK) {
					headline.show();
					self.directionsDisplay.setDirections(response);
				} else {
					self._noResultsFound(originZip + ' ' + originCity + ' ' + originStreet);
				}

				self._hideIndicator();
			});
		}


		e.preventDefault();
		return false;
	},

	/**
	 * Initialize default locations
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
		this.map.panBy(0, -50);

		this._hideIndicator();
	},

	/**
	 * Initialize direction service
	 *
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
	 * Initialize options
	 *
	 * @private
	 */
	_initializeOptions: function(options) {
		this.options = $.extend({}, this.defaultOptions, options);
		this.options.maxResultItemsOriginal = this.options.maxResultItems;
	},

	/**
	 * Initialize google map
	 *
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
	 * Initialize map position
	 *
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
	 * Initialize markers
	 *
	 * @private
	 */
	_initializeMarkers: function() {
		this.markers = [];
	},

	/**
	 * Initialize info window (overlay)
	 *
	 * @private
	 */
	_initializeInfoWindow: function() {
		this.infoWindow = new StoreLocatorInfoWindow(this.options.useCustomInfoBox);
	},

	/**
	 * Create location marker (overlay)
	 *
	 * @param markerContent
	 * @param location
	 * @param latlng
	 * @param infoWindowIsOpen
	 * @private
	 */
	_createLocationMarker: function(markerContent, location, latlng, infoWindowIsOpen) {
		var self = this;

		if (self.options.markerIcon != '') {
			var icon = self.options.markerIcon;
		}

		var marker = new google.maps.Marker({
			map: self.map,
			position: latlng,
			icon: icon
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
	 * Attach events
	 *
	 * @private
	 */
	_attachEvents: function() {
		var self = this;
		var $body = $(document.body);

		$body.on('click', '.storeSearch #searchButton', $.proxy(function(e) {
			this.options.maxResultItems = this.options.maxResultItemsOriginal;
			this._hideMoreButton();
			$('[data-showOnResponse]').hide();
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
			this._hideMoreButton();
			$('[data-showOnResponse]').hide();
			this._startSearch(e);
			this._clearAllLocations();
			this._initializeRadius();
			this._clearNotification();
		}, this));

		self.listenerDragend = google.maps.event.addListener(this.map, 'dragend', function() {
			self._updateByMapBounds();
		});

		self.listenerZoomChanged = google.maps.event.addListener(this.map, 'zoom_changed', function() {
			self._updateByMapBounds();
		});

	},

	/**
	 * Initialize toggle map (show/hide map)
	 *
	 * @private
	 */
	_initializeToggleMap: function() {
		var self = this;
		var $el = $('#retailer_search');
		var $mapHolder = $el.find('.map-container');
		var mapHeight = $mapHolder.height();
		var $btn = $el.find('.js-toggle-map');
		var $btnInner = $btn.find('span');
		var textClose = $btnInner.text();
		var textOpen = $btn.attr('data-text-open');

		$btn.bind('click', function() {
			if (!$mapHolder.is(':animated')) {
				if ($btn.hasClass('closed')) {
					$mapHolder.animate({height: mapHeight}, 500);
					$btnInner.html(textClose);
					self._showIndicator();
				}
				else {
					$mapHolder.animate({height: 0}, 500, function() {
						self._hideIndicator();
					});
					$btnInner.html(textOpen);
				}

				$btn.toggleClass('closed');
			}

			return false;
		});
	},

	/**
	 * Displays a notification if no results found
	 *
	 * @private
	 */
	_noResultsFound: function(address) {
		var notificationText = this.options.labels.notificationNoDealerFound.replace('_KEYWORD_', address);
		$('#notification').html(notificationText);
		this._hideIndicator();
	},

	/**
	 * Clear the notification box
	 *
	 * @private
	 */
	_clearNotification: function() {
		$('#notification').html('');
	},

	/**
	 * Hide the indicator (spinner) icon
	 *
	 * @private
	 */
	_hideIndicator: function() {
		$('.progress-indicator').hide();
	},

	/**
	 * Show the indicator (spinner) icon
	 *
	 * @private
	 */
	_showIndicator: function() {
		$('.progress-indicator').show();
	},

	/**
	 * Hide more button to show more result items
	 *
	 * @private
	 */
	_hideMoreButton: function() {
		$('[data-showMoreButton]').hide();
	},

	/**
	 * Show more button to show more result items
	 *
	 * @private
	 */
	_showMoreButton: function() {
		$('[data-showMoreButton]').show();
	}


}
