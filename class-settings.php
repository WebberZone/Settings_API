<?php
/**
 * Settings management.
 *
 * @package    AutoClose
 */

namespace WebberZone\AutoClose\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AutoClose Settings class to register the settings.
 *
 * @since 3.0.0
 */
class Settings {

	/**
	 * Settings API.
	 *
	 * @since 3.0.0
	 * @var   object
	 */
	public $settings_api;

	/**
	 * Prefix which is used for creating the unique filters and actions.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	public static $prefix;

	/**
	 * Settings Key.
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	public $settings_key;

	/**
	 * The slug name to refer to this menu by (should be unique for this menu).
	 *
	 * @since 3.0.0
	 * @var   string
	 */
	public $menu_slug;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->settings_key = 'acc_settings';
		self::$prefix       = 'acc';
		$this->menu_slug    = 'acc_options_page';

		add_action( 'admin_menu', array( $this, 'init_settings_api' ) );
		add_action( 'admin_head', array( $this, 'admin_head' ), 11 );
		add_action( self::$prefix . '_settings_page_header', array( $this, 'settings_page_header' ) );
		add_filter( self::$prefix . '_settings_sanitize', array( $this, 'change_settings_on_save' ), 99 );
	}

	/**
	 * Initialise the Settings API and set up all properties.
	 *
	 * @since 3.0.0
	 */
	public function init_settings_api() {
		$props = array(
			'default_tab'       => 'general',
			'help_sidebar'      => $this->get_help_sidebar(),
			'help_tabs'         => $this->get_help_tabs(),
			'admin_footer_text' => $this->get_admin_footer_text(),
			'menus'             => $this->get_menus(),
		);

		$args = array(
			'props'               => $props,
			'translation_strings' => $this->get_translation_strings(),
			'settings_sections'   => $this->get_settings_sections(),
			'registered_settings' => $this->get_registered_settings(),
			'upgraded_settings'   => array(),
		);

		$this->settings_api = new Settings\Settings_API( $this->settings_key, self::$prefix, $args );
	}

	/**
	 * Array containing the settings' sections.
	 *
	 * @since 3.0.0
	 * @return array Translation strings.
	 */
	public static function get_translation_strings() {
		$strings = array(
			'page_title'           => esc_html__( 'AutoClose', 'autoclose' ),
			'menu_title'           => esc_html__( 'AutoClose', 'autoclose' ),
			'page_header'          => esc_html__( 'Automatically Close Comments, Pingbacks and Trackbacks Settings', 'autoclose' ),
			'reset_message'        => esc_html__( 'Settings have been reset to their default values. Reload this page to view the updated settings.', 'autoclose' ),
			'success_message'      => esc_html__( 'Settings updated.', 'autoclose' ),
			'save_changes'         => esc_html__( 'Save Changes', 'autoclose' ),
			'reset_settings'       => esc_html__( 'Reset all settings', 'autoclose' ),
			'reset_button_confirm' => esc_html__( 'Do you really want to reset all these settings to their default values?', 'autoclose' ),
			'checkbox_modified'    => esc_html__( 'Modified from default setting', 'autoclose' ),
		);

		/**
		 * Filter the array containing the settings' sections.
		 *
		 * @since 3.0.0
		 * @param array $strings Translation strings.
		 */
		return apply_filters( self::$prefix . '_translation_strings', $strings );
	}

	/**
	 * Get the admin menus.
	 *
	 * @return array Admin menus.
	 */
	public function get_menus() {
		$menus = array();

		// Settings menu.
		$menus[] = array(
			'settings_page' => true,
			'type'          => 'options',
			'page_title'    => esc_html__( 'AutoClose Settings', 'autoclose' ),
			'menu_title'    => esc_html__( 'AutoClose', 'autoclose' ),
			'menu_slug'     => $this->menu_slug,
		);

		return $menus;
	}


	/**
	 * Array containing the settings' sections.
	 *
	 * @since 3.0.0
	 * @return array Settings sections.
	 */
	public static function get_settings_sections() {
		$settings_sections = array(
			'general'    => __( 'General', 'autoclose' ),
			'comments'   => __( 'Comments', 'autoclose' ),
			'pingtracks' => __( 'Pingbacks/Trackbacks', 'autoclose' ),
			'revisions'  => __( 'Revisions', 'autoclose' ),
		);

		/**
		 * Filter the array containing the settings' sections.
		 *
		 * @since 3.0.0
		 * @param array $settings_sections Settings array.
		 */
		return apply_filters( self::$prefix . '_settings_sections', $settings_sections );
	}

	/**
	 * Retrieve the array of plugin settings.
	 *
	 * @since 3.0.0
	 * @return array Settings array.
	 */
	public static function get_registered_settings() {
		$settings = array(
			'general'    => self::settings_general(),
			'comments'   => self::settings_comments(),
			'pingtracks' => self::settings_pingtracks(),
			'revisions'  => self::settings_revisions(),
		);

		/**
		 * Filters the settings array.
		 *
		 * @since 3.0.0
		 * @param array $settings Settings array.
		 */
		return apply_filters( self::$prefix . '_registered_settings', $settings );
	}

	/**
	 * Returns the general settings.
	 *
	 * @since 3.0.0
	 * @return array General settings.
	 */
	public static function settings_general() {
		$settings = array(
			'cron_on'         => array(
				'id'      => 'cron_on',
				'name'    => esc_html__( 'Activate scheduled closing', 'autoclose' ),
				'desc'    => esc_html__( 'This creates a WordPress cron job using the schedule settings below. This cron job will execute the tasks to close comments, pingbacks/trackbacks or delete post revisions based on the settings from the other tabs.', 'autoclose' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'cron_range_desc' => array(
				'id'   => 'cron_range_desc',
				'name' => '<strong>' . esc_html__( 'Time to run closing', 'autoclose' ) . '</strong>',
				'desc' => esc_html__( 'The next two options allow you to set the time to run the cron. The cron job will run now if the hour:min set below if before the current time. e.g. if the time now is 20:30 hours and you set the schedule to 9:00. Else it will run later today at the scheduled time.', 'autoclose' ),
				'type' => 'descriptive_text',
			),
			'cron_hour'       => array(
				'id'      => 'cron_hour',
				'name'    => esc_html__( 'Hour', 'autoclose' ),
				'desc'    => '',
				'type'    => 'number',
				'options' => '0',
				'min'     => '0',
				'max'     => '23',
				'size'    => 'small',
			),
			'cron_min'        => array(
				'id'      => 'cron_min',
				'name'    => esc_html__( 'Minute', 'autoclose' ),
				'desc'    => '',
				'type'    => 'number',
				'options' => '0',
				'min'     => '0',
				'max'     => '59',
				'size'    => 'small',
			),
			'cron_recurrence' => array(
				'id'      => 'cron_recurrence',
				'name'    => esc_html__( 'Run maintenance', 'autoclose' ),
				'desc'    => '',
				'type'    => 'radio',
				'default' => 'daily',
				'options' => array(
					'daily'       => esc_html__( 'Daily', 'autoclose' ),
					'weekly'      => esc_html__( 'Weekly', 'autoclose' ),
					'fortnightly' => esc_html__( 'Fortnightly', 'autoclose' ),
					'monthly'     => esc_html__( 'Monthly', 'autoclose' ),
				),
			),
		);

		/**
		 * Filters the general settings array.
		 *
		 * @since 3.0.0
		 * @param array $settings General settings array.
		 */
		return apply_filters( self::$prefix . '_settings_general', $settings );
	}

	/**
	 * Returns the comments settings.
	 *
	 * @since 3.0.0
	 * @return array Comments settings.
	 */
	public static function settings_comments() {
		$settings = array(
			'close_comment'      => array(
				'id'      => 'close_comment',
				'name'    => esc_html__( 'Close comments', 'autoclose' ),
				'desc'    => esc_html__( 'Enable to close comments - used for the automatic schedule as well as one time runs under the Tools tab.', 'autoclose' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'comment_post_types' => array(
				'id'      => 'comment_post_types',
				'name'    => esc_html__( 'Post types to include', 'autoclose' ),
				'desc'    => esc_html__( 'At least one option should be selected above. Select which post types on which you want comments closed.', 'autoclose' ),
				'type'    => 'posttypes',
				'options' => 'post',
			),
			'comment_age'        => array(
				'id'      => 'comment_age',
				'name'    => esc_html__( 'Close comments on posts/pages older than', 'autoclose' ),
				'desc'    => esc_html__( 'Comments that are older than the above number, in days, will be closed automatically if the schedule is enabled', 'autoclose' ),
				'type'    => 'number',
				'options' => '90',
			),
			'comment_pids'       => array(
				'id'      => 'comment_pids',
				'name'    => esc_html__( 'Keep comments on these posts/pages open', 'autoclose' ),
				'desc'    => esc_html__( 'Comma-separated list of post, page or custom post type IDs. e.g. 188,320,500', 'autoclose' ),
				'type'    => 'numbercsv',
				'options' => '',
				'size'    => 'large',
			),
		);

		/**
		 * Filters the comments settings array.
		 *
		 * @since 3.0.0
		 * @param array $settings Comments settings array.
		 */
		return apply_filters( self::$prefix . '_settings_comments', $settings );
	}

	/**
	 * Returns the pingbacks/trackbacks settings.
	 *
	 * @since 3.0.0
	 * @return array Pingbacks/trackbacks settings.
	 */
	public static function settings_pingtracks() {
		$settings = array(
			'close_pbtb'       => array(
				'id'      => 'close_pbtb',
				'name'    => esc_html__( 'Close Pingbacks/Trackbacks', 'autoclose' ),
				'desc'    => esc_html__( 'Enable to close pingbacks and trackbacks - used for the automatic schedule as well as one time runs under the Tools tab.', 'autoclose' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'pbtb_post_types'  => array(
				'id'      => 'pbtb_post_types',
				'name'    => esc_html__( 'Post types to include', 'autoclose' ),
				'desc'    => esc_html__( 'At least one option should be selected above. Select which post types on which you want pingbacks/trackbacks closed.', 'autoclose' ),
				'type'    => 'posttypes',
				'options' => 'post',
			),
			'pbtb_age'         => array(
				'id'      => 'pbtb_age',
				'name'    => esc_html__( 'Close pingbacks/trackbacks on posts/pages older than', 'autoclose' ),
				'desc'    => esc_html__( 'Pingbacks/Trackbacks that are older than the above number, in days, will be closed automatically if the schedule is enabled', 'autoclose' ),
				'type'    => 'number',
				'options' => '90',
			),
			'pbtb_pids'        => array(
				'id'      => 'pbtb_pids',
				'name'    => esc_html__( 'Keep pingbacks/trackbacks on these posts/pages open', 'autoclose' ),
				'desc'    => esc_html__( 'Comma-separated list of post, page or custom post type IDs. e.g. 188,320,500', 'autoclose' ),
				'type'    => 'numbercsv',
				'options' => '',
				'size'    => 'large',
			),
			'block_self_pings' => array(
				'id'      => 'block_self_pings',
				'name'    => esc_html__( 'Block Self-Pings', 'autoclose' ),
				'desc'    => esc_html__( 'Enable to block self-pings (pings to your own site).', 'autoclose' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'block_ping_urls'  => array(
				'id'      => 'block_ping_urls',
				'name'    => esc_html__( 'Block Ping URLs', 'autoclose' ),
				'desc'    => esc_html__( 'Enter one URL per line. Pings to any of these URLs will be blocked in addition to self-pings.', 'autoclose' ),
				'type'    => 'textarea',
				'options' => '',
				'size'    => 'large',
			),
		);

		/**
		 * Filters the pingbacks/trackbacks settings array.
		 *
		 * @since 3.0.0
		 * @param array $settings Pingbacks/trackbacks settings array.
		 */
		return apply_filters( self::$prefix . '_settings_pingtracks', $settings );
	}

	/**
	 * Returns the revisions settings.
	 *
	 * @since 3.0.0
	 * @return array Revisions settings.
	 */
	public static function settings_revisions() {
		$settings = array(
			'delete_revisions'    => array(
				'id'      => 'delete_revisions',
				'name'    => esc_html__( 'Delete post revisions', 'autoclose' ),
				'desc'    => esc_html__( 'The WordPress revisions system stores a record of each saved draft or published update. This can gather up a lot of overhead in the long run. Use this option to delete old post revisions.', 'autoclose' ),
				'type'    => 'checkbox',
				'options' => false,
			),
			'revision_post_types' => array(
				'id'   => 'revision_post_types',
				'name' => '<strong>' . esc_html__( 'Number of revisions', 'autoclose' ) . '</strong>',
				/* translators: 1: Code. */
				'desc' => sprintf( esc_html__( 'Limit the number of revisions that WordPress stores in the database for each of the post types below. %1$s -2: ignore setting from this plugin, %1$s -1: store every revision, %1$s 0: do not store any revisions, %1$s >0: store that many revisions per post. Old revisions are automatically deleted.', 'autoclose' ), '<br />' ),
				'type' => 'descriptive_text',
			),
		);

		// Create array of settings for post types that support revisions.
		$revisions_instance  = new \WebberZone\AutoClose\Features\Revisions();
		$revision_post_types = $revisions_instance->get_revision_post_types();

		foreach ( $revision_post_types as $post_type => $name ) {
			$settings[ 'revision_' . $post_type ] = array(
				'id'      => 'revision_' . $post_type,
				'name'    => $name,
				'desc'    => '',
				'type'    => 'number',
				'options' => -2,
				'min'     => -2,
				'size'    => 'small',
			);
		}

		/**
		 * Filters the revisions settings array.
		 *
		 * @since 3.0.0
		 * @param array $settings Revisions settings array.
		 */
		return apply_filters( self::$prefix . '_settings_revisions', $settings );
	}

	/**
	 * Get the upgrade settings.
	 *
	 * @since 3.0.0
	 * @return array Upgrade settings.
	 */
	public static function get_upgrade_settings() {
		$settings = array();

		/**
		 * Filters the upgrade settings array.
		 *
		 * @since 3.0.0
		 * @param array $settings Upgrade settings array.
		 */
		return apply_filters( self::$prefix . '_upgrade_settings', $settings );
	}

	/**
	 * Get the help sidebar content.
	 *
	 * @since 3.0.0
	 * @return string Help sidebar content.
	 */
	public static function get_help_sidebar() {
		$help_sidebar =
			'<p><strong>' . __( 'For more information:', 'autoclose' ) . '</strong></p>' .
			'<p><a href="https://webberzone.com/plugins/autoclose/" target="_blank">' . __( 'AutoClose Homepage', 'autoclose' ) . '</a></p>' .
			'<p><a href="https://wordpress.org/plugins/autoclose/faq/" target="_blank">' . __( 'FAQ', 'autoclose' ) . '</a></p>' .
			'<p><a href="https://wordpress.org/support/plugin/autoclose/" target="_blank">' . __( 'Support Forum', 'autoclose' ) . '</a></p>';

		/**
		 * Filters the help sidebar content.
		 *
		 * @since 3.0.0
		 * @param string $help_sidebar Help sidebar content.
		 */
		return apply_filters( self::$prefix . '_help_sidebar', $help_sidebar );
	}

	/**
	 * Get the help tabs.
	 *
	 * @since 3.0.0
	 * @return array Help tabs.
	 */
	public static function get_help_tabs() {
		$help_tabs = array(
			array(
				'id'      => 'acc-settings-general',
				'title'   => __( 'General', 'autoclose' ),
				'content' => '<p>' . __( 'This screen provides the basic settings for configuring AutoClose.', 'autoclose' ) . '</p>' .
					'<p>' . __( 'Enable the scheduler option to autoclose comments and/or pingbacks/trackbacks as per the options set in the Comments and Pingbacks/Trackbacks tabs.', 'autoclose' ) . '</p>',
			),
			array(
				'id'      => 'acc-settings-comments',
				'title'   => __( 'Comments', 'autoclose' ),
				'content' => '<p>' . __( 'This screen provides settings to automatically close comments.', 'autoclose' ) . '</p>' .
					'<p>' . __( 'Enable the Close comments option to close comments on posts as per the schedule in the General tab. You can select the post types on which you want to close comments and the age of the post in days.', 'autoclose' ) . '</p>' .
					'<p>' . __( 'If you want to keep comments on certain posts open, enter a comma-separated list of post IDs in the field provided.', 'autoclose' ) . '</p>',
			),
			array(
				'id'      => 'acc-settings-pingtracks',
				'title'   => __( 'Pingbacks/Trackbacks', 'autoclose' ),
				'content' => '<p>' . __( 'This screen provides settings to automatically close pingbacks and trackbacks.', 'autoclose' ) . '</p>' .
					'<p>' . __( 'Enable the Close pingbacks/trackbacks option to close pingbacks and trackbacks on posts as per the schedule in the General tab. You can select the post types on which you want to close pingbacks/trackbacks and the age of the post in days.', 'autoclose' ) . '</p>' .
					'<p>' . __( 'If you want to keep pingbacks/trackbacks on certain posts open, enter a comma-separated list of post IDs in the field provided.', 'autoclose' ) . '</p>' .
					'<p>' . __( 'Additionally, you can choose to delete all pingbacks and trackbacks when the scheduled maintenance runs.', 'autoclose' ) . '</p>',
			),
			array(
				'id'      => 'acc-settings-revisions',
				'title'   => __( 'Revisions', 'autoclose' ),
				'content' => '<p>' . __( 'This screen provides settings to automatically delete post revisions.', 'autoclose' ) . '</p>' .
					'<p>' . __( 'Enable the Delete post revisions option to delete post revisions when the scheduled maintenance runs.', 'autoclose' ) . '</p>' .
					'<p>' . __( 'You can also set the number of revisions to keep for each post type. Set to 0 to delete all revisions.', 'autoclose' ) . '</p>',
			),
		);

		/**
		 * Filters the help tabs.
		 *
		 * @since 3.0.0
		 * @param array $help_tabs Help tabs.
		 */
		return apply_filters( self::$prefix . '_help_tabs', $help_tabs );
	}

	/**
	 * Add CSS to admin head.
	 *
	 * @since 3.0.0
	 */
	public function admin_head() {
		?>
		<style type="text/css">
			.wrap .acc-settings-section {
				clear: both;
				padding: 0 0 40px;
			}
			.wrap .acc-settings-section > div {
				max-width: 1200px;
			}
			.wrap .acc-settings-section > h2 {
				display: inline-block;
				padding: 0;
			}
			.wrap .acc-settings-section > p {
				margin-top: 0;
			}
			.wrap .acc-settings-section .postbox {
				margin-bottom: 0;
			}
			.wrap .acc-settings-section .inside {
				padding: 0 12px 12px;
				margin-top: 10px;
				margin-bottom: 0;
			}
		</style>
		<?php
	}

	/**
	 * Get the admin footer text.
	 *
	 * @return string Admin footer text.
	 */
	public function get_admin_footer_text() {
		return sprintf(
			/* translators: 1: Opening anchor tag with Plugin page link, 2: Closing anchor tag, 3: Opening anchor tag with review link. */
			__( 'Thank you for using %1$sAutoClose%2$s! Please %3$srate us%2$s on %3$sWordPress.org%2$s', 'autoclose' ),
			'<a href="https://webberzone.com/plugins/autoclose/" target="_blank">',
			'</a>',
			'<a href="https://wordpress.org/support/plugin/autoclose/reviews/#new-post" target="_blank">'
		);
	}

	/**
	 * Add a link to the Tools page from the settings page.
	 *
	 * @since 3.0.0
	 */
	public static function settings_page_header() {
		?>
		<p>
			<a class="button button-primary" style="color: #0A0A0A; background: #FFBD59; border: 1px solid #FFA500;" href="<?php echo esc_url( admin_url( 'tools.php?page=acc_tools_page' ) ); ?>">
				<?php esc_html_e( 'Visit the Tools page', 'autoclose' ); ?>
			</a>
		</p>

		<?php
	}

	/**
	 * Change settings when saved.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Settings array.
	 * @return array Filtered settings array.
	 */
	public function change_settings_on_save( $settings ) {
		// Sanitize cron hour and minute.
		$settings['cron_hour'] = min( 23, absint( $settings['cron_hour'] ) );
		$settings['cron_min']  = min( 59, absint( $settings['cron_min'] ) );

		$cron = new \WebberZone\AutoClose\Utilities\Cron();
		if ( ! empty( $settings['cron_on'] ) ) {
			$cron->enable_run( $settings['cron_hour'], $settings['cron_min'], $settings['cron_recurrence'] );
		} else {
			$cron->disable_run();
		}

		return $settings;
	}
}
