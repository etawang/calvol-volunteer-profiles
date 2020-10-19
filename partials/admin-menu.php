<?php
  $calvol_nonce = wp_create_nonce('calvol_volunteer_profiles_admin');

  if (isset($_POST['calvol_volunteer_profiles_submit'])) {
	  calvol_volunteer_profiles_save_options($_POST);
  }

  $calvol_create_vol_profiles_checked = get_option('calvol_create_vol_profiles', false) ? 'checked' : '';
?>

<div class="container-fluid mt-4">
	<form id="calvol-volunteer-profiles-admin-form" action="<?php print esc_url(admin_url('admin.php?page=calvol-volunteer-profiles-menu')); ?>" method="POST">
    <div class="row">
      <br>
      <input type="hidden" name="calvol_volunteer_profiles_admin" value="<?php echo esc_attr($calvol_nonce); ?>" />
      <label for="create_vol_profiles">Create volunteer profiles when contact form is submitted?</label>&emsp;
      <input type="checkbox" name="create_vol_profiles" id="create_vol_profiles" <?php print $calvol_create_vol_profiles_checked;?>>
    <br>
    <br>
    </div>
    <div class="row">
      <input type="submit" value="Save Changes">
      <input type="hidden" name="calvol_volunteer_profiles_submit" >
    <div>
	</form>
</div>
