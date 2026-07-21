<?php

if (! defined('ABSPATH')) {
  exit;
}

function properties_shortcode()
{
  ob_start();
  // TODO: change link args to status
  $category = isset($_GET['status'])
    ? sanitize_title(wp_unslash($_GET['status']))
    : "for-sale";

  $term = get_term_by('slug', $category, 'es_status');

  $allowed_sorts = array(
    'newest',
    'oldest',
    'lowest_price',
    'highest_price',
  );

  $current_sort = isset($_GET['sort'])
    ? sanitize_key(wp_unslash($_GET['sort']))
    : 'newest';

  if (! in_array($current_sort, $allowed_sorts, true)) {
    $current_sort = 'newest';
  }

  require_once plugin_dir_path(__FILE__) . 'sort.php';
  get_sort_component($allowed_sorts, $current_sort);

  if (!$term) {
?>
    <div class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-house-gear" viewBox="0 0 16 16">
        <path d="M7.293 1.5a1 1 0 0 1 1.414 0L11 3.793V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3.293l2.354 2.353a.5.5 0 0 1-.708.708L8 2.207l-5 5V13.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 2 13.5V8.207l-.646.647a.5.5 0 1 1-.708-.708z" />
        <path d="M11.886 9.46c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.044c-.613-.181-.613-1.049 0-1.23l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0" />
      </svg>
      <h2>No properties for that category right now.</h2>
      <p>We're working on adding more, so stay tuned!</p>
    </div>
  <?php
  } else {
    $per_page = 9;

    $city_id = isset($_GET['city-id'])
      ? absint(wp_unslash($_GET['city-id']))
      : 0;

    $query_args = es_get_properties_query_args(
      array(
        'query' => array(
          'posts_per_page' => $per_page,
          'paged'          => max(1, get_query_var('paged')),
        ),
        'fields' => array(
          'es_status' => $term ? array($term->term_id) : array(0),
          'sort'        => $current_sort,
        ),
      )
    );

    // Apply the StarFlan city filter when a valid city is selected.
    if ($city_id && 'sf_city' === get_post_type($city_id)) {
      echo 'true';
      $city_property_ids = array_values(
        array_filter(
          array_map(
            'absint',
            (array) get_post_meta(
              $city_id,
              '_sf_estatik_property_ids',
              true
            )
          )
        )
      );

      /*
	 * An empty post__in array means "no restriction" in WordPress,
	 * so use array( 0 ) when the city has no assigned properties.
	 */
      $query_args['post__in'] = $city_property_ids ?: array(0);
    }

    $properties = new WP_Query($query_args);
  ?>

    <?php
    if (!$properties->have_posts()) {
    ?>
      <div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-house-gear" viewBox="0 0 16 16">
          <path d="M7.293 1.5a1 1 0 0 1 1.414 0L11 3.793V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3.293l2.354 2.353a.5.5 0 0 1-.708.708L8 2.207l-5 5V13.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 2 13.5V8.207l-.646.647a.5.5 0 1 1-.708-.708z" />
          <path d="M11.886 9.46c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.044c-.613-.181-.613-1.049 0-1.23l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0" />
        </svg>
        <h2>No properties for that city or category right now.</h2>
        <p>We're working on adding more, so stay tuned!</p>
      </div>
    <?php
    } else {
    ?>
      <div class="properties">
        <?php
        while ($properties->have_posts()) {
          $properties->the_post();
          $property = es_get_the_property();

          $property_url = add_query_arg(
            'property',
            $property->ID,
            home_url('/single-property/')
          );
          $name = get_the_title($property->ID);
          $image_ids   = (array) es_get_the_field('gallery', $property->ID);
          $image_id    = $image_ids[0] ?? 0;
          $src         = $image_id
            ? wp_get_attachment_image_url($image_id, 'large')
            : '';
        ?>
          <a href="<?php echo esc_url($property_url); ?>" class="property-card">
            <div class="property-card__tag">
              <?php
              $status  = get_the_terms($property->ID, 'es_status');

              if ($status && ! is_wp_error($status)) {
                $status_names = array_filter(wp_list_pluck($status, 'name'), function ($value) {
                  return $value != "Featured";
                });

                echo esc_html(strtoupper(implode(', ', $status_names)));
              }
              ?>
            </div>
            <div class="property-card__detail">
              <p>PHP<?php echo esc_html($property->price); ?></p>
              <p><?php echo esc_html($property->post_title); ?></p>
            </div>
            <img src="<?php echo esc_url($src); ?>"
              alt="<?php
                    echo esc_attr(
                      sprintf(
                        'Properties in %s',
                        $name
                      )
                    );
                    ?>" class="property-card__image">
            <div class="property-card__overlay"></div>
            <div class="property-card__filter"></div>
          </a>

      <?php
        }
        wp_reset_postdata();
      }
      ?>

      </div>

  <?php

    require_once plugin_dir_path(__FILE__) . 'pagination.php';

    $count = wp_count_posts('properties');

    $total_properties = isset($count->publish) ? (int) $count->publish : 0;

    $total_pages  = (int) ceil($total_properties / $per_page);

    $current_page = isset($_GET['page'])
      ? (wp_unslash($_GET['page']))
      : "1";

    $properties_page_url = home_url('/properties-list/');
    add_pagination($properties_page_url, $total_pages, $current_page);
  }

  return ob_get_clean();
}

add_shortcode('properties', 'properties_shortcode');
