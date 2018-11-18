<?php
// To be included in canada-post-shipping-woocommerce.php
namespace canada_post_shipping_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

require_once 'box.php'; // Defines a Box class.
require_once 'product.php'; // Defines a Product class.

function pack_products() {
  global $woocommerce;

  // Save the products into an array for sorting below.
  $products_arr = $woocommerce->cart->get_cart();
  $products     = array();

  foreach ($products_arr as $key => $_product) {
    array_push($products, new Product($_product));
  }

  // Sort the array based on volume.
  usort($products, function ($a, $b) {
    $a_vol = $a->get_volume();
    $b_vol = $b->get_volume();

    return $a_vol > $b_vol ? -1 : 1;
  });

  // foreach ($products as $_product) {
  //   print_r($_product->get_volume() . ' ');
  // }
}

?>
