<?php
/**
 * Admin class.
 *
 * @package WebberZone\Settings_API\Admin
 */

namespace WebberZone\Settings_API\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to initialise reusable admin features.
 *
 * @since 2.10.1
 */
class Admin {

	/**
	 * Admin banner helper instance.
	 *
	 * @since 2.10.1
	 *
	 * @var Admin_Banner
	 */
	public Admin_Banner $admin_banner;

	/**
	 * Main constructor class.
	 *
	 * @since 2.10.1
	 */
	public function __construct() {
		$this->admin_banner = new Admin_Banner( $this->get_admin_banner_config() );
	}

	/**
	 * Retrieve the configuration array for the admin banner.
	 *
	 * @since 2.10.1
	 *
	 * @return array<string, mixed>
	 */
	private function get_admin_banner_config(): array {
		return array(
			'capability' => 'manage_options',
			'prefix'     => 'settings_api',
			'style'      => array(
				'version' => Settings\Settings_API::VERSION,
			),
			'screen_ids' => array(
				'settings_page_settings_api_options_page',
			),
			'page_slugs' => array(
				'settings_api_options_page',
			),
			'strings'    => array(
				'region_label' => esc_html__( 'Settings API quick links', 'settings-api' ),
				'nav_label'    => esc_html__( 'Settings API admin shortcuts', 'settings-api' ),
				'eyebrow'      => esc_html__( 'WebberZone Settings API', 'settings-api' ),
				'title'        => esc_html__( 'Build consistent WordPress settings pages.', 'settings-api' ),
				'text'         => esc_html__( 'Manage plugin settings and explore more WebberZone plugins.', 'settings-api' ),
			),
			'sections'   => array(
				'settings' => array(
					'label'      => esc_html__( 'Settings', 'settings-api' ),
					'url'        => admin_url( 'options-general.php?page=settings_api_options_page' ),
					'screen_ids' => array( 'settings_page_settings_api_options_page' ),
					'page_slugs' => array( 'settings_api_options_page' ),
				),
				'plugins'  => array(
					'label'  => esc_html__( 'WebberZone Plugins', 'settings-api' ),
					'url'    => 'https://webberzone.com/plugins/',
					'type'   => 'secondary',
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				),
			),
		);
	}
}
