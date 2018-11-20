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
      $this->product  = $_product;
      $this->data     = $_product['data'];
      $this->length   = $this->data->get_length();
      $this->width    = $this->data->get_width();
      $this->height   = $this->data->get_height();
      $this->weight   = $this->data->get_weight();
      $this->quantity = $_product['quantity'];
      $this->packed   = false;
      $this->stacked  = false;
    }

    function get_volume() {
      return $this->length * $this->width * $this->height;
    }

    function new_stacked_product($offsets) {
      $n_product         = new Product($this->product);
      $n_product->length = $this->length + $offsets[0];
      $n_product->width  = $this->width + $offsets[1];
      $n_product->height = $this->height + $offsets[2];
      return $n_product;
    }

    function get_volume_difference($_product) {
      return $_product->get_volume() - $this->get_volume();
    }

    function get_stack_type() {
      //print_r('product type: ' . $this->data->get_type() . ' | ');
      $stack_type = $this->data->get_attribute('pa_stackable');

      // if ($this->data->get_type() == 'variation') {
      // } else {
      // }

      if (!$stack_type) {
        //print_r('stack type false | ');
      } else {
        //print_r('get_attribute result: ');
        //print_r($stack_type);
        //print_r(' | ');
      }
      if (strpos($stack_type, ',')) {
        print_r('Product has more than one stackable type. Only one type per product is supported');
        return false;
      }
      return $stack_type;
    }
  }
}

?>
