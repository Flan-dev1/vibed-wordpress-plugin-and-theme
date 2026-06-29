<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! hello_get_header_display() ) {
	return;
}

$is_editor = isset( $_GET['elementor-preview'] );
$site_name = get_bloginfo( 'name' );
$tagline   = get_bloginfo( 'description', 'display' );
$header_class = did_action( 'elementor/loaded' ) ? hello_get_header_layout_class() : '';
$menu_args = [
	'theme_location' => 'menu-1',
	'fallback_cb' => false,
	'container' => false,
	'echo' => false,
];
$header_nav_menu = wp_nav_menu( $menu_args );
$header_mobile_nav_menu = true; //wp_nav_menu( $menu_args ); // The same menu but separate call to avoid duplicate ID attributes.
?>
<header id="site-header" class="site-header dynamic-header <?php echo esc_attr( $header_class ); ?>">
	<div class="header-inner">
		<div class="site-branding show-<?php echo esc_attr( hello_elementor_get_setting( 'hello_header_logo_type' ) ); ?>">
			<?php if ( has_custom_logo() && ( 'title' !== hello_elementor_get_setting( 'hello_header_logo_type' ) || $is_editor ) ) : ?>
				<div class="site-logo <?php echo esc_attr( hello_show_or_hide( 'hello_header_logo_display' ) ); ?>">
					<?php the_custom_logo(); ?>
				</div>
			<?php endif;

			if ( $site_name && ( 'logo' !== hello_elementor_get_setting( 'hello_header_logo_type' ) || $is_editor ) ) : ?>
				<div class="site-title <?php echo esc_attr( hello_show_or_hide( 'hello_header_logo_display' ) ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr__( 'Home', 'hello-elementor' ); ?>" rel="home">
						<?php echo esc_html( $site_name ); ?>
					</a>
				</div>
			<?php endif;

			if ( $tagline && ( hello_elementor_get_setting( 'hello_header_tagline_display' ) || $is_editor ) ) : ?>
				<p class="site-description <?php echo esc_attr( hello_show_or_hide( 'hello_header_tagline_display' ) ); ?>">
					<?php echo esc_html( $tagline ); ?>
				</p>
			<?php endif; ?>
		</div>

		<?php if ( $header_nav_menu ) : ?>
			<nav class="site-navigation <?php echo esc_attr( hello_show_or_hide( 'hello_header_menu_display' ) ); ?>" aria-label="<?php echo esc_attr__( 'Main menu', 'hello-elementor' ); ?>">
				<?php
				// PHPCS - escaped by WordPress with "wp_nav_menu"
				echo $header_nav_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</nav>
		<?php endif; ?>
		<?php if ( $header_mobile_nav_menu ) : ?>
      
      <button class="nav-menu" type="button" aria-label="menu">
        <svg id="bars" width="18" height="12" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <line y1="0.649988" x2="18" y2="0.649988" stroke="#FEEDE6" stroke-width="1.3"/>
            <line y1="5.64999" x2="18" y2="5.64999" stroke="#FEEDE6" stroke-width="1.3"/>
            <line y1="10.65" x2="18" y2="10.65" stroke="#FEEDE6" stroke-width="1.3"/>
        </svg>
        <svg id="x" width="18" height="18" class="hide" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">><path fill="#FEEDE6" d="M504.6 148.5C515.9 134.9 514.1 114.7 500.5 103.4C486.9 92.1 466.7 93.9 455.4 107.5L320 270L184.6 107.5C173.3 93.9 153.1 92.1 139.5 103.4C125.9 114.7 124.1 134.9 135.4 148.5L278.3 320L135.4 491.5C124.1 505.1 125.9 525.3 139.5 536.6C153.1 547.9 173.3 546.1 184.6 532.5L320 370L455.4 532.5C466.7 546.1 486.9 547.9 500.5 536.6C514.1 525.3 515.9 505.1 504.6 491.5L361.7 320L504.6 148.5z"/></svg>
      </button>

		<?php endif; ?>
	</div>
</header>

<div class="side-nav">
  <div class="side-nav-top">
      <button type="button" class="side-nav-exit">
        <svg width="18" height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">><path fill="#FEEDE6" d="M504.6 148.5C515.9 134.9 514.1 114.7 500.5 103.4C486.9 92.1 466.7 93.9 455.4 107.5L320 270L184.6 107.5C173.3 93.9 153.1 92.1 139.5 103.4C125.9 114.7 124.1 134.9 135.4 148.5L278.3 320L135.4 491.5C124.1 505.1 125.9 525.3 139.5 536.6C153.1 547.9 173.3 546.1 184.6 532.5L320 370L455.4 532.5C466.7 546.1 486.9 547.9 500.5 536.6C514.1 525.3 515.9 505.1 504.6 491.5L361.7 320L504.6 148.5z"/></svg>
      </button>
  </div>
  <a href="/" style="align-self:end;">
    <h2 class="h3" >Meily Properties</h2>
  </a>
  <div class="side-nav-links">
    <a href="/about" class="link link--major">Who We Are</a>
    <a href="/about" class="link">About</a>
    <a href="/testimonials" class="link">Testimonials</a>
    <a href="/" class="link link--major">Services</a>
    <a href="" class="link">Buy</a>
    <a href="" class="link">Rent</a>
    <a href="" class="link">Sell</a>
    <a href="/" class="link link--major">Cities</a>
    <a href="" class="link">Makati</a>
    <a href="" class="link">Manila</a>
    <a href="" class="link">BGC</a>
    <a href="" class="link">Muntinlupa</a>
    <a href="" class="link">Alabang Village</a>
    <a href="" class="link">Hillsborough</a>
    <a href="" class="link">Alabang Hills</a>
    <a href="" class="link">Parañaque</a>
    <a href="" class="link">Sucat</a>
    <a href="" class="link">Better Living</a>
    <a href="" class="link">BF Homes</a>
    <a href="" class="link">Siargao</a>
    <a href="" class="link">Palawan</a>
    <a href="" class="link">El Nido</a>
    <a href="" class="link">Coron</a>
    <a href="" class="link">Puerto Prinsesa</a>
    <a href="" class="link">Tagaytay</a>
    <a href="" class="link">Cavite</a>
    <a href="" class="link">Batangas</a>
  </div>
</div>