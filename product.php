<?php
// To be included in shipping-canada-post-woocommerce.php and/or any other files in the project.
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('Product')) {
  // A convenience wrapper for a WC_Product object.
  class Product {

    // Takes a WC_Product object as its argument.
    function __construct($_product) {
      $this->product   = $_product;
      $this->data      = $_product['data'];
      $this->length    = $this->data->get_length();
      $this->width     = $this->data->get_width();
      $this->height    = $this->data->get_height();
      $this->weight    = $this->data->get_weight();
      $this->price     = $this->data->get_price();
      $this->quantity  = $_product['quantity'];
      $this->packed    = false;
      $this->stacked   = false;
      $this->flat_rate = false;

      $terms = get_the_terms($this->data->get_id(), 'product_shipping_class');

      if ($terms) {
        foreach ($terms as $term) {
          if (is_flat_rate($term->slug)) {
            $this->flat_rate = true;
            break;
          }
        }
      }
    }

    // Get the product's volume.
    function get_volume() {
      return $this->length * $this->width * $this->height;
    }

    // Duplicate a product. They will share the same underlying
    // data though, so be careful.
    public static function from_product($_product) {
      return new Product($_product->product);
    }

    // Returns an array of identical products of length n,
    // where n is the quantity. Sets their quantity field to 1,
    // and be careful because they all share the same underlying data.
    function get_individual_products() {
      $quantity = $this->product['quantity'];
      //print_r('quantity: ' . $quantity . ' | ');
      $products = array();

      for ($idx = 0; $idx < $quantity; $idx++) {
        $n_product                      = Product::from_product($this);
        $n_product->product['quantity'] = 1;
        array_push($products, $n_product);
      }

      return $products;
    }

    // Returns a new stacked product based on the current product.
    // It shares the same underlying data so be careful.
    function new_stacked_product($offsets) {
      $n_product         = new Product($this->product);
      $n_product->length = $this->length + $offsets[0];
      $n_product->width  = $this->width + $offsets[1];
      $n_product->height = $this->height + $offsets[2];

      return $n_product;
    }

    // Get the difference in volume between the product passed in, and this product.
    function get_volume_difference($_product) {
      return $_product->get_volume() - $this->get_volume();
    }

    // Returns the product attribute term name.
    function get_stack_type() {
      //print_r('product type: ' . $this->data->get_type() . ' | ');
      $stack_type         = '';
      $attrib_taxonomy_id = 'pa_stackable';
      $attribs;
      $product_id;

      // If a variable product.
      if ($this->data->is_type('variation')) {
        $parent     = new \WC_Product_Variable($this->data->get_parent_id());
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

      $attrib         = $attribs[$attrib_taxonomy_id];
      $product_terms  = wc_get_product_terms($product_id, $attrib_taxonomy_id, array('fields' => 'all'));
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

      return $stack_type;
    }
  }
}
?>
