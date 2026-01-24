<?php
/**
 * Register Settings.
 *
 * @link  https://webberzone.com
 * @since 1.7.0
 *
 * @package WebberZone\Settings_API\Admin
 */

namespace WebberZone\Settings_API\Admin;

use WebberZone\Settings_API\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * ATA Settings class to register the settings.
 *
 * @version 1.0
 * @since   1.7.0
 */
class Settings {

	/**
	 * Settings API.
	 *
	 * @since 1.7.0
	 *
	 * @var object Settings API.
	 */
	public $settings_api;

	/**
	 * Settings Page in Admin area.
	 *
	 * @since 1.7.0
	 *
	 * @var string Settings Page.
	 */
	public $settings_page;

	/**
	 * Prefix which is used for creating the unique filters and actions.
	 *
	 * @since 1.7.0
	 *
	 * @var string Prefix.
	 */
	public static $prefix;

	/**
	 * Settings Key.
	 *
	 * @since 1.7.0
	 *
	 * @var string Settings Key.
	 */
	public $settings_key;

	/**
	 * The slug name to refer to this menu by (should be unique for this menu).
	 *
	 * @since 1.7.0
	 *
	 * @var string Menu slug.
	 */
	public $menu_slug;

	/**
	 * Main constructor class.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		$this->settings_key = 'settings_api_settings';
		self::$prefix       = 'settings_api';
		$this->menu_slug    = 'settings_api_options_page';

		Hook_Registry::add_action( 'admin_menu', array( $this, 'initialise_settings' ) );
		Hook_Registry::add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 11, 2 );
		Hook_Registry::add_filter( self::$prefix . '_settings_sanitize', array( $this, 'change_settings_on_save' ), 99 );
		Hook_Registry::add_action( 'admin_menu', array( $this, 'redirect_on_save' ) );
	}

	/**
	 * Initialise the settings API.
	 *
	 * @since 3.3.0
	 */
	public function initialise_settings() {
		$props = array(
			'default_tab'       => 'general',
			'help_sidebar'      => $this->get_help_sidebar(),
			'help_tabs'         => $this->get_help_tabs(),
			'admin_footer_text' => $this->get_admin_footer_text(),
			'menus'             => $this->get_menus(),
		);

		$args = array(
			'translation_strings' => $this->get_translation_strings(),
			'props'               => $props,
			'settings_sections'   => $this->get_settings_sections(),
			'registered_settings' => $this->get_registered_settings(),
			'upgraded_settings'   => array(),
		);

		$this->settings_api = new Settings\Settings_API( $this->settings_key, self::$prefix, $args );
	}



