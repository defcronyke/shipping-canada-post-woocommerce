<?php
/*
 * Plugin Name: Shipping Canada Post WooCommerce
 * Description: Use Canada Post shipping with WooCommerce. Provides some of the premium features from other similar plugins for free.
 * Version: 0.2.0
 * Author: Jeremy Carter and Daphne Volante
 * Author URI: https://eternalvoid.net
 * WC requires at least: 3.5.0
 * WC tested up to: 3.5.3
 * Text Domain: scpwc
 */
namespace shipping_canada_post_woocommerce;

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
require_once 'pack-products.php'; // Defines pack_products() which returns an array of boxes.

// Shipping Method init function.
function scpwc_init() {
  // Only define class if it isn't defined yet.
  if (!class_exists('WC_CPSWC_Shipping_Method')) {

    // Define the new shipping method class.
    class WC_SCPWC_Shipping_Method extends \WC_Shipping_Method {
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
        $this->id = 'scpwc';

        // The shipping method title.
        $this->method_title = __('Shipping Canada Post WooCommerce', 'scpwc');

        // The shipping method description.
        $this->method_description = __('Use Canada Post shipping with WooCommerce. Provides some of the premium features from other similar plugins for free.', 'scpwc');

        // // Set to 'including' if we want our $this->countries array below to be a whitelist, blacklisting all others implicitly.
        // $this->availablity = 'including';

        // // The list of countries that the shipping method is available for.
        // $this->countries = array('CA');

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
          scpwc_flat_rate($settings);
        });

        // Register the handling fee with WooCommerce.
        add_action('woocommerce_cart_calculate_fees', function () use ($settings) {
          scpwc_handling_fee($settings);
        });
      }

      /**
       * Initialise Settings Form Fields
       */
      function init_form_fields() {
        // Set all the settings form data.
        $this->form_fields = form_fields();

        // Copy the global settings to the shipping zone instance settings area.
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
        $country     = $package['destination']['country'];
        $postal_code = $package['destination']['postcode'];

        if (!$country) {
          return;
        }

        if ($country == 'CA' && !$postal_code) {
          return;
        }

        if ($country == 'US' && !$postal_code) {
          return;
        }

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

        $boxes = pack_products($settings);
        // print_r('number of boxes: ' . sizeof($boxes) . ' | ');

        // Build the XML request body.
        $api_responses   = array();
        $num_empty_boxes = 0;
        foreach ($boxes as $box) {
          // If the box is empty, move on to the next box and don't make a Canada Post API request.
          if (sizeof($box->products) <= 0) {
            $num_empty_boxes++;
            continue;
          }

          $xml_request = xml_request($package, $settings, $country, $postal_code, $dev_mode, $box);

          // Make the HTTP request to the API server with curl.
          array_push($api_responses, get_cp_rates($service_url, $xml_request, $username, $password));

          // Get Letter Mail rates.
          if (is_letter($box->shipping_class->slug)) {
            // Make a new shipping rate object.
            $rate = array(
              // Populate our shipping rate object.
              // A unique ID for the shipping rate.
              'id'       => 'letter_mail',

              // A label to display what the rate is called.
              'label'    => esc_html__('Canada Post Letter Mail (approx. 2 - 7 business days)', 'scpwc'),

              // The shipping rate returned by Canada Post with our rate multiplier and markup from the settings applied.
              'cost'     => round((float) $box->get_rate($country) * (float) $settings['rate_multiplier'] + (float) $settings['rate_markup'], 2),

              // Calculate tax per_order or per_item.
              'calc_tax' => 'per_order',
            );

            $this->add_rate($rate);
          }
        }

        // If all boxes are empty, then all we have are flat rate items.
        if ($num_empty_boxes >= sizeof($boxes)) {
          // Make a new shipping rate object.
          $rate = array(
            // Populate our shipping rate object.
            // A unique ID for the shipping rate.
            'id'       => 'flat_rate',

            // A label to display what the rate is called.
            'label'    => esc_html__('Flat rate shipping, see below', 'scpwc'),

            // The shipping rate returned by Canada Post with our rate multiplier and markup from the settings applied.
            // 'cost'     => false,

            // Calculate tax per_order or per_item.
            'calc_tax' => 'per_order',
          );

          $this->add_rate($rate);
        } else { // If not all boxes are empty.
          // Add the shipping rates that we got from the API response.
          add_rates($api_responses, $settings, $this);
        }
      }
    }
  }
}
