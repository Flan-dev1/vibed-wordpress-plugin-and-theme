<?php

add_filter(
  'wpforms_frontend_enqueue_css_layout_field_viewport_breakpoint',
  function() {
    return 768;
  }
);

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
      'swiper',
      'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css'
    );

    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css'
    );

    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style'),
        filemtime(get_stylesheet_directory() . '/style.css')
    );


    wp_enqueue_script(
        'swiperjs',
        'https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js',
        array(),
        null,
        true
    );
   
    wp_enqueue_script(
        'custom-js',
        get_stylesheet_directory_uri() . '/js/index.js',
        array(),
        filemtime(get_stylesheet_directory() . '/js/index.js'),
        true
    );

    wp_enqueue_script(
        'log-script',
        get_stylesheet_directory_uri() . '/js/log.js',
        [],
        filemtime(get_stylesheet_directory() . '/js/log.js'),
        true
    );

    // Logs
    
    $locations = get_terms([
        'taxonomy'   => 'es_location',
        'hide_empty' => false,
    ]);


    $terms = get_terms([
      'hide_empty' => false,
    ]);

    $address_components = es_get_address_components_container();

    $cities = $address_components::get_locations(
      array( 'locality' )
    );

    $taxonomies = get_taxonomies();

    $url = get_page_link();

    if (!is_wp_error($locations)) {
        wp_localize_script('log-script', 'logData', [
            'locations' => $locations,
            'terms' => $terms,
            'taxonomies' => $taxonomies,
            'cities' => $cities,
            'currentURL' => $url,
        ]);
    }

    //wp_script_add_data('custom-js','type','module');

    // $meta = get_post_meta(226);

    // wp_localize_script(
    //   'custom-js',
    //   'propertyData',
    //   [
    //     'meta'=>$meta,
    //   ]
    // );
   
});

function mytheme_module_scripts( $tag, $handle, $src ) {
  if ( 'custom-js' === $handle ) {
    return '<script type="module" src="' . esc_url( $src ) . '"></script>';
  }

  return $tag;
}
add_filter( 'script_loader_tag', 'mytheme_module_scripts', 10, 3 );


// Expose property price in rest api 
add_action('rest_api_init', function () {
    register_rest_field(
        'properties',
        'price',
        [
            'get_callback' => function ($post) {
                return get_post_meta(
                    $post['id'],
                    'es_property_price',
                    true
                );
            },
            'schema' => [
                'description' => 'Property price',
                'type'        => 'string',
                'context'     => ['view', 'edit'],
            ],
        ]
    );
});

// Register the [page_title] shortcode
function custom_page_title_shortcode() {
  return get_the_title();
}
add_shortcode('page_title', 'custom_page_title_shortcode');

function get_featured_listings(){
  ob_start();
    ?>
  <div class="swiper featured">
    <div class="swiper-wrapper" id="featured-wrapper"></div>
    <div class="swiper-button-prev featured">
      <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1.5376 7.52756C2.03465 7.52756 2.4376 7.12462 2.4376 6.62756C2.4376 6.13051 2.03465 5.72756 1.5376 5.72756V6.62756V7.52756ZM0.263702 5.99117C-0.0877702 6.34264 -0.0877702 6.91249 0.263702 7.26396L5.99127 12.9915C6.34274 13.343 6.91259 13.343 7.26406 12.9915C7.61553 12.6401 7.61553 12.0702 7.26406 11.7187L2.17289 6.62756L7.26406 1.53639C7.61553 1.18492 7.61553 0.615075 7.26406 0.263603C6.91259 -0.0878692 6.34274 -0.0878692 5.99127 0.263603L0.263702 5.99117ZM1.5376 6.62756V5.72756H0.900098L0.900098 6.62756L0.900098 7.52756H1.5376V6.62756Z" fill="#454545"/>
      </svg>
    </div>
    <div class="swiper-button-next featured">
      <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M5.98999 7.52756C5.49293 7.52756 5.08999 7.12462 5.08999 6.62756C5.08999 6.13051 5.49293 5.72756 5.98999 5.72756V6.62756V7.52756ZM7.26389 5.99117C7.61536 6.34264 7.61536 6.91249 7.26389 7.26396L1.53632 12.9915C1.18485 13.343 0.615001 13.343 0.263529 12.9915C-0.0879426 12.6401 -0.0879426 12.0702 0.263529 11.7187L5.3547 6.62756L0.263529 1.53639C-0.0879426 1.18492 -0.0879426 0.615075 0.263529 0.263603C0.615001 -0.0878692 1.18485 -0.0878692 1.53632 0.263603L7.26389 5.99117ZM5.98999 6.62756V5.72756H6.62749V6.62756V7.52756H5.98999V6.62756Z" fill="#454545"/>
      </svg>
    </div>
    <button class="cta properties">View All Properties</button>
  </div>
  <?php

  return ob_get_clean();
}

