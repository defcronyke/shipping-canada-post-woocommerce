<?php
// To be included in canada-post-shipping-woocommerce.php
namespace canada_post_shipping_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

function add_rates($curl_response, $settings, $that) {
  // Parse the API XML response with SimpleXML.
  libxml_use_internal_errors(true);

  $xml = simplexml_load_string('<root>' . preg_replace('/<\?xml.*\?>/', '', $curl_response) . '</root>');

  // Display error if the XML response is invalid.
  if (!$xml) {
    echo 'Failed loading XML' . "\n";
    echo $curl_response . "\n";

    foreach (libxml_get_errors() as $error) {
      echo "\t" . $error->message;
    }
  } else { // If the XML is valid.
    // Get the price quotes.
    if ($xml->{'price-quotes'}) {
      $price_quotes = $xml->{'price-quotes'}->children('http://www.canadapost.ca/ws/ship/rate-v3');

      if ($price_quotes->{'price-quote'}) {
        // Make a new array to hold the rates, so we can sort them later.
        $rates = array();

        // Iterate over each shipping rate.
        foreach ($price_quotes as $price_quote) {
          // Get the expected number of business days until arrival,
          // taking into account our handling time from the settings.
          $transit_time = $price_quote->{'service-standard'}->{'expected-transit-time'}+$settings['handling_time'];

          // Populate our shipping rate object.
          $rate = array(
            // A unique ID for the shipping rate.
            'id'       => str_replace(' ', '-', $price_quote->{'service-name'}),

            // A label to display what the rate is called.
            'label'    => sprintf(esc_html__('Canada Post %1$s (approx. %2$d business %3$s)', 'cpswc'), $price_quote->{'service-name'}, $transit_time, _n('day', 'days', $transit_time, 'cpswc')),

            // The shipping rate returned by Canada Post with our rate multiplier and markup from the settings applied.
            'cost'     => round((float) $price_quote->{'price-details'}->{'due'} * (float) $settings['rate_multiplier'] + (float) $settings['rate_markup'], 2),

            // Calculate tax per_order or per_item.
            'calc_tax' => 'per_order',
          );

          // Add the rate object to our array of rates for sorting.
          array_push($rates, $rate);
        }

        // Sort the rates from cheapest to most expensive.
        usort($rates, function ($a, $b) {
          if ((float) $a['cost'] == (float) $b['cost']) {
            return 0;
          }

          return ((float) $a['cost'] < (float) $b['cost']) ? -1 : 1;
        });

        // Add the rates to the shipping checkout area in sorted order.
        foreach ($rates as $rate) {
          $that->add_rate($rate);
        }
      }
    }

    // If we made an error somewhere in our API query, display the error code and a helpful message.
    if ($xml->{'messages'}) {
      $messages = $xml->{'messages'}->children('http://www.canadapost.ca/ws/messages');

      foreach ($messages as $message) {
        echo 'Error Code: ' . $message->code . "\n";
        echo 'Error Msg: ' . $message->description . "\n\n";
      }
    }
  }
}

?>
