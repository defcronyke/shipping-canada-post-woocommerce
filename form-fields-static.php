<?php
// To be included in form-fields.php
namespace canada_post_shipping_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

function form_fields_static() {
  return array(
    'title'               => array(
      'title'       => __('Title', 'cpswc'),
      'type'        => 'text',
      'description' => __('This controls the shipping method which the user sees during checkout.', 'cpswc'),
      'default'     => __('Canada Post', 'cpswc'),
    ),

    'description'         => array(
      'title'       => __('Description', 'cpswc'),
      'type'        => 'textarea',
      'description' => __('This controls the description which the user sees during checkout.', 'cpswc'),
      'default'     => __("Ship via Canada Post", 'cpswc'),
    ),

    'commercial_rates'    => array(
      'title'       => __('Commercial Rates', 'cpswc'),
      'type'        => 'checkbox',
      'description' => __('Use your API Customer Number below to get special rates and extra shipping methods. This is recommended if you have a customer number.', 'cpwsc'),
      'default'     => 'no',
    ),

    'api_customer_number' => array(
      'title'       => __('API Customer Number', 'cpswc'),
      'type'        => 'text',
      'description' => __('Canada Post API customer number.', 'cpswc'),
    ),

    'dev_mode'            => array(
      'title'       => __('Development Mode', 'cpswc'),
      'type'        => 'checkbox',
      'description' => __('Use the Canada Post development sandbox for testing. Turn this off for production.', 'cpwsc'),
      'default'     => 'yes',
    ),

    'api_username_dev'    => array(
      'title'       => __('Dev API Username', 'cpswc'),
      'type'        => 'text',
      'description' => __('Canada Post development sandbox API merchant username.', 'cpswc'),
    ),

    'api_password_dev'    => array(
      'title'       => __('Dev API Password', 'cpswc'),
      'type'        => 'text',
      'description' => __('Canada Post development sandbox API merchant password.', 'cpswc'),
    ),

    'api_username'        => array(
      'title'       => __('Prod API Username', 'cpswc'),
      'type'        => 'text',
      'description' => __('Canada Post production API merchant username.', 'cpswc'),
    ),

    'api_password'        => array(
      'title'       => __('Prod API Password', 'cpswc'),
      'type'        => 'text',
      'description' => __('Canada Post production API merchant password.', 'cpswc'),
    ),

    'origin_postal_code'  => array(
      'title'       => __('Origin Postal Code', 'cpswc'),
      'type'        => 'text',
      'description' => __('Postal Code of where you send from.', 'cpwsc'),
    ),

    'handling_time'       => array(
      'title'       => __('Handling Time', 'cpswc'),
      'type'        => 'text',
      'description' => __('Number of business days to add to Canada Post\'s delivery timeframe estimate.', 'cpwsc'),
      'default'     => '1',
    ),

    'handling_fee'        => array(
      'title'       => __('Handling Fee', 'cpswc'),
      'type'        => 'text',
      'description' => __('A fixed dollar amount to add to every shipping quote.', 'cpwsc'),
      'default'     => '0.0',
    ),

    'rate_multiplier'     => array(
      'title'       => __('Shipping Rate Multiplier', 'cpswc'),
      'type'        => 'text',
      'description' => __('A multiplier to adjust the shipping quotes by.', 'cpwsc'),
      'default'     => '1.0',
    ),

    'rate_markup'         => array(
      'title'       => __('Shipping Rate Markup', 'cpswc'),
      'type'        => 'text',
      'description' => __('Add this amount to the shipping quotes.', 'cpwsc'),
      'default'     => '0.0',
    ),

    'buy_insurance'       => array(
      'title'       => __('Buy Insurance', 'cpswc'),
      'type'        => 'checkbox',
      'description' => __('Include insurance coverage for the value of the shipment up to the maximum allowed by Canada Post. This will increase shipping costs a lot, but without it there will be little to no coverage if the shipment is lost or damaged. Some shipping methods include up to $100 of coverage, but some don\'t.<br>IMPORTANT: For your insurance to be valid, you will need to take photographs of your products, both unpackaged and packaged with attached shipping label, and you will have to enable the Signature Required option below.', 'cpwsc'),
      'default'     => 'yes',
    ),

    'signature_required'  => array(
      'title'       => __('Signature Required', 'cpswc'),
      'type'        => 'checkbox',
      'description' => __('Require a signature upon delivery. This is mandatory if you buy insurance for your insurance to be valid. You have been warned.', 'cpwsc'),
      'default'     => 'yes',
    ),
  );
}

?>