function get_cities_slider(){
  ob_start();
    ?>
  <div class="swiper cities">
    <div class="swiper-wrapper" id="cities-wrapper"></div>
    <div class="swiper-button-prev cities">
      <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1.5376 7.52756C2.03465 7.52756 2.4376 7.12462 2.4376 6.62756C2.4376 6.13051 2.03465 5.72756 1.5376 5.72756V6.62756V7.52756ZM0.263702 5.99117C-0.0877702 6.34264 -0.0877702 6.91249 0.263702 7.26396L5.99127 12.9915C6.34274 13.343 6.91259 13.343 7.26406 12.9915C7.61553 12.6401 7.61553 12.0702 7.26406 11.7187L2.17289 6.62756L7.26406 1.53639C7.61553 1.18492 7.61553 0.615075 7.26406 0.263603C6.91259 -0.0878692 6.34274 -0.0878692 5.99127 0.263603L0.263702 5.99117ZM1.5376 6.62756V5.72756H0.900098L0.900098 6.62756L0.900098 7.52756H1.5376V6.62756Z" fill="#454545"/>
      </svg>
    </div>
    <div class="swiper-button-next cities">
      <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M5.98999 7.52756C5.49293 7.52756 5.08999 7.12462 5.08999 6.62756C5.08999 6.13051 5.49293 5.72756 5.98999 5.72756V6.62756V7.52756ZM7.26389 5.99117C7.61536 6.34264 7.61536 6.91249 7.26389 7.26396L1.53632 12.9915C1.18485 13.343 0.615001 13.343 0.263529 12.9915C-0.0879426 12.6401 -0.0879426 12.0702 0.263529 11.7187L5.3547 6.62756L0.263529 1.53639C-0.0879426 1.18492 -0.0879426 0.615075 0.263529 0.263603C0.615001 -0.0878692 1.18485 -0.0878692 1.53632 0.263603L7.26389 5.99117ZM5.98999 6.62756V5.72756H6.62749V6.62756V7.52756H5.98999V6.62756Z" fill="#454545"/>
      </svg>
    </div>
    <button class="cta cities">Explore More</button>
  </div>
  <?php

  return ob_get_clean();
}

function get_cities_pagination() {
  ob_start();

  $city_page_url = home_url( '/city/' );
  $per_page      = 6;

  $current_page = isset( $_GET['city_page'] )
      ? max( 1, absint( wp_unslash( $_GET['city_page'] ) ) )
      : 1;

  $offset = ( $current_page - 1 ) * $per_page;

  $query_args = array(
      'taxonomy'   => 'es_location',
      'hide_empty' => false,
      'number'     => $per_page,
      'offset'     => $offset,
      'orderby'    => 'name',
      'order'      => 'ASC',
      'meta_query' => array(
          array(
              'key'     => 'type',
              'value'   => 'locality',
              'compare' => '=',
          ),
      ),
  );

  $city_terms = get_terms( $query_args );

  if ( is_wp_error( $city_terms ) ) {
      ?>
      <p>
          <?php esc_html_e( 'The cities could not be loaded.', 'your-theme' ); ?>
      </p>
      <?php
  } elseif ( empty( $city_terms ) ) {
      ?>
      <p>
          <?php esc_html_e( 'No cities were found.', 'your-theme' ); ?>
      </p>
      <?php
  } else {
      $cities = array_map(
        function ( $city_term ) {
            $attachments = get_posts(
                array(
                    'post_type'      => 'attachment',
                    'post_status'    => 'inherit',
                    'name'           => sanitize_title( $city_term->name ),
                    'posts_per_page' => 1,
                    'fields'         => 'ids',
                )
            );

            $attachment_id = ! empty( $attachments )
                ? $attachments[0]
                : null;

            return array(
                'term'          => $city_term,
                'attachment_id' => $attachment_id,
                'source_url'    => $attachment_id
                    ? wp_get_attachment_url( $attachment_id )
                    : null,
            );
        },
        $city_terms
      );
      ?>
      <div class="cities-list">
          <?php foreach ( $cities as $city ) : ?>
              <?php
                $city_url = add_query_arg(
                    'city',
                    $city['term']->slug,
                    home_url( '/city/' )
                );
              ?>

              <article class="city-card">
                  <a href="<?php echo esc_url( $city_url ); ?>" class="city-card">
                    <?php if ( $city['source_url'] ) : ?>
                      <img
                        src="<?php echo esc_url( $city['source_url'] ); ?>"
                        alt="<?php echo esc_attr(
                            sprintf(
                                'Properties in %s',
                                $city['term']->name
                            )
                        ); ?>"
                          class="city-card__image"
                      >
                    <?php endif; ?>
                    <span
                        class="city-card__overlay"
                        aria-hidden="true"
                    ></span>
                    <span class="city-card__content">
                        <span class="city-card__title">
                            <?php echo esc_html( $city['term']->name ); ?>
                        </span>

                        <span class="city-card__link">
                            Explore Area
                        </span>
                    </span>
                  </a>
              </article>
          <?php endforeach; ?>
      </div>
      <div class="pagination">
        <button type="button"></button>
        <button type="button"></button>
      </div>
      <?php
  }

  return ob_get_clean();
}

add_shortcode('featured_listings','get_featured_listings');
add_shortcode('cities_slider','get_cities_slider');
add_shortcode('cities_pagination','get_cities_pagination');

function mytheme_widgets_init() {
    register_sidebar(array(
        'name' => 'Footer Widget Area',
        'id'   => 'footer-widget-area',
    ));
}
add_action('widgets_init', 'mytheme_widgets_init');

