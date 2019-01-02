<?php
// To be included in shipping-canada-post-woocommerce.php and/or any other files in the project.
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('Box')) {

  // A box for use with our volumetric packing algorithm.
  class Box {

    // Make a new box object using the settings and the shipping class.
    function __construct(
      $settings,
      $shipping_class) {

      $this->settings       = $settings;
      $this->shipping_class = $shipping_class;

      $key_prefix = slug_to_key($shipping_class->slug);

      // Set the inner box dimensions.
      $this->inner_lwh_str = $settings[$key_prefix . '_inner_dimensions'];
      $this->inner_lwh     = explode('x', str_replace(' ', '', $this->inner_lwh_str));
      $this->inner_l       = (float) $this->inner_lwh[0];
      $this->inner_w       = (float) $this->inner_lwh[1];
      $this->inner_h       = (float) $this->inner_lwh[2];
      $this->inner_lwh     = array(
        $this->inner_l, $this->inner_w, $this->inner_h,
      );

      // Set the outer box dimensions.
      $this->outer_lwh_str = $settings[$key_prefix . '_outer_dimensions'];
      $this->outer_lwh     = explode('x', str_replace(' ', '', $this->outer_lwh_str));
      $this->outer_l       = (float) $this->outer_lwh[0];
      $this->outer_w       = (float) $this->outer_lwh[1];
      $this->outer_h       = (float) $this->outer_lwh[2];
      $this->outer_lwh     = array(
        $this->outer_l, $this->outer_w, $this->outer_h,
      );

      // Set the box empty and max weight.
      $this->empty_max_weight_str = $settings[$key_prefix . '_weight'];
      $empty_max_weight_str2      = str_replace(' ', '', $this->empty_max_weight_str);
      $pos1                       = strpos($empty_max_weight_str2, '-');

      $this->empty_weight = (float) substr($empty_max_weight_str2, 0, $pos1);
      $this->max_weight   = (float) substr($empty_max_weight_str2, $pos1 + 5);

      $this->empty_max_weight = array(
        $this->empty_weight, $this->max_weight,
      );

      // Initialize the empty products array. This will hold the products when
      // they are packed into the box.
      $this->products = array();
    }

    // Duplicate a box.
    public static function from_box($box) {
      return new Box($box->settings, $box->shipping_class);
    }

    // Get the total value of all the products in the box.
    function get_products_value() {
      $products_value = 0;

      foreach ($this->products as $_product) {
        $products_value += $_product->price;
      }

      return $products_value;
    }

    // Get the inner volume of the box.
    function get_volume() {
      return $this->inner_l * $this->inner_w * $this->inner_h;
    }

    // Get the total weight of all the products.
    function get_products_weight() {
      $products_weight = 0;

      foreach ($this->products as $_product) {
        $products_weight += $_product->weight;
      }

      return $products_weight;
    }

    // Get the remaining volume in the box.
    function get_free_volume() {
      //print_r('getting free volume | ');
      $products_volume = 0;

      foreach ($this->products as $_product) {
        if ($_product->stacked) {
          $offsets           = get_offsets($this->settings, $_product->get_stack_type());
          $volume_difference = $_product->get_volume_difference($_product->new_stacked_product($offsets));
          //print_r('volume difference: ' . $volume_difference . ' | ');
          $products_volume += $volume_difference;
        } else {
          $products_volume += $_product->get_volume();
        }
      }

      //print_r('products volume: ' . $products_volume . ' | ');
      //print_r('free volume: ');
      //print_r($this->get_volume() - $products_volume);
      //print_r(' | ');
      return $this->get_volume() - $products_volume;
    }

    // Get the remaining amount of weight that will fit in the box.
    function get_free_weight() {
      $products_weight = 0;

      foreach ($this->products as $_product) {
        $products_weight += $_product->weight;
      }

      return $this->max_weight - $products_weight;
    }

    // Check if a product fits in the box by size.
    function fits_by_size($_product) {
      //print_r('checking fits by size | ');
      $product_dimensions = array($_product->length, $_product->width, $_product->height);

      // Check if product is stackable.
      $stack_type = $_product->get_stack_type();
      if ($stack_type) {
        // Loop through products in box and check if any share the stack type.
        foreach ($this->products as $b_product) {
          $b_stack_type = $b_product->get_stack_type();
          //print_r('b_stack_type: ' . $b_stack_type . ' | ');

          if ($b_stack_type == $stack_type) {
            //print_r($this->settings);
            //print_r('stacking ' . $stack_type . ' | ');
            $product_dimensions = get_offsets($this->settings, $stack_type);

            // if so, use offset values for that type, rather than product dimensions.
            $_product->stacked = true;
            break;
          }
        }
      } else {
        //print_r('item not stackable: stack type ' . $stack_type . ' | ');
      }

      // Sort the product dimensions, largest first.
      usort($product_dimensions, function ($a, $b) {
        if ($a == $b) {
          return 0;
        }

        return $a > $b ? -1 : 1;
      });

      // Sort the box dimensions, largest first.
      $box_dimensions = array($this->inner_l, $this->inner_w, $this->inner_h);
      usort($box_dimensions, function ($a, $b) {
        if ($a == $b) {
          return 0;
        }

        return $a > $b ? -1 : 1;
      });

      // Does the product fit in the box by size? We will return this below.
      //print_r('box dimensions 0: ' . $box_dimensions[0] . ' | ');
      //print_r('product dimensions 0: ' . $product_dimensions[0] . ' | ');
      //print_r('box dimensions 1: ' . $box_dimensions[1] . ' | ');
      //print_r('product dimensions 1: ' . $product_dimensions[1] . ' | ');
      //print_r('box dimensions 2: ' . $box_dimensions[2] . ' | ');
      //print_r('product dimensions 2: ' . $product_dimensions[2] . ' | ');

      $fits = $box_dimensions[0] > $product_dimensions[0] &&
      $box_dimensions[1] > $product_dimensions[1] &&
      $box_dimensions[2] > $product_dimensions[2];

      // If the product doesn't fit, set it as not stacked.
      if (!$fits) {
        $_product->stacked = false;
      }

      return $fits;
    }

    // Returns true if the product fits in the box by volume, and false otherwise.
    function fits_by_volume($_product) {
      //print_r('checking fits by volume | ');
      $stack_type = $_product->get_stack_type();

      if ($stack_type) {
        foreach ($this->products as $b_product) {
          $b_stack_type = $b_product->get_stack_type();

          if ($stack_type == $b_stack_type) {
            //print_r('stack type match | ');
            $_product->stacked = true;
            $offsets           = get_offsets($this->settings, $stack_type);
            //print_r('offsets: ');
            // print_r($offsets);

            $fits = $this->get_free_volume() > $_product->get_volume_difference($_product->new_stacked_product($offsets));

            if (!$fits) {
              $_product->stacked = false;
            }

            return $fits;
          }
        }
      } else {
        //print_r('item not stackable | ');
      }

      return $this->get_free_volume() > $_product->get_volume();
    }

    // Returns whether the product fits in the box by weight.
    function fits_by_weight($_product) {
      //print_r($_product->weight);
      //print_r($this->get_free_weight());
      return $this->get_free_weight() > $_product->weight;
    }

    // Add a product to the box. It will have to fit by size,
    // by volume, and by weight.
    function add_product($_product) {
      // print_r($this->fits_by_size($_product) ? 'true' : 'false');
      //print_r($this->fits_by_volume($_product) ? 'true' : 'false');
      //print_r($this->fits_by_weight($_product) ? 'true' : 'false');
      if ($this->fits_by_size($_product) && $this->fits_by_volume($_product) && $this->fits_by_weight($_product)) {
        $_product->packed = true;
        array_push($this->products, $_product);

        return true;
      }

      return false;
    }

    // Empty the box.
    function unpack() {
      foreach ($this->products as $_product) {
        $_product->packed = false;
      }
      $this->products = array();
    }
  }
}


?>
