<?php
function register_shortcodes () {
	add_shortcode('connected-opportunities', 'get_connection_table');
	add_shortcode('connection-count', 'get_connection_count');
	add_shortcode('hours-logged', 'get_hours_logged');
}

function query_form_entries ($form_id, $query) {
	global $wpdb;
	$results = $wpdb->get_results($query);
	foreach ($results as $index => $row) {
		$row->fields = json_decode($row->fields);
	}
	return $results;
}

function get_connected_opportunities () {
	$form_id = '49343';
	$user_email = um_user('user_email');
	// $query = "select fields from $table_name where json_extract(fields, '$.\"3\".value') = '$user_email' and form_id=$form_id;";
	// lesliethomasmph@gmail.com
	$table_name = 'wp_4_wpforms_entries';
	$query = "select fields from $table_name where json_extract(fields, '$.\"3\".value') = 'lesliethomasmph@gmail.com' and form_id=$form_id;";
	$results = query_form_entries($form_id, $query);
	foreach($results as $index => $row) {
		if ($row->fields->{'19'}->value == "") {
			unset($results[$index]);
		} else {
			$results[$index] = $row->fields;
		}
	}
	return $results;
}

function get_connection_table () {
  ob_start();
  include CALVOL_VOLUNTEER_PROFILES_DIR . '/partials/connection-table.php';
  return ob_get_clean();
}

function get_connection_count () {
	return count(get_connected_opportunities());
}

function get_hours_logged () {
	$form_id = '51535';
	$cur_user = get_current_user_id();
	$table_name = 'wp_4_wpforms_entries';
	$query = "select fields from $table_name where user_id=$cur_user and form_id=$form_id;";
	$results = query_form_entries($form_id, $query);

	$hours = 0;
	foreach($results as $row) {
		$hours += (int)$row->fields->{'5'}->value;
	}
	return $hours;
}

function get_hours_logged_for_opportunity ($opportunity_name) {
	$form_id = '51535';
	$cur_user = get_current_user_id();
	$table_name = 'wp_4_wpforms_entries';
	$query = "select fields from $table_name where user_id=$cur_user and form_id=$form_id;";
	$results = query_form_entries($form_id, $query);

	$hours = 0;
	foreach($results as $row) {
    if ($row->fields->{'2'}->value == $opportunity_name) {
      $hours += (int)$row->fields->{'5'}->value;
    }
	}
	return $hours;
}
?>
