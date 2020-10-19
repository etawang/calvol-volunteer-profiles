<?php
add_action('admin_menu', 'calvol_volunteer_profiles_admin_menu');

function calvol_volunteer_profiles_admin_menu () {
  add_menu_page(
		'CalVol Volunteer Profiles',
		'CalVol Volunteer Profiles',
		'manage_options',
		'calvol-volunteer-profiles-menu',
		'calvol_volunteer_profiles_menu_page'
	);
}

function calvol_volunteer_profiles_menu_page () {
  require_once CALVOL_VOLUNTEER_PROFILES_DIR . '/partials/admin-menu.php';
}

function calvol_volunteer_profiles_save_options ($values = array()) {
  update_option('calvol_create_vol_profiles', isset( $values['create_vol_profiles'] ) ? true : false);
}

?>
