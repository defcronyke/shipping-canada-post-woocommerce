<?php
// To be included in canada-post-shipping-woocommerce.php
namespace canada_post_shipping_woocommerce;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

function xml_request($package, $settings, $dev_mode) {
  //get box size based on cart items
  // TODO: Select correct set of boxes from our list of boxes instead of using this hard-coded fake box.
  $box_l      = 30.0;
  $box_w      = 20.0;
  $box_h      = 20.0;
  $box_weight = 0.01;

  //get weight from $package
  // TODO: Change this to get the weight of all the contents of each box,
  // and store the weights for each in an array.
  $contents_weight = 0.0;
  $total_price     = 0.0;

  foreach ($package['contents'] as $item) {
    $contents_weight += $item['data']->get_weight();
    $total_price += $item['data']->get_price();
  }

  // The total weight of the shipment.
  // TODO: This may need to be an array if we are shipping multiple boxes.
  $weight = $contents_weight + $box_weight;

  //get saved login information

  // API customer number
  $mailed_by = $settings['api_customer_number'];

  // Postal code you are sending from.
  $origin_postal_code = str_replace(' ', '', $settings['origin_postal_code']);

  // Postal code you are sending to.
  $postal_code = str_replace(' ', '', $package['destination']['postcode']);

  // Commercial or counter rates. Select commercial to use your customer number to get discounted rates
  // and more shipping methods. You can mark up the prices later if you don't want to give the customer
  // the discounted rates that you're getting. Set it to counter to return retail rates, but there will
  // sometimes be less shipping methods available.
  $quote_type = $settings['commercial_rates'] == 'yes' ? 'commercial' : 'counter';

  // Add some extra handling time for the order. It will cause the API to increase its delivery timeframe estimates.
  $expected_mailing_date = (new \DateTime(date('Y-m-d')))->modify('+ ' . $settings['handling_time'] . ' Weekday')->format('Y-m-d');

  //connect to CP API
  //get rates based on total weight of items plus weight of box, and size of box.
  //return rates and day estimates

  // REST URL

  // Add the customer number field to the request if we are requesting commercial rates.
  $customer_number_tmpl = $quote_type == 'commercial' ? "<customer-number>$mailed_by</customer-number>" : '';

  $signature_required_tmpl = $settings['signature_required'] == 'no' ? '' : <<<XML
<option>
	<option-code>SO</option-code>
</option>
XML;

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
	<destination>
		<domestic>
			<postal-code>{$postal_code}</postal-code>
		</domestic>
	</destination>
	<quote-type>{$quote_type}</quote-type>
	<expected-mailing-date>{$expected_mailing_date}</expected-mailing-date>
	{$options_tmpl}
</mailing-scenario>
XML;
}

?>