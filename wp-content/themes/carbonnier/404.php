<?php get_template_part('templates/page', 'header'); ?>

<div class="alert alert-warning">
  <?php _e('Sorry, but the page you were trying to view does not exist.', 'sage'); ?>
</div>

<h2><a href="<?php bloginfo('');?>/cart">Accédez à votre panier</a></h2>

<?php get_search_form(); ?>
