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
      $this->price    = $this->data->get_price();
      $this->quantity = $_product['quantity'];
      $this->packed   = false;
      $this->stacked  = false;
    }

    function get_volume() {
      return $this->length * $this->width * $this->height;
    }

    public static function from_product($_product) {
      return new Product($_product->product);
    }

    function get_individual_products() {
      $quantity = $this->product['quantity'];
      $products = array();
      //print_r('quantity: ' . $quantity . ' | ');
      for ($idx = 0; $idx < $quantity; $idx++) {
        $n_product                      = Product::from_product($this);
        $n_product->product['quantity'] = 1;
        array_push($products, $n_product);
      }
      return $products;
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

    // Returns the product attribute term name.
    function get_stack_type() {
      //print_r('product type: ' . $this->data->get_type() . ' | ');
      $stack_type         = '';
      $attrib_taxonomy_id = 'pa_stackable';

      // $parent;
      $attribs;
      $product_id;

      // If a variable product.
      if ($this->data->is_type('variation')) {
        $parent = new \WC_Product_Variable($this->data->get_parent_id());

        // Get all child variations.
        // $available_variations = $parent->get_available_variations();

        $attribs    = $parent->get_attributes();
        $product_id = $this->data->get_parent_id();
      } else { // If not a variable product, we need to get the stack type in a different way.
        $attribs    = $this->data->get_attributes();
        $product_id = $this->data->get_id();
      }

      if (!array_key_exists($attrib_taxonomy_id, $attribs)) {
        // print_r('product is not stackable | ');
        return false;
      }

      $attrib        = $attribs[$attrib_taxonomy_id];
      $product_terms = wc_get_product_terms($product_id, $attrib_taxonomy_id, array('fields' => 'all'));

      $stack_type_ids = $attrib->get_options();

      if (sizeof($stack_type_ids) <= 0) {
        print_r('product has more than one stackable type. only one type per product is supported | ');
        return false;
      }

      // Ignore all but the first stack type because we currently
      // only support one stack type per product.
      $stack_type_id = $stack_type_ids[0];

      foreach ($product_terms as $term) {
        if ($term->term_id == $stack_type_id) {
          $stack_type = $term->name;
          break;
        }
      }

      // print_r(' stack type: ');
      // print_r($stack_type);
      // print_r(' | ');

      // if ($this->data->get_type() == 'variation') {
      // } else {
      // }

      // if (!$stack_type) {
      //   //print_r('stack type false | ');
      // } else {
      //   //print_r('get_attribute result: ');
      //   //print_r($stack_type);
      //   //print_r(' | ');
      // }
      // if (strpos($stack_type, ',')) {
      //   print_r('Product has more than one stackable type. Only one type per product is supported');
      //   return false;
      // }
      return $stack_type;
    }
  }
}

?>
