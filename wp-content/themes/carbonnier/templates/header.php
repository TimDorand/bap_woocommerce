
<body>

<nav class="navbar navbar-default navbar-fixed-top">
  <div class="navbar-header">
    <a href="<?= esc_url(home_url('/')); ?>"><img src="<?php bloginfo('template_directory');?>/img/logofinal.png" class="logomobile" style="float:left;margin-left:10%;" width="150" alt="logo yc"></a>

    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
  </div>
  <a href="<?= esc_url(home_url('/')); ?>"><img src="<?php bloginfo('template_directory');?>/img/logofinal.png" class="logoprincipal" style="float:left;margin-left:10%;" width="150" alt="logo yc"></a>

  <div id="navbar" class="navbar-collapse collapse">

    <ul style="margin-left:-20%;" class="nav navbar-nav navbar-center">
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
          <li><a href="<?= esc_url(home_url('/')); ?>categorie-produit/video-a-telecharger">VIDEOS</a></li>
        </ul>
      </li>

      <li><a href="<?= esc_url(home_url('/')); ?>contact">CONTACT</a></li>
<!--      <a href="#"><i class="fa fa-search" style="font-size:20px;color:white;"></i></a></li>-->
      <li id="icon-cart"><a href="<?= esc_url(home_url('/')); ?>cart"><i class="fa fa-shopping-cart" style="font-size:20px;margin-left:5px;"></i></a></li>
    </ul>

    </div>
</nav>
