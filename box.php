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
      $inner_lwh_str = '0.0 x 0.0 x 0.0',
      $outer_lwh_str = '0.0 x 0.0 x 0.0',
      $empty_max_weight_str = '0.0 -> 0.0') {

      $this->inner_lwh = explode('x', str_replace(' ', '', $inner_lwh_str));
      $this->inner_l   = (float) $this->inner_lwh[0];
      $this->inner_w   = (float) $this->inner_lwh[1];
      $this->inner_h   = (float) $this->inner_lwh[2];
      $this->inner_lwh = array(
        $this->inner_l, $this->inner_w, $this->inner_h,
      );

      $this->outer_lwh = explode('x', str_replace(' ', '', $outer_lwh_str));
      $this->outer_l   = (float) $this->outer_lwh[0];
      $this->outer_w   = (float) $this->outer_lwh[1];
      $this->outer_h   = (float) $this->outer_lwh[2];
      $this->outer_lwh = array(
        $this->outer_l, $this->outer_w, $this->outer_h,
      );

      $this->empty_max_weight = preg_split('/[->]+/', str_replace(' ', '', $empty_max_weight_str));
      $this->empty_weight     = (float) $this->empty_max_weight[0];
      $this->max_weight       = (float) $this->empty_max_weight[1];
      $this->empty_max_weight = array(
        $this->empty_weight, $this->max_weight,
      );
    }

    function get_volume() {
      return $this->inner_l * $this->inner_w * $this->inner_h;
    }
  }
}

?>
