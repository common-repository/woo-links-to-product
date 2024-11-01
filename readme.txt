=== WooCommerce Links to Product ===

Contributors: ernestortiz
Plugin URI: https://github.com/ernestortiz/woocommerce-woocommerce-links-to-product
Donate link: http://paypal.me/ernestortiz
Tags: woocommerce, retailer, product links
Requires at least: 3.0.1
Tested up to: 4.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add links to your woocommerce products (in order to consider many retailers, extend product information, etc.). 


== Description ==

With this plugin you can link your product to an external page, or just open a modal containing a note, an inline content, or a content in other page of the same domain. These links can be displayed on regular woocommerce's product positions (after add to cart button, before add to cart button, etc.), or you use a shortcode instead to decide where those links appears.


== Installation ==

1. Upload unzipped plugin directory to the /wp-content/plugins/ directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.


== Frequently Asked Questions ==

= How can use this plugin in a widget? =

Well, shortcodes can be used as widgets using the text widget. Just write the shortcode on the text widget content; for example <em>[wclinks2product]</em>

= Where can I add retailers? =

Please, go to the options page, to add retailer / anchors. 
Note that this plugin is not only for link to a retailer; you can also link to any external webpage, as well as show a modal containing information.

= How can I get the modal content? =

The url that you write on the field 'Link to the product' decides the behaviour when you click on the button. For example:

amazon.es
This is an external link; so it will open the corresponding page on a new window.

&#35;extra-info
In this case, a modal will pop up, showing the inner content on the inline element width id 'extra-info'. Of course, you can use here a class name instead of an id. For example:
    
.info.extra
The plugin will try to hide those inline elements that will appears in the modal, in case you forgot to do that.

&#35;
If this is the case, the plugin popup a modal containing an html note that you wrote. Previously, you should go to the plugin options and add the extra field of "Note", and of course write some note; otherwise, the button does not appear.

http://samedomain.com/otherpage.php #info
In this case, the content of a modal is taking from the element with id 'info' which is inside other webpage (on the same domain). Please, note the space before '#info'

= How can I change the look of the modal, buttons, etc. =

Please, feel free of change the styles on the css files of the plugin, until you reach what you want. Remember to write your style on your theme (for example) or elsewhere, or it will be erased when updating the plugin.


== Screenshots ==

1. The options page (retailer / Anchors options).
2. The options page (Link options).
3. The product page.


== Donations ==

If you want to help me in writing more code or better poetry, please invite me to a beer (or coffee, maybe) by sending your thanks to http://paypal.me/ernestortiz. Thanks in advance.


== Changelog ==

= 1.0.0 =
* Stable Release
