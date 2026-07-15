<?php

namespace StarFlan\RealEstate;

defined('ABSPATH') || exit;

final class AdminPage
{
  private $records;

  public function __construct(RecordService $records)
  {
    $this->records = $records;
  }

  public function menu(): void
  {
    add_menu_page(__('StarFlan Data', 'starflan-real-estate'), __('StarFlan Data', 'starflan-real-estate'), 'edit_posts', 'starflan-data', array($this, 'render'), 'dashicons-database-import', 25);
    add_submenu_page('starflan-data', __('Add / Import', 'starflan-real-estate'), __('Add / Import', 'starflan-real-estate'), 'edit_posts', 'starflan-data', array($this, 'render'));
  }

  public function assets(string $hook): void
  {
    $screen = get_current_screen();
    $post_types = array_column(Schema::all(), 'post_type');
    if ('toplevel_page_starflan-data' !== $hook && (! $screen || ! in_array($screen->post_type, $post_types, true))) {
      return;
    }
    wp_enqueue_media();
    wp_enqueue_style('starflan-admin', STARFLAN_RE_URL . 'assets/css/admin.css', array(), STARFLAN_RE_VERSION);
    wp_enqueue_script('starflan-admin', STARFLAN_RE_URL . 'assets/admin.js', array('jquery'), STARFLAN_RE_VERSION, true);
    wp_localize_script(
      'starflan-admin',
      'StarFlanAdmin',
      array(
        'ajaxUrl'       => admin_url('admin-ajax.php'),
        'nonce'         => wp_create_nonce('starflan_search_estatik_properties'),
        'noResults'     => __('No Estatik properties found.', 'starflan-real-estate'),
        'searchError'   => __('Property search failed.', 'starflan-real-estate'),
        'alreadyAdded'  => __('That property is already assigned.', 'starflan-real-estate'),
        'remove'        => __('Remove', 'starflan-real-estate'),
      )
    );
  }

  public function render(): void
  {
    if (! current_user_can('edit_posts')) {
      wp_die(esc_html__('You do not have permission to manage this data.', 'starflan-real-estate'));
    }
    $schemas = Schema::all();
    $type = isset($_GET['type']) ? sanitize_key($_GET['type']) : array_key_first($schemas);
    $type = isset($schemas[$type]) ? $type : array_key_first($schemas);
    $schema = $schemas[$type];
?>
    <div class="wrap starflan-admin">
      <h1><?php esc_html_e('StarFlan Data', 'starflan-real-estate'); ?></h1>
      <?php $this->notice(); ?>
      <nav class="nav-tab-wrapper">
        <?php foreach ($schemas as $key => $item) : ?>
          <a class="nav-tab <?php echo $key === $type ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url(admin_url('admin.php?page=starflan-data&type=' . $key)); ?>"><?php echo esc_html($item['label']); ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="starflan-grid">
        <section class="starflan-card">
          <h2><?php printf(esc_html__('Add %s', 'starflan-real-estate'), esc_html($schema['label'])); ?></h2>
          <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="starflan_create">
            <input type="hidden" name="type" value="<?php echo esc_attr($type); ?>">
            <?php wp_nonce_field('starflan_create_' . $type); ?>
            <?php foreach ($schema['fields'] as $key => $field) {
              self::render_field($key, $field);
            } ?>
            <?php submit_button(sprintf(__('Create %s', 'starflan-real-estate'), $schema['label'])); ?>
          </form>
        </section>
        <section class="starflan-card">
          <h2><?php esc_html_e('CSV Upload', 'starflan-real-estate'); ?></h2>
          <p><?php esc_html_e('Use a header row with these columns:', 'starflan-real-estate'); ?> <code><?php echo esc_html(implode(',', array_keys($schema['fields']))); ?></code></p>
          <p><?php esc_html_e('Media and property fields use WordPress IDs.', 'starflan-real-estate'); ?></p>
          <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="starflan_import">
            <input type="hidden" name="type" value="<?php echo esc_attr($type); ?>">
            <?php wp_nonce_field('starflan_import_' . $type); ?>
            <input type="file" name="csv" accept=".csv,text/csv" required>
            <?php submit_button(__('Import CSV', 'starflan-real-estate'), 'secondary'); ?>
          </form>
        </section>
        <?php if ('city' === $type) {
          $this->render_city_assignment_manager($schema);
        } ?>
      </div>
    </div>
  <?php
  }

