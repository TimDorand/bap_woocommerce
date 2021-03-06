<?php
/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product.php
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) ) {
	$woocommerce_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) ) {
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
}

// Ensure visibility
if ( ! $product || ! $product->is_visible() ) {
	return;
}

// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array();
if ( 0 == ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 == $woocommerce_loop['columns'] ) {
	$classes[] = 'first';
}
if ( 0 == $woocommerce_loop['loop'] % $woocommerce_loop['columns'] ) {
	$classes[] = 'last';
}
$classes[] = 'hsov';
?>
<li <?php post_class( $classes ); ?>>

	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>


		<?php
			/**
			 * woocommerce_before_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			?>
             <div class="hov">
			<?php
			do_action( 'woocommerce_before_shop_loop_item_title' );
			$link =  "checkout/?add-to-cart=".$product->id;

			?>
					<!--<div class="content-hover">
						<a href="<?php /*the_permalink(); */?>">
							<img src="<?php /*bloginfo('template_directory'); */?>/img/logo.png"  class="iconeseyes icon-custom">
						</a>
						<a href="<?php /*echo $link; */?>">
						<img src="<?php /*bloginfo('template_directory'); */?>/img/logo1.png"  class="iconespanier icon-custom">
						</a>
					</div>-->

				 	<div class="content-hover">
						<a href="<?php the_permalink(); ?>">
							<div class="icon-view"></div>
						</a>
						<a href="<?php echo $link; ?>">
							<div class="icon-cart"></div>
						</a>
					</div>
			</div>

			<?php
			/**
			 * woocommerce_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_template_loop_product_title - 10
			 */
			echo '	<a style="color:black" href="';
			the_permalink();
			echo '">';
			do_action( 'woocommerce_shop_loop_item_title' );
			echo '</a>';
			/**
			 * woocommerce_after_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			do_action( 'woocommerce_after_shop_loop_item_title' );
		?>

	</a>

	<?php

		/**
		 * woocommerce_after_shop_loop_item hook
		 *
		 * @hooked woocommerce_template_loop_add_to_cart - 10
		 */
		do_action( 'woocommerce_after_shop_loop_item' );

	?>

</li>
