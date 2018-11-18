<?php
// To be included in canada-post-shipping-woocommerce.php and/or any other files in the project.
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

function is_box($slug) {
  $pos = strpos($slug, '-');

  return strtolower(substr($slug, 0, $pos)) == 'box';
}

function parse_offsets($offsetLWH) {
  $offsets_array = explode('x', str_replace(' ', '', $offsetLWH));
  return array(
    (float) $offsets_array[0],
    (float) $offsets_array[1],
    (float) $offsets_array[2]
  );
}

?>
