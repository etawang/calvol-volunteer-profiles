<?php
/*
Plugin Name: Calvol Volunteer Profiles
Description: Implementation of volunteer profiles for californiavolunteers. Combines a custom user dashboard with functionality from the UltimateMember plugin. Please install UltimateMember in order to use this plugin.
Version: 0.1
// */
define('CALVOL_VOLUNTEER_PROFILES_DIR', plugin_dir_path( __FILE__ ));
define('CALVOL_VOLUNTEER_PROFILE_URL', '/volunteer-dashboard');
define('CALVOL_VOLUNTEER_CONTACT_FORMS', array(49343));
add_action('init', 'vol_profiles_init');
add_action('init', 'register_shortcodes');
add_action('admin_init', 'vol_profiles_admin_init');
add_action('wp_enqueue_scripts', 'volunteer_match_wp_enqueue_scripts');
add_action('um_after_user_updated', 'redirect_to_dashboard');

function vol_profiles_init () {
	remove_action('um_profile_header', 'um_profile_header', 9);
  /* Include Functionality */
	foreach (glob(__DIR__ . '/includes/*.php') as $file) {
		require_once $file;
	}

  foreach (CALVOL_VOLUNTEER_CONTACT_FORMS as $form_id) {
    add_action("wpforms_process_complete_{$form_id}", 'create_volunteer_user');
  }
}

function vol_profiles_admin_init () {
  require_once CALVOL_VOLUNTEER_PROFILES_DIR . 'includes/class-calvol-volunteer-profiles-plugin-updater.php';
}

function vol_profiles_enqueue_scripts () {
	$version      = get_plugin_data( __FILE__ )['Version'];
	$frontend_css = CALVOL_VOLUNTEER_PROFILES_DIR .  'css/frontend.css';

	// Enqueue FrontEnd Style.
	wp_enqueue_style('volunteer-profile-styles', $frontend_css, array(), $version);

}

function redirect_to_dashboard () {
  exit(wp_redirect(CALVOL_VOLUNTEER_PROFILE_URL));
}
?>
