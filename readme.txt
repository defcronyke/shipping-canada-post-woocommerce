=== Shipping Canada Post WooCommerce ===
Contributors: defcronyke, girlboybot
Donate link: https://eternalvoid.net
Tags: woocommerce, shipping, shipping rates, canadapost, canada, post, canada post
Requires at least: 4.0.1
Tested up to: 5.0
Requires PHP: 5.6
Stable tag: 0.1.6
License: GPLv3 or later License
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Use Canada Post shipping with WooCommerce. Provides some of the premium features from other similar plugins for free.

== Description ==
This is a free WooCommerce plugin which gets very accurate shipping rate estimates for shipping with Canada Post.

It uses a volumetric packing algorithm to figure out how to pack the products in the shopping cart into user-defined boxes. 

It supports individual items having flat rates.

You can mark up the shipping rates by a percentage and a fixed amount.

You can set a handling fee.

You can specify that certain items should stack together, saving space in the box and leading to much more accurate estimates.

It shows the customer the estimated number of days it will take for the item to ship, and you can add extra handling time that will increase the estimates if needed.

Gives a more accurate estimate than any other free WooCommerce Canada Post shipping plugin we could find.

== Installation ==
1. Install and enable through the WordPress dashboard's "Plugins -> Add New" section.
2. The settings will be available in "WooCommerce -> Settings -> Shipping -> Shipping Canada Post WooCommerce".
3. Make some shipping zones, and add "Shipping Canada Post WooCommerce" as the shipping method.
4. Add some boxes and set their dimensions and weight properties on the plugin settings page (see FAQ for instructions).
5. Read the FAQ for info about more features that are available.
6. If there are any price calculation issues, try enabling "Debug mode" in "WooCommerce -> Settings -> Shipping -> Shipping options". It will bypass the cache and give a fresh price calculation every time.

== Frequently Asked Questions ==
= How do I make new boxes? =
Make a new shipping class with a slug that starts with "box-". Then go to the plugin settings page and there will be a new section to fill in the box's properties.

= How do I make certain items have a flat rate shipping cost? =
Make a new shipping class with a slug that starts with "flat-rate-". Then assign that shipping class to a product. There will now be a new field in the plugin settings page which allows you to set the flat rate for everything with that new shipping class.

= How do I set certain items as stackable? =
Make a new global product attribute with the slug "stackable", and add an item to it for each product that can stack. For example, if you have two different sizes of hats that can each stack with their own size, you would make two items, and maybe call them "small hat 1" and "medium hat 1". Next, edit a product, go to "Product data -> Attributes". You should see the attribute name in bold there of the global attribute you made with the slug "stackable". If not, you can add it from the "Custom product attribute" dropdown menu. Next, expand the attribute by clicking on its name in bold. Now simply add one of the values you made to the Value(s) box, and that item will now stack with any other item that has that value. Note that only one stackable value is currently supported per item.

= I am using the Storefront theme, and the shipping estimate section on the checkout page is too narrow. How can I fix that to make it look better? =
Make a child theme of Storefront (using instructions from the WordPress Codex, or using some plugin), and make sure you switch your active theme to the new child theme. Then add this to the child theme's style.css file:
```
/* Fix Storefront checkout table display. It was too narrow. */
table.woocommerce-checkout-review-order-table .product-name {
  width: unset;
}
```

== Screenshots ==
1. The plugin settings page.

== Changelog ==
= 0.1.6 =
* Update readme.txt because it doesn't support markdown syntax highlighting for code blocks.

= 0.1.5 =
* Update the installation instructions in readme.txt to make the setup process more clear. Add Storefront css fix to FAQ.

= 0.1.4 =
* Update the readme.txt file, and fix the default values for box dimensions in the settings.

= 0.1.3 =
* Update Changelog in the readme.txt file.

= 0.1.2 =
* Fix incorrect Version header and readme.txt file.

= 0.1.1 =
* Fix some mistakes in the readme.txt file.

= 0.1.0 =
* Initial release.

== Upgrade Notice ==
= 0.1.0 =
This is the first version.
