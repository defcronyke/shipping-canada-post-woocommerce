<?php
// To be included in canada-post-shipping-woocommerce.php
namespace canada_post_shipping_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

function cpswc_handling_fee($settings) {
  global $woocommerce;

  if (is_admin() && !defined('DOING_AJAX')) {
    return;
  }

  // Get handling fee from settings.
  $fee = $settings['handling_fee'];

  // Add the handling fee if it isn't 0.
  if ((float) $fee != 0.0) {
    $woocommerce->cart->add_fee(__('Handling', 'cpswc'), $fee, true, '');
  }
}

// Register shipping method class with WooCommerce.
add_action('woocommerce_shipping_init', 'canada_post_shipping_woocommerce\cpswc_init');

// Define the shipping method key and value, like $methods['key'] = 'value'
// where 'key' is the $this->id that you specify in the constructor,
// and 'value' is the name of the shipping method class.
function add_cpswc_shipping_method($methods) {
  $methods['cpswc'] = 'canada_post_shipping_woocommerce\WC_CPSWC_Shipping_Method';
  return $methods;
}

// Register shipping method with WooCommerce.
add_filter('woocommerce_shipping_methods', 'canada_post_shipping_woocommerce\add_cpswc_shipping_method');

?>