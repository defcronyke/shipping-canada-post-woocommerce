<?php
// To be included in shipping-canada-post-woocommerce.php
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// This is where the API request to Canada Post is made to get the shipping rates.
function get_cp_rates($service_url, $xml_request, $username, $password) {
  $headers = array(
    'content-type'  => 'application/vnd.cpc.ship.rate-v3+xml',
    'accept'        => 'application/vnd.cpc.ship.rate-v3+xml',
    'authorization' => 'Basic ' . base64_encode($username . ':' . $password),
  );

  $args = array(
    'method'      => 'POST',
    'timeout'     => 10,
    'redirection' => 5,
    'httpversion' => 1.0,
    'blocking'    => true,
    'headers'     => $headers,
    'body'        => $xml_request,
    'cookies'     => array(),
  );

  $response             = wp_remote_get($service_url, $args);
  $response_status_code = $response['response']['code'];
  $response_status_msg  = $response['response']['message'];

  // Output the HTTP status code if it isn't 200.
  if ($response_status_code != 200) {
    print_r('HTTP Response Status: ');
    print_r($response_status_code . ' ' . $response_status_msg);
  }

  return $response;
}
?>
