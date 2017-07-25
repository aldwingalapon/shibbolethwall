<?php
/**
 * Check that Avada theme is used
 *
 * @return bool
 * @since 1.5.0
 */
function wphb_et_avada_theme_active() {
	$theme = wp_get_theme();
	return ( 'avada' === strtolower( $theme ) || 'avada' === strtolower( $theme->get_template() ) );
}

/**
 * Show notice
 *
 * @since 1.5.0
 */
function wphb_avada_compiler_notice() {
	$settings_url = admin_url( 'themes.php?page=avada_options' );
	?>
	<div class="notice-info notice wphb-notice">
		<p><?php printf( esc_attr__( 'JS Compiler detected in Avada Theme. For Hummingbird minification settings to work correctly, it is recommended you %s in Advanced - Dynamic CSS & JS.', 'wphb' ), '<a href="' . $settings_url . '" target="_blank">disable it</a>' ); ?></p>
	</div>
	<?php

}

/**
 * Check to see if JS Compiler is enabled in Avada
 *
 * @since 1.5.0
 */
if ( wphb_et_avada_theme_active() ) {
	if ( ! function_exists( 'wphb_get_module' ) ) {
		include_once( wphb_plugin_dir() . 'helpers/wp-hummingbird-helpers-modules.php' );
	}
	$fusion = get_option( 'fusion_options' );
	if ( '1' === $fusion['js_compiler'] ) {
		add_action( 'admin_notices', 'wphb_avada_compiler_notice' );
	}
}
