<?php
// To be included in shipping-canada-post-woocommerce.php
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// Add the shipping rates from Canada Post to WooCommerce.
function add_rates($api_responses, $settings, $that) {
  // Parse the API XML response with SimpleXML.
  libxml_use_internal_errors(true);

  $total_rates = array();
  foreach ($api_responses as $api_response) {
    $xml = simplexml_load_string('<root>' . preg_replace('/<\?xml.*\?>/', '', $api_response['body']) . '</root>');

    // Display error if the XML response is invalid.
    if (!$xml) {
      print_r('Failed loading XML: ');
      print_r($api_response['body']);

      foreach (libxml_get_errors() as $error) {
        print_r('\t');
        print_r($error->message);
      }
    } else { // If the XML is valid.
      // Get the price quotes.
      if ($xml->{'price-quotes'}) {
        $price_quotes = $xml->{'price-quotes'}->children('http://www.canadapost.ca/ws/ship/rate-v3');

        if ($price_quotes->{'price-quote'}) {
          // Iterate over each shipping rate.
          foreach ($price_quotes as $idx => $price_quote) {
            // Get the expected number of business days until arrival,
            // taking into account our handling time from the settings.
            $transit_time = $price_quote->{'service-standard'}->{'expected-transit-time'}+$settings['handling_time'];

            // If this is the first box.
            $id   = str_replace(' ', '_', $price_quote->{'service-name'});
            $cost = round((float) $price_quote->{'price-details'}->{'due'} * (float) $settings['rate_multiplier'] + (float) $settings['rate_markup'], 2);
            // print_r('id: ' . $id . ' | ');
            // print_r(array_key_exists($id, $total_rates) ? 'true ' : 'false ');

            if (!array_key_exists($id, $total_rates)) {
              //print_r('making new shipping rate object | ');

              // Make a new shipping rate object.
              $rate = array(
                // Populate our shipping rate object.
                // A unique ID for the shipping rate.
                'id'       => $id,

                // A label to display what the rate is called.
                'label'    => sprintf(esc_html__('Canada Post %1$s (approx. %2$d business %3$s)', 'scpwc'), $price_quote->{'service-name'}, $transit_time, _n('day', 'days', $transit_time, 'scpwc')),

                // The shipping rate returned by Canada Post with our rate multiplier and markup from the settings applied.
                'cost'     => $cost,

                // Calculate tax per_order or per_item.
                'calc_tax' => 'per_order',
              );

              // Add the rate object to our array of rates for sorting.
              $total_rates[$id] = $rate;
              //print_r('current total rates | ');
              //print_r($total_rates);
            } else {
              //print_r('modifying existing shipping rate | ');

              // Modify the existing shipping rates
              $total_rates[$id]['cost'] += $cost;
            }
          }
        }
      }

      // If we made an error somewhere in our API query, display the error code and a helpful message.
      if ($xml->{'messages'}) {
        $messages = $xml->{'messages'}->children('http://www.canadapost.ca/ws/messages');

        foreach ($messages as $message) {
          print_r('Error Code: ');
          print_r($message->code);

          print_r('Error Msg: ');
          print_r($message->description);
        }
      }
    }
  }

  // Sort the rates from cheapest to most expensive.
  usort($total_rates, function ($a, $b) {
    if ((float) $a['cost'] == (float) $b['cost']) {
      return 0;
    }

    return ((float) $a['cost'] < (float) $b['cost']) ? -1 : 1;
  });

  foreach ($total_rates as $rate) {
    $that->add_rate($rate);
  }
}
?>
