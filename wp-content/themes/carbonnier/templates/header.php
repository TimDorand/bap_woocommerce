<!--<header>
  <div class="container-fluid">
    <a class="brand" href="<?/*= esc_url(home_url('/')); */?>"><?php /*bloginfo('name'); */?></a>

    <div class="row">
      <div class="col-md-offset-6 col-md-6">
        <form id="recherche" method="post">
          <input name="saisie" type="text" placeholder="Recherche" required />
          <i class="fa fa-search"></i>

        </form>
        <div class="icons">
          <i class="fa fa-shopping-cart" style="font-size:30px;color:#c42845;"></i></a>
          <i class="fa fa-user" style="font-size:30px;color:black;"></i></a>
        </div>
      </div>
    </div>
  </div>
</header>-->
<?php
/*if (has_nav_menu('primary_navigation')) :
  wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'navbar navbar-default navbar-fixed']);
endif;*/
?>
<body>

<nav class="navbar navbar-default navbar-fixed-top">
  <div class="navbar-header">
    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
  </div>

  <div id="navbar navbartop" class="navbar-collapse collapse">
    <a href="<?= esc_url(home_url('/')); ?>"><img src="<?php bloginfo('template_directory');?>/img/logofinal.png" width="100" alt="logo yc"></a>

    <ul class="nav navbar-nav navbar-center">
      <li><a href="<?= esc_url(home_url('/')); ?>">ACCUEIL</a></li>

      <li><a href="<?= esc_url(home_url('/')); ?>categorie-produit/cartomagie/">CARTOMAGIE</a></li>

      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">LIVRES ET PDF<span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="<?= esc_url(home_url('/')); ?>categorie-produit/livres-2/">LIVRES</a></li>
          <li><a href="<?= esc_url(home_url('/')); ?>categorie-produit/pdf/">PDF</a></li>
        </ul>
      </li>

      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">TOURS & VIDEOS<span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="<?= esc_url(home_url('/')); ?>categorie-produit/tours-de-magie">TOURS</a></li>
          <li><a href="<?= esc_url(home_url('/')); ?>categorie-produit/videos-a-telecharger">VIDEOS</a></li>
        </ul>
      </li>

      <li><a href="<?= esc_url(home_url('/')); ?>contact">CONTACT</a></li>
    <li><div class="icons">
<!--      <a href="#"><i class="fa fa-search" style="font-size:20px;color:white;"></i></a></li>-->
      <li id="icon-cart"><a href="<?= esc_url(home_url('/')); ?>cart"><i class="fa fa-shopping-cart" style="font-size:20px;margin-left:5px;"></i></a></li>
    </ul>

    </div>
  </div>
</nav>



<!--
<header class="banner">
  <div class="container">
    <a class="brand" href="<?/*= esc_url(home_url('/')); */?>"><?php /*bloginfo('name'); */?></a>
    <nav class="nav-primary">
      <?php
/*      if (has_nav_menu('primary_navigation')) :
        wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']);
      endif;
      */?>
    </nav>
  </div>
</header>
-->