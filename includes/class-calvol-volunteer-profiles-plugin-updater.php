<?php
/**
 * Plugin Updater
 *
 * @see https://github.com/WordPress/WordPress/blob/master/wp-admin/update.php
 * @see https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/class-theme-upgrader.php
 * @see https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/class-wp-upgrader.php
 * @see https://github.com/Danny-Guzman/volunteer-match/blob/6a96cd7143e2066480ea7fd3956756849eb0cc5f/core/class-volunteer-match-plugin-update.php
 *
 */

if ( ! class_exists( 'Calvol_Volunteer_Profiles_Plugin_Updater' ) ) {
	class Calvol_Volunteer_Profiles_Plugin_Updater {

		protected $plugin_name;
		protected $current_version;
		protected $slug;
		protected $plugin_file;
		protected $transient_name = 'calvol_volunteer_profiles_update_plugins';
		protected $repo = 'https://api.github.com/repos/etawang/calvol-volunteer-profiles';
		protected $args;
		public function __construct( $plugin_slug ) {
			$plugin_data = get_plugin_data( sprintf( '%1$s/%2$s/%2$s.php', WP_PLUGIN_DIR, $plugin_slug ) );

			// Set the class public variables.
			$this->current_version = $plugin_data['Version'];
			$this->plugin_name     = $plugin_data['Name'];
			$this->slug            = $plugin_slug;
			$this->plugin_file     = sprintf( '%1$s/%1$s.php', $plugin_slug );

			$this->args = array(
				'headers' => array(
					'Accept:'       => 'application/vnd.github.v3+json',
					'application/vnd.github.VERSION.raw',
					'application/octet-stream',
				),
			);

			// define the alternative API for plugin update checking.
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_plugin_update' ) );

			// Define the alternative response for information checking.
			add_filter( 'site_transient_update_plugins', array( $this, 'add_plugins_to_update_notification' ) );

			add_filter( 'plugins_api', array( $this, 'update_plugins_changelog' ), 20, 3 );

			// Define the alternative response for download_package which gets called during theme upgrade.
			add_filter( 'upgrader_pre_download', array( $this, 'download_package' ), 10, 3 );

			// Define the alternative response for upgrader_pre_install.
			add_filter( 'upgrader_source_selection', array( $this, 'upgrader_source_selection' ), 10, 4 );

		}

		/**
		 * Alternative theme download for the WordPress Updater
		 *
		 * @see https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/class-wp-upgrader.php#L265
		 *
		 * @param  bool        $reply Whether to bail without returning the package. Default false.
		 * @param  string      $package The package file name.
		 * @param  WP_Upgrader $upgrader The WP_Upgrader instance.
		 *
		 * @return string
		 */
		public function download_package( $reply, $package, $upgrader ) {
			if ( isset( $upgrader->skin->plugin_info ) && $upgrader->skin->plugin_info['Name'] === $this->plugin_name ) {
				$theme = wp_remote_retrieve_body( wp_remote_get( $package, array_merge( $this->args, array( 'timeout' => 60 ) ) ) );
				// Now use the standard PHP file functions.
				$fp = fopen( sprintf( '%1$s/%2$s.zip', plugin_dir_path( __DIR__ ), $this->slug ), 'w' );
				fwrite( $fp, $theme );
				fclose( $fp );

				return sprintf( '%1$s/%2$s.zip', plugin_dir_path( __DIR__ ), $this->slug );
			}
			return $reply;
		}

		/**
		 * Alternative API for checking for plugin updates.
		 *
		 * @param  array $update_transient Transient containing plugin updates.
		 *
		 * @return array
		 */
		public function check_plugin_update( $update_transient ) {
			if ( ! isset( $update_transient->checked ) ) {
				return $update_transient;
			}

			$plugins = $update_transient->checked;

			$last_update = new stdClass();

			$payload = wp_remote_get( sprintf( '%1$s/releases/latest', $this->repo ), $this->args );

			if ( ! is_wp_error( $payload ) && wp_remote_retrieve_response_code( $payload ) === 200 ) {
				$payload = json_decode( wp_remote_retrieve_body( $payload ) );

				if ( ! empty( $payload ) && version_compare( $payload->tag_name, $this->current_version, '>' ) ) {
					$obj                 = new StdClass();
					$obj->name           = $this->plugin_name;
					$obj->slug           = $this->slug;
					$obj->plugin         = $this->plugin_file;
					$obj->new_version    = $payload->tag_name;
					$obj->published_date = ( new DateTime( $payload->published_at ) )->format( 'm/d/Y' );
					$obj->package        = $payload->zipball_url;

					$theme_response = array( $this->plugin_file => $obj );

					$update_transient->response = array_merge( ! empty( $update_transient->response ) ? $update_transient->response : array(), $theme_response );

					$last_update->checked  = $plugins;
					$last_update->response = $theme_response;
				} else {
					delete_site_transient( $this->transient_name );
				}
			}

			$last_update->last_checked = time();
			set_site_transient( $this->transient_name, $last_update );

			return $update_transient;
		}

		/**
		 * Adds the Calvol Volunteer Profiles Plugin Update Notification to List of Available Updates.
		 *
		 * @param  array $update_transient Transient containing plugin updates.
		 *
		 * @return array
		 */
		public function add_plugins_to_update_notification( $update_transient ) {
			$calvol_volunteer_profiles_update_plugins = get_site_transient( $this->transient_name );
			if ( ! is_object( $calvol_volunteer_profiles_update_plugins ) || ! isset( $calvol_volunteer_profiles_update_plugins->response ) ) {
				return $update_transient;
			}
			// Fix for warning messages on Dashboard / Updates page.
			if ( ! is_object( $update_transient ) ) {
				$update_transient = new stdClass();
			}

			$update_transient->response = array_merge(
				! empty( $update_transient->response ) ? $update_transient->response : array(),
				$calvol_volunteer_profiles_update_plugins->response
			);

			return $update_transient;
		}

		/**
		 * Filters the response for the current WordPress.org Plugin Installation API request.
		 * 
		 * @see https://developer.wordpress.org/reference/functions/plugins_api/
		 * 
		 * @param  false|object|array $result The result object or array. Default false.
		 * @param  string             $action The type of information being requested from the Plugin Installation API.
		 * @param  object             $args Plugin API arguments.
		 *
		 * @return false|object|array
		 */
		public function update_plugins_changelog( $result, $action, $args ) {
			if ( isset( $args->slug ) && $args->slug === $this->slug ) {
				$update_plugins = get_site_transient( $this->transient_name );
				if ( isset( $calvol_volunteer_profiles_update_plugins->response ) && isset( $calvol_volunteer_profiles_update_plugins->response[ $this->plugin_file ] ) ) {
					$tmp = $this->plugin_details();

					$tmp['version']      = $calvol_volunteer_profiles_update_plugins->response[ $this->plugin_file ]->new_version;
					$tmp['last_updated'] = $calvol_volunteer_profiles_update_plugins->response[ $this->plugin_file ]->published_date;

					$tmp['sections']['Changelog'] = $this->get_plugin_changelog( $tmp['version'] );

					return (object) $tmp;
				}
			}
			return $result;
		}

		/**
		 * Retrieve Plugin Changelog from repository
		 *
		 * @param  string $ver Repository branch.
		 *
		 * @return string
		 */
		public function get_plugin_changelog( $ver = 'master' ) {
			$logurl = sprintf( '%1$s/contents/changelog.txt?ref=%2$s', $this->repo, $ver );

			$changelog = wp_remote_get( $logurl, $this->args );

			if ( ! is_wp_error( $changelog ) && 200 === wp_remote_retrieve_response_code( $changelog ) ) {
				return sprintf( '<pre style="white-space: pre-line !important;">%1$s</pre>', base64_decode( json_decode( wp_remote_retrieve_body( $changelog ) )->content ) );
			} else {
				return 'No Changelog Available';
			}
		}

		/**
		 * Filters the source file location for the upgrade package.
		 *
		 * @see https://github.com/WordPress/WordPress/blob/master/wp-admin/includes/class-wp-upgrader.php#L524
		 *
		 * @param  string      $src File source location.
		 * @param  string      $rm_src Remote file source location.
		 * @param  WP_Upgrader $upgr WP_Upgrader instance.
		 * @param  array       $options Extra arguments passed to hooked filters.
		 *
		 * @return string
		 */
		public function upgrader_source_selection( $src, $rm_src, $upgr, $options ) {

			if ( ! isset( $options['plugin'] ) || $options['plugin'] !== $this->plugin_file ) {
				return $src;
			}

			$tmp = explode( '/', $src );
			array_shift( $tmp );
			array_pop( $tmp );
			$tmp[ count( $tmp ) - 1 ] = $tmp[ count( $tmp ) - 2 ];
			$tmp                      = sprintf( '/%1$s/', implode( '/', $tmp ) );

			rename( $src, $tmp );

			return $tmp;
		}

		/**
		 * Plugin Details
		 * 
		 * @see https://developer.wordpress.org/reference/functions/plugins_api/
		 * @return array
		 */
		public function plugin_details() {
			$view_details = array(
				'slug'     => plugin_basename( plugin_dir_path( __DIR__ ) ),
				'author'   => 'Esther Wang',
				'name'     => 'CalVol Volunteer Profiles',
				'sections' => array(
					'Description' => 'Implementation of volunteer profiles for californiavolunteers. Combines a custom user dashboard with functionality from the UltimateMember plugin.',
				),
			);

			return $view_details;
		}

	}
}

new Calvol_Volunteer_Profiles_Plugin_Updater( plugin_basename( plugin_dir_path( __DIR__ ) ) );
