<?php

if (! defined('ABSPATH')) {
  exit;
}

function get_hero_featured_shortcode($atts)
{
  $atts = shortcode_atts(
    array(
      'property' => 0,
    ),
    $atts,
    'featured'
  );

  ob_start();

  $property_id = absint($atts['property']);

  if (
    !$property_id ||
    get_post_type($property_id) !== 'properties' ||
    get_post_status($property_id) !== 'publish'
  ) {
    $featured = new WP_Query(array(
      'post_type'              => 'properties',
      'post_status'            => 'publish',
      'posts_per_page'         => 1,
      'orderby'                => 'date',
      'order'                  => 'DESC',
      'fields'                 => 'ids',
      'no_found_rows'          => true,
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
      'tax_query'              => array(
        array(
          'taxonomy' => 'es_category',
          'field'    => 'slug',
          'terms'    => array('featured'),
        ),
      ),
    ));

    $property_id = isset($featured->posts[0]) ? (int) $featured->posts[0] : 0;
  }

  if (!$property_id) {
    $latest = new WP_Query(array(
      'post_type'              => 'properties',
      'post_status'            => 'publish',
      'posts_per_page'         => 1,
      'orderby'                => 'date',
      'order'                  => 'DESC',
      'fields'                 => 'ids',
      'no_found_rows'          => true,
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
    ));

    $property_id = isset($latest->posts[0]) ? (int) $latest->posts[0] : 0;
  }

  if (!$property_id) {
    ob_get_clean();
    return '';
  }

  $property = es_get_property($property_id);
  $property_url = add_query_arg('property', $property_id, home_url('/single-property/'));
  $image_ids = (array) es_get_the_field('gallery', $property_id);
  $image_id = $image_ids[0] ?? 0;
  $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
  $categories = get_the_terms($property_id, 'es_category');
  $category_names = array();

  if ($categories && !is_wp_error($categories)) {
    $category_names = array_filter(
      wp_list_pluck($categories, 'name'),
      static function ($name) {
        return strcasecmp($name, 'Featured') !== 0;
      }
    );
  }
?>
  <div class="featured-hero">
    <div class="overlay"></div>
    <img class="image" src="<?php echo esc_url($image_url) ?>"></img>
    <h2>Featured Sales</h2>
    <div class="featured-hero__bottom">
      <div class="featured-hero__details">
        <p>PHP<?php echo esc_html($property->price); ?></p>
        <p><?php echo esc_html($property->post_title); ?></p>
      </div>
      <a class="learn-more" href="<?php echo esc_url($property_url); ?>">
        <span class="learn-more__content">
          <span class="learn-more__text">LEARN MORE</span>

          <span class="learn-more__icon" aria-hidden="true">
            <svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5.56281 6.69117C5.12098 6.69117 4.76281 6.333 4.76281 5.89117C4.76281 5.44935 5.12098 5.09117 5.56281 5.09117V5.89117V6.69117ZM6.45685 5.32549C6.76927 5.63791 6.76927 6.14444 6.45685 6.45686L1.36568 11.548C1.05326 11.8604 0.546728 11.8604 0.234309 11.548C-0.0781107 11.2356 -0.0781107 10.7291 0.234309 10.4167L4.75979 5.89117L0.234309 1.36569C-0.0781107 1.05327 -0.0781107 0.54674 0.234309 0.23432C0.546728 -0.0780993 1.05326 -0.0780993 1.36568 0.23432L6.45685 5.32549ZM5.56281 5.89117V5.09117H5.89116V5.89117V6.69117H5.56281V5.89117Z" fill="#454545" />
            </svg>

          </span>
        </span>
      </a>
    </div>
  </div>
<?php

  return ob_get_clean();
}


add_shortcode('hero_featured', 'get_hero_featured_shortcode');
