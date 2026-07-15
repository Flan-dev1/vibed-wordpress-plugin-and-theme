<?php
if (! defined('ABSPATH')) {
  exit;
}

if (! function_exists('elementor_theme_do_location') || ! elementor_theme_do_location('footer')) :
?>
  <footer class="site-footer">
    <div class="footer-container">
      <div class="footer-column">
        <h2>Meily Properties</h2>
        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/remax.webp'); ?>" alt="remax-logo">
      </div>

      <div class="footer-column">
        <p style="font-weight:500;margin-bottom:24px">Cities</p>
        <div class="footer-cities">
          <a href="">Makati</a>
          <a href="">Manila</a>
          <a href="">BGC</a>
          <a href="">Muntinlupa</a>
          <a href="">Alabang Village</a>
          <a href="">Hillsborough</a>
          <a href="">Alabang Hills</a>
          <a href="">Parañaque</a>
          <a href="">Sucat</a>
          <a href="">Better Living</a>
          <a href="">BF Homes</a>
          <a href="">Siargao</a>
          <a href="">Palawan</a>
          <a href="">El Nido</a>
          <a href="">Coron</a>
          <a href="">Puerto Prinsesa</a>
          <a href="">Tagaytay</a>
          <a href="">Cavite</a>
          <a href="">Batangas</a>
        </div>
        <hr>
        <div class="footer-links">
          <div class="link-column">
            <p style="font-weight:500">Services</p>
            <a href="">Buy</a>
            <a href="">Rent</a>
            <a href="">Sell</a>
          </div>
          <div class="link-column">
            <p style="font-weight:500">Who we are</p>
            <a href="">About</a>
            <a href="">Testimonials</a>
          </div>
          <div class="link-column">
            <p style="font-weight:500">Phone</p>
            <a href="">+12 345 6789</a>
          </div>
          <div class="link-column">
            <p style="font-weight:500">Email</p>
            <a href="">email@gmail.com</a>
          </div>
        </div>

        <div class="footer-bottom">
          <p>Copyright &copy; <?php echo date('Y'); ?> Meily Properties. All Rights Reserved</p>
          <p class="divider">|</p>
          <p> Privacy Policy </p>
          <p class="divider">|</p>
          <p> Website Design by <span style="font-weight:700">StarFlan</span></p>
        </div>
      </div>

    </div>


  </footer>
<?php endif; ?>

<?php wp_footer(); ?>

<?php dynamic_sidebar('footer-widget-area'); ?>

</body>

</html>