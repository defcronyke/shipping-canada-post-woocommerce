<?php
/*
 * Plugin Name: Canada Post Shipping WooCommerce
 * Description: Use Canada Post for shipping with WooCommerce. Aims to provide some of the premium features from other similar plugins for free.
 * Version: 1.0.0
 * Author: Daphne Volante and Jeremy Carter
 * Author URI: https://eternalvoid.net
 * WC requires at least: 3.5.0
 * WC tested up to: 3.5.0
 * Text Domain: cpswc
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// Exit if WooCommerce isn't active.
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
  exit;
}

// Shipping Method init function.
function cpswc_init() {
  // Only define class if it isn't defined yet.
  if (!class_exists('WC_CPSWC_Shipping_Method')) {

    // Define the new shipping method class.
    class WC_CPSWC_Shipping_Method extends WC_Shipping_Method {
      /**
       * Constructor for the shipping class. Supports shipping zones.
       *
       * @access public
       * @return void
       */
      public function __construct($instance_id = 0) {
        // Support multiple instances of the class to support shipping zones.
        $this->instance_id = absint($instance_id);

        // The shipping method ID.
        $this->id = 'cpswc';

        // The shipping method title.
        $this->method_title = __('Canada Post Shipping WooCommerce', 'cpswc');

        // The shipping method description.
        $this->method_description = __('Use Canada Post for shipping with WooCommerce. Aims to provide some of the premium features from other similar plugins for free.', 'cpswc');

        // Set to 'including' if we want our $this->countries array below to be a whitelist, blacklisting all others implicitly.
        $this->availablity = 'including';

        // The list of countries that the shipping method is available for.
        $this->countries = array('CA');

        // Features that the shipping method supports.
        $this->supports = array(
          'shipping-zones',
          'settings',
          'instance-settings',
          'instance-settings-modal',
        );

        // Run some initialization, after this we can access fields from the settings.
        $this->init();

        // Is the shipping method enabled?
        $this->enabled = 'yes';

        // Another field which wants the shipping method title.
        $this->title = $this->method_title;
      }

      /**
       * Init your settings
       *
       * @access public
       * @return void
       */
      function init() {
        // Load the settings API
        $this->init_form_fields();
        $this->init_settings();

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
      }

      /**
       * Initialise Settings Form Fields
       */
      function init_form_fields() {
        // The fields to display on the WooCommerce settings page.
        // This will show in a new tab called "Canada Post Shipping WooCommerce" in the Shipping section.
        $this->form_fields = array(
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

          'dev_mode'            => array(
            'title'       => __('Development Mode', 'cpswc'),
            'type'        => 'checkbox',
            'description' => __('Use the Canada Post development sandbox for testing. Turn this off for production.', 'cpwsc'),
            'default'     => 'yes',
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

        // Copy the global settings to the shipping zone instance settings area.
        // TODO: Maybe we should only copy the global settings over if the instance settings don't exist yet.
        $this->instance_form_fields = $this->form_fields;
      }

      /**
       * calculate_shipping function.
       *
       * @access public
       * @param mixed $package
       * @return void
       */
      public function calculate_shipping($package = array()) {
        // Save the settings so we can use them in a closure later.
        $settings = $this->settings;

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
        // If true, use the Canada Post development sandbox API and your sandbox credentials,
        // otherwise use the production API.
        $dev_mode = $settings['dev_mode'] == 'yes' ? true : false;

        // API customer number
        $mailed_by = $settings['api_customer_number'];

        // API username
        $username = $dev_mode ? $settings['api_username_dev'] : $settings['api_username'];

        // API password
        $password = $dev_mode ? $settings['api_password_dev'] : $settings['api_password'];

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
        $expected_mailing_date = (new DateTime(date('Y-m-d')))->modify('+ ' . $settings['handling_time'] . ' Weekday')->format('Y-m-d');

        //connect to CP API
        //get rates based on total weight of items plus weight of box, and size of box.
        //return rates and day estimates

        // REST URL

        // The sandbox server domain prefix.
        $ctd = $dev_mode ? 'ct.' : '';

        // The Canada Post API shipping quotes endpoint.
        $service_url = 'https://' . $ctd . 'soa-gw.canadapost.ca/rs/ship/price';

        // Add the customer number field to the request if we are requesting commercial rates.
        $customer_number_tmpl = $quote_type == 'commercial' ? "<customer-number>$mailed_by</customer-number>" : '';

        $signature_required_tmpl = $this->settings['signature_required'] == 'no' ? '' : <<<XML
<option>
	<option-code>SO</option-code>
</option>
XML;

        $buy_insurance_tmpl = $this->settings['buy_insurance'] == 'no' ? '' : <<<XML
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
        $xml_request = <<<XML
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

        // Make the HTTP request to the API server with curl.
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
                  'id'       => $this->id . '-' . str_replace(' ', '-', $price_quote->{'service-name'}),

                  // A label to display what the rate is called.
                  'label'    => sprintf(esc_html__('Canada Post %1$s (approx. %2$d business %3$s)', 'cpswc'), $price_quote->{'service-name'}, $transit_time, _n('day', 'days', $transit_time, 'cpswc')),

                  // The shipping rate returned by Canada Post with our rate multiplier from the settings applied.
                  'cost'     => round((float) $price_quote->{'price-details'}->{'due'} * (float) $settings['rate_multiplier'], 2),

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
                $this->add_rate($rate);
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

        // Add the handling fee from our settings.
        function cpswc_handling_fee($settings) {
          global $woocommerce;

          if (is_admin() && !defined('DOING_AJAX')) {
            return;
          }

          // Get handling fee from settings.
          $fee = $settings['handling_fee'];

          // Add the handling fee if it isn't 0.
          if ((float) $fee != 0.0) {
            $woocommerce->cart->add_fee(__('Handling', 'cpswc'), $fee, true, '');
          }
        }

        // Register the handling fee with WooCommerce.
        add_action('woocommerce_cart_calculate_fees', function () use ($settings) {
          cpswc_handling_fee($settings);
        });
      }
    }
  }
}

// Register shipping method class with WooCommerce.
add_action('woocommerce_shipping_init', 'cpswc_init');

// Define the shipping method key and value, like $methods['key'] = 'value'
// where 'key' is the $this->id that you specify in the constructor,
// and 'value' is the name of the shipping method class.
function add_cpswc_shipping_method($methods) {
  $methods['cpswc'] = 'WC_CPSWC_Shipping_Method';
  return $methods;
}

// Register shipping method with WooCommerce.
add_filter('woocommerce_shipping_methods', 'add_cpswc_shipping_method');
