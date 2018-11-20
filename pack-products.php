<?php
// To be included in canada-post-shipping-woocommerce.php
namespace canada_post_shipping_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

require_once 'box.php'; // Defines a Box class.
require_once 'product.php'; // Defines a Product class.

function pack_products($settings) {
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

    if ($a_vol == $b_vol) {
      return 0;
    }

    return $a_vol > $b_vol ? -1 : 1;
  });

  $boxes = get_boxes($settings);
  // print_r($boxes);

  usort($boxes, function ($a, $b) {
    $a_vol = $a->get_volume();
    $b_vol = $b->get_volume();

    if ($a_vol == $b_vol) {
      return 0;
    }

    return $a_vol < $b_vol ? -1 : 1;
  });

  $packed_boxes = array();

  while (true) { // TODO: This is looping forever when set to true.
    $break3 = false;

    foreach ($boxes as $idx => $box) {
      $break2 = false;
      $box    = Box::from_box($box);

      foreach ($products as $idx2 => $_product) {
        if ($_product->packed) {
          print_r('this product was already packed in another box | ');
          continue;
        }

        // try to add product to box
        print_r('adding product to box | ');
        // print_r($_product->data);
        if (!$box->add_product($_product)) {
          print_r('adding product to box failed | ');
          // add product fails and we're at the last box.
          if ($idx >= sizeof($boxes) - 1) {
            print_r('last box | ');
            // there is no box large enough for one or more items.
            if (sizeof($box->products) == 0) {
              //print_r('nothing is in the box ');
              foreach ($packed_boxes as $p_box) {
                //print_r('unpacking box ');
                $p_box->unpack();
              }

              $packed_boxes = array();
              print_r('Box packing failed because you have one or more items that are too large for your largest box. Please add a larger box.');

              $break2 = true;
              $break3 = true;
              break;
            }

            // add the packed box to the packed boxes array
            print_r('adding packed box to array | ');
            array_push($packed_boxes, $box);
            //print_r($packed_boxes);

            $break2 = true;
            break;
          }

          // unpack and try a larger box.
          $box->unpack();
          break;
        }
      }

      if (all_packed($products)) {
        print_r('all items packed, adding packed box to array | ');
        array_push($packed_boxes, $box);

        return $packed_boxes;
      }

      if ($break2) {
        break;
      }
    }
    if ($break3) {
      break;
    }
  }

  return $packed_boxes;

  //$boxes[0]->add_product($products[0]);
  //print_r($boxes[0]->products);

  //

  // foreach ($products as $_product) {
  //   print_r($_product->get_volume() . ' ');
  // }
}

function all_packed($products) {
  foreach ($products as $_product) {
    if (!$_product->packed) {
      // print_r('something isn\'t packed ');
      return false;
    }
  }

  // print_r('everything is packed ');

  return true;
}

?>
