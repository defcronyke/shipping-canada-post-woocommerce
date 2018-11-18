<?php
/*
 * Plugin Name: Canada Post Shipping WooCommerce
 * Description: Use Canada Post shipping with WooCommerce. Provides some of the premium features from other similar plugins for free.
 * Version: 1.0.0
 * Author: Jeremy Carter and Daphne Volante
 * Author URI: https://eternalvoid.net
 * WC requires at least: 3.5.0
 * WC tested up to: 3.5.0
 * Text Domain: cpswc
 */
namespace canada_post_shipping_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// Exit if WooCommerce isn't active.
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
  exit;
}

require_once 'form-fields.php'; // Defines form_fields() which returns the object needed for setting $this->form_fields.
require_once 'xml-request.php'; // Defines xml_request() which returns the XML request body.
require_once 'get-cp-rates.php'; // Defines get_cp_rates() which requests the shipping rates from the API server.
require_once 'add-rates.php'; // Defines add_rates() which adds the shipping rates from the API response.
require_once 'actions-and-filters.php'; // Adds some actions and filters to WooCommerce.
require_once 'box.php'; // Defines a Box class.

// Shipping Method init function.
function cpswc_init() {
  // Only define class if it isn't defined yet.
  if (!class_exists('WC_CPSWC_Shipping_Method')) {

    // Define the new shipping method class.
    class WC_CPSWC_Shipping_Method extends \WC_Shipping_Method {
      /**
       * Constructor for the shipping class. Supports shipping zones.
       *
       * @access public
       * @return void
       */
      public function __construct($instance_id = 0) {
        // Support multiple instances of the class to support shipping zones.
        $this->instance_id = absint($instance_id);

        // The shipping method ID.
        $this->id = 'cpswc';

        // The shipping method title.
        $this->method_title = __('Canada Post Shipping WooCommerce', 'cpswc');

        // The shipping method description.
        $this->method_description = __('Use Canada Post shipping with WooCommerce. Provides some of the premium features from other similar plugins for free.', 'cpswc');

        // Set to 'including' if we want our $this->countries array below to be a whitelist, blacklisting all others implicitly.
        $this->availablity = 'including';

        // The list of countries that the shipping method is available for.
        $this->countries = array('CA');

        // Features that the shipping method supports.
        $this->supports = array(
          'shipping-zones',
          'settings',
          'instance-settings',
          'instance-settings-modal',
        );

        // Run some initialization, after this we can access fields from the settings.
        $this->init();

        // Is the shipping method enabled?
        $this->enabled = 'yes';

        // Another field which wants the shipping method title.
        $this->title = $this->method_title;
      }

      /**
       * Init your settings
       *
       * @access public
       * @return void
       */
      function init() {
        // Load the settings API
        $this->init_form_fields();
        $this->init_settings();
        $settings = $this->settings;

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));

        // Register the flat shipping rates with WooCommerce.
        add_action('woocommerce_cart_calculate_fees', function () use ($settings) {
          cpswc_flat_rate($settings);
        });

        // Register the handling fee with WooCommerce.
        add_action('woocommerce_cart_calculate_fees', function () use ($settings) {
          cpswc_handling_fee($settings);
        });
      }

      /**
       * Initialise Settings Form Fields
       */
      function init_form_fields() {
        // Set all the settings form data.
        $this->form_fields = form_fields();

        // Copy the global settings to the shipping zone instance settings area.
        // TODO: Maybe we should only copy the global settings over if the instance settings don't exist yet.
        $this->instance_form_fields = $this->form_fields;
      }

      /**
       * calculate_shipping function.
       *
       * @access public
       * @param mixed $package
       * @return void
       */
      public function calculate_shipping($package = array()) {
        // Save the settings so we can use them in a closure later.
        $settings = $this->settings;

        // If true, use the Canada Post development sandbox API and your sandbox credentials,
        // otherwise use the production API.
        $dev_mode = $settings['dev_mode'] == 'yes' ? true : false;

        // The sandbox server domain prefix.
        $ctd = $dev_mode ? 'ct.' : '';

        // The Canada Post API shipping quotes endpoint.
        $service_url = 'https://' . $ctd . 'soa-gw.canadapost.ca/rs/ship/price';

        // API username
        $username = $dev_mode ? $settings['api_username_dev'] : $settings['api_username'];

        // API password
        $password = $dev_mode ? $settings['api_password_dev'] : $settings['api_password'];

        // Build the XML request body.
        $xml_request = xml_request($package, $settings, $dev_mode);

        // Make the HTTP request to the API server with curl.
        $curl_response = get_cp_rates($service_url, $xml_request, $username, $password);

        // Add the shipping rates that we got from the API response.
        add_rates($curl_response, $settings, $this);

        $box1 = new Box($settings['box_1_inner_dimensions'], $settings['box_1_outer_dimensions'], $settings['box_1_weight']);
        //print_r($box1);

        
      }
    }
  }
}