  public function handle_create(): void
  {
    $this->authorize('create');
    $type = sanitize_key(wp_unslash($_POST['type']));
    check_admin_referer('starflan_create_' . $type);
    $raw = isset($_POST['starflan']) && is_array($_POST['starflan']) ? wp_unslash($_POST['starflan']) : array();
    $result = $this->records->create($type, $raw);
    $this->redirect($type, is_wp_error($result) ? $result->get_error_message() : __('Record created.', 'starflan-real-estate'), is_wp_error($result));
  }

  public function handle_import(): void
  {
    $this->authorize('import');
    $type = sanitize_key(wp_unslash($_POST['type']));
    check_admin_referer('starflan_import_' . $type);
    if (empty($_FILES['csv']['tmp_name']) || ! is_string($_FILES['csv']['tmp_name']) || ! is_string($_FILES['csv']['name'] ?? null) || UPLOAD_ERR_OK !== (int) $_FILES['csv']['error'] || ! is_uploaded_file($_FILES['csv']['tmp_name'])) {
      $this->redirect($type, __('The CSV upload failed.', 'starflan-real-estate'), true);
    }
    $name = sanitize_file_name(wp_unslash($_FILES['csv']['name']));
    if ('csv' !== strtolower(pathinfo($name, PATHINFO_EXTENSION)) || (int) $_FILES['csv']['size'] > 5 * MB_IN_BYTES) {
      $this->redirect($type, __('Upload a CSV file no larger than 5 MB.', 'starflan-real-estate'), true);
    }
    $handle = fopen($_FILES['csv']['tmp_name'], 'rb'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
    if (false === $handle) {
      $this->redirect($type, __('The CSV file could not be read.', 'starflan-real-estate'), true);
    }
    $headers = fgetcsv($handle);
    $headers = is_array($headers) ? array_map(static function ($header) {
      return sanitize_key(preg_replace('/^\xEF\xBB\xBF/', '', $header));
    }, $headers) : array();
    $schema = Schema::get($type);
    if (! $schema || ! $headers || array_diff(array_keys($schema['fields']), $headers)) {
      fclose($handle);
      $this->redirect($type, __('The CSV header does not contain all required columns.', 'starflan-real-estate'), true);
    }
    $created = 0;
    $failed = 0;
    while (($row = fgetcsv($handle)) !== false) {
      if (2000 <= $created + $failed) {
        break;
      }
      $row = array_pad($row, count($headers), '');
      $data = array_combine($headers, array_slice($row, 0, count($headers)));
      $result = $this->records->create($type, $data);
      is_wp_error($result) ? ++$failed : ++$created;
    }
    fclose($handle);
    $message = sprintf(__('Import complete: %1$d created, %2$d skipped.', 'starflan-real-estate'), $created, $failed);
    $this->redirect($type, $message, 0 === $created && 0 < $failed);
  }

  public function handle_assign_properties(): void
  {
    if (! current_user_can('edit_posts') || ! isset($_POST['city_id'])) {
      wp_die(esc_html__('Invalid request.', 'starflan-real-estate'));
    }
    $city_id = absint($_POST['city_id']);
    check_admin_referer('starflan_assign_properties_' . $city_id);
    if (! current_user_can('edit_post', $city_id)) {
      wp_die(esc_html__('You do not have permission to edit this City.', 'starflan-real-estate'));
    }
    $property_ids = isset($_POST['starflan']) && is_array($_POST['starflan']) && isset($_POST['starflan']['properties'])
      ? wp_unslash($_POST['starflan']['properties'])
      : array();
    $result = $this->records->assign_estatik_properties($city_id, $property_ids);
    $this->redirect(
      'city',
      is_wp_error($result) ? $result->get_error_message() : __('City property assignments saved.', 'starflan-real-estate'),
      is_wp_error($result),
      array('city_id' => $city_id)
    );
  }

  public function search_estatik_properties(): void
  {
    check_ajax_referer('starflan_search_estatik_properties', 'nonce');
    if (! current_user_can('edit_posts')) {
      wp_send_json_error(array('message' => __('You do not have permission to search properties.', 'starflan-real-estate')), 403);
    }
    if (! Estatik::is_available()) {
      wp_send_json_error(array('message' => __('Estatik is not active or its property post type is unavailable.', 'starflan-real-estate')), 409);
    }
    $search = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
    $results = array_map(
      static function (\WP_Post $property): array {
        return array('id' => $property->ID, 'title' => get_the_title($property));
      },
      Estatik::search($search)
    );
    wp_send_json_success($results);
  }

  public static function render_field(string $key, array $field, $value = ''): void
  {
    $id = 'starflan-' . sanitize_html_class($key);
    $required = ! empty($field['required']) ? ' required' : '';
    echo '<div class="starflan-field"><label for="' . esc_attr($id) . '"><strong>' . esc_html($field['label']) . '</strong></label>';
    if ('estatik_properties' === $field['type']) {
      $ids = is_array($value) ? $value : array();
      echo '<div class="starflan-property-picker" data-field="' . esc_attr($key) . '">';
      if (! Estatik::is_available()) {
        echo '<p class="notice notice-warning inline">' . esc_html__('Estatik is not active or its property post type is unavailable.', 'starflan-real-estate') . '</p>';
      }
      echo '<div class="starflan-assigned-properties">';
      foreach ($ids as $property_id) {
        $property_id = absint($property_id);
        if (! $property_id) {
          continue;
        }
        $property_title = Estatik::is_property($property_id) ? get_the_title($property_id) : __('Unavailable Estatik property', 'starflan-real-estate');
        echo '<div class="starflan-assigned-property"><input type="hidden" name="starflan[' . esc_attr($key) . '][]" value="' . esc_attr($property_id) . '"><span>' . esc_html($property_title) . ' (#' . esc_html($property_id) . ')</span><button type="button" class="button-link-delete starflan-remove-property">' . esc_html__('Remove', 'starflan-real-estate') . '</button></div>';
      }
      echo '</div><div class="starflan-property-search-row"><input type="search" class="regular-text starflan-property-search" placeholder="' . esc_attr__('Search Estatik property name or ID', 'starflan-real-estate') . '"><button type="button" class="button starflan-search-properties">' . esc_html__('Search', 'starflan-real-estate') . '</button></div><div class="starflan-property-results"></div>';
      echo '<p class="description">' . esc_html__('Search Estatik listings, then add one or more properties to this city.', 'starflan-real-estate') . '</p></div>';
    } elseif ('textarea' === $field['type']) {
      echo '<textarea class="large-text" rows="5" id="' . esc_attr($id) . '" name="starflan[' . esc_attr($key) . ']"' . $required . '>' . esc_textarea($value) . '</textarea>';
    } elseif ('relation' === $field['type']) {
      $target = Schema::get($field['target']);
      $posts = get_posts(array('post_type' => $target['post_type'], 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC'));
      echo '<select class="regular-text" id="' . esc_attr($id) . '" name="starflan[' . esc_attr($key) . ']"' . $required . '><option value="">' . esc_html__('Select…', 'starflan-real-estate') . '</option>';
      foreach ($posts as $post) {
        echo '<option value="' . esc_attr($post->ID) . '"' . selected((int) $value, $post->ID, false) . '>' . esc_html($post->post_title) . ' (#' . esc_html($post->ID) . ')</option>';
      }
      echo '</select>';
    } elseif ('media' === $field['type']) {
      echo '<input type="hidden" class="starflan-media-id" id="' . esc_attr($id) . '" name="starflan[' . esc_attr($key) . ']" value="' . esc_attr($value) . '"><button type="button" class="button starflan-select-media">' . esc_html__('Choose from Media Library', 'starflan-real-estate') . '</button><span class="starflan-media-label">' . ($value ? esc_html(get_the_title((int) $value)) : '') . '</span>';
    } else {
      $type = in_array($field['type'], array('url', 'number'), true) ? $field['type'] : 'text';
      $attrs = 'number' === $type ? ' min="' . esc_attr($field['min'] ?? '') . '" max="' . esc_attr($field['max'] ?? '') . '" step="' . esc_attr($field['step'] ?? 'any') . '"' : '';
      echo '<input class="regular-text" type="' . esc_attr($type) . '" id="' . esc_attr($id) . '" name="starflan[' . esc_attr($key) . ']" value="' . esc_attr($value) . '"' . $attrs . $required . '>';
    }
    echo '</div>';
  }

  private function authorize(string $action): void
  {
    if (! current_user_can('edit_posts') || ! isset($_POST['type'])) {
      wp_die(esc_html__('Invalid request.', 'starflan-real-estate'));
    }
  }

  private function render_city_assignment_manager(array $schema): void
  {
    $cities = get_posts(
      array(
        'post_type'      => $schema['post_type'],
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
      )
    );
    $city_id = isset($_GET['city_id']) ? absint($_GET['city_id']) : 0;
    if ($city_id && $schema['post_type'] !== get_post_type($city_id)) {
      $city_id = 0;
    }
  ?>
    <section class="starflan-card starflan-card-wide">
      <h2><?php esc_html_e('Assign Properties to Existing City', 'starflan-real-estate'); ?></h2>
      <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" class="starflan-city-selector">
        <input type="hidden" name="page" value="starflan-data">
        <input type="hidden" name="type" value="city">
        <label for="starflan-existing-city"><strong><?php esc_html_e('City', 'starflan-real-estate'); ?></strong></label>
        <select id="starflan-existing-city" name="city_id" required>
          <option value=""><?php esc_html_e('Select a City…', 'starflan-real-estate'); ?></option>
          <?php foreach ($cities as $city) : ?>
            <option value="<?php echo esc_attr($city->ID); ?>" <?php selected($city_id, $city->ID); ?>><?php echo esc_html(get_the_title($city)); ?> (#<?php echo esc_html($city->ID); ?>)</option>
          <?php endforeach; ?>
        </select>
        <?php submit_button(__('Load City', 'starflan-real-estate'), 'secondary', 'submit', false); ?>
      </form>
      <?php if (! $cities) : ?>
        <p><?php esc_html_e('Create or import a City before assigning properties.', 'starflan-real-estate'); ?></p>
      <?php elseif ($city_id) : ?>
        <hr>
        <h3><?php echo esc_html(get_the_title($city_id)); ?></h3>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <input type="hidden" name="action" value="starflan_assign_properties">
          <input type="hidden" name="city_id" value="<?php echo esc_attr($city_id); ?>">
          <?php wp_nonce_field('starflan_assign_properties_' . $city_id); ?>
          <?php
          $property_ids = get_post_meta($city_id, $schema['fields']['properties']['meta_key'], true);
          self::render_field('properties', $schema['fields']['properties'], is_array($property_ids) ? $property_ids : array());
          submit_button(__('Save Property Assignments', 'starflan-real-estate'));
          ?>
        </form>
      <?php endif; ?>
    </section>
<?php
  }

  private function redirect(string $type, string $message, bool $error = false, array $extra = array()): void
  {
    $url = add_query_arg(array_merge(array('page' => 'starflan-data', 'type' => $type, 'sf_message' => $message, 'sf_error' => $error ? 1 : 0), $extra), admin_url('admin.php'));
    wp_safe_redirect($url);
    exit;
  }

  private function notice(): void
  {
    if (empty($_GET['sf_message'])) {
      return;
    }
    $message = sanitize_text_field(wp_unslash($_GET['sf_message']));
    $class = ! empty($_GET['sf_error']) ? 'notice notice-error' : 'notice notice-success';
    echo '<div class="' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
  }
}
