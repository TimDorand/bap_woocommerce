=== WooCommerce Products Filter ===
Contributors: RealMag777
Donate link: http://codecanyon.net/item/woof-woocommerce-products-filter/11498469?ref=realmag777
Tags: woocommerce products filter, woocommerce product filter, products filter, ajax products filter, filter for woocommerce, filter, shortcode, widget, woocommerce, products, ajax
Requires at least: 4.1.0
Tested up to: 4.4.0
Stable tag: 1.1.3.1

WooCommerce Products Filter – Quick&Easy products filter

== Description ==

WooCommerce Products Filter – is a plugin that allows you filter products by products categories, products attributes, products tags, products custom taxonomies.
Supports latest version of the WooCommerce plugin.

ATTENTION: before update from v.1.1.2 to v.1.1.3 read this please http://www.woocommerce-filter.com/migration-v-2-1-2-or-1-1-2-and-lower-to-2-1-3-or-1-1-3-and-higher/

### The Plugin Features:

* Shortcode&Widget -> [woof]
* Products shortcode [woof_products per_page=8 columns=3 is_ajax=1 taxonomies=product_cat:9]
* Uses native woocommerce API only
* Products searching by AJAX
* Dynamic products recount
* You can show your taxonomies as: radio, checkbox, drop-down, multi-drop-down and (color,label,hierarchy drop-down) in the premium version
* Different skins for radio and checkboxes in the plugin settings
* Simple options
* Compatible with WooCommerce Currency Switcher - https://wordpress.org/plugins/woocommerce-currency-switcher/
* Compatible with WooCommerce Brands - http://codecanyon.net/item/woocommerce-brands/8039481?ref=realmag777
* WPML compatibility
* Demo site is: http://demo.woocommerce-filter.com
* Documentation: http://www.woocommerce-filter.com/documentation/
* Premium version: http://codecanyon.net/item/woof-woocommerce-products-filter/11498469?ref=realmag777


https://www.youtube.com/watch?v=jZPtdWgAxKk


== Installation ==
* Download to your plugin directory or simply install via Wordpress admin interface.
* Activate.
* Set product taxonomies in the plugin settings tab of the wocommerce settings page
* Drop the WooCommerce Products Filter widget in the sidebar.
* Use.


== Frequently Asked Questions ==

Q: Where can I see demo?
R: http://demo.woocommerce-filter.com

Q: Where can I get the Premium version of WOOF
R: http://codecanyon.net/item/woof-woocommerce-products-filter/11498469?ref=realmag777

Q: How to create a custom taxomomy?
R: Use this plugin https://wordpress.org/plugins/custom-post-type-ui/

Q: FAQ?
R: http://www.woocommerce-filter.com/category/faq/

Q: Documentation?
R: http://www.woocommerce-filter.com/documentation/ and http://www.woocommerce-filter.com/interaction/


== Screenshots ==
1. The plugin settings
2. The plugin settings
3. The plugin settings
4. The plugin settings
5. The plugin settings

== Changelog ==

= 1.1.3.1 =
* Hot js fix: https://wordpress.org/support/topic/variable-products-not-working-3

= 1.1.3 =
* ATTENTION: before update from v.1.1.2 to v.1.1.3 read this please http://www.woocommerce-filter.com/migration-v-2-1-2-or-1-1-2-and-lower-to-2-1-3-or-1-1-3-and-higher/
* Fixed bugs from customers
* New wp filter: $wr = apply_filters('woof_products_query', $wr); in [woof_products]
* New attributes added: [woof tax_only='pa_color,pa_size' items_only='by_text,by_author']
* http://www.woocommerce-filter.com/documentation/#!/hierarchy-drop-down
* Color type improved, now its possible set background image too
* Search by text: by excerpt, by content OR excerpt, by title OR content OR excerpt
* Added new shortcode: [woof_text_filter]
* Added new shortcode: [woof_author_filter]
* Added new shortcode: [woof_search_options]
* Improved shortcode: [woof_price_filter type="slider"] //slider,select
* Improved shortcode: [woof_products behaviour='recent' per_page=12 columns=3]
* Improved shortcode: [woof redirect="xxx" autosubmit=1]
* Improved shortcode: [woof redirect="http://www.dev.woocommerce-filter.com/test-all/" autosubmit=1 ajax_redraw=1 is_ajax=1 tax_only="locations" by_only="none"] - new attributes - tax_only,by_only,redirect
* Disable swoof influence option
* Custom front css styles file link option
* Additional text in the widget optionally
* Additional options in the widget optionally
* Custom extensions possibility implemented
* Show helper button option
* Old v.1.1.2: http://www.woocommerce-filter.com/wp-content/uploads/2015/12/woocommerce-products-filter-112.zip


= 1.1.2 =
* Fixed minor issues from customers
* Added: Search by SKU - premium only
* Added: Filter by price as drop-down - premium only
* Added shortcode: [woof_title_filter placeholder="custom placeholder text"]
* the color description selectable so that it can be highlighted and pasted into colour selector by the term description textarea
* Added condition attribute 'taxonomies': [woof taxonomies=product_cat:9 sid="auto_shortcode"][woof_products is_ajax=1 per_page=8 taxonomies=product_cat:9]
* Added: the "eyeball" search icon image - can be changed in the plugin settings -> tab Miscellaneous
* Added: dynamic recount cron cache periods of cleaning
* Added: option - Hide woof top panel buttons
* Added: option - storage type: session or transient
* PHP code optimization
* Added some features to API: http://www.woocommerce-filter.com/documentation/#!/section_6

= 1.1.1.1 =
* Hot fix update for compatibility with WordPress 4.3

= 1.1.1 =
* Some little bugs fixed + 1 strict notice
* Added compatibility for WOOCS 2.0.9 and 1.0.9

= 1.1.0 =
* Too much improvements
* AJAX mode added!!

= 1.0.7 =
* Too much improvements
* Premium version on codecanyon: http://codecanyon.net/item/woof-woocommerce-products-filter/11498469?ref=realmag777

= 1.0.5 =
* Heap of bugs from customers is fixed
* Possibility to add a FILTER button, so the plugin dont search automatically until someone click on Filter
* New option 'Use chosen' - you can switch off/on this js lib from now
* In stock only checkbox on the front

= 1.0.4 =
Partly WPML compatibility + some little fixes

= 1.0.3 =
Adopted to woocommerce 2.3.2 and higher for products attributes filtering

= 1.0.2 =
Very important 1 bug fixed with Fatal Error. Corrected work with the native price filter (dynamic recount)

= 1.0.1 =
Dynamic products recount

= 1.0.0 =
Plugin release. Operate all the basic functions.



== License ==

This plugin is copyright pluginus.net ɠ2015 with [GNU General Public License][] by realmag777.

This program is free software; you can redistribute it and/or modify it under
the terms of the [GNU General Public License][] as published by the Free
Software Foundation; either version 2 of the License, or (at your option) any
later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY. See the GNU General Public License for more details.

  [GNU General Public License]: http://www.gnu.org/copyleft/gpl.html


== Upgrade Notice ==
Old v.1.1.2: http://www.woocommerce-filter.com/wp-content/uploads/2015/12/woocommerce-products-filter-112.zip

