<?php
// To be included in canada-post-shipping-woocommerce.php and any other files in the project.
namespace canada_post_shipping_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

function slug_to_key($slug) {
  return str_replace('-', '_', $slug);
}

function is_flat_rate($slug) {
  $str1 = '-';
  $pos1 = strpos($slug, $str1);
  $pos2 = strpos($slug, $str1, $pos1 + strlen($str1));

  return strtolower(substr($slug, 0, $pos2)) == 'flat-rate';
}

?>