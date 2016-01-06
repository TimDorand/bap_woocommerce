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
<div class="container-fluid slider">
  <div id="myCarousel" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
      <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
      <li data-target="#myCarousel" data-slide-to="1"></li>
      <li data-target="#myCarousel" data-slide-to="2"></li>
      <li data-target="#myCarousel" data-slide-to="3"></li>
    </ol>
    <div class="carousel-inner" role="listbox">
      <div class="item active">
        <img src="<?php get_template_directory_uri()?>img/photo1.jpg" alt="Slide">
      </div>
      <div class="item">
        <img src="<?php get_template_directory_uri() ?>img/slide1.jpg" alt="Slide">
      </div>
      <div class="item">
        <img src="<?php get_template_directory_uri() ?>img/slide.jpg" alt="Slide">
      </div>
      <div class="item">
        <img src="<?php get_template_directory_uri() ?>img/photo1.jpg" alt="Slide">
      </div>
    </div>
    <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
    </a>
    <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
    </a>
  </div>
</div>


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