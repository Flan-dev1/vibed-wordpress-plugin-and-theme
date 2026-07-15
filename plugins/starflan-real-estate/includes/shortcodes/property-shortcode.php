<?php

if (! defined('ABSPATH')) {
  exit;
}

function property_shortcode()
{
  ob_start();

  $property_id = isset($_GET['property'])
    ? max(1, absint(wp_unslash($_GET['property'])))
    : 1;

  if (get_post_type($property_id) !== 'properties') {
?>
    <div class="property-empty">
      Property <?php echo esc_html($property_id); ?> does not exist.
    </div>
    <?php
  } else {
    $image_ids = es_get_the_field('gallery', $property_id);
    $statuses = get_the_terms($property_id, 'es_status');
    if (! empty($image_ids)) {
      $image = $image_ids[0];
      $src = wp_get_attachment_image_url($image, 'full');
    ?>
      <!-- Hero -->
      <div class="property-hero" style="--bg:url(<?php echo esc_url($src); ?>)">
        <div class="property-hero-details">
          <?php
          if (! empty($statuses) && ! is_wp_error($statuses)) {
          ?>
            <p class="propert-status"><?php echo esc_html($statuses[0]->name); ?></p>
          <?php
          }
          ?>
          <p><?php echo es_get_the_field('post_title', $property_id); ?></p>
          <p><?php echo es_get_the_field('address', $property_id); ?></p>
          <p>PHP<?php echo es_get_the_field('price', $property_id); ?></p>
        </div>
        <div class="property-hero-links">
          <a href="#content">V</a>
          <a id="view-gallery" href="#gallery">view gallery</a>
        </div>
      </div>
    <?php
    }
    // NO EMPTY IMAGE YET
    ?>
    <div class="property-nav">
      <div class="property-nav-links">
        <a href="#description">Description</a>
        <a href="#overview">Overview</a>
        <a href="#features-and-amenities">Features & Amenities</a>
      </div>
      <a href="/contact-us" class="contact-link">
        <span>Contact Us</span>
        >
      </a>
    </div>

    <div id="content" class="property-content">
      <div class="property-details">
        <div class="property-description">
          <h2 id="description">Property Description</h2>
          <p id="description-text"><?php echo es_get_the_field('property-description', $property_id); ?></p>
          <button id="description-button" type="button" onclick="readmore()">READ MORE V</button>
        </div>
        <div class="">
          <h2 id=" overview">Overview</h2>
          <h4 style="padding-left:40px">Basic Information</h4>

          <div class="detail-grid">
            <?php
            if (! empty($statuses) && ! is_wp_error($statuses)) {
            ?>
              <p>Property Status</p>
              <p><?php
                  foreach ($statuses as $status) {
                    echo esc_html($status->name) . ' ';
                  }

                  ?></p>
            <?php
            }

            //property type
            $types = get_the_terms($property_id, 'es_type');

            if (! empty($types) && ! is_wp_error($types)) {
            ?>
              <p>Property Type</p>
              <p>
                <?php
                foreach ($types as $type) {
                  echo esc_html($type->name);
                }
                ?>
              </p>
            <?php
            }

            ?>
            <p>Floor Area</p>
            <p><?php echo es_get_the_field('floor-area', $property_id); ?> sqm</p>
            <p>Lot Area</p>
            <p><?php echo es_get_the_field('lot-area', $property_id); ?> sqm</p>
            <p>Bedrooms</p>
            <p><?php echo es_get_the_field('bedrooms', $property_id); ?></p>
            <p>Bathrooms</p>
            <p><?php echo es_get_the_field('bathrooms', $property_id); ?></p>
          </div>
        </div>
        <div class="">
          <h2 id="features-and-amenities">Features and Amenities</h2>
          <h4 style="padding-left:40px">Interior and Exterior</h4>
          <div class="detail-grid">
            <p>Stories</p>
            <p><?php echo es_get_the_field('floors', $property_id); ?></p>
            <p>Units</p>
            <p><?php echo es_get_the_field('units', $property_id); ?></p>
            <p>Air Conditioning</p>
            <?php
            $air_conditioning = es_get_the_field('air-conditioning', $property_id);
            ?>
            <p><?php
                foreach ($air_conditioning as $ac) {
                  echo esc_html($ac);
                }
                ?></p>
            <p>Laundry Room</p>
            <p><?php echo es_get_the_field('laundry-room', $property_id); ?></p>
            <p>Appliances</p>
            <p><?php echo es_get_the_field('appliances', $property_id); ?></p>
            <p>Other Interior Features</p>
            <p><?php echo es_get_the_field('other-interior-features', $property_id); ?></p>
            <p>Other Exterior Features</p>
            <p><?php echo es_get_the_field('other-exterior-features', $property_id); ?></p>
          </div>
        </div>
      </div>
      <div class="property-cta">
        <h3>Schedule a Tour</h3>
        <a href="/contact-us" class="contact-link">Pick your Data and Time ></a>
        <hr class="full-divider">
        <div class="divider">
          <hr>
          or
          <hr>
        </div>
        <div class="contact-info">
          <p>Maryanne Meily</p>
          <p><a href="tel:+12 345 6789">+12 345 6789</a></p>
          <p><a href="mailto:Email@gmail.com">Email@gmail.com</a></p>
        </div>
      </div>
    </div>



    <div id="gallery" class="property-images">
      <?php
      $image_ids = es_get_the_field('gallery', $property_id);

      if (! empty($image_ids)) {

        $total_images = count((array)$image_ids);
        $current_index = 0;

        while ($current_index < $total_images) {
          $remaining_images = $total_images - $current_index;

          // Randomly select 1, 2, or 3 images for the current row.
          $items_in_row = min(
            random_int(1, 3),
            $remaining_images
          );
          $column_span = 6 / $items_in_row;

          for ($index = 0; $index < $items_in_row; $index++) {
            $image = $image_ids[$current_index + $index];
            $src = wp_get_attachment_image_url($image, 'large');
            if ($items_in_row == 2 && $index == 0) {
              $column_span = random_int(1, 2) * 2;
            } else if ($items_in_row == 2 && $index == 1) {
              $column_span = 6 - $column_span;
            }
      ?>
            <img
              data-column-span="<?php echo esc_attr($column_span); ?>"
              src="<?php echo esc_url($src); ?>"
              style="--column-span: <?php echo esc_attr($column_span); ?>">
      <?php
          }

          $current_index += $items_in_row;
        }
      }
      ?>
    </div>

    <div class="property-map empty">
      <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-geo-alt" viewBox="0 0 16 16">
        <path d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10" />
        <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
      </svg>
      <h3>Location features coming soon.</h3>
      <h4>Stay tuned!</h4>
    </div>

    <script>
      function readmore() {
        text = document.getElementById('description-text');
        text.classList.toggle('expand');
        button = document.getElementById('description-button');
        button.textContent = button.textContent == 'READ MORE V' ? 'READ LESS V' : 'READ MORE V';
        document.getElementById("description").scrollIntoView({
          behavior: "smooth",
          block: "start"
        });
      }
    </script>
<?php
  }

  return ob_get_clean();
}

add_shortcode('property', 'property_shortcode');

?>