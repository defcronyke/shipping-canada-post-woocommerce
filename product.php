<?php
// To be included in canada-post-shipping-woocommerce.php and/or any other files in the project.
namespace canada_post_shipping_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('Product')) {
  class Product {
    function __construct($_product) {
      $this->product = $_product;
      $this->data    = $_product['data'];
      $this->length  = $this->data->get_length();
      $this->width   = $this->data->get_width();
      $this->height  = $this->data->get_height();
      $this->weight  = $this->data->get_weight();
      $this->packed  = false;
    }

    function get_volume() {
      return $this->length * $this->width * $this->height;
    }
  }
}

?>
