<?php
/**
 * Plugin Name: Sendy Delivery Request for WooCommerce orders
 * Plugin URI: http://www.witstechnologies.co.ke/
 * Description: This plugin allows you to request delivery or quote to Sendy Delivery API. It only works with WooCommerce orders.
 * Version: 1.4
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Author: Sammy Waweru
 * Author URI: http://www.witstechnologies.co.ke
 * Text Domain: woocommerce-sendy-delivery
**/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


define('WCSD_PATH', plugin_dir_path(__FILE__));
define('WCSD_LINK', plugin_dir_url(__FILE__));
define('WCSD_PLUGIN_NAME', plugin_basename(__FILE__));

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function wc_sendy_shipping_method_init() {
		if ( ! class_exists( 'WC_Sendy_Shipping_Method' ) ) {
			class WC_Sendy_Shipping_Method extends WC_Shipping_Method {
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct() {
					$this->id                 = 'wc_sendy_shipping_method'; // Id for your shipping method. Should be uunique.
					$this->method_title       = __( 'Sendy Shipping Method' );  // Title shown in admin
					$this->method_description = __( 'This plugin allows you to request delivery or quote to Sendy Delivery API. It only works with WooCommerce orders.' ); // Description shown in admin

					$this->title              = "Sendy Shipping Method"; // This can be added as a setting.

					$this->init();
				}			

				/**
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				public function init() {
					// Load the settings API
					$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); // This is part of the settings API. Loads settings you previously init.
					
					$this->enabled					= $this->get_option( 'enabled' );
					$this->title						= $this->get_option( 'title' );
					$this->availability			= $this->get_option( 'availability' );
					$this->countries				= $this->get_option( 'countries' );
					$this->api_uri					= $this->get_option( 'api_uri' );
					$this->api_key					= $this->get_option( 'api_key' );
					$this->api_username			= $this->get_option( 'api_username' );
					$this->vendor_type			= $this->get_option( 'vendor_type' );
					$this->request_type			= $this->get_option( 'request_type' );
					$this->order_type				= $this->get_option( 'order_type' );
					$this->from_name				= $this->get_option( 'from_name' );
					$this->from_lat					= $this->get_option( 'from_lat' );
					$this->from_long				= $this->get_option( 'from_long' );
					$this->from_description	= $this->get_option( 'from_description' );
					$this->google_api_key		= $this->get_option( 'google_api_key' );

					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				}
				
				/* The form for the settings */
				public function init_form_fields() {
					$fields = array(
						'enabled' => array(
							'title' => __('Enable', 'woocommerce-sendy-delivery'),
							'type' => 'checkbox',
							'label' => __('Enable this shipping method', 'woocommerce-sendy-delivery'),
							'default' => 'no',
							'desc_tip' => true
						),
						'title' => array(
							'title' => __('Method Title', 'woocommerce-sendy-delivery'),
							'type' => 'text',
							'description'	=> __('This controls the title which the user sees during checkout.', 'woocommerce-sendy-delivery'),
							'default' => __('Sendy Shipping Method', 'woocommerce-sendy-delivery'),
							'desc_tip' => true
						),
						'availability' => array(
							'title' => __( 'Method availability', 'woocommerce' ),
							'type' => 'select',
							'default' => 'all',
							'class' => 'availability wc-enhanced-select',
							'options' => array(
								'all' => __( 'All allowed countries', 'woocommerce' ),
								'specific' => __( 'Specific countries', 'woocommerce' ),
							),
						),
						'countries' => array(
							'title' => __( 'Specific countries', 'woocommerce' ),
							'type' => 'multiselect',
							'class' => 'wc-enhanced-select',
							'css' => 'width: 400px;',
							'default' => '',
							'options' => WC()->countries->get_shipping_countries(),
							'custom_attributes' => array(
								'data-placeholder' => __( 'Select some countries', 'woocommerce' ),
							),
						),
						'api_uri' => array(
							'title' => __('API URI', 'woocommerce-sendy-delivery'),
							'type' => 'text',
							'description'	=> __('Get Sendy API URI. You can test your code end to end by using the sandbox account. Defaults to https://api.sendyit.com/v1/. For test, use https://apitest.sendyit.com/v1/', 'woocommerce-sendy-delivery'),
							'default' => 'https://api.sendyit.com/v1/',
							'desc_tip' => true
						),
						'api_key' => array(
							'title' => __('API Key', 'woocommerce-sendy-delivery'),
							'type' => 'text',
							'description'	=> __('The string API Key provided by Sendy.', 'woocommerce-sendy-delivery'),
							'default' => '',
							'desc_tip' => true
						),
						'api_username' => array(
							'title' => __('API Username', 'woocommerce-sendy-delivery'),
							'type' => 'text',
							'description'	=> __('The string API Username provided by Sendy.', 'woocommerce-sendy-delivery'),
							'default' => '',
							'desc_tip' => true
						),
						'vendor_type' => array(
							'title' => __('Vendor Type', 'woocommerce-sendy-delivery'),
							'type' => 'select',
							'description'	=> __("Vendor type can be 1 => Bike, 2 => Pick up, or 3 => Van", "woocommerce-sendy-delivery"),
							'default' => 1,
							'options' => array(
								1 => __('Bike', 'woocommerce-sendy-delivery'),
								2 => __('Pick up', 'woocommerce-sendy-delivery'),
								3 => __('Van', 'woocommerce-sendy-delivery'),
							),
							'desc_tip' => true
						),
						'request_type' => array(
							'title' => __('Request Type', 'woocommerce-sendy-delivery'),
							'type' => 'select',
							'description' => __("By default this is 'quote' this gives you a price estimate. You can set this to 'delivery' to do a complete delivery request.", "woocommerce-sendy-delivery"),
							'default' => 'quote',
							'options' => array(
								'quote' => __('Quote', 'woocommerce-sendy-delivery'),
								'delivery' => __('Delivery', 'woocommerce-sendy-delivery'),
							),
							'desc_tip' => true
						),
						'order_type' => array(
							'title' => __('Order Type', 'woocommerce-sendy-delivery'),
							'type' => 'select',
							'description'	=> __("By default this is 'ondemand_order' this will have your order done imediately. if set to batch_later_order it will be batched and dispatched later.", "woocommerce-sendy-delivery"),
							'default' => 'ondemand_order',
							'options' => array(
								'ondemand_order' => __('Dispatch order immediately', 'woocommerce-sendy-delivery'),
								'batch_later_order'	=> __('Dispatch order later', 'woocommerce-sendy-delivery'),
							),
							'desc_tip' => true
						),						
						'from_name' => array(
							'title' => __('From Name', 'woocommerce-sendy-delivery'),
							'type' => 'text',
							'description'	=> __('Here you can enter your name or name of your store.', 'woocommerce-sendy-delivery'),
							'default' => get_bloginfo(),
							'desc_tip' => true
						),
						'from_lat' => array(
							'title' => __('From Latitude', 'woocommerce-sendy-delivery'),
							'type' => 'text',
							'description'	=> __('Enter latitude of the pick up location.', 'woocommerce-sendy-delivery'),
							'default' => '-1.2920659',
							'desc_tip' => true
						),
						'from_long' => array(
							'title' => __('From Longitude', 'woocommerce-sendy-delivery'),
							'type' => 'text',
							'description'	=> __('Enter longitude of the pick up location.', 'woocommerce-sendy-delivery'),
							'default' => '36.82194619999996',
							'desc_tip' => true
						),
						'from_description' => array(
							'title' => __('From Description', 'woocommerce-sendy-delivery'),
							'type' => 'text',
							'description'	=> __('Enter description of the pick up location.', 'woocommerce-sendy-delivery'),
							'default' => 'Haile Selassie Ave, Nairobi, Kenya',
							'desc_tip' => true
						),
						'google_api_key' => array(
							'title' => __('Google Maps API Key', 'woocommerce-sendy-delivery'),
							'type' => 'text',
							'description'	=> __('The string API Key provided by Google Maps API.', 'woocommerce-sendy-delivery'),
							'default' => '',
							'desc_tip' => true
						)
						
					);
					$this->form_fields=$fields;					
				}
				
				public function sendy_wc_curl_exec( $url, $command, $data ) {
					$ch = curl_init();

					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
					curl_setopt($ch, CURLOPT_HEADER, FALSE);
					
					curl_setopt($ch, CURLOPT_POST, TRUE);
					# Setup request to send json via POST.
					$payload = json_encode(array('command' => $command, 'data' => $data));
					
					curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
					
					# Send request.
					$result = curl_exec($ch);
					curl_close($ch);
					
					return $result;
				}
				
				public function google_maps_api_curl_exec( $url ) {
					$ch = curl_init();
				
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
					curl_setopt($ch, CURLOPT_HEADER, FALSE);
				
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
					
					# Send request.
					$result = curl_exec($ch);
					curl_close($ch);
					
					return $result;
				}

				/**
				 * calculate_shipping function.
				 *
				 * @access public
				 * @param array $package (default: array())
				 * @return void
				 */
				public function calculate_shipping( $package = array() ) {
					global $woocommerce,$order_id;
					$result = $data = $rate = array();
					$prepURL = "";
					
					// Needed customer details
					$address = WC()->customer->get_shipping_address();
					$address_2 = WC()->customer->get_shipping_address_2();
					$city = WC()->customer->get_shipping_city();
					$state = WC()->customer->get_shipping_state();
					$country = WC()->customer->get_shipping_country();
					
					// Needed cart details
					$total_amount = WC()->cart->get_cart_total();
					
					if (!empty($order_id)) :
						$order = new WC_Order( $order_id );
												
						$recepient_name = $order->billing_first_name ." ". $order->billing_last_name;
						$recepient_email = $order->billing_email;
						$recepient_phone = $order->billing_phone;
						
					else :
						$recepient_name = get_bloginfo('name');
						$recepient_email = get_bloginfo('admin_email');
						$recepient_phone = "0724567990";
					endif;
					
					// Format address for Goole Maps API use
					// Convert an address to geocode Latitude/Longitude positioning with Google Maps API.
					// Example http://maps.google.com/maps/api/geocode/json?address=Kindaruma+Rd,+Nairobi,+Kenya&components=country:KE&sensor=false
					//$fullAddress = $address.",".$city.",".$state;
					$fullAddress = $address;
					$prepAddr = str_replace(" ", "+", $fullAddress);					
					$prepURL = "https://maps.google.com/maps/api/geocode/json?address=$prepAddr&components=country:$country&key=".$this->google_api_key;
					$geocode = $this->google_maps_api_curl_exec( $prepURL );					
					$georesults = json_decode($geocode, true);
					
					//Debug purpose ONLY!!!
					//_e( "<h1>GOOGLE MAP API RESPONSE</h1>" );
					//_e( $prepURL."<br>" );
					//print_r( $georesults );					
					
					$geo_lat = $georesults["results"][0]["geometry"]["location"]["lat"];
					$geo_long = $georesults["results"][0]["geometry"]["location"]["lng"];
					$geo_addr = $georesults["results"][0]["formatted_address"];
					
					if( !empty($this->api_uri) && !empty($geo_lat) && !empty($geo_long) ){
						$command = 'request';
						$api_key = $this->api_key;
						$api_username = $this->api_username;
						$vendor_type = intval($this->vendor_type);
						$from_name = $this->from_name;
						$from_lat = floatval($this->from_lat);
						$from_long = floatval($this->from_long);
						$from_description = $this->from_description;
						$to_name = 'Customer';
						$to_lat = floatval($geo_lat);
						$to_long = floatval($geo_long);
						$to_description = "To be delivered to $geo_addr.";
						$recepient_name = $recepient_name;
						$recepient_phone = $recepient_phone;
						$recepient_email = $recepient_email;
						$pick_up_date = date('Y-m-d', strtotime("+1 week"));
						$status = false;
						$pay_method = 0;
						$amount = 0;
						$return = false;
						$note = $from_description." ".$to_description;
						$note_status = true;
						$request_type = $this->request_type;
						$order_type = $this->vendor_type;
						$ecommerce_order = true;
						$express = true;
						$skew = 1;
						$weight = 0;
						$height = 0;
						$width = 0;
						$length = 0;
						$item_name = "";
												
						$data = array(
							"api_key" => "".$api_key."",
							"api_username" => "".$api_username."",
							"vendor_type" => $vendor_type,
							"from" => array(
								'from_name' => "".$from_name."",
								'from_lat' => $from_lat,
								'from_long' => $from_long,
								'from_description' => "".$from_description."",
							),
							"to" => array(
								'to_name' => "".$to_name."",
								'to_lat' => $to_lat,
								'to_long' => $to_long,
								'to_description' => "".$to_description."",
							),
							"recepient" => array(
								'recepient_name' => "".$recepient_name."",
								'recepient_phone' => "".$recepient_phone."",
								'recepient_email' => "".$recepient_email."",
							),
							"delivery_details" => array(
								"pick_up_date" => "".$pick_up_date."",
								"collect_payment" => array(
									"status" => $status,
									"pay_method" => $pay_method,
									"amount" => $amount,
								),
								"return" => $return,
								"note" => "".$note."",
								"note_status" => $note_status,
								"request_type" => "".$request_type."",
								"order_type" => "".$order_type."",
								"ecommerce_order" => "".$ecommerce_order."",
								"express" => "".$express."",
								"skew" => $skew,
								"package_size" => array(
									"weight" => $weight,
									"height" => $height,
									"width" => $width,
									"length" => $length,
									"item_name" => "".$item_name."",
								)
							),
						);
						
						# Execute the curl command
						$result = $this->sendy_wc_curl_exec( $this->api_uri, $command, $data );						
						
						//Debug purpose ONLY!!!
						//_e( "<h1>DATA SENT TO SENDY API</h1>" );
						//print_r($data);
						
						# Print response
						$result = json_decode($result, true);
						
						//Debug purpose ONLY!!!
						//_e( "<br/><h1>DATA RECEIVED FROM SENDY API</h1>" );
						//print_r($result);
					}
					
					if(!empty($result['data']['amount'])){
						
						$cost = 100+$result['data']['amount'];
						
						//We have issues where the cost have been exorbitant
						//Cost should not be above KES. 750 in this case
						if( $cost > 750 ){
							$cost = 750;
						}
						
						$rate = array(
							'id' 				=> $this->id,
							'label' 		=> $this->title,
							'package' 	=> $package,						
							'cost'			=> $cost,
							'calc_tax'	=> 'per_order'
						);
												
					}else{
						$is_available = false;
						$shipping_message = '';
						
						$shipping_message = $result['data'];
						
						apply_filters( 'woocommerce_no_shipping_available_html', $shipping_message );
						apply_filters( 'woocommerce_cart_no_shipping_available_html', $shipping_message );
					}

					// Register the rate
					$this->add_rate( $rate );
				}
				
				/**
				 * See if the method is available.
				 *
				 * @param array $package Package information.
				 * @return bool
				 */
				public function is_available( $package ) {
					$is_available = 'yes' === $this->enabled;
			
					if ( $is_available ) {
						if ( 'specific' === $this->availability ) {
							$ship_to_countries = $this->countries;
						} else {
							$ship_to_countries = array_keys( WC()->countries->get_shipping_countries() );
						}
						if ( is_array( $ship_to_countries ) && ! in_array( $package['destination']['country'], $ship_to_countries, true ) ) {
							$is_available = false;
						}
					}
			
					return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package, $this );
				}
				
			}
		}
	}
	add_action( 'woocommerce_shipping_init', 'wc_sendy_shipping_method_init' );

	function wc_sendy_shipping_method( $methods ) {
		$methods['wc_sendy_shipping_method'] = 'WC_Sendy_Shipping_Method';
		return $methods;
	}
	add_filter( 'woocommerce_shipping_methods', 'wc_sendy_shipping_method' );
	
	//Load the theme JS and CSS files
	function sendy_wc_theme_styles() {
		//first check that woo exists to prevent fatal errors
		if ( function_exists( 'is_woocommerce' ) ) {
			//dequeue scripts and styles
			if ( is_checkout() ) {		
				$settings = new WC_Sendy_Shipping_Method();
				
				wp_enqueue_script( 'googlemaps-api', 'https://maps.googleapis.com/maps/api/js?key='.$settings->google_api_key.'&libraries=places', array(), '1.0', true );
				wp_enqueue_script( 'geocomplete', WCSD_LINK . 'geocomplete/jquery.geocomplete.js', array('jquery','googlemaps-api'), '1.0', true );
				wp_enqueue_script( 'sendy-wc-theme-js', WCSD_LINK . 'js/sendy-wc-theme-js.js', array('jquery','geocomplete'), '1.0', true );
				wp_add_inline_script( 'geocomplete', 'jQuery(function($) {
						$("#billing_address_1").attr( "billing-data-geo", "formatted_address" );
						$("#billing_address_2").attr( "billing-data-geo", "name" );
						$("#billing_city").attr( "billing-data-geo", "locality" );
						$("#billing_state").attr( "billing-data-geo", "administrative_area_level_1" );
						$("#shipping_address_1").attr( "shipping-data-geo", "formatted_address" );
						$("#shipping_address_2").attr( "shipping-data-geo", "name" );
						$("#shipping_city").attr( "shipping-data-geo", "locality" );
						$("#shipping_state").attr( "shipping-data-geo", "administrative_area_level_1" );
					});'
				);
			}
		}
	}
		
	add_action( 'wp_enqueue_scripts', 'sendy_wc_theme_styles' );
	
	/**
	 * This plugin changes postcode/zip required status to false (it's depended to address, city, state/county fields)
	 * 
	 * Hook sendy_wc_checkout_fields to customize the WooCommerce fields
	 */
	function sendy_wc_checkout_fields( $address_fields  ) {		
		
		$address_fields['postcode']['label'] = __( 'Postcode / Zip', 'woocommerce' );
		
		return $address_fields ;
	}
	
	add_filter( 'woocommerce_default_address_fields' , 'sendy_wc_checkout_fields' );
	
}