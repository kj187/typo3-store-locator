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

function StoreLocatorInfoWindow(useCustomInfoBox) {
	if (useCustomInfoBox) {
		this.infoWindow = new InfoBox({
			closeBoxURL: '',
			alignBottom: true,
			pixelOffset: new google.maps.Size(-70, -57),
			boxClass: 'map-tooltip'
		});
	} else {
		this.infoWindow = new google.maps.InfoWindow();
	}
}

StoreLocatorInfoWindow.prototype.setContent = function(content) {
	this.infoWindow.setContent(content);
};

StoreLocatorInfoWindow.prototype.open = function(map, marker) {
	this.infoWindow.open(map, marker);
};

StoreLocatorInfoWindow.prototype.close = function() {
	this.infoWindow.close();
};