	/**
	 * Array containing the settings' sections.
	 *
	 * @since 1.8.0
	 *
	 * @return array Settings array
	 */
	public function get_translation_strings() {
		$strings = array(
			'page_header'          => esc_html__( 'Settings API Settings', 'settings-api' ),
			'reset_message'        => esc_html__( 'Settings have been reset to their default values. Reload this page to view the updated settings.', 'settings-api' ),
			'success_message'      => esc_html__( 'Settings updated.', 'settings-api' ),
			'save_changes'         => esc_html__( 'Save Changes', 'settings-api' ),
			'reset_settings'       => esc_html__( 'Reset all settings', 'settings-api' ),
			'reset_button_confirm' => esc_html__( 'Do you really want to reset all these settings to their default values?', 'settings-api' ),
			'checkbox_modified'    => esc_html__( 'Modified from default setting', 'settings-api' ),
		);

		/**
		 * Filter the array containing the settings' sections.
		 *
		 * @since 1.8.0
		 *
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
		return array(
			array(
				'settings_page' => true,
				'type'          => 'submenu',
				'parent_slug'   => 'options-general.php',
				'page_title'    => esc_html__( 'Settings API Settings', 'settings-api' ),
				'menu_title'    => esc_html__( 'Settings API', 'settings-api' ),
				'menu_slug'     => $this->menu_slug,
			),
		);
	}

	/**
	 * Array containing the settings' sections.
	 *
	 * @since 1.7.0
	 *
	 * @return array Settings array
	 */
	public static function get_settings_sections() {
		$settings_sections = array(
			'general'     => esc_html__( 'General', 'settings-api' ),
			'third_party' => esc_html__( 'Third Party', 'settings-api' ),
			'head'        => esc_html__( 'Header', 'settings-api' ),
			'body'        => esc_html__( 'Body', 'settings-api' ),
			'footer'      => esc_html__( 'Footer', 'settings-api' ),
			'feed'        => esc_html__( 'Feed', 'settings-api' ),
		);

		/**
		 * Filter the array containing the settings' sections.
		 *
		 * @since 1.2.0
		 *
		 * @param array $settings_sections Settings array
		 */
		return apply_filters( self::$prefix . '_settings_sections', $settings_sections );
	}


	/**
	 * Retrieve the array of plugin settings
	 *
	 * @since 1.7.0
	 *
	 * @return array Settings array
	 */
	public static function get_registered_settings() {

		$settings = array();
		$sections = self::get_settings_sections();

		foreach ( $sections as $section => $value ) {
			$method_name = 'settings_' . $section;
			if ( method_exists( __CLASS__, $method_name ) ) {
				$settings[ $section ] = self::$method_name();
			}
		}

		/**
		 * Filters the settings array
		 *
		 * @since 1.2.0
		 *
		 * @param array $settings Settings array
		 */
		return apply_filters( self::$prefix . '_registered_settings', $settings );
	}

	/**
	 * Returns the Header settings.
	 *
	 * @since 1.7.0
	 *
	 * @return array Header settings.
	 */
	public static function settings_general() {

		$settings = array(
			'enable_snippets'        => array(
				'id'      => 'enable_snippets',
				'name'    => esc_html__( 'Enable Snippets Manager', 'settings-api' ),
				'desc'    => esc_html__( 'Disabling this will turn off the Snippets manager and any of the associated functionality. This will not delete any snippets data that was created before this was turned off.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'enable_external_css_js' => array(
				'id'      => 'enable_external_css_js',
				'name'    => esc_html__( 'Enable external CSS/JS files', 'settings-api' ),
				'desc'    => esc_html__( 'Save CSS and JS snippets as external minified files instead of inline output. Improves page load performance.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'enable_combination'     => array(
				'id'      => 'enable_combination',
				'name'    => esc_html__( 'Enable file combination', 'settings-api' ),
				'desc'    => esc_html__( 'Combine all CSS/JS snippets into single files. Note: Conditions are ignored for combined files.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'snippet_priority'       => array(
				'id'      => 'snippet_priority',
				'name'    => esc_html__( 'Snippet content priority', 'settings-api' ),
				'desc'    => esc_html__( 'Priority of the snippet content. Lower number means all snippets are added earlier relative to other content. Number below 10 is not recommended. At the next level, priority of each snippet is independently set from the Edit Snippets screen.', 'settings-api' ),
				'type'    => 'text',
				'default' => 999,
			),
		);

		/**
		 * Filters the Header settings array
		 *
		 * @since 1.7.0
		 *
		 * @param array $settings Header Settings array
		 */
		return apply_filters( self::$prefix . '_settings_general', $settings );
	}

	/**
	 * Returns the Third party settings.
	 *
	 * @since 1.5.0
	 *
	 * @return array Third party settings.
	 */
	public static function settings_third_party() {

		$settings = array(
			'statcounter_header'           => array(
				'id'   => 'statcounter_header',
				'name' => '<h3>' . esc_html__( 'StatCounter', 'settings-api' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'sc_project'                   => array(
				'id'      => 'sc_project',
				'name'    => esc_html__( 'Project ID', 'settings-api' ),
				'desc'    => esc_html__( 'This is the value of sc_project in your StatCounter code.', 'settings-api' ),
				'type'    => 'text',
				'default' => '',
			),
			'sc_security'                  => array(
				'id'      => 'sc_security',
				'name'    => esc_html__( 'Security ID', 'settings-api' ),
				'desc'    => esc_html__( 'This is the value of sc_security in your StatCounter code.', 'settings-api' ),
				'type'    => 'text',
				'default' => '',
			),
			'google_analytics_header'      => array(
				'id'   => 'google_analytics_header',
				'name' => '<h3>' . esc_html__( 'Google Analytics', 'settings-api' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'ga_uacct'                     => array(
				'id'      => 'ga_uacct',
				'name'    => esc_html__( 'Tracking ID', 'settings-api' ),
				/* translators: 1: Google Tag ID link. */
				'desc'    => sprintf( esc_html__( 'Find your %s', 'settings-api' ), '<a href="https://www.google.com/webmasters/verification/verification" target="_blank">' . esc_html__( 'Google Tag ID', 'settings-api' ) . '</a>' ),
				'type'    => 'text',
				'default' => '',
			),
			'verification_header'          => array(
				'id'   => 'verification_header',
				'name' => '<h3>' . esc_html__( 'Site verification', 'settings-api' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'google_verification'          => array(
				'id'      => 'google_verification',
				'name'    => esc_html__( 'Google', 'settings-api' ),
				/* translators: 1: Google verification details page. */
				'desc'    => sprintf( esc_html__( 'Value of the content portion of the HTML tag method on the %s', 'settings-api' ), '<a href="https://www.google.com/webmasters/verification/verification" target="_blank">' . esc_html__( 'verification details page', 'settings-api' ) . '</a>' ),
				'type'    => 'text',
				'default' => '',
			),
			'bing_verification'            => array(
				'id'      => 'bing_verification',
				'name'    => esc_html__( 'Bing', 'settings-api' ),
				/* translators: 1: Bing verification details page. */
				'desc'    => sprintf( esc_html__( 'Value of the content portion of the HTML tag method on the %s', 'settings-api' ), '<a href="https://www.bing.com/webmaster/" target="_blank">' . esc_html__( 'verification details page', 'settings-api' ) . '</a>' ),
				'type'    => 'text',
				'default' => '',
			),
			'facebook_domain_verification' => array(
				'id'      => 'facebook_domain_verification',
				'name'    => esc_html__( 'Meta', 'settings-api' ),
				/* translators: 1: Meta tag details page. */
				'desc'    => sprintf( esc_html__( 'Value of the content portion of the Meta tag method. Read how to verify your domain in the %s', 'settings-api' ), '<a href="https://www.facebook.com/business/help/321167023127050" target="_blank">' . esc_html__( 'Meta Business Help Centre', 'settings-api' ) . '</a>' ),
				'type'    => 'text',
				'default' => '',
			),
			'pinterest_verification'       => array(
				'id'      => 'pinterest_verification',
				'name'    => esc_html__( 'Pinterest', 'settings-api' ),
				/* translators: 1: Pinterest meta tag details page. */
				'desc'    => sprintf( esc_html__( 'Read how to get the Meta Tag from the %s', 'settings-api' ), '<a href="https://help.pinterest.com/en/articles/confirm-your-website" target="_blank">' . esc_html__( 'Pinterest help page', 'settings-api' ) . '</a>' ),
				'type'    => 'text',
				'default' => '',
			),
		);

		/**
		 * Filters the Third party settings array
		 *
		 * @since 1.5.0
		 *
		 * @param array $settings Third party Settings array
		 */
		return apply_filters( self::$prefix . '_settings_third_party', $settings );
	}

	/**
	 * Returns the Header settings.
	 *
	 * @since 1.5.0
	 *
	 * @return array Header settings.
	 */
	public static function settings_head() {

		$settings = array(
			'head_css'        => array(
				'id'          => 'head_css',
				'name'        => esc_html__( 'Custom CSS', 'settings-api' ),
				'desc'        => esc_html__( 'Add the CSS code without the <style></style> tags.', 'settings-api' ),
				'type'        => 'css',
				'default'     => '',
				'field_class' => 'codemirror_css',
			),
			'head_other_html' => array(
				'id'          => 'head_other_html',
				'name'        => esc_html__( 'HTML to add to the header', 'settings-api' ),
				/* translators: 1: Code. */
				'desc'        => sprintf( esc_html__( 'The code entered here is added to %1$s. Please ensure that you enter valid HTML or JavaScript.', 'settings-api' ), '<code>wp_head()</code>' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
		);

		/**
		 * Filters the Header settings array
		 *
		 * @since 1.5.0
		 *
		 * @param array $settings Header Settings array
		 */
		return apply_filters( self::$prefix . '_settings_head', $settings );
	}

	/**
	 * Returns the Content settings.
	 *
	 * @since 1.5.0
	 *
	 * @return array Content settings.
	 */
	public static function settings_body() {

		$settings = array(
			'wp_body_open_header'            => array(
				'id'   => 'wp_body_open_header',
				'name' => '<h3>' . esc_html__( 'Opening Body Tag', 'settings-api' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'wp_body_open'                   => array(
				'id'          => 'wp_body_open',
				'name'        => esc_html__( 'HTML to add to wp_body_open()', 'settings-api' ),
				'desc'        => esc_html__( 'wp_body_open() is called after the opening body tag. Please ensure that you enter valid HTML or JavaScript. This might not work if your theme does not include the tag.', 'settings-api' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
			'content_header'                 => array(
				'id'   => 'content_header',
				'name' => '<h3>' . esc_html__( 'Content settings', 'settings-api' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'content_filter_priority'        => array(
				'id'      => 'content_filter_priority',
				'name'    => esc_html__( 'Content filter priority', 'settings-api' ),
				'desc'    => esc_html__( 'A higher number will cause the settings output to be processed after other filters. Number below 10 is not recommended.', 'settings-api' ),
				'type'    => 'text',
				'default' => 999,
			),
			'exclude_on_post_ids'            => array(
				'id'      => 'exclude_on_post_ids',
				'name'    => esc_html__( 'Exclude display on these post IDs', 'settings-api' ),
				'desc'    => esc_html__( 'Comma-separated list of post or page IDs to exclude displaying the above content. e.g. 188,320,500', 'settings-api' ),
				'type'    => 'postids',
				'default' => '',
			),
			'content_process_shortcode'      => array(
				'id'      => 'content_process_shortcode',
				'name'    => esc_html__( 'Process shortcodes in content', 'settings-api' ),
				'desc'    => esc_html__( 'Check this box to execute any shortcodes that you enter in the options below.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'content_header_all'             => array(
				'id'   => 'content_header_all',
				'name' => '<h3>' . esc_html__( 'Home and other views', 'settings-api' ) . '</h3>',
				'desc' => esc_html__( 'Displays when viewing single posts, home, category, tag and other archives.', 'settings-api' ),
				'type' => 'header',
			),
			'content_add_html_before'        => array(
				'id'      => 'content_add_html_before',
				'name'    => esc_html__( 'Add HTML before content?', 'settings-api' ),
				'desc'    => esc_html__( 'Check this to add the HTML below before the content of your post.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'content_html_before'            => array(
				'id'          => 'content_html_before',
				'name'        => esc_html__( 'HTML to add before the content', 'settings-api' ),
				'desc'        => esc_html__( 'Enter valid HTML or JavaScript (wrapped in script tags). No PHP allowed.', 'settings-api' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
			'content_add_html_after'         => array(
				'id'      => 'content_add_html_after',
				'name'    => esc_html__( 'Add HTML after content?', 'settings-api' ),
				'desc'    => esc_html__( 'Check this to add the HTML below before the content of your post.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'content_html_after'             => array(
				'id'          => 'content_html_after',
				'name'        => esc_html__( 'HTML to add after the content', 'settings-api' ),
				'desc'        => esc_html__( 'Enter valid HTML or JavaScript (wrapped in script tags). No PHP allowed.', 'settings-api' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
			'content_header_single'          => array(
				'id'   => 'content_header_single',
				'name' => '<h3>' . esc_html__( 'Single posts views', 'settings-api' ) . '</h3>',
				'desc' => esc_html__( 'Displays when viewing single views including posts, pages, custom-post-types.', 'settings-api' ),
				'type' => 'header',
			),
			'content_add_html_before_single' => array(
				'id'      => 'content_add_html_before_single',
				'name'    => esc_html__( 'Add HTML before content?', 'settings-api' ),
				'desc'    => esc_html__( 'Check this to add the HTML below before the content of your post.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'content_html_before_single'     => array(
				'id'          => 'content_html_before_single',
				'name'        => esc_html__( 'HTML to add before the content', 'settings-api' ),
				'desc'        => esc_html__( 'Enter valid HTML or JavaScript (wrapped in script tags). No PHP allowed.', 'settings-api' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
			'content_add_html_after_single'  => array(
				'id'      => 'content_add_html_after_single',
				'name'    => esc_html__( 'Add HTML after content?', 'settings-api' ),
				'desc'    => esc_html__( 'Check this to add the HTML below before the content of your post.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'content_html_after_single'      => array(
				'id'          => 'content_html_after_single',
				'name'        => esc_html__( 'HTML to add after the content', 'settings-api' ),
				'desc'        => esc_html__( 'Enter valid HTML or JavaScript (wrapped in script tags). No PHP allowed.', 'settings-api' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
			'content_header_post'            => array(
				'id'   => 'content_header_post',
				'name' => '<h3>' . esc_html__( 'Post only views', 'settings-api' ) . '</h3>',
				'desc' => esc_html__( 'Displays only on posts', 'settings-api' ),
				'type' => 'header',
			),
			'content_add_html_before_post'   => array(
				'id'      => 'content_add_html_before_post',
				'name'    => esc_html__( 'Add HTML before content?', 'settings-api' ),
				'desc'    => esc_html__( 'Check this to add the HTML below before the content of your post.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'content_html_before_post'       => array(
				'id'          => 'content_html_before_post',
				'name'        => esc_html__( 'HTML to add before the content', 'settings-api' ),
				'desc'        => esc_html__( 'Enter valid HTML or JavaScript (wrapped in script tags). No PHP allowed.', 'settings-api' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
			'content_add_html_after_post'    => array(
				'id'      => 'content_add_html_after_post',
				'name'    => esc_html__( 'Add HTML after content?', 'settings-api' ),
				'desc'    => esc_html__( 'Check this to add the HTML below before the content of your post.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'content_html_after_post'        => array(
				'id'          => 'content_html_after_post',
				'name'        => esc_html__( 'HTML to add after the content', 'settings-api' ),
				'desc'        => esc_html__( 'Enter valid HTML or JavaScript (wrapped in script tags). No PHP allowed.', 'settings-api' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
			'content_header_page'            => array(
				'id'   => 'content_header_page',
				'name' => '<h3>' . esc_html__( 'Page only views', 'settings-api' ) . '</h3>',
				'desc' => esc_html__( 'Displays only on pages', 'settings-api' ),
				'type' => 'header',
			),
			'content_add_html_before_page'   => array(
				'id'      => 'content_add_html_before_page',
				'name'    => esc_html__( 'Add HTML before content?', 'settings-api' ),
				'desc'    => esc_html__( 'Check this to add the HTML below before the content of your page.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'content_html_before_page'       => array(
				'id'          => 'content_html_before_page',
				'name'        => esc_html__( 'HTML to add before the content', 'settings-api' ),
				'desc'        => esc_html__( 'Enter valid HTML or JavaScript (wrapped in script tags). No PHP allowed.', 'settings-api' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
			'content_add_html_after_page'    => array(
				'id'      => 'content_add_html_after_page',
				'name'    => esc_html__( 'Add HTML after content?', 'settings-api' ),
				'desc'    => esc_html__( 'Check this to add the HTML below before the content of your page.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'content_html_after_page'        => array(
				'id'          => 'content_html_after_page',
				'name'        => esc_html__( 'HTML to add after the content', 'settings-api' ),
				'desc'        => esc_html__( 'Enter valid HTML or JavaScript (wrapped in script tags). No PHP allowed.', 'settings-api' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
		);

		/**
		 * Filters the Content settings array
		 *
		 * @since 1.5.0
		 *
		 * @param array $settings Content Settings array
		 */
		return apply_filters( self::$prefix . '_settings_body', $settings );
	}

	/**
	 * Returns the Footer settings.
	 *
	 * @since 1.5.0
	 *
	 * @return array Footer settings.
	 */
	public static function settings_footer() {

		$settings = array(
			'footer_process_shortcode' => array(
				'id'      => 'footer_process_shortcode',
				'name'    => esc_html__( 'Process shortcodes in footer', 'settings-api' ),
				'desc'    => esc_html__( 'Check this box to execute any shortcodes that you enter in the option below.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'footer_other_html'        => array(
				'id'          => 'footer_other_html',
				'name'        => esc_html__( 'HTML to add to the footer', 'settings-api' ),
				/* translators: 1: Code. */
				'desc'        => sprintf( esc_html__( 'The code entered here is added to %1$s. Please ensure that you enter valid HTML or JavaScript.', 'settings-api' ), '<code>wp_footer()</code>' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
		);

		/**
		 * Filters the Footer settings array
		 *
		 * @since 1.5.0
		 *
		 * @param array $settings Footer Settings array
		 */
		return apply_filters( self::$prefix . '_settings_footer', $settings );
	}

	/**
	 * Returns the Feed settings.
	 *
	 * @since 1.5.0
	 *
	 * @return array Feed settings.
	 */
	public static function settings_feed() {

		$settings = array(
			'feed_add_copyright'     => array(
				'id'      => 'feed_add_copyright',
				'name'    => esc_html__( 'Add copyright notice?', 'settings-api' ),
				'desc'    => esc_html__( 'Check this to add the below copyright notice to your feed.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'feed_copyrightnotice'   => array(
				'id'          => 'feed_copyrightnotice',
				'name'        => esc_html__( 'Coyright text', 'settings-api' ),
				/* translators: No strings here. */
				'desc'        => esc_html__( 'Enter valid HTML only. This copyright notice is added as the last item of your feed. You can also use %year% for the year or %first_year% for the year of the first post,', 'settings-api' ),
				'type'        => 'html',
				'default'     => self::get_copyright_text(),
				'field_class' => 'codemirror_html',
			),
			'feed_add_title'         => array(
				'id'      => 'feed_add_title',
				'name'    => esc_html__( 'Add post title?', 'settings-api' ),
				'desc'    => esc_html__( 'Add a link to the title of the post in the feed.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => true,
			),
			'feed_title_text'        => array(
				'id'      => 'feed_title_text',
				'name'    => esc_html__( 'Title text', 'settings-api' ),
				/* translators: No strings here. */
				'desc'    => esc_html__( 'The above text will be added to the feed. You can use %title% to add a link to the post, %date% and %time% to display the date and time of the post respectively.', 'settings-api' ),
				'type'    => 'textarea',
				/* translators: No strings here. */
				'default' => esc_html__( '%title% was first posted on %date% at %time%.', 'settings-api' ),
			),
			'feed_process_shortcode' => array(
				'id'      => 'feed_process_shortcode',
				'name'    => esc_html__( 'Process shortcodes in feed', 'settings-api' ),
				'desc'    => esc_html__( 'Check this box to execute any shortcodes that you enter in the options below.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'feed_add_html_before'   => array(
				'id'      => 'feed_add_html_before',
				'name'    => esc_html__( 'Add HTML before content?', 'settings-api' ),
				'desc'    => esc_html__( 'Check this to add the HTML below before the content of your post.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'feed_html_before'       => array(
				'id'          => 'feed_html_before',
				'name'        => esc_html__( 'HTML to add before the content', 'settings-api' ),
				'desc'        => esc_html__( 'Enter valid HTML or JavaScript (wrapped in script tags). No PHP allowed.', 'settings-api' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
			'feed_add_html_after'    => array(
				'id'      => 'feed_add_html_after',
				'name'    => esc_html__( 'Add HTML after content?', 'settings-api' ),
				'desc'    => esc_html__( 'Check this to add the HTML below before the content of your post.', 'settings-api' ),
				'type'    => 'checkbox',
				'default' => false,
			),
			'feed_html_after'        => array(
				'id'          => 'feed_html_after',
				'name'        => esc_html__( 'HTML to add after the content', 'settings-api' ),
				'desc'        => esc_html__( 'Enter valid HTML or JavaScript (wrapped in script tags). No PHP allowed.', 'settings-api' ),
				'type'        => 'html',
				'default'     => '',
				'field_class' => 'codemirror_html',
			),
			'add_credit'             => array(
				'id'      => 'add_credit',
				'name'    => esc_html__( 'Add a link to the Settings API package page', 'settings-api' ),
				'desc'    => '',
				'type'    => 'checkbox',
				'default' => false,
			),
		);

		/**
		 * Filters the Feed settings array
		 *
		 * @since 1.5.0
		 *
		 * @param array $settings Feed Settings array
		 */
		return apply_filters( self::$prefix . '_settings_feed', $settings );
	}

	/**
	 * Copyright notice text.
	 *
	 * @since 1.7.0
	 * @return string Copyright notice
	 */
	public static function get_copyright_text() {

		$copyrightnotice  = '&copy;' . gmdate( 'Y' ) . ' &quot;<a href="' . get_option( 'home' ) . '">' . get_option( 'blogname' ) . '</a>&quot;. ';
		$copyrightnotice .= esc_html__( 'Use of this feed is for personal non-commercial use only. If you are not reading this article in your feed reader, then the site is guilty of copyright infringement. Please contact me at ', 'settings-api' );
		$copyrightnotice .= '<!--email_off-->' . get_option( 'admin_email' ) . '<!--/email_off-->';

		/**
		 * Copyright notice text.
		 *
		 * @since 1.2.0
		 * @param string $copyrightnotice Copyright notice
		 */
		return apply_filters( self::$prefix . '_copyright_text', $copyrightnotice );
	}


	/**
	 * Upgrade v1.1.0 settings to v1.2.0.
	 *
	 * @since 1.7.0
	 * @return array Settings array
	 */
	public function get_upgrade_settings() {
		$old_settings = get_option( 'ald_ata_settings' );

		if ( empty( $old_settings ) ) {
			return array();
		} else {
			$map = array(
				'add_credit'                     => 'addcredit',

				// Content options.
				'content_html_before'            => 'content_htmlbefore',
				'content_html_after'             => 'content_htmlafter',
				'content_add_html_before'        => 'content_addhtmlbefore',
				'content_add_html_after'         => 'content_addhtmlafter',
				'content_html_before_single'     => 'content_htmlbeforeS',
				'content_html_after_single'      => 'content_htmlafterS',
				'content_add_html_before_single' => 'content_addhtmlbeforeS',
				'content_add_html_after_single'  => 'content_addhtmlafterS',
				'content_filter_priority'        => 'content_filter_priority',

				// Feed options.
				'feed_html_before'               => 'feed_htmlbefore',
				'feed_html_after'                => 'feed_htmlafter',
				'feed_add_html_before'           => 'feed_addhtmlbefore',
				'feed_add_html_after'            => 'feed_addhtmlafter',
				'feed_copyrightnotice'           => 'feed_copyrightnotice',
				'feed_add_title'                 => 'feed_addtitle',
				'feed_title_text'                => 'feed_titletext',
				'feed_add_copyright'             => 'feed_addcopyright',

				// 3rd party options.
				'sc_project'                     => 'tp_sc_project',
				'sc_security'                    => 'tp_sc_security',
				'ga_uacct'                       => 'tp_ga_uacct',
				'tynt_id'                        => 'tp_tynt_id',

				// Footer options.
				'footer_other_html'              => 'ft_other',

				// Header options.
				'head_css'                       => 'head_CSS',
				'head_other_html'                => 'head_other',
			);

			foreach ( $map as $key => $value ) {
				$settings[ $key ] = strval( $old_settings[ $value ] );
			}

			delete_option( 'ald_ata_settings' );

			return $settings;
		}
	}

	/**
	 * Adding WordPress plugin action links.
	 *
	 * @since 1.7.0
	 *
	 * @param array $links Array of links.
	 * @return array
	 */
	public function plugin_actions_links( $links ) {

		$location = $this->get_settings_location();
		return array_merge(
			array(
				'settings' => '<a href="' . $location . '">' . esc_html__( 'Settings', 'settings-api' ) . '</a>',
			),
			$links
		);
	}

	/**
	 * Add meta links on Plugins page.
	 *
	 * @since 1.7.0
	 *
	 * @param array  $links Array of Links.
	 * @param string $file Current file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {

		if ( false !== strpos( $file, 'Settings_API' ) ) {
			$new_links = array(
				'support'    => '<a href = "https://webberzone.com/support/">' . esc_html__( 'Support', 'settings-api' ) . '</a>',
				'donate'     => '<a href = "https://ajaydsouza.com/donate/">' . esc_html__( 'Donate', 'settings-api' ) . '</a>',
				'contribute' => '<a href = "https://github.com/webberzone">' . esc_html__( 'Contribute', 'settings-api' ) . '</a>',
			);

			$links = array_merge( $links, $new_links );
		}
		return $links;
	}

	/**
	 * Get the help sidebar content to display on the plugin settings page.
	 *
	 * @since 1.8.0
	 */
	public function get_help_sidebar() {

		$help_sidebar =
		/* translators: 1: Plugin support site link. */
		'<p>' . sprintf( __( 'For more information or how to get support visit the <a href="%s">support site</a>.', 'settings-api' ), esc_url( 'https://webberzone.com/support/' ) ) . '</p>' .
		/* translators: 1: WordPress.org support forums link. */
			'<p>' . sprintf( __( 'Support queries should be posted in the <a href="%s">WordPress.org support forums</a>.', 'settings-api' ), esc_url( 'https://wordpress.org/support/' ) ) . '</p>' .
		'<p>' . sprintf(
			/* translators: 1: Github issues link, 2: Github plugin page link. */
			__( '<a href="%1$s">Post an issue</a> on <a href="%2$s">GitHub</a> (bug reports only).', 'settings-api' ),
			esc_url( 'https://github.com/webberzone' ),
			esc_url( 'https://github.com/webberzone' )
		) . '</p>';

		/**
		 * Filter to modify the help sidebar content.
		 *
		 * @since 1.8.0
		 *
		 * @param string $help_sidebar Help sidebar content.
		 */
		return apply_filters( self::$prefix . '_settings_help', $help_sidebar );
	}

	/**
	 * Get the help tabs to display on the plugin settings page.
	 *
	 * @since 1.8.0
	 */
	public function get_help_tabs() {

		$help_tabs = array(
			array(
				'id'      => 'settings-api-settings-general-help',
				'title'   => esc_html__( 'General', 'settings-api' ),
				'content' =>
					'<p><strong>' . esc_html__( 'This screen provides general settings.', 'settings-api' ) . '</strong></p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'settings-api' ) . '</p>',
			),
			array(
				'id'      => 'settings-api-settings-third-party-help',
				'title'   => esc_html__( 'Third Party', 'settings-api' ),
				'content' =>
					'<p><strong>' . esc_html__( 'This screen provides the settings for configuring the integration with third party scripts.', 'settings-api' ) . '</strong></p>' .
					'<p>' . sprintf(
						/* translators: 1: Google Analystics help article. */
						esc_html__( 'Google Analytics tracking can be found by visiting this %s', 'settings-api' ),
						'<a href="https://support.google.com/analytics/topic/9303319" target="_blank">' . esc_html__( 'article', 'settings-api' ) . '</a>.'
					) .
					'</p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'settings-api' ) . '</p>',
			),
			array(
				'id'      => 'settings-api-settings-header-help',
				'title'   => esc_html__( 'Header', 'settings-api' ),
				'content' =>
					'<p><strong>' . esc_html__( 'This screen allows you to control what content is added to the header of your site.', 'settings-api' ) . '</strong></p>' .
					'<p>' . esc_html__( 'You can add custom CSS or HTML code. Useful for adding meta tags for site verification, etc.', 'settings-api' ) . '</p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'settings-api' ) . '</p>',
			),
			array(
				'id'      => 'settings-api-settings-body-help',
				'title'   => esc_html__( 'Body', 'settings-api' ),
				'content' =>
					'<p><strong>' . esc_html__( 'This screen allows you to control what content is added to the content of posts, pages and custom post types.', 'settings-api' ) . '</strong></p>' .
					'<p>' . esc_html__( 'You can set the priority of the filter and choose if you want this to be displayed on either all content (including archives) or just single posts/pages.', 'settings-api' ) . '</p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'settings-api' ) . '</p>',
			),
			array(
				'id'      => 'settings-api-settings-footer-help',
				'title'   => esc_html__( 'Footer', 'settings-api' ),
				'content' =>
					'<p><strong>' . esc_html__( 'This screen allows you to control what content is added to the footer of your site.', 'settings-api' ) . '</strong></p>' .
					'<p>' . esc_html__( 'You can add custom HTML code. Useful for adding tracking code for analytics, etc.', 'settings-api' ) . '</p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'settings-api' ) . '</p>',
			),
			array(
				'id'      => 'settings-api-settings-feed-help',
				'title'   => esc_html__( 'Feed', 'settings-api' ),
				'content' =>
					'<p><strong>' . esc_html__( 'This screen allows you to control what content is added to the feed of your site.', 'settings-api' ) . '</strong></p>' .
					'<p>' . esc_html__( 'You can add copyright text, a link to the title and date of the post, and HTML before and after the content', 'settings-api' ) . '</p>' .
					'<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.', 'settings-api' ) . '</p>',
			),
		);

		/**
		 * Filter to add more help tabs.
		 */
		return apply_filters( self::$prefix . '_help_tabs', $help_tabs );
	}

	/**
	 * Get admin footer text.
	 *
	 * @return string
	 */
	public function get_admin_footer_text() {
		return sprintf(
			/* translators: 1: Opening anchor tag with Plugin page link, 2: Closing anchor tag, 3: Opening anchor tag with review link. */
			__( 'Thank you for using %1$sSettings API%2$s! Please %3$srate us%2$s on %3$sWordPress.org%2$s', 'settings-api' ),
			'<a href="https://webberzone.com/" target="_blank">',
			'</a>',
			'<a href="https://wordpress.org/support/" target="_blank">'
		);
	}

	/**
	 * Modify settings when they are being saved.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $settings Settings array.
	 * @return array Sanitized settings array.
	 */
	public function change_settings_on_save( $settings ) {
		return $settings;
	}

	/**
	 * Redirect to the correct settings page on save.
	 *
	 * @since 2.0.0
	 */
	public function redirect_on_save() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === $this->menu_slug && isset( $_GET['settings-updated'] ) && true === (bool) $_GET['settings-updated'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$location = $this->get_settings_location();
			wp_safe_redirect( $location );
			exit;
		}
	}

	/**
	 * Get link of the Settings page.
	 *
	 * @since 2.0.1
	 */
	public function get_settings_location() {
		return admin_url( "/options-general.php?page={$this->menu_slug}" );
	}

	/**
	 * Default settings.
	 *
	 * @since 2.2.0
	 *
	 * @return array Default settings.
	 */
	public static function settings_defaults() {
		$options       = array();
		$default_types = array(
			'color',
			'css',
			'csv',
			'file',
			'html',
			'multicheck',
			'number',
			'numbercsv',
			'password',
			'postids',
			'posttypes',
			'radio',
			'radiodesc',
			'repeater',
			'select',
			'sensitive',
			'taxonomies',
			'text',
			'textarea',
			'thumbsizes',
			'url',
			'wysiwyg',
		);

		// Populate some default values.
		foreach ( self::get_registered_settings() as $tab => $settings ) {
			foreach ( $settings as $option ) {
				if ( ! isset( $option['id'] ) ) {
					continue;
				}

				$setting_id    = $option['id'];
				$setting_type  = $option['type'] ?? '';
				$default_value = '';

				// When checkbox is set to true, set this to 1.
				if ( 'checkbox' === $setting_type ) {
					$default_value = isset( $option['default'] ) ? (int) (bool) $option['default'] : 0;
				} elseif ( isset( $option['default'] ) && in_array( $setting_type, $default_types, true ) ) {
					$default_value = $option['default'];
				}

				$options[ $setting_id ] = $default_value;
			}
		}

		/**
		 * Filters the default settings array.
		 *
		 * @since 2.2.0
		 *
		 * @param array $options Default settings.
		 */
		return apply_filters( self::$prefix . '_settings_defaults', $options );
	}
}
