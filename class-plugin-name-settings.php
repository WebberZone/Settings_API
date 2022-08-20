<?php
/**
 * Register Plugin_Name Settings.
 *
 * @link  https://webberzone.com
 * @since 1.0.0
 *
 * @package Plugin_Name
 * @subpackage Admin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Plugin_Name_Settings' ) ) :
	/**
	 * Plugin_Name Settings class to register the settings.
	 *
	 * @version 1.0
	 * @since   1.0.0
	 */
	class Plugin_Name_Settings {

		/**
		 * Class instance.
		 *
		 * @var class Class instance.
		 */
		public static $instance;

		/**
		 * Settings API.
		 *
		 * @since 1.0.0
		 *
		 * @var object Settings API.
		 */
		public $settings_api;

		/**
		 * Tools Page in Admin area.
		 *
		 * @since 1.0.0
		 *
		 * @var string Tools Page.
		 */
		public $tools_page;

		/**
		 * Prefix which is used for creating the unique filters and actions.
		 *
		 * @since 1.0.0
		 *
		 * @var string Prefix.
		 */
		public static $prefix;

		/**
		 * Settings Key.
		 *
		 * @since 1.0.0
		 *
		 * @var string Settings Key.
		 */
		public $settings_key;

		/**
		 * The slug name to refer to this menu by (should be unique for this menu).
		 *
		 * @since 1.0.0
		 *
		 * @var string Menu slug.
		 */
		public $menu_slug;

		/**
		 * Main constructor class.
		 *
		 * @since 1.0.0
		 */
		protected function __construct() {
			$this->settings_key = 'plugin_name_settings';
			self::$prefix       = 'plugin_name';
			$this->menu_slug    = 'plugin_name_options_page';

			$args = array(
				'menu_slug'         => $this->menu_slug,
				'default_tab'       => 'general',
				'help_sidebar'      => $this->get_help_sidebar(),
				'help_tabs'         => $this->get_help_tabs(),
				'admin_footer_text' => sprintf(
					/* translators: 1: Opening achor tag with Plugin page link, 2: Closing anchor tag, 3: Opening anchor tag with review link. */
					__( 'Thank you for using %1$sPlugin_Name%2$s! Please %3$srate us%2$s on %3$sWordPress.org%2$s', 'plugin-name' ),
					'<a href="https://webberzone.com/plugins/plugin-name/" target="_blank">',
					'</a>',
					'<a href="https://wordpress.org/support/plugin/plugin-name/reviews/#new-post" target="_blank">'
				),
			);

			$this->settings_api = new Plugin_Name_Admin\Settings_API( $this->settings_key, self::$prefix );
			$this->settings_api->set_translation_strings( $this->get_translation_strings() );
			$this->settings_api->set_props( $args );
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_registered_settings( $this->get_registered_settings() );
			$this->settings_api->set_upgraded_settings( $this->get_upgrade_settings() );

			add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );
			add_action( 'admin_head', array( $this, 'admin_head' ), 11 );
			add_action( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_actions_links' ) );
			add_action( 'plugin_name_settings_page_header', array( $this, 'settings_page_header' ), 11 );
			add_action( 'plugin_name_settings_sanitize', array( $this, 'change_settings_on_save' ), 99 );
		}

		/**
		 * Singleton instance
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Array containing the various strings used by the Settings_API.
		 *
		 * @since 1.0.0
		 *
		 * @return array Settings array
		 */
		public function get_translation_strings() {
			$strings = array(
				'page_title'           => esc_html__( 'Plugin_Name', 'plugin-name' ),
				'menu_title'           => esc_html__( 'Plugin_Name', 'plugin-name' ),
				'page_header'          => esc_html__( 'Header of the Settings Page', 'plugin-name' ),
				'reset_message'        => esc_html__( 'Settings have been reset to their default values. Reload this page to view the updated settings.', 'plugin-name' ),
				'success_message'      => esc_html__( 'Settings updated.', 'plugin-name' ),
				'save_changes'         => esc_html__( 'Save Changes', 'plugin-name' ),
				'reset_settings'       => esc_html__( 'Reset all settings', 'plugin-name' ),
				'reset_button_confirm' => esc_html__( 'Do you really want to reset all these settings to their default values?', 'plugin-name' ),
				'checkbox_modified'    => esc_html__( 'Modified from default setting', 'plugin-name' ),
			);

			/**
			 * Filter the array containing the settings' sections.
			 *
			 * @since 1.0.0
			 *
			 * @param array $strings Translation strings.
			 */
			return apply_filters( self::$prefix . '_translation_strings', $strings );

		}

		/**
		 * Array containing the settings' sections.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of settings' sections.
		 */
		public function get_settings_sections() {
			$sections = array(
				'general'    => __( 'General', 'plugin-name' ),
				'comments'   => __( 'Comments', 'plugin-name' ),
				'pingtracks' => __( 'Pingbacks/Trackbacks', 'plugin-name' ),
				'revisions'  => __( 'Revisions', 'plugin-name' ),
			);

			/**
			 * Filter the array containing the settings' sections.
			 *
			 * @since 1.0.0
			 *
			 * @param array $sections Array of settings' sections
			 */
			return apply_filters( self::$prefix . '_settings_sections', $sections );

		}


		/**
		 * Retrieve the array of plugin settings
		 *
		 * @since 1.0.0
		 *
		 * @return array Settings array
		 */
		public static function get_registered_settings() {

			$settings = array(
				'general'    => self::settings_general(),
				'comments'   => self::settings_comments(),
				'pingtracks' => self::settings_pingtracks(),
				'revisions'  => self::settings_revisions(),
			);

			/**
			 * Filters the settings array
			 *
			 * @since 1.0.0
			 *
			 * @param array $plugin_name_setings Settings array
			 */
			return apply_filters( self::$prefix . '_registered_settings', $settings );

		}

		/**
		 * Returns the Header settings.
		 *
		 * @since 1.0.0
		 *
		 * @return array Header settings.
		 */
		public static function settings_general() {

			$settings = array(
				'cron_on'         => array(
					'id'      => 'cron_on',
					'name'    => esc_html__( 'Activate scheduled closing', 'plugin-name' ),
					'desc'    => esc_html__( 'This creates a WordPress cron job using the schedule settings below. This cron job will execute the tasks to close comments, pingbacks/trackbacks or delete post revisions based on the settings from the other tabs.', 'plugin-name' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'cron_range_desc' => array(
					'id'   => 'cron_range_desc',
					'name' => '<strong>' . esc_html__( 'Time to run closing', 'plugin-name' ) . '</strong>',
					'desc' => esc_html__( 'The next two options allow you to set the time to run the cron. The cron job will run now if the hour:min set below if before the current time. e.g. if the time now is 20:30 hours and you set the schedule to 9:00. Else it will run later today at the scheduled time.', 'plugin-name' ),
					'type' => 'descriptive_text',
				),
				'cron_hour'       => array(
					'id'      => 'cron_hour',
					'name'    => esc_html__( 'Hour', 'plugin-name' ),
					'desc'    => '',
					'type'    => 'number',
					'options' => '0',
					'min'     => '0',
					'max'     => '23',
					'size'    => 'small',
				),
				'cron_min'        => array(
					'id'      => 'cron_min',
					'name'    => esc_html__( 'Minute', 'plugin-name' ),
					'desc'    => '',
					'type'    => 'number',
					'options' => '0',
					'min'     => '0',
					'max'     => '59',
					'size'    => 'small',
				),
				'cron_recurrence' => array(
					'id'      => 'cron_recurrence',
					'name'    => esc_html__( 'Run maintenance', 'plugin-name' ),
					'desc'    => '',
					'type'    => 'radio',
					'default' => 'daily',
					'options' => array(
						'daily'       => esc_html__( 'Daily', 'plugin-name' ),
						'weekly'      => esc_html__( 'Weekly', 'plugin-name' ),
						'fortnightly' => esc_html__( 'Fortnightly', 'plugin-name' ),
						'monthly'     => esc_html__( 'Monthly', 'plugin-name' ),
					),
				),
			);

			/**
			 * Filters the Header settings array
			 *
			 * @since 1.0.0
			 *
			 * @param array $settings Header Settings array
			 */
			return apply_filters( self::$prefix . '_settings_general', $settings );
		}

		/**
		 * Returns the Comments settings.
		 *
		 * @since 1.0.0
		 *
		 * @return array Comments settings.
		 */
		public static function settings_comments() {

			$settings = array(
				'close_comment'      => array(
					'id'      => 'close_comment',
					'name'    => esc_html__( 'Close comments', 'plugin-name' ),
					'desc'    => esc_html__( 'Enable to close comments - used for the automatic schedule as well as one time runs under the Tools tab.', 'plugin-name' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'comment_post_types' => array(
					'id'      => 'comment_post_types',
					'name'    => esc_html__( 'Post types to include', 'plugin-name' ),
					'desc'    => esc_html__( 'At least one option should be selected above. Select which post types on which you want comments closed.', 'plugin-name' ),
					'type'    => 'posttypes',
					'options' => 'post',
				),
				'comment_age'        => array(
					'id'      => 'comment_age',
					'name'    => esc_html__( 'Close comments on posts/pages older than', 'plugin-name' ),
					'desc'    => esc_html__( 'Comments that are older than the above number, in days, will be closed automatically if the schedule is enabled', 'plugin-name' ),
					'type'    => 'number',
					'options' => '90',
				),
				'comment_pids'       => array(
					'id'      => 'comment_pids',
					'name'    => esc_html__( 'Keep comments on these posts/pages open', 'plugin-name' ),
					'desc'    => esc_html__( 'Comma-separated list of post, page or custom post type IDs. e.g. 188,320,500', 'plugin-name' ),
					'type'    => 'numbercsv',
					'options' => '',
					'size'    => 'large',
				),
			);

			/**
			 * Filters the Comments settings array
			 *
			 * @since 1.0.0
			 *
			 * @param array $settings Comments Settings array
			 */
			return apply_filters( self::$prefix . '_settings_comments', $settings );
		}

		/**
		 * Returns the Pingbacks/Trackbacks settings.
		 *
		 * @since 1.0.0
		 *
		 * @return array Pingbacks/Trackbacks settings.
		 */
		public static function settings_pingtracks() {

			$settings = array(
				'close_pbtb'      => array(
					'id'      => 'close_pbtb',
					'name'    => esc_html__( 'Close Pingbacks/Trackbacks', 'plugin-name' ),
					'desc'    => esc_html__( 'Enable to close pingbacks and trackbacks - used for the automatic schedule as well as one time runs under the Tools tab.', 'plugin-name' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'pbtb_post_types' => array(
					'id'      => 'pbtb_post_types',
					'name'    => esc_html__( 'Post types to include', 'plugin-name' ),
					'desc'    => esc_html__( 'At least one option should be selected above. Select which post types on which you want pingbacks/trackbacks closed.', 'plugin-name' ),
					'type'    => 'posttypes',
					'options' => 'post',
				),
				'pbtb_age'        => array(
					'id'      => 'pbtb_age',
					'name'    => esc_html__( 'Close pingbacks/trackbacks on posts/pages older than', 'plugin-name' ),
					'desc'    => esc_html__( 'Pingbacks/Trackbacks that are older than the above number, in days, will be closed automatically if the schedule is enabled', 'plugin-name' ),
					'type'    => 'number',
					'options' => '90',
				),
				'pbtb_pids'       => array(
					'id'      => 'pbtb_pids',
					'name'    => esc_html__( 'Keep pingbacks/trackbacks on these posts/pages open', 'plugin-name' ),
					'desc'    => esc_html__( 'Comma-separated list of post, page or custom post type IDs. e.g. 188,320,500', 'plugin-name' ),
					'type'    => 'numbercsv',
					'options' => '',
					'size'    => 'large',
				),
			);

			/**
			 * Filters the Pingbacks/Trackbacks settings array
			 *
			 * @since 1.0.0
			 *
			 * @param array $settings Pingbacks/Trackbacks Settings array
			 */
			return apply_filters( self::$prefix . '_settings_pingtracks', $settings );
		}

		/**
		 * Returns the Revisions settings.
		 *
		 * @since 1.0.0
		 *
		 * @return array Revisions settings.
		 */
		public static function settings_revisions() {

			$settings = array(
				'delete_revisions'    => array(
					'id'      => 'delete_revisions',
					'name'    => esc_html__( 'Delete post revisions', 'plugin-name' ),
					'desc'    => esc_html__( 'The WordPress revisions system stores a record of each saved draft or published update. This can gather up a lot of overhead in the long run. Use this option to delete old post revisions.', 'plugin-name' ),
					'type'    => 'checkbox',
					'options' => false,
				),
				'revision_post_types' => array(
					'id'   => 'revision_post_types',
					'name' => '<strong>' . esc_html__( 'Number of revisions', 'plugin-name' ) . '</strong>',
					/* translators: 1: Code. */
					'desc' => sprintf( esc_html__( 'Limit the number of revisions that WordPress stores in the database for each of the post types below. %1$s -2: ignore setting from this plugin, %1$s -1: store every revision, %1$s 0: do not store any revisions, %1$s >0: store that many revisions per post. Old revisions are automatically deleted.', 'plugin-name' ), '<br />' ),
					'type' => 'descriptive_text',
				),
			);

			/**
			 * Filters the Revisions settings array
			 *
			 * @since 1.0.0
			 *
			 * @param array $settings Revisions Settings array
			 */
			return apply_filters( self::$prefix . '_settings_revisions', $settings );
		}

		/**
		 * Upgrade settings from one key to another.
		 *
		 * You can also use this to remap old settings keys to new ones.
		 * This only runs if the main settings key is not available.
		 *
		 * @since 1.0.0
		 * @return array Settings array
		 */
		public function get_upgrade_settings() {
			$old_settings = get_option( 'ald_plugin_name_settings' );

			if ( empty( $old_settings ) ) {
				return false;
			} else {

				$settings = $old_settings;

				$settings['cron_on'] = $old_settings['daily_run'];

				return $settings;
			}

		}

		/**
		 * Adding WordPress plugin action links.
		 *
		 * @since 1.0.0
		 *
		 * @param array $links Array of links.
		 * @return array
		 */
		public function plugin_actions_links( $links ) {

			return array_merge(
				array(
					'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->menu_slug ) . '">' . esc_html__( 'Settings', 'plugin-name' ) . '</a>',
				),
				$links
			);
		}

		/**
		 * Add meta links on Plugins page.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $links Array of Links.
		 * @param string $file Current file.
		 * @return array
		 */
		public function plugin_row_meta( $links, $file ) {

			if ( false !== strpos( $file, 'plugin-name.php' ) ) {
				$new_links = array(
					'support' => '<a href = "https://wordpress.org/support/plugin/plugin-name">' . esc_html__( 'Support', 'plugin-name' ) . '</a>',
				);

				$links = array_merge( $links, $new_links );
			}
			return $links;
		}

		/**
		 * Get the help sidebar content to display on the plugin settings page.
		 *
		 * @since 1.0.0
		 */
		public function get_help_sidebar() {

			$help_sidebar =
				/* translators: 1: Plugin support site link. */
				'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%s">support site</a>.', 'plugin-name' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
				/* translators: 1: WordPress.org support forums link. */
					'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%s">WordPress.org support forums</a>.', 'plugin-name' ), esc_url( 'https://wordpress.org/support/plugin/plugin-name' ) ) . '</p>' .
				'<p>' . sprintf(
					/* translators: 1: Github issues link, 2: Github plugin page link. */
					__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'plugin-name' ),
					esc_url( 'https://github.com/ajaydsouza/plugin-name/issues' ),
					esc_url( 'https://github.com/ajaydsouza/plugin-name' )
				) . '</p>';

			/**
			 * Filter to modify the help sidebar content.
			 *
			 * @since 1.0.0
			 *
			 * @param array $help_sidebar Help sidebar content.
			 */
			return apply_filters( self::$prefix . '_settings_help_sidebar', $help_sidebar );
		}

		/**
		 * Get the help tabs to display on the plugin settings page.
		 *
		 * @since 1.0.0
		 */
		public function get_help_tabs() {

			$help_tabs = array(
				array(
					'id'      => 'plugin-name-settings-general',
					'title'   => __( 'General', 'plugin-name' ),
					'content' =>
					'<p>' . __( 'This screen provides the basic settings for configuring Plugin_Name.', 'plugin-name' ) . '</p>' .
						'<p>' . __( 'Set up the schedule at which this will take place automatically.', 'plugin-name' ) . '</p>',
				),
				array(
					'id'      => 'plugin-name-settings-comments',
					'title'   => __( 'Comments', 'plugin-name' ),
					'content' =>
					'<p>' . __( 'This screen provides options to configure options for Comments.', 'plugin-name' ) . '</p>' .
						'<p>' . __( 'Select the post types on which comments will be closed, period to close and exceptions.', 'plugin-name' ) . '</p>',
				),
				array(
					'id'      => 'plugin-name-settings-pingtracks',
					'title'   => __( 'Pingbacks / Trackbacks', 'plugin-name' ),
					'content' =>
					'<p>' . __( 'This screen provides options to configure options for Pingbacks/Trackbacks.', 'plugin-name' ) . '</p>' .
						'<p>' . __( 'Select the post types on which pingbacks/trackbacks will be closed, period to close and exceptions.', 'plugin-name' ) . '</p>',
				),
				array(
					'id'      => 'plugin-name-settings-revisions',
					'title'   => __( 'Revisions', 'plugin-name' ),
					'content' =>
					'<p>' . __( 'This screen provides options to configure options for managing revisions.', 'plugin-name' ) . '</p>' .
						'<p>' . __( 'Delete post revisions or limit the number of revisions for each post type.', 'plugin-name' ) . '</p>',
				),
			);

			/**
			 * Filter to add more help tabs.
			 *
			 * @since 1.0.0
			 *
			 * @param array $help_tabs Associative array of help tabs.
			 */
			return apply_filters( self::$prefix . '_settings_help_tabs', $help_tabs );
		}

		/**
		 * Add an additional admin menu.
		 *
		 * @since 1.0.0
		 */
		public function admin_menu() {
			$menu = array(
				'type'       => 'management',
				'page_title' => esc_html__( 'Plugin_Name Tools', 'plugin-name' ),
				'menu_title' => esc_html__( 'Plugin_Name Tools', 'plugin-name' ),
				'capability' => 'manage_options',
				'menu_slug'  => self::$prefix . '_tools_page',
				'function'   => 'plugin_name_tools_page',
			);

			$this->tools_page = $this->settings_api->add_custom_menu_page( $menu );

			// Load the settings contextual help.
			add_action( 'load-' . $this->tools_page, array( $this, 'settings_help' ) );
		}

		/**
		 * Add CSS to admin head.
		 *
		 * @since 1.0.0
		 */
		public function admin_head() {
			if ( ! is_customize_preview() ) {
				$css = '
					<style type="text/css">
						a.plugin_name_button {
							background: green;
							padding: 10px;
							color: white;
							text-decoration: none;
							text-shadow: none;
							border-radius: 3px;
							transition: all 0.3s ease 0s;
							border: 1px solid green;
						}
						a.plugin_name_button:hover {
							box-shadow: 3px 3px 10px #666;
						}
					</style>';

				echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		/**
		 * Function to add contextual help in the Tools page.
		 *
		 * @since 1.0.0
		 */
		public function settings_help() {
			$screen = get_current_screen();

			if ( $screen->id === $this->tools_page ) {

				$screen->set_help_sidebar( $this->get_help_sidebar() );

				$screen->add_help_tab(
					array(
						'id'      => 'plugin-name-tools-general',
						'title'   => __( 'Tools', 'plugin-name' ),
						'content' =>
						'<p>' . __( 'This screen gives you a few tools namely one click buttons to run the closing algorithm or open comments, pingbacks/trackbacks.', 'plugin-name' ) . '</p>' .
							'<p>' . __( 'You can also delete the old settings from prior to v2.0.0', 'plugin-name' ) . '</p>',
					)
				);
			}

		}

		/**
		 * Function to add a link below the page header of the Settings page.
		 *
		 * @since 1.0.0
		 */
		public function settings_page_header() {
			?>
			<p>
				<a class="plugin_name_button" href="<?php echo admin_url( 'tools.php?page=plugin_name_tools_page' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
					<?php esc_html_e( 'Visit the Tools page', 'plugin-name' ); ?>
				</a>
			<p>
			<?php

		}


		/**
		 * Modify settings when they are being saved.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $settings Settings array.
		 * @return string  $settings  Sanitized settings array.
		 */
		public function change_settings_on_save( $settings ) {

			// Update cron settings before settings are saved.
			$settings['cron_hour'] = min( 23, absint( $settings['cron_hour'] ) );
			$settings['cron_min']  = min( 59, absint( $settings['cron_min'] ) );

			return $settings;
		}

	}

	/**
	 * Register settings function
	 *
	 * @since 1.0.0
	 */
	function plugin_name_register_settings() {
		Plugin_Name_Settings::get_instance();
	}
	add_action( 'init', 'plugin_name_register_settings', 999 );

endif;
