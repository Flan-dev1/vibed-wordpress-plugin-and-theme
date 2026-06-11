<?php
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
        wp_get_theme()->get('Version')
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
        array(),      // Dependencies
        '1.0.0',      // Version
        true          // Load in footer
    );
   

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
    <button class="cta-properties">View All Properties</button>
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
    <button class="cta-properties">View All Properties</button>
  </div>
  <?php

  return ob_get_clean();
}

add_shortcode('featured_listings','get_featured_listings');
add_shortcode('cities_slider','get_cities_slider');