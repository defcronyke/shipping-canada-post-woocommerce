<?php
// To be included in shipping-canada-post-woocommerce.php
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

require_once 'utils.php'; // Some helper functions.

// Add any applicable flat rate shipping fees to the cart.
//
// You can specify these in the dashboard by adding new
// shipping classes with a slug that starts with "flat-rate-".
// Once you have some of those, there will be new fields in this
// plugin's settings area where you can set the rates.
function scpwc_flat_rate($settings) {
  global $woocommerce;

  $cart = $woocommerce->cart->get_cart();

  foreach ($cart as $key => $item) {
    $_product = $item['data'];
    $terms    = get_the_terms($_product->get_id(), 'product_shipping_class');

    if (!$terms) {
      continue;
    }

    foreach ($terms as $term) {
      if (!is_flat_rate($term->slug)) {
        continue;
      }

      // Get handling fee from settings.
      $rate = $settings[slug_to_key($term->slug)];

      // Add the handling fee if it isn't 0.
      if ((float) $rate != 0.0) {
        $woocommerce->cart->add_fee(sprintf(__('Shipping %s', 'scpwc'), $term->name), $rate, true, '');
      }
    }
  }
}

// Adds a handling fee to the cart. You can set the fee on the
// plugin settings page in the dashboard.
function scpwc_handling_fee($settings) {
  global $woocommerce;

  if (is_admin() && !defined('DOING_AJAX')) {
    return;
  }

  // Get handling fee from settings.
  $fee = $settings['handling_fee'];

  // Add the handling fee if it isn't 0.
  if ((float) $fee != 0.0) {
    $woocommerce->cart->add_fee(__('Handling', 'scpwc'), $fee, true, '');
  }
}

// Register shipping method class with WooCommerce.
add_action('woocommerce_shipping_init', 'shipping_canada_post_woocommerce\scpwc_init');

// Define the shipping method key and value, like $methods['key'] = 'value'
// where 'key' is the $this->id that you specify in the constructor,
// and 'value' is the name of the shipping method class.
function add_scpwc_shipping_method($methods) {
  $methods['scpwc'] = 'shipping_canada_post_woocommerce\WC_SCPWC_Shipping_Method';
  return $methods;
}

// Register shipping method with WooCommerce.
add_filter('woocommerce_shipping_methods', 'shipping_canada_post_woocommerce\add_scpwc_shipping_method');
?>
