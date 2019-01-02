<?php
// To be included in shipping-canada-post-woocommerce.php and/or any other files in the project.
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

require_once 'letter.php';

// Keys in arrays usually have underscores instead of hyphens
// by convension.
function slug_to_key($slug) {
  return str_replace('-', '_', $slug);
}

// Replaces spaces with underscores, and lowercases the value,
// to make it a nicer looking array key.
function value_to_key($value) {
  return strtolower(str_replace(' ', '_', $value));
}

// Check if a given slug starts with "flat-rate-".
function is_flat_rate($slug) {
  $str1 = '-';
  $pos1 = strpos($slug, $str1);
  $pos2 = strpos($slug, $str1, $pos1 + strlen($str1));

  return strtolower(substr($slug, 0, $pos2)) == 'flat-rate';
}

// Check if a given slug starts with "box-".
function is_box($slug) {
  $pos = strpos($slug, '-');

  return strtolower(substr($slug, 0, $pos)) == 'box';
}

// Check if a given slug starts with "letter-".
function is_letter($slug) {
  $pos = strpos($slug, '-');

  return strtolower(substr($slug, 0, $pos)) == 'letter';
}

// Returns an array of the offsets of a given stack type.
function get_offsets($settings, $stack_type) {
  $stack_type    = value_to_key($stack_type);
  $offsets_str   = $settings[$stack_type];
  $offsets_array = explode('x', str_replace(' ', '', $offsets_str));

  return array(
    (float) $offsets_array[0],
    (float) $offsets_array[1],
    (float) $offsets_array[2],
  );
}

// Get the list of boxes from the list of shipping classes.
function get_boxes($settings) {
  $shipping_classes = get_terms(array('taxonomy' => 'product_shipping_class', 'hide_empty' => false));
  $boxes            = array();

  foreach ($shipping_classes as $shipping_class) {
    $is_box = is_box($shipping_class->slug);
    $is_letter = is_letter($shipping_class->slug);

    if (!$is_box && !$is_letter) {
      continue;
    }
    
    if ($is_box) {
      array_push($boxes, new Box(
        $settings,
        $shipping_class
      ));  
      continue;
    }

    $letter = new Letter(
      $settings,
      $shipping_class
    );

    //print_r($letter);
    array_push($boxes, $letter);

  }

  return $boxes;
}
?>
