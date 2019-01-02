<?php

// To be included in shipping-canada-post-woocommerce.php and/or any other files in the project.
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

require_once 'conf.php'; // Some project details.
require_once 'box.php'; // Defines a Box class.

if (!class_exists('Letter')) {

  // A letter mail envelope for use with our volumetric packing algorithm.
  class Letter extends Box {

    // Duplicate a letter.
    public static function from_letter($letter) {
      return new Letter($letter->settings, $letter->shipping_class);
    }

    // Get Letter mail rates based on letter-rates.json.
    public function get_rate($country) {
      
      // Open file and decode json.
      $rates_str = file_get_contents(get_plugin_dir() . '/letter-rates.json');
      $rates_arr = json_decode($rates_str, true);

      // Check that the package weight is within Canada Post letter mail guidelines.
      $max_weight = $rates_arr['oversize']['max weight'];
      $products_weight = $this->get_products_weight();

      if ($products_weight > $max_weight) {
        print_r('Letter envelope is too heavy for letter mail. Please consult the settings page for maximum weights.');
        return;
      }

      // Check if letter fits in oversize letter slot.
      $outer_dimensions = $this->outer_lwh;
      usort($outer_dimensions, function ($a, $b) {
        if ($a == $b) {
          return 0;
        }
    
        return $a > $b ? -1 : 1;
      });

      $max_size = $rates_arr['oversize']['max size'];

      usort($max_size, function ($a, $b) {
        if ($a == $b) {
          return 0;
        }
    
        return $a > $b ? -1 : 1;
      });

      $fits = $max_size[0] >= $outer_dimensions[0] &&
      $max_size[1] >= $outer_dimensions[1] &&
      $max_size[2] >= $outer_dimensions[2];

      if (!$fits) {
        print_r('Letter Envelope too large for letter mail. Please use slugs starting in box- for extra large envelopes and consult the settings page for maximum letter mail dimensions.');
        return;
      }

      // Check if letter fits in standard letter slot.
      $max_size = $rates_arr['standard']['max size'];
        usort($max_size, function ($a, $b) {
          if ($a == $b) {
            return 0;
          }
      
          return $a > $b ? -1 : 1;
      });

      $fits_standard = $max_size[0] >= $outer_dimensions[0] &&
      $max_size[1] >= $outer_dimensions[1] &&
      $max_size[2] >= $outer_dimensions[2];

      $max_weight_standard = $rates_arr['standard']['max weight'];

      $weight1 = $rates_arr['oversize']['weight1'];
      $weight2 = $rates_arr['oversize']['weight2'];
      $weight3 = $rates_arr['oversize']['weight3'];
      $weight4 = $rates_arr['oversize']['weight4'];
      $weight5 = $rates_arr['oversize']['weight5'];

      // Get Oversize Rates.
      if (!$fits_standard || $products_weight > $max_weight_standard ) {
        switch ($products_weight) {
          // Products weight is within the first oversize weight class.
          case $products_weight < $weight1['weight'][1]: {
            if ($country == 'CA') {
              return $weight1['rates']['ca'];
            } else if ($country == 'US') {
              return $weight1['rates']['us'];
            }
            return $weight1['rates']['intl'];
            break;
          }
          // Products weight is within the second oversize weight class.
          case $products_weight < $oversize_rates['weight2']['weight'][1]: {
            if ($country == 'CA') {
              return $weight2['rates']['ca'];
            } else if ($country == 'US') {
              return $weight2['rates']['us'];
            }
            return $weight2['rates']['intl'];
            break;
          }
          // Products weight is within the third oversize weight class.
          case $products_weight < $oversize_rates['weight3']['weight'][1]: {
            if ($country == 'CA') {
              return $weight3['rates']['ca'];
            } else if ($country == 'US') {
              return $weight3['rates']['us'];
            }
            return $weight3['rates']['intl'];
            break;
          }
          // Products weight is within the fourth oversize weight class.
          case $products_weight < $oversize_rates['weight4']['weight'][1]: {
            if ($country == 'CA') {
              return $weight4['rates']['ca'];
            } else if ($country == 'US') {
              return $weight4['rates']['us'];
            }
            return $weight4['rates']['intl'];
            break;
          }
          // Products weight is within the fifth oversize weight class.
          case $products_weight < $oversize_rates['weight5']['weight'][1]: {
            if ($country == 'CA') {
              return $weight5['rates']['ca'];
            } else if ($country == 'US') {
              return $weight5['rates']['us'];
            }
            return $weight5['rates']['intl'];
            break;
          }
          
          default: {
            print_r('Letter envelope is too heavy for letter mail. Please consult the settings page for maximum weights.');
            return;
            break;
          }
        }

      }

      $weight1 = $rates_arr['standard']['light'];
      $weight2 = $rates_arr['standard']['heavy'];

      // Get Standard rates,
      switch ($products_weight) {
        // Product weight fits within the first standard weight class.
        case $products_weight < $weight1['weight'][1]: {
          if ($country == 'CA') {
            return $weight1['rates']['ca'];
          } else if ($country == 'US') {
            return $weight1['rates']['us'];
          }
          return $weight1['rates']['intl'];
          break;
        }
        // Product weight fits within the second standard weight class.
        case $products_weight < $oversize_rates['weight2']['weight'][1]: {
          if ($country == 'CA') {
            return $weight2['rates']['ca'];
          } else if ($country == 'US') {
            return $weight2['rates']['us'];
          }
          return $weight2['rates']['intl'];
          break;
        }
        default: {
          print_r('Letter envelope is too heavy for letter mail. Please consult the settings page for maximum weights.');
          return;
          break;
        }
      }
    }
  }
}

?>
