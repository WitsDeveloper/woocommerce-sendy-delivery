# woocommerce-sendy-delivery
This plugin allows you to request delivery or quote to Sendy Delivery API. It only works with WooCommerce orders.

## About the plugin
* Plugin Name: Sendy Delivery Request for WooCommerce orders
* Version: 1.4
* Requires at least: 5.2
* Requires PHP: 7.2
* Author: Sammy Waweru
* Author URI: http://www.witstechnologies.co.ke
* License: GPLv3 or later
* License URI: http://www.gnu.org/licenses/gpl-3.0.html

## What the code does
* Extends WC_Shipping_Method class by adding Sendy Delivery as a shipping method
* Consumes Sendy API as guided on [Sendy Public API](https://sendypublicapi.docs.apiary.io/)
* Convert the shoppers address to geocode Latitude/Longitude positioning with Google Maps
* Sends the Latitude and Longitude coordinates to Sendy API for rate calculation
* Obtains distance and delivery rates as calculated by Sendy API

## Requires
* [jQuery Geocoding and Places Autocomplete Plugin](https://github.com/ubilabs/geocomplete)
* [Google Maps API geocoding](https://developers.google.com/maps/documentation/geocoding/overview)
* [Sendy Public API](https://sendypublicapi.docs.apiary.io/)
