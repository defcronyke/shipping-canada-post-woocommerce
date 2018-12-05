<?php
// To be included in form-fields.php
namespace canada_post_shipping_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

function form_fields_dynamic($form_fields) {
  $shipping_classes = WC()->shipping->get_shipping_classes();

  foreach ($shipping_classes as $idx => $shipping_class) {
    if (is_flat_rate($shipping_class->slug)) {
      $new_field_key = slug_to_key($shipping_class->slug);

      $new_field = array(
        'title'       => sprintf(esc_html__('Cost For Shipping Class: %s', 'cpswc'), $shipping_class->name),
        'type'        => 'text',
        'description' => __('This amount will be added as flat rate for each item which uses this shipping class. These items will be excluded from the volume-based shipping calculations. To make your shipping class show up on this settings page, make sure its slug starts with \'flat-rate\'.', 'cpwsc'),
        'default'     => '0.0',
      );

      $form_fields[$new_field_key] = $new_field;
    }

    if (is_box($shipping_class->slug)) {
      $slug_key = slug_to_key($shipping_class->slug);

      $new_field_key = $slug_key . '_inner_dimensions';
      $new_field     = array(
        'title'       => sprintf(esc_html__('Box Inner Dimensions: %s', 'cpswc'), $shipping_class->name),
        'type'        => 'text',
        'description' => __('cm (L x W x H)', 'cpwsc'),
        'default'     => '0.0 x 0.0 x 0.0',
      );
      $form_fields[$new_field_key] = $new_field;

      $new_field_key = $slug_key . '_outer_dimensions';
      $new_field     = array(
        'title'       => sprintf(esc_html__('Box Outer Dimensions: %s', 'cpswc'), $shipping_class->name),
        'type'        => 'text',
        'description' => __('cm (L x W x H) To avoid errors, use dimensions from real boxes. See <a href="https://www.canadapost.ca/tools/pg/manual/PGpscanada-e.asp?fbclid=IwAR0mYrW3dg42lsklOPMcEeKs_v8hHikK9iYM602pYvzaqZSYoNjKOstidXw#1431012">Canada Post website</a> for size guidelines', 'cpwsc'),
        'default'     => '0.0 x 0.0 x 0.0',
      );
      $form_fields[$new_field_key] = $new_field;

      $new_field_key = $slug_key . '_weight';
      $new_field     = array(
        'title'       => sprintf(esc_html__('Box Empty And Max Weight: %s', 'cpswc'), $shipping_class->name),
        'type'        => 'text',
        'description' => __('kg (E -> M) Empty plus max weight must be 30kg or less per box', 'cpwsc'),
        'default'     => '0.0 -> 0.0',
      );
      $form_fields[$new_field_key] = $new_field;
    }
  }
  
  if (get_taxonomy('pa_stackable')) {
    $terms = get_terms('pa_stackable');
    //print_r($terms);
    foreach($terms as $term) {
      $new_field_key = slug_to_key($term->slug);
      $new_field     = array(
        'title'       => sprintf(esc_html__('Offset for stackable type: %s', 'cpswc'), $term->name),
        'type'        => 'text',
        'description' => __('cm (W x L x H)', 'cpwsc'),
        'default'     => '0.0 x 0.0 x 0.0',
      );
      $form_fields[$new_field_key] = $new_field;
    }
  }

  return $form_fields;
}
