<?php

add_filter(
  'wpforms_frontend_enqueue_css_layout_field_viewport_breakpoint',
  function () {
    return 768;
  }
);

add_action('wp_enqueue_scripts', function () {
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
    array('locality')
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

function mytheme_module_scripts($tag, $handle, $src)
{
  if ('custom-js' === $handle) {
    return '<script type="module" src="' . esc_url($src) . '"></script>';
  }

  return $tag;
}
add_filter('script_loader_tag', 'mytheme_module_scripts', 10, 3);


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
function custom_page_title_shortcode()
{
  return get_the_title();
}
add_shortcode('page_title', 'custom_page_title_shortcode');

function get_featured_listings()
{
  ob_start();

  $featured_properties = new WP_Query(
    array(
      'post_type'      => 'properties',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => 'date',
      'order'          => 'DESC',
      'tax_query'      => array(
        array(
          'taxonomy' => 'es_label',
          'field'    => 'slug',
          'terms'    => array('featured'),
        ),
      ),
    )
  );

?>
  <div class="swiper featured">
    <div class="swiper-wrapper" id="featured-wrapper">
      <?php
      while ($featured_properties->have_posts()) {
        $featured_properties->the_post();

        $property_id  = get_the_ID();
        $property     = es_get_property($property_id);
        $property_url = add_query_arg(
          'property',
          $property_id,
          home_url('/single-property/')
        );
        $image_ids = (array) es_get_the_field('gallery', $property_id);
        $image_id  = $image_ids[0] ?? get_post_thumbnail_id($property_id);
        $image_url = $image_id
          ? wp_get_attachment_image_url($image_id, 'full')
          : '';
      ?>
        <div class="swiper-slide">
          <div class="slide-wrapper">
            <div class="slide-overlay"></div>
            <img
              src="<?php echo esc_url($image_url); ?>"
              alt="<?php echo esc_attr(get_the_title($property_id)); ?>"
              class="featured-image">
            <div class="featured-details">
              <div>PHP<?php echo esc_html($property->price); ?></div>
              <div><?php echo esc_html(get_the_title($property_id)); ?></div>
            </div>
          </div>
        </div>
      <?php
      }
      wp_reset_postdata();
      ?>
    </div>
    <div class="swiper-button-prev featured">
      <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1.5376 7.52756C2.03465 7.52756 2.4376 7.12462 2.4376 6.62756C2.4376 6.13051 2.03465 5.72756 1.5376 5.72756V6.62756V7.52756ZM0.263702 5.99117C-0.0877702 6.34264 -0.0877702 6.91249 0.263702 7.26396L5.99127 12.9915C6.34274 13.343 6.91259 13.343 7.26406 12.9915C7.61553 12.6401 7.61553 12.0702 7.26406 11.7187L2.17289 6.62756L7.26406 1.53639C7.61553 1.18492 7.61553 0.615075 7.26406 0.263603C6.91259 -0.0878692 6.34274 -0.0878692 5.99127 0.263603L0.263702 5.99117ZM1.5376 6.62756V5.72756H0.900098L0.900098 6.62756L0.900098 7.52756H1.5376V6.62756Z" fill="#454545" />
      </svg>
    </div>
    <div class="swiper-button-next featured">
      <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M5.98999 7.52756C5.49293 7.52756 5.08999 7.12462 5.08999 6.62756C5.08999 6.13051 5.49293 5.72756 5.98999 5.72756V6.62756V7.52756ZM7.26389 5.99117C7.61536 6.34264 7.61536 6.91249 7.26389 7.26396L1.53632 12.9915C1.18485 13.343 0.615001 13.343 0.263529 12.9915C-0.0879426 12.6401 -0.0879426 12.0702 0.263529 11.7187L5.3547 6.62756L0.263529 1.53639C-0.0879426 1.18492 -0.0879426 0.615075 0.263529 0.263603C0.615001 -0.0878692 1.18485 -0.0878692 1.53632 0.263603L7.26389 5.99117ZM5.98999 6.62756V5.72756H6.62749V6.62756V7.52756H5.98999V6.62756Z" fill="#454545" />
      </svg>
    </div>
    <a href="/properties"><button class="cta cta-properties">View All Properties</button></a>
  </div>
<?php
  return ob_get_clean();
}

function get_cities_slider()
{
  ob_start();

  $cities = get_posts(
    array(
      'post_type'      => 'sf_city',
      'post_status'    => 'publish',
      'orderby'        => 'title',
      'order'          => 'ASC',
    )
  );

?>
  <div class="swiper cities">
    <div class="swiper-wrapper" id="cities-wrapper">
      <?php
      foreach ($cities as $city) {
        $city_id = $city->ID;
        $name = get_the_title($city_id);
        $image_id = (int) get_post_meta($city_id, '_sf_image_id', true);
        $city_url = add_query_arg(
          'city-id',
          $city_id,
          home_url('/properties/')
        );
        $image = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';

      ?>
        <a href="<?php echo esc_url($city_url); ?>" class="swiper-slide city">
          <div class="slide-wrapper">
            <img class="city-image" src="<?php echo esc_url($image); ?>"
              alt="<?php
                    echo esc_attr(
                      sprintf(
                        'Properties in %s',
                        $name
                      )
                    );
                    ?>">
            </img>
            <div class="slide-overlay"></div>
            <div class="city-details"><?php echo esc_html($name); ?></div>
          </div>
        </a>
      <?php
      }
      ?>
    </div>
    <div class="swiper-button-prev cities">
      <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1.5376 7.52756C2.03465 7.52756 2.4376 7.12462 2.4376 6.62756C2.4376 6.13051 2.03465 5.72756 1.5376 5.72756V6.62756V7.52756ZM0.263702 5.99117C-0.0877702 6.34264 -0.0877702 6.91249 0.263702 7.26396L5.99127 12.9915C6.34274 13.343 6.91259 13.343 7.26406 12.9915C7.61553 12.6401 7.61553 12.0702 7.26406 11.7187L2.17289 6.62756L7.26406 1.53639C7.61553 1.18492 7.61553 0.615075 7.26406 0.263603C6.91259 -0.0878692 6.34274 -0.0878692 5.99127 0.263603L0.263702 5.99117ZM1.5376 6.62756V5.72756H0.900098L0.900098 6.62756L0.900098 7.52756H1.5376V6.62756Z" fill="#454545" />
      </svg>
    </div>
    <div class="swiper-button-next cities">
      <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M5.98999 7.52756C5.49293 7.52756 5.08999 7.12462 5.08999 6.62756C5.08999 6.13051 5.49293 5.72756 5.98999 5.72756V6.62756V7.52756ZM7.26389 5.99117C7.61536 6.34264 7.61536 6.91249 7.26389 7.26396L1.53632 12.9915C1.18485 13.343 0.615001 13.343 0.263529 12.9915C-0.0879426 12.6401 -0.0879426 12.0702 0.263529 11.7187L5.3547 6.62756L0.263529 1.53639C-0.0879426 1.18492 -0.0879426 0.615075 0.263529 0.263603C0.615001 -0.0878692 1.18485 -0.0878692 1.53632 0.263603L7.26389 5.99117ZM5.98999 6.62756V5.72756H6.62749V6.62756V7.52756H5.98999V6.62756Z" fill="#454545" />
      </svg>
    </div>
    <button class="cta cities">Explore More</button>
  </div>
<?php

  return ob_get_clean();
}

function getPaginationUI(
  $current_page,
  $next_page_url,
  $previous_page_url,
  $page_number_links,
  $total_pages,
  $current_sort = 'default'
) {
  $allowed_sorts = array(
    'default',
    'rating-desc',
    'rating-asc',
    'name-asc',
    'name-desc',
  );

  // prevent an arbitrary query value from entering the URLs.
  if (! in_array($current_sort, $allowed_sorts, true)) {
    $current_sort = 'default';
  }

  // preserve the sort argument in previous and next links.
  $previous_page_url = add_query_arg(
    'sort',
    $current_sort,
    $previous_page_url
  );

  $next_page_url = add_query_arg(
    'sort',
    $current_sort,
    $next_page_url
  );
?>
  <nav
    class="custom-pagination"
    aria-label="<?php
                esc_attr_e('Cities pagination', 'your-theme');
                ?>">
    <div class="custom-pagination__links">

      <?php if ($current_page > 1) : ?>
        <a
          class="prev page-numbers"
          href="<?php echo esc_url($previous_page_url); ?>"
          aria-label="<?php
                      esc_attr_e('Previous page', 'your-theme');
                      ?>">
          <span aria-hidden="true">&lsaquo;</span>
        </a>
      <?php else : ?>
        <span
          class="prev page-numbers is-disabled"
          aria-disabled="true">
          <span aria-hidden="true">&lsaquo;</span>
        </span>
      <?php endif; ?>

      <?php foreach ((array) $page_number_links as $page_link) : ?>
        <?php
        /*
                 * These links must already contain the sort argument.
                 * Add it when generating $page_number_links.
                 */
        echo wp_kses_post($page_link);
        ?>
      <?php endforeach; ?>

      <?php if ($current_page < $total_pages) : ?>
        <a
          class="next page-numbers"
          href="<?php echo esc_url($next_page_url); ?>"
          aria-label="<?php
                      esc_attr_e('Next page', 'your-theme');
                      ?>">
          <span aria-hidden="true">&rsaquo;</span>
        </a>
      <?php else : ?>
        <span
          class="next page-numbers is-disabled"
          aria-disabled="true">
          <span aria-hidden="true">&rsaquo;</span>
        </span>
      <?php endif; ?>

    </div>
  </nav>
  <?php
}

function get_cities_pagination()
{
  ob_start();

  $city_page_url = home_url('/city/');
  $per_page = 6;

  $requested_page = isset($_GET['city_page'])
    ? max(1, absint(wp_unslash($_GET['city_page'])))
    : 1;

  $cities = get_posts(
    array(
      'post_type'      => 'sf_city',
      'post_status'    => 'publish',
      'posts_per_page' => -1,
      'orderby'        => 'title',
      'order'          => 'ASC',
    )
  );

  if (is_wp_error($cities)) {
  ?>
    <p>
      <?php esc_html_e('The cities could not be loaded.', 'your-theme'); ?>
    </p>
  <?php

    return ob_get_clean();
  }

  $total_cities = count($cities);

  $total_pages  = (int) ceil($total_cities / $per_page);

  $current_page = $total_pages > 0
    ? min($requested_page, $total_pages)
    : 1;

  $offset = ($current_page - 1) * $per_page;

  $cities = get_posts(
    array(
      'post_type'      => 'sf_city',
      'post_status'    => 'publish',
      'posts_per_page' => $per_page,
      'offset'         => $offset,
      'orderby'        => 'title',
      'order'          => 'ASC',
    )
  );

  if (is_wp_error($cities)) {
  ?>
    <p>
      <?php esc_html_e('The cities could not be loaded.', 'your-theme'); ?>
    </p>
  <?php
  } elseif (empty($cities)) {
  ?>
    <p>
      <?php esc_html_e('No cities were found.', 'your-theme'); ?>
    </p>
  <?php
  } else {
  ?>

    <div class="cities-list">
      <?php foreach ($cities as $city) : ?>
        <?php
        $city_id = $city->ID;
        $name = get_the_title($city_id);
        $image_id = (int) get_post_meta($city_id, '_sf_image_id', true);
        $city_url = add_query_arg(
          'city-id',
          $city_id,
          home_url('/properties/')
        );
        $image = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
        ?>
        <article class="city-card">
          <a
            href="<?php echo esc_url($city_url); ?>"
            class="city-card">
            <?php if ($image) : ?>
              <img
                src="<?php echo esc_url($image); ?>"
                alt="<?php
                      echo esc_attr(
                        sprintf(
                          'Properties in %s',
                          $name
                        )
                      );
                      ?>"
                class="city-card__image">
            <?php endif; ?>

            <span
              class="city-card__overlay"
              aria-hidden="true"></span>

            <span class="city-card__content">
              <span class="city-card__title">
                <?php echo esc_html($name); ?>
              </span>

              <span class="city-card__link">
                <?php esc_html_e('Explore Area', 'your-theme'); ?>
              </span>
            </span>
          </a>
        </article>
      <?php endforeach; ?>
    </div>

    <?php
    if ($total_pages > 1) {
      $pagination_placeholder = 999999999;

      $pagination_base = str_replace(
        (string) $pagination_placeholder,
        '%#%',
        add_query_arg(
          'city_page',
          $pagination_placeholder,
          $city_page_url
        )
      );

      $page_number_links = paginate_links(
        array(
          'base'      => $pagination_base,
          'format'    => '',
          'current'   => $current_page,
          'total'     => $total_pages,
          'type'      => 'array',
          'prev_next' => false,
          'end_size'  => 1,
          'mid_size'  => 4,
        )
      );

      $previous_page_url = add_query_arg(
        'city_page',
        $current_page - 1,
        $city_page_url
      );

      $next_page_url = add_query_arg(
        'city_page',
        $current_page + 1,
        $city_page_url
      );
    ?>

      <nav
        class="custom-pagination"
        aria-label="<?php
                    esc_attr_e('Cities pagination', 'your-theme');
                    ?>">
        <div class="custom-pagination__links">

          <?php if ($current_page > 1) : ?>
            <a
              class="prev page-numbers"
              href="<?php echo esc_url($previous_page_url); ?>"
              aria-label="<?php
                          esc_attr_e('Previous page', 'your-theme');
                          ?>">
              <span aria-hidden="true">&lsaquo;</span>
            </a>
          <?php else : ?>
            <span
              class="prev page-numbers is-disabled"
              aria-disabled="true">
              <span aria-hidden="true">&lsaquo;</span>
            </span>
          <?php endif; ?>

          <?php foreach ((array) $page_number_links as $page_link) : ?>
            <?php echo wp_kses_post($page_link); ?>
          <?php endforeach; ?>

          <?php if ($current_page < $total_pages) : ?>
            <a
              class="next page-numbers"
              href="<?php echo esc_url($next_page_url); ?>"
              aria-label="<?php
                          esc_attr_e('Next page', 'your-theme');
                          ?>">
              <span aria-hidden="true">&rsaquo;</span>
            </a>
          <?php else : ?>
            <span
              class="next page-numbers is-disabled"
              aria-disabled="true">
              <span aria-hidden="true">&rsaquo;</span>
            </span>
          <?php endif; ?>

        </div>
      </nav>
  <?php
    }
  }

  return ob_get_clean();
}

function get_testimonials()
{
  ob_start();

  $allowed_sorts = array(
    'default',
    'rating-desc',
    'rating-asc',
    'name-asc',
    'name-desc',
  );

  $current_sort = isset($_GET['sort'])
    ? sanitize_key(wp_unslash($_GET['sort']))
    : 'default';

  if (! in_array($current_sort, $allowed_sorts, true)) {
    $current_sort = 'default';
  }

  ?>

  <div class="testimonials-toolbar">
    <div class="sort-control">
      <span class="screen-reader-text">Sort testimonials</span>

      <form
        class="testimonials-sort"
        method="get"
        action="<?php echo esc_url(get_permalink()); ?>">
        <label for="testimonial-sort" class="screen-reader-text">
          <?php esc_html_e('Sort testimonials', 'your-theme'); ?>
        </label>

        <select
          id="testimonial-sort"
          name="sort"
          onchange="this.form.submit()">
          <option value="default" <?php selected($current_sort, 'default'); ?>>
            <?php esc_html_e('Sort', 'your-theme'); ?>
          </option>

          <option
            value="rating-desc"
            <?php selected($current_sort, 'rating-desc'); ?>>
            <?php esc_html_e('Highest rating', 'your-theme'); ?>
          </option>

          <option
            value="rating-asc"
            <?php selected($current_sort, 'rating-asc'); ?>>
            <?php esc_html_e('Lowest rating', 'your-theme'); ?>
          </option>

          <option
            value="name-asc"
            <?php selected($current_sort, 'name-asc'); ?>>
            <?php esc_html_e('Name: A–Z', 'your-theme'); ?>
          </option>

          <option
            value="name-desc"
            <?php selected($current_sort, 'name-desc'); ?>>
            <?php esc_html_e('Name: Z–A', 'your-theme'); ?>
          </option>
        </select>

        <noscript>
          <button type="submit">
            <?php esc_html_e('Apply', 'your-theme'); ?>
          </button>
        </noscript>
      </form>
    </div>
  </div>

  <div class="testimonials-grid">
    <?php
    $per_page = 6;

    $testimonials_page_url = home_url('/testimonials/');

    $requested_page = isset($_GET['testimonials_page'])
      ? max(1, absint(wp_unslash($_GET['testimonials_page'])))
      : 1;

    $testimonials = get_posts(
      array(
        'post_type'      => 'sf_testimonial',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'ASC',
      )
    );

    if (is_wp_error($testimonials)) {
    ?>
      <p>
        <?php esc_html_e('The testimonials could not be loaded.', 'your-theme'); ?>
      </p>
    <?php

      return ob_get_clean();
    }

    $total_testimonials = count($testimonials);

    $total_pages  = (int) ceil($total_testimonials / $per_page);

    $current_page = $total_pages > 0
      ? min($requested_page, $total_pages)
      : 1;

    $offset = ($current_page - 1) * $per_page;

    $query_args = array(
      'post_type'      => 'sf_testimonial',
      'post_status'    => 'publish',
      'posts_per_page' => $per_page,
      'offset'         => $offset,
      'orderby'        => 'date',
      'order'          => 'ASC',
    );

    switch ($current_sort) {
      case 'rating-desc':
        $query_args['meta_key'] = '_sf_rating';
        $query_args['orderby']  = 'meta_value_num';
        $query_args['order']    = 'DESC';
        break;

      case 'rating-asc':
        $query_args['meta_key'] = '_sf_rating';
        $query_args['orderby']  = 'meta_value_num';
        $query_args['order']    = 'ASC';
        break;

      case 'name-asc':
        $query_args['meta_key'] = '_sf_name';
        $query_args['orderby'] = 'meta_value';
        $query_args['order']   = 'ASC';
        break;

      case 'name-desc':
        $query_args['meta_key'] = '_sf_name';
        $query_args['orderby'] = 'meta_value';
        $query_args['order']   = 'DESC';
        break;

      case 'default':
      default:
        $query_args['orderby'] = 'date';
        $query_args['order']   = 'DESC';
        break;
    }

    $testimonials = get_posts($query_args);

    foreach ($testimonials as $testimonial) :
      $id     = $testimonial->ID;
      $rating = (float) get_post_meta($id, '_sf_rating', true);
      $name   = get_post_meta($id, '_sf_name', true) ? get_post_meta($id, '_sf_name', true) : 'Anonymous';
      $text   = get_post_field('post_content', $id);
    ?>
      <article class="testimonial-card">
        <div
          class="star-rating"
          style="--rating: <?php echo esc_attr($rating); ?>;"
          role="img"
          aria-label="<?php echo esc_attr($rating . ' out of 5 stars'); ?>">
          <span class="star-rating__empty" aria-hidden="true">
            ★★★★★
          </span>

          <span class="star-rating__filled" aria-hidden="true">
            ★★★★★
          </span>
        </div>

        <p class="testimonial-card__text">
          <?php echo esc_html($text); ?>
        </p>

        <p class="testimonial-card__name">
          <?php echo esc_html($name); ?>
        </p>
      </article>
    <?php endforeach; ?>
  </div>
<?php
  // pagination
  if ($total_pages > 1) {
    $big = 999999999;

    $pagination_base = add_query_arg(
      array(
        'testimonials_page' => $big,
        'sort'             => $current_sort,
      ),
      get_permalink()
    );

    $pagination_base = str_replace(
      (string) $big,
      '%#%',
      $pagination_base
    );

    $current_page = $total_pages > 0
      ? min($requested_page, $total_pages)
      : 1;

    $page_number_links = paginate_links(
      array(
        'base'      => $pagination_base,
        'format'    => '',
        'current'   => $current_page,
        'total'     => $total_pages,
        'type'      => 'array',
        'prev_next' => false,
        'mid_size'  => 1,
        'end_size'  => 1,
      )
    );

    $page_number_links = $page_number_links ?: array();

    $previous_page_url = add_query_arg(
      'testimonials_page',
      $current_page - 1,
      $testimonials_page_url
    );

    $next_page_url = add_query_arg(
      'testimonials_page',
      $current_page + 1,
      $testimonials_page_url
    );

    getPaginationUI($current_page, $next_page_url, $previous_page_url, $page_number_links, $total_pages, $current_sort);
  }

  return ob_get_clean();
}

add_shortcode('featured_listings', 'get_featured_listings');
add_shortcode('cities_slider', 'get_cities_slider');
add_shortcode('cities_pagination', 'get_cities_pagination');
add_shortcode('testimonials', 'get_testimonials');

function mytheme_widgets_init()
{
  register_sidebar(array(
    'name' => 'Footer Widget Area',
    'id'   => 'footer-widget-area',
  ));
}
add_action('widgets_init', 'mytheme_widgets_init');
