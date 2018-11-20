<?php
// To be included in canada-post-shipping-woocommerce.php and/or any other files in the project.
namespace canada_post_shipping_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

if (!class_exists('Box')) {
  class Box {
    function __construct(
      $settings,
      $shipping_class,
      $inner_lwh_str = '0.0 x 0.0 x 0.0',
      $outer_lwh_str = '0.0 x 0.0 x 0.0',
      $empty_max_weight_str = '0.0 -> 0.0') {

      $this->settings       = $settings;
      $this->shipping_class = $shipping_class;

      $this->inner_lwh_str = $inner_lwh_str;
      $this->inner_lwh     = explode('x', str_replace(' ', '', $inner_lwh_str));
      $this->inner_l       = (float) $this->inner_lwh[0];
      $this->inner_w       = (float) $this->inner_lwh[1];
      $this->inner_h       = (float) $this->inner_lwh[2];
      $this->inner_lwh     = array(
        $this->inner_l, $this->inner_w, $this->inner_h,
      );

      $this->outer_lwh_str = $outer_lwh_str;
      $this->outer_lwh     = explode('x', str_replace(' ', '', $outer_lwh_str));
      $this->outer_l       = (float) $this->outer_lwh[0];
      $this->outer_w       = (float) $this->outer_lwh[1];
      $this->outer_h       = (float) $this->outer_lwh[2];
      $this->outer_lwh     = array(
        $this->outer_l, $this->outer_w, $this->outer_h,
      );

      $this->empty_max_weight_str = $empty_max_weight_str;
      // print_r(str_replace(' ', '', $empty_max_weight_str));
      // $this->empty_max_weight = preg_split('/[-][->]/', str_replace(' ', '', $empty_max_weight_str));
      $empty_max_weight_str2 = str_replace(' ', '', $empty_max_weight_str);
      $pos1                  = strpos($empty_max_weight_str2, '-');

      // print_r(substr($empty_max_weight_str2, $pos1 + 5));
      // print_r($this->empty_max_weight);
      $this->empty_weight = (float) substr($empty_max_weight_str2, 0, $pos1);
      $this->max_weight   = (float) substr($empty_max_weight_str2, $pos1 + 5);
      // $this->empty_weight     = (float) $this->empty_max_weight[0];
      // $this->max_weight       = (float) $this->empty_max_weight[1];
      $this->empty_max_weight = array(
        $this->empty_weight, $this->max_weight,
      );
      // print_r($this->empty_max_weight);

      $this->products = array();
    }

    public static function from_box($box) {
      return new Box($box->settings, $box->shipping_class, $box->inner_lwh_str, $box->outer_lwh_str, $box->empty_max_weight_str);
    }

    function get_volume() {
      return $this->inner_l * $this->inner_w * $this->inner_h;
    }

    function get_free_volume() {
      print_r('getting free volume | ');
      $products_volume = 0;
      foreach ($this->products as $_product) {
        if ($_product->stacked) {
          $offsets           = get_offsets($this->settings, $_product->get_stack_type());
          $volume_difference = $_product->get_volume_difference($_product->new_stacked_product($offsets));
          print_r('volume difference: ' . $volume_difference . ' | ');
          $products_volume += $volume_difference;
        } else {
          $products_volume += $_product->get_volume();
        }
      }
      print_r('products volume: ' . $products_volume . ' | ');
      print_r('free volume: ');
      print_r($this->get_volume() - $products_volume);
      print_r(' | ');
      return $this->get_volume() - $products_volume;
    }

    function get_free_weight() {
      // print_r($this->max_weight);
      $products_weight = 0;
      foreach ($this->products as $_product) {
        $products_weight += $_product->weight;
      }
      return $this->max_weight - $products_weight;
    }

    function fits_by_size($_product) {
      print_r('checking fits by size | ');
      $product_dimensions = array($_product->length, $_product->width, $_product->height);

      //check if product is stackable
      $stack_type = $_product->get_stack_type();
      if ($stack_type) {
        //loop through products in box and check if any share the stack type
        foreach ($this->products as $b_product) {
          $b_stack_type = $b_product->get_stack_type();
          //print_r('b_stack_type: ' . $b_stack_type . ' | ');

          if ($b_stack_type == $stack_type) {
            //print_r($this->settings);
            //print_r('stacking ' . $stack_type . ' | ');
            $product_dimensions = get_offsets($this->settings, $stack_type);
            //if so, use offset values for that type, rather than product dimensions.
            $_product->stacked = true;
            break;
          }
        }
      } else {
        print_r('item not stackable: stack type ' . $stack_type . ' | ');
      }

      usort($product_dimensions, function ($a, $b) {
        if ($a == $b) {
          return 0;
        }
        return $a > $b ? -1 : 1;
      });

      $box_dimensions = array($this->inner_l, $this->inner_w, $this->inner_h);
      usort($box_dimensions, function ($a, $b) {
        if ($a == $b) {
          return 0;
        }
        return $a > $b ? -1 : 1;
      });

      $fits = $box_dimensions[0] > $product_dimensions[0] &&
      $box_dimensions[1] > $product_dimensions[1] &&
      $box_dimensions[2] > $product_dimensions[2];

      if (!$fits) {
        $_product->stacked = false;
      }

      return $fits;
    }

    function fits_by_volume($_product) {
      print_r('checking fits by volume | ');
      $stack_type = $_product->get_stack_type();
      if ($stack_type) {
        foreach ($this->products as $b_product) {
          $b_stack_type = $b_product->get_stack_type();
          if ($stack_type == $b_stack_type) {
            print_r('stack type match | ');
            $_product->stacked = true;
            $offsets           = get_offsets($this->settings, $stack_type);
            print_r('offsets: ');
            print_r($offsets);
            $fits = $this->get_free_volume() > $_product->get_volume_difference($_product->new_stacked_product($offsets));
            if (!$fits) {
              $_product->stacked = false;
            }
            return $fits;
          }
        }
      } else {
        print_r('item not stackable | ');
      }
      return $this->get_free_volume() > $_product->get_volume();
    }

    function fits_by_weight($_product) {
      //print_r($_product->weight);
      //print_r($this->get_free_weight());
      return $this->get_free_weight() > $_product->weight;
    }

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

    function unpack() {
      foreach ($this->products as $_product) {
        $_product->packed = false;
      }
      $this->products = array();
    }
  }
}

// Get the list of boxes from the list of shipping classes.
function get_boxes($settings) {
  $shipping_classes = get_terms(array('taxonomy' => 'product_shipping_class', 'hide_empty' => false));
  $boxes            = array();

  foreach ($shipping_classes as $shipping_class) {
    if (!is_box($shipping_class->slug)) {
      continue;
    }

    $key_prefix = slug_to_key($shipping_class->slug);

    array_push($boxes, new Box(
      $settings,
      $shipping_class,
      $settings[$key_prefix . '_inner_dimensions'],
      $settings[$key_prefix . '_outer_dimensions'],
      $settings[$key_prefix . '_weight']
    ));
  }

  return $boxes;
}

?>
