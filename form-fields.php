<?php
// To be included in shipping-canada-post-woocommerce.php
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

require_once 'utils.php';
require_once 'form-fields-static.php';
require_once 'form-fields-dynamic.php';

// The fields to display on the WooCommerce settings page.
// This will show in a new tab called "Canada Post Shipping WooCommerce" in the Shipping section.
function form_fields() {
  $form_fields = form_fields_static();
  $form_fields = form_fields_dynamic($form_fields);

  return $form_fields;
}
?>
