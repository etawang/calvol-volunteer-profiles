<?php
function create_volunteer_user ($fields) {
  if (!get_option('calvol_create_vol_profiles')) {
    return;
  }
  error_log("Creating user");
  error_log(print_r($fields));
}
?>
