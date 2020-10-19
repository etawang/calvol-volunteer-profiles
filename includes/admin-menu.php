<?php
add_action('admin_menu', 'calvol_volunteer_profiles_admin_menu');

function calvol_volunteer_profiles_admin_menu () {
  add_menu_page(
		'CalVol Volunteer Profiles',
		'CalVol Volunteer Profiles',
		'manage_options',
		'calvol-volunteer-profile-menu',
		'calvol_volunteer_profile_menu_page'
	);
}

function calvol_volunteer_profiles_menu_page () {
  echo "<h1>Hello World!</h1>";
}

?>
