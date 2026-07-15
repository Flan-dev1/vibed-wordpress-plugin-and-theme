<?php

function get_sort_component(array $allowed_sorts, string $current_sort)
{
  $query_args = array();

  if (! empty($_GET) && is_array($_GET)) {
    $query_args = wp_unslash($_GET);
    unset($query_args['sort']);
  }

  $render_hidden_inputs = function ($value, string $name) use (&$render_hidden_inputs) {
    if (is_array($value)) {
      foreach ($value as $key => $nested_value) {
        $render_hidden_inputs($nested_value, $name . '[' . $key . ']');
      }

      return;
    }
?>
    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
<?php
  };

  $format_sort_label = function (string $sort): string {
    return ucwords(str_replace(array('_', '-'), ' ', $sort));
  };
?>
  <!-- change testimonials name -->
  <div class="testimonials-toolbar">
    <div class="sort-control">
      <span class="screen-reader-text">Sort Component</span>

      <form
        class="testimonials-sort"
        method="get"
        action="<?php echo esc_url(get_permalink()); ?>">
        <label for="testimonial-sort" class="screen-reader-text">
          <?php esc_html_e('Sort testimonials'); ?>
        </label>

        <select
          id="testimonial-sort"
          name="sort"
          onchange="this.form.submit()">
          <?php
          foreach ($allowed_sorts as $sort) {
          ?>
            <option value="<?php echo esc_attr($sort); ?>" <?php selected($current_sort, $sort); ?>>
              <?php echo esc_html($format_sort_label($sort)); ?>
            </option>
          <?php
          }
          ?>
        </select>

        <?php
        foreach ($query_args as $name => $value) {
          $render_hidden_inputs($value, (string) $name);
        }
        ?>

        <noscript>
          <button type="submit">
            <?php esc_html_e('Apply'); ?>
          </button>
        </noscript>
      </form>
    </div>
  </div>

<?php
}
