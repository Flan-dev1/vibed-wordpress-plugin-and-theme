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
            <p class="property-status"><?php echo strtoupper(esc_html($statuses[0]->name)); ?></p>
          <?php
          }
          ?>
          <h3><?php echo es_get_the_field('post_title', $property_id); ?></h3>
          <h4><?php echo es_get_the_field('address', $property_id); ?></h4>
          <h3>PHP<?php echo es_get_the_field('price', $property_id); ?></h3>
        </div>
        <div class="property-hero-links">
          <a href="#content" class="chevron-circle">
            <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5.72756 5.99005C5.72756 5.49299 6.13051 5.09005 6.62756 5.09005C7.12462 5.09005 7.52756 5.49299 7.52756 5.99005H6.62756H5.72756ZM7.26396 7.26395C6.91249 7.61542 6.34264 7.61542 5.99117 7.26395L0.263603 1.53638C-0.0878692 1.18491 -0.0878692 0.615062 0.263603 0.26359C0.615075 -0.0878816 1.18492 -0.0878816 1.53639 0.26359L6.62756 5.35476L11.7187 0.26359C12.0702 -0.0878816 12.6401 -0.0878816 12.9915 0.26359C13.343 0.615062 13.343 1.18491 12.9915 1.53638L7.26396 7.26395ZM6.62756 5.99005H7.52756V6.62755H6.62756H5.72756V5.99005H6.62756Z" fill="#454545" />
            </svg>

          </a>
          <a id="view-gallery" href="#gallery">VIEW GALLERY</a>
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
        <span>CONTACT US</span>
        <div class="chevron-circle">
          <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5.98999 7.52756C5.49293 7.52756 5.08999 7.12462 5.08999 6.62756C5.08999 6.13051 5.49293 5.72756 5.98999 5.72756V6.62756V7.52756ZM7.26389 5.99117C7.61536 6.34264 7.61536 6.91249 7.26389 7.26396L1.53632 12.9915C1.18485 13.343 0.615001 13.343 0.263529 12.9915C-0.0879426 12.6401 -0.0879426 12.0702 0.263529 11.7187L5.3547 6.62756L0.263529 1.53639C-0.0879426 1.18492 -0.0879426 0.615075 0.263529 0.263603C0.615001 -0.0878692 1.18485 -0.0878692 1.53632 0.263603L7.26389 5.99117ZM5.98999 6.62756V5.72756H6.62749V6.62756V7.52756H5.98999V6.62756Z" fill="#454545" />
          </svg>
        </div>
      </a>
    </div>

    <div id="content" class="property-content">
      <div class="property-details">
        <div class="property-description">
          <h2 id="description">Property Description</h2>
          <p id="description-text"><?php echo es_get_the_field('property-description', $property_id); ?></p>
          <button id="description-button" type="button" onclick="readmore()">
            <span id="description-button-text">READ MORE</span>
            <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5.72769 5.99011C5.72769 5.49306 6.13063 5.09011 6.62769 5.09011C7.12474 5.09011 7.52769 5.49306 7.52769 5.99011H6.62769H5.72769ZM7.26408 7.26401C6.91261 7.61548 6.34276 7.61548 5.99129 7.26401L0.263725 1.53644C-0.0877471 1.18497 -0.0877471 0.615123 0.263725 0.263651C0.615197 -0.0878205 1.18505 -0.0878205 1.53652 0.263651L6.62769 5.35482L11.7189 0.263651C12.0703 -0.0878205 12.6402 -0.0878205 12.9916 0.263651C13.3431 0.615123 13.3431 1.18497 12.9916 1.53644L7.26408 7.26401ZM6.62769 5.99011H7.52769V6.62761H6.62769H5.72769V5.99011H6.62769Z" fill="white" />
            </svg>
          </button>
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
        <a href="/contact-us" class="contact-link">
          <h4>Pick your Date and Time</h4>
          <div class="chevron-circle alt">
            <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5.98999 7.52756C5.49293 7.52756 5.08999 7.12462 5.08999 6.62756C5.08999 6.13051 5.49293 5.72756 5.98999 5.72756V6.62756V7.52756ZM7.26389 5.99117C7.61536 6.34264 7.61536 6.91249 7.26389 7.26396L1.53632 12.9915C1.18485 13.343 0.615001 13.343 0.263529 12.9915C-0.0879426 12.6401 -0.0879426 12.0702 0.263529 11.7187L5.3547 6.62756L0.263529 1.53639C-0.0879426 1.18492 -0.0879426 0.615075 0.263529 0.263603C0.615001 -0.0878692 1.18485 -0.0878692 1.53632 0.263603L7.26389 5.99117ZM5.98999 6.62756V5.72756H6.62749V6.62756V7.52756H5.98999V6.62756Z" fill="white" />
            </svg>
          </div>
        </a>
        <div class="full-divider">
          <hr>
        </div>
        <div class="divider">
          <hr>
          or
          <hr>
        </div>
        <div class="contact-info">
          <h4>Maryanne Meily</h4>
          <h5><a href="tel:+12 345 6789">+12 345 6789</a></h5>
          <h5><a href="mailto:Email@gmail.com">Email@gmail.com</a></h5>
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

    <div style="width:100%;display:flex; justify-content:center;">
      <button class="expand-button" type="button" onclick="expandGallery()">
        <span id="gallery-button-text">VIEW MORE</span>
        <div class="chevron-circle">
          <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5.98999 7.52756C5.49293 7.52756 5.08999 7.12462 5.08999 6.62756C5.08999 6.13051 5.49293 5.72756 5.98999 5.72756V6.62756V7.52756ZM7.26389 5.99117C7.61536 6.34264 7.61536 6.91249 7.26389 7.26396L1.53632 12.9915C1.18485 13.343 0.615001 13.343 0.263529 12.9915C-0.0879426 12.6401 -0.0879426 12.0702 0.263529 11.7187L5.3547 6.62756L0.263529 1.53639C-0.0879426 1.18492 -0.0879426 0.615075 0.263529 0.263603C0.615001 -0.0878692 1.18485 -0.0878692 1.53632 0.263603L7.26389 5.99117ZM5.98999 6.62756V5.72756H6.62749V6.62756V7.52756H5.98999V6.62756Z" fill="#454545" />
          </svg>
        </div>
      </button>
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
      descriptionText = document.getElementById('description-text');
      descriptionButtonText = document.getElementById('description-button-text');
      gallery = document.getElementById('gallery');
      galleryButtonText = document.getElementById('gallery-button-text');

      function readmore() {
        descriptionText.classList.toggle('expand');
        descriptionButtonText.textContent = descriptionButtonText.textContent == 'READ MORE' ? 'READ LESS' : 'READ MORE';
        document.getElementById("description").scrollIntoView();
      }

      function expandGallery() {
        gallery.classList.toggle('expand');
        galleryButtonText.textContent = galleryButtonText.textContent == 'VIEW MORE' ? 'VIEW LESS' : 'VIEW MORE';
      }
    </script>
<?php
  }

  return ob_get_clean();
}

add_shortcode('property', 'property_shortcode');

?>