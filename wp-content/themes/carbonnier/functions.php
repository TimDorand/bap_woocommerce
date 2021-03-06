<?php
/**
 * Sage includes
 *
 * The $sage_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 * @link https://github.com/roots/sage/pull/1042
 */
$sage_includes = [
  'lib/assets.php',    // Scripts and stylesheets
  'lib/extras.php',    // Custom functions
  'lib/setup.php',     // Theme setup
  'lib/titles.php',    // Page titles
  'lib/wrapper.php',   // Theme wrapper class
  'lib/customizer.php' // Theme customizer
];

foreach ($sage_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);

add_filter('show_admin_bar', '__return_false');

remove_action ('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

if ( function_exists('register_sidebar') ) {
  register_sidebar(array(
      'name' => 'header_right',
      'before_widget' => '<div class="header_right" id="%1$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3>',
      'after_title' => '</h3>',
  ));
}