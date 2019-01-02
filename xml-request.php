<?php
// To be included in shipping-canada-post-woocommerce.php
namespace shipping_canada_post_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// Build the XML Request body.
function xml_request($_package, $settings, $country, $postal_code, $dev_mode, $box) {
  // Get box size based on cart items.
  $box_l           = $box->outer_l;
  $box_w           = $box->outer_w;
  $box_h           = $box->outer_h;
  $box_weight      = $box->empty_weight;
  $contents_weight = $box->get_products_weight();
  //print_r('contents weight: ' . $contents_weight . ' | ');
  $total_price = $box->get_products_value();

  // The total weight of the shipment.
  $weight = max(round($contents_weight + $box_weight, 2), 0.01);

  // API customer number.
  $mailed_by = $settings['api_customer_number'];

  // Postal code you are sending from.
  $origin_postal_code = strtoupper(str_replace(' ', '', $settings['origin_postal_code']));

  // Postal code you are sending to.
  $postal_code = str_replace(' ', '', $_package['destination']['postcode']);

  // Commercial or counter rates. Select commercial to use your customer number to get discounted rates
  // and more shipping methods. You can mark up the prices later if you don't want to give the customer
  // the discounted rates that you're getting. Set it to counter to return retail rates, but there will
  // sometimes be less shipping methods available.
  $quote_type = $settings['commercial_rates'] == 'yes' ? 'commercial' : 'counter';

  // Add some extra handling time for the order. It will cause the API to increase its delivery timeframe estimates.
  $expected_mailing_date = (new \DateTime(date('Y-m-d')))->modify('+ ' . $settings['handling_time'] . ' Weekday')->format('Y-m-d');

  // Set the destination.
  $destination = '';

  switch ($country) {
  case 'CA':
    $destination = <<<XML
<destination>
  <domestic>
    <postal-code>{$postal_code}</postal-code>
  </domestic>
</destination>
XML;
    break;

  case 'US':
    $destination = <<<XML
<destination>
  <united-states>
    <zip-code>{$postal_code}</zip-code>
  </united-states>
</destination>
XML;
    break;

  default:
    $destination = <<<XML
<destination>
  <international>
    <country-code>{$country}</country-code>
  </international>
</destination>
XML;
    break;
  }

  // Add the customer number field to the request if we are requesting commercial rates.
  $customer_number_tmpl = $quote_type == 'commercial' ? "<customer-number>$mailed_by</customer-number>" : '';

  // Add whether or not a signature is required.
  $signature_required_tmpl = $settings['signature_required'] == 'no' ? '' : <<<XML
<option>
	<option-code>SO</option-code>
</option>
XML;

  // Add insurance if desired.
  $buy_insurance_tmpl = $settings['buy_insurance'] == 'no' ? '' : <<<XML
<option>
	<option-code>COV</option-code>
	<option-amount>{$total_price}</option-amount>
</option>
XML;

  $options_tmpl = $signature_required_tmpl == '' && $buy_insurance_tmpl == '' ? '' : <<<XML
<options>
	{$signature_required_tmpl}
	{$buy_insurance_tmpl}
</options>
XML;

  // The API request body.
  return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mailing-scenario xmlns="http://www.canadapost.ca/ws/ship/rate-v3">
	{$customer_number_tmpl}
	<parcel-characteristics>
		<weight>{$weight}</weight>
		<dimensions>
			<length>{$box_l}</length>
			<width>{$box_w}</width>
			<height>{$box_h}</height>
		</dimensions>
	</parcel-characteristics>
	<origin-postal-code>{$origin_postal_code}</origin-postal-code>
	{$destination}
	<quote-type>{$quote_type}</quote-type>
	<expected-mailing-date>{$expected_mailing_date}</expected-mailing-date>
	{$options_tmpl}
</mailing-scenario>
XML;
}
?>
