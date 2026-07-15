<?php
function add_pagination(string $properties_page_url, $total_pages = 1, $current_page = 1)
{
  if ($total_pages > 1) {
    $pagination_placeholder = 999999999;

    $pagination_base = str_replace(
      (string) $pagination_placeholder,
      '%#%',
      add_query_arg(
        'page',
        $pagination_placeholder,
        $properties_page_url
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
      'page',
      $current_page - 1,
      $properties_page_url
    );

    $next_page_url = add_query_arg(
      'page',
      $current_page + 1,
      $properties_page_url
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
