=== Shipping Canada Post WooCommerce ===
Contributors: defcronyke, girlboybot
Donate link: https://eternalvoid.net
Tags: woocommerce, shipping, shipping rates, canadapost, canada, post, canada post
Requires at least: 4.0.1
Tested up to: 5.0
Requires PHP: 5.6
Stable tag: 0.1.3
License: GPLv3 or later License
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Use Canada Post shipping with WooCommerce. Provides some of the premium features from other similar plugins for free.

== Description ==
This is a free WooCommerce plugin which allows you to calculate approximate shipping costs for shipping with Canada Post.

It uses an approximated volumetric packing algorithm to figure out how best to pack the products in the shopping cart into user-defined boxes. 

It supports individual items having flat rates if needed.

You can mark up the shipping rates by a percentage and/or a fixed amount.

You can set a handling fee.

You can specify that certain items should stack together, saving space in the box.

Gives a more accurate estimate than any other free WooCommerce Canada Post shipping plugin we could find.

== Installation ==
1. Install and enable through the WordPress dashboard's "Plugins -> Add New" section.
2. The settings will be available in "WooCommerce -> Settings -> Shipping -> Shipping Canada Post WooCommerce".
3. Make some shipping zones, and add "Shipping Canada Post WooCommerce" as the shipping method.

== Frequently Asked Questions ==
= How do I make new boxes? =
Make a new shipping class with a slug that starts with "box-". Then go to the plugin settings page and there will be a new section to fill in the box's properties.

= How do I make certain items have a flat rate shipping cost? =
Make a new shipping class with a slug that starts with "flat-rate-". Then assign that shipping class to a product. There will now be a new field in the plugin settings page which allows you to set the flat rate for everything with that new shipping class.

= How do I set certain items as stackable? =
Make a new global product attribute with the slug "stackable", and add an item to it for each product that can stack. For example, if you have two different sizes of hats that can each stack with their own size, you would make two items, and maybe call them "small hat 1" and "medium hat 1". Next, edit a product, go to "Product data -> Attributes". You should see the attribute name in bold there of the global attribute you made with the slug "stackable". If not, you can add it from the "Custom product attribute" dropdown menu. Next, expand the attribute by clicking on its name in bold. Now simply add one of the values you made to the Value(s) box, and that item will now stack with any other item that has that value. Note that only one stackable value is currently supported per item.

== Screenshots ==
1. The plugin settings page.

== Changelog ==
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
