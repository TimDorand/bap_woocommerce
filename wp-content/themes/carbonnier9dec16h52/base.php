<?php

use Roots\Sage\Setup;
use Roots\Sage\Wrapper;

?>

<!doctype html>
<html <?php language_attributes(); ?>>
<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

<!--[if IE]>
<div class="alert alert-warning">
  <?php _e('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'sage'); ?>
</div>
<![endif]-->


<?php
do_action('get_header');
get_template_part('templates/header');
?>

<?php if(is_front_page()): ?>

<?php if ( function_exists( 'easingslider' ) ) { easingslider( 779 ); } ?>


  <nav class="navbar navbar-default navbar-fixed2">
    <div id="navbar" class="navbar-collapse collapse">
      <ul class="nav navbar-nav navbar-center2">
        <li><a href="#"><i class="fa fa-truck" style="font-size:40px;color:white;margin-right: 20px;"></i>LIVRAISON GRATUITE</a></li>
        <li><a href="#"><i class="fa fa-table" style="font-size:40px;color:white;margin-right: 20px;"></i>CARTE CADEAUX</a></li>
        <li><a href="#"><i class="fa fa-calendar "style="font-size:40px;color:white;margin-right: 20px;"></i>SOLDES REDUCTION 20%</a></li>
      </ul>
    </div>
  </nav>
<?php endif; ?>
<section>
  <div class="container-fluid">
    <div class="wrap container" role="document">
      <div class="content row">
        <main class="main">
          <div class="col-md-offset-2 col-md-8">
            <?php include Wrapper\template_path(); ?>
          </div>
        </main><!-- /.main -->
        <?php if (Setup\display_sidebar()) : ?>
          <aside class="sidebar">
            <?php include Wrapper\sidebar_path(); ?>
          </aside><!-- /.sidebar -->
        <?php endif; ?>
      </div><!-- /.content -->
    </div><!-- /.wrap -->
  </div>

<!--  NEWSLETTER MARION 9 DEC-->
  <div class="container-fluid">
    <div class="row text-center news">
      <form action="https://campmon.createsend.com/t/y/s/z/" method="post" id="subForm">
        <h2>NEWSLETTER</h2>
        <?php echo do_shortcode("[contact-form-7 id='773' title='Newsletter']"); ?>
      </form>
    </div>
  </div>
  <!--FIN NEWSLETTER-->
  
  <?php
  do_action('get_footer');
  get_template_part('templates/footer');
  wp_footer();

  ?>


  

</section>

<!--
//////////////////////
ORIGINAL
//////////////////////
-->

<!--<div class="wrap container" role="document">
      <div class="content row">
        <main class="main">
          <?php /*include Wrapper\template_path(); */?>
        </main><!-- /.main -->
<?php /*if (Setup\display_sidebar()) : */?>
<!--<aside class="sidebar">-->
  <?php /*include Wrapper\sidebar_path(); */?>
<!--</aside><!-- /.sidebar -->
<?php /*endif; */?>
<!--</div><!-- /.content -->
<!--</div><!-- /.wrap -->
<?php
/*      do_action('get_footer');
      get_template_part('templates/footer');
      wp_footer();
    */?>

</body>