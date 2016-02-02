<footer class="content-info">

  
  <div class="container">
    <?php dynamic_sidebar('sidebar-footer'); ?>
  </div>

  <div class="container footer-fixed">
    <div class="row">
      <div class="col-md-offset-2 col-md-10 f11">
        <h1>A PROPOS</h1>
      </div>
    </div>
    <div class="row ft">
      <div class="col-md-offset-2 col-md-2">
        <img src="<?php bloginfo('template_directory'); ?>/img/yvc.jpg" alt="photo" class="img-responsive yvc">
      </div>
      <div class="col-md-2 f1">
        <h2>A propos de </h2>
        <h2>Yves Carbonnier </h2>
        <h2>et de sa boutique en ligne</h2>
      </div>
      <div class="col-md-2 f3">
        <h1>BOUTIQUE</h1>
        <div class="footer3">
        <a href="<?php bloginfo( 'url' ); ?>">Boutique</a><br>
        <a href="<?php bloginfo( 'url' ); ?>/categories">Catégories</a><br>
        <a href="<?php bloginfo( 'url' ); ?>/cart">Panier</a><br>
        </div>
      </div>
      <div class="col-md-2 f3">
        <h1>INFORMATION</h1>
        <div class="footer3">
        <a href="<?php echo get_page_link(69); ?>">A propos</a><br>
        <a href="<?php echo get_page_link(658); ?>">GCV</a><br>
        <a href="<?php echo get_page_link(69); ?>">Mentions légales</a><br>
        <a href="<?php bloginfo( 'url' ); ?>">Crédits</a><br>
        </div>
      </div>
      <div class="col-md-2 f3">
        <h1>LIENS</h1>
        <div class="footer3">
        <a href="http://repasmagique.fr/">Repas magique</a><br>
        <a href="http://mg-animations-ludiques.com/">MGAnimations</a><br>
        <a href="http://www.dfitv.fr/">DFITV</a><br>
        <a href="http://www.museedelamagie.com/">Musée de la magie</a><br>
        </div>
      </div>
    </div>
  </div>
</footer>

<div class="footer2">
  <div class="text-center">
    <h6>© 2015 Boutique Yves Carbonnier</h6>
  </div>
</div>


<script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
</html>