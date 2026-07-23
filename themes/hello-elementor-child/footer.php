<?php
if (! defined('ABSPATH')) {
  exit;
}

if (! function_exists('elementor_theme_do_location') || ! elementor_theme_do_location('footer')) :
  $city_posts = get_posts([
    'post_type'      => 'sf_city',
    'post_status'    => 'publish',
    'posts_per_page' => 21,
    'orderby'        => 'title',
    'order'          => 'ASC',
  ]);

  $cities = array_map(
    static function ($city) {
      return [
        'id'   => (int) $city->ID,
        'name' => get_the_title($city),
        'url'  => add_query_arg('city-id', (int) $city->ID, home_url('/properties/')),
      ];
    },
    $city_posts
  );
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
          <?php
          foreach ($cities as $city) {
          ?>
            <a href="<?php echo esc_url($city['url']) ?>"><?php echo esc_html($city['name']) ?></a>
          <?php
          }
          ?>
        </div>
        <hr>
        <div class="footer-links">
          <div class="link-column">
            <p style="font-weight:500">Services</p>
            <a href="/properties/?category=for-sale">Buy</a>
            <a href="/properties/?category=for-rent">Rent</a>
            <a href="/sell">Sell</a>
          </div>
          <div class="link-column">
            <p style="font-weight:500">Who we are</p>
            <a href="/about">About</a>
            <a href="/testimonials">Testimonials</a>
          </div>
          <div class="link-column">
            <p style="font-weight:500">Phone</p>
            <a href="tel:+12 345 6789">+12 345 6789</a>
          </div>
          <div class="link-column">
            <p style="font-weight:500">Email</p>
            <a href="mailto:email@gmail.com">email@gmail.com</a>
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