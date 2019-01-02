<?php

// To be included in shipping-canada-post-woocommerce.php and/or any other files in the project.
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

define('PLUGIN_SLUG', 'shipping-canada-post-woocommerce');

function get_plugin_dir() {
  return WP_PLUGIN_DIR . '/' . PLUGIN_SLUG;
}

?>