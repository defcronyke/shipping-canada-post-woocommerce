<?php
// To be included in shipping-canada-post-woocommerce.php
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// The ugly curl stuff. This is where the API request to Canada Post is made,
// to get the shipping rates.
function get_cp_rates($service_url, $xml_request, $username, $password) {
  $curl = curl_init($service_url);

  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
  curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . '/third-party/cert/cacert.pem');
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $xml_request);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . $password);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/vnd.cpc.ship.rate-v3+xml', 'Accept: application/vnd.cpc.ship.rate-v3+xml'));

  $curl_response = curl_exec($curl);

  // Output curl error if there is one.
  if (curl_errno($curl)) {
    echo 'Curl error: ' . curl_error($curl) . "\n";
  }

  // Output the HTTP status code if it isn't 200.
  if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
    echo 'HTTP Response Status: ' . curl_getinfo($curl, CURLINFO_HTTP_CODE) . "\n";
  }

  // Close the connection.
  curl_close($curl);

  return $curl_response;
}
?>
