<?php
// To be included in form-fields.php
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// This will show in a new tab called "Canada Post Shipping WooCommerce" in the Shipping section.
function form_fields_dynamic($form_fields) {
  $shipping_classes = WC()->shipping->get_shipping_classes();

  foreach ($shipping_classes as $idx => $shipping_class) {
    if (is_flat_rate($shipping_class->slug)) {
      $new_field_key = slug_to_key($shipping_class->slug);

      $new_field = array(
        'title'       => sprintf(esc_html__('Flat Rate Cost: %s', 'scpwc'), $shipping_class->name),
        'type'        => 'text',
        'description' => __('This amount will be added as a flat rate for each item which uses this shipping class. To make your shipping class show up on this settings page, make sure its slug starts with \'flat-rate-\'.', 'cpwsc'),
        'default'     => '0.0',
      );

      $form_fields[$new_field_key] = $new_field;
    }

    if (is_box($shipping_class->slug)) {
      $slug_key = slug_to_key($shipping_class->slug);

      $new_field_key = $slug_key . '_inner_dimensions';
      $new_field     = array(
        'title'       => sprintf(esc_html__('Box Inner Dimensions: %s', 'scpwc'), $shipping_class->name),
        'type'        => 'text',
        'description' => __('cm (L x W x H) To make a new box that will show up here, make a new shipping class with a slug starting with \'box-\'.', 'cpwsc'),
        'default'     => '0 x 0 x 0',
      );
      $form_fields[$new_field_key] = $new_field;

      $new_field_key = $slug_key . '_outer_dimensions';
      $new_field     = array(
        'title'       => sprintf(esc_html__('Box Outer Dimensions: %s', 'scpwc'), $shipping_class->name),
        'type'        => 'text',
        'description' => __('cm (L x W x H) To avoid errors, use dimensions from real boxes. See <a href="https://www.canadapost.ca/tools/pg/manual/PGpscanada-e.asp?fbclid=IwAR0mYrW3dg42lsklOPMcEeKs_v8hHikK9iYM602pYvzaqZSYoNjKOstidXw#1431012">Canada Post website</a> for size guidelines.', 'cpwsc'),
        'default'     => '0 x 0 x 0',
      );
      $form_fields[$new_field_key] = $new_field;

      $new_field_key = $slug_key . '_weight';
      $new_field     = array(
        'title'       => sprintf(esc_html__('Box Empty And Max Weight: %s', 'scpwc'), $shipping_class->name),
        'type'        => 'text',
        'description' => __('kg (E -> M) Empty plus max weight must be 30kg or less per box.', 'cpwsc'),
        'default'     => '0.0 -> 0.0',
      );
      $form_fields[$new_field_key] = $new_field;
    }

    if (is_letter($shipping_class->slug)) {
      $slug_key = slug_to_key($shipping_class->slug);

      $new_field_key = $slug_key . '_inner_dimensions';
      $new_field     = array(
        'title'       => sprintf(esc_html__('Envelope Inner Dimensions: %s', 'scpwc'), $shipping_class->name),
        'type'        => 'text',
        'description' => __('cm (L x W x H) To make a new letter envelope that will show up here, make a new shipping class with a slug starting with \'letter-\'.', 'cpwsc'),
        'default'     => '0 x 0 x 0',
      );
      $form_fields[$new_field_key] = $new_field;

      $new_field_key = $slug_key . '_outer_dimensions';
      $new_field     = array(
        'title'       => sprintf(esc_html__('Envelope Outer Dimensions: %s', 'scpwc'), $shipping_class->name),
        'type'        => 'text',
        'description' => __('cm (L x W x H) Maximum sizes for standard letter mail are: L - 24.5cm, W - 15.6cm, H - 0.5cm. Maximum sizes for oversize letter mail are: L - 38cm, W - 27cm, H - 2cm', 'cpwsc'),
        'default'     => '0 x 0 x 0',
      );
      $form_fields[$new_field_key] = $new_field;

      $new_field_key = $slug_key . '_weight';
      $new_field     = array(
        'title'       => sprintf(esc_html__('Envelope Empty And Max Weight: %s', 'scpwc'), $shipping_class->name),
        'type'        => 'text',
        'description' => __('kg (E -> M) Empty plus max weight must be 50g or less for standard letter mail and 500g or less for oversize letter mail.', 'cpwsc'),
        'default'     => '0.0 -> 0.0',
      );
      $form_fields[$new_field_key] = $new_field;
    }
  }

  if (get_taxonomy('pa_stackable')) {
    $terms = get_terms('pa_stackable');
    //print_r($terms);
    foreach ($terms as $term) {
      $new_field_key = slug_to_key($term->slug);
      $new_field     = array(
        'title'       => sprintf(esc_html__('Offset For Stackable Type: %s', 'scpwc'), $term->name),
        'type'        => 'text',
        'description' => __('cm (W x L x H) See the FAQ in the plugin details for instructions on how to make a product stackable.', 'cpwsc'),
        'default'     => '0.0 x 0.0 x 0.0',
      );
      $form_fields[$new_field_key] = $new_field;
    }
  }

  return $form_fields;
}
