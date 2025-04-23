<?php
/**
 * Admin: Metabox logic for close date and future options.
 *
 * @package    WebberZone\AutoClose
 * @subpackage Admin
 */

namespace WebberZone\AutoClose\Admin;

use WebberZone\AutoClose\Admin\Settings\Metabox_API;
use WebberZone\AutoClose\Features\Close_Date;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Metabox
 *
 * Handles the registration, rendering, and saving of the AutoClose metabox.
 *
 * @since 3.0.0
 */
class Metabox {
	/**
	 * Array of metabox API instances keyed by post type.
	 *
	 * @var array
	 */
	protected $metaboxes = array();

	/**
	 * Settings key for post meta.
	 *
	 * @var string
	 */
	public $settings_key = 'acc_meta';

	/**
	 * Prefix for meta keys and filters.
	 *
	 * @var string
	 */
	public $prefix = 'acc';

	/**
	 * Feature logic instance.
	 *
	 * @var Close_Date
	 */
	protected $close_date_logic;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->close_date_logic = new Close_Date();
		add_action( 'admin_menu', array( $this, 'initialise_metabox_api' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
	}

	/**
	 * Register the metabox using Metabox_API for each supported post type.
	 */
	public function initialise_metabox_api(): void {
		foreach ( $this->get_supported_post_types() as $post_type ) {
			$this->metaboxes[ $post_type ] = new Metabox_API(
				array(
					'settings_key'           => $this->settings_key,
					'prefix'                 => $this->prefix,
					'post_type'              => $post_type,
					'title'                  => esc_html__( 'AutoClose Settings', 'autoclose' ),
					'registered_settings'    => $this->get_registered_settings(),
					'checkbox_modified_text' => __( 'Modified from default', 'autoclose' ),
				)
			);
		}
	}

	/**
	 * Save the metabox data using the API and trigger close logic.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_metabox( $post_id ): void {
		$post_type = get_post_type( $post_id );
		if ( isset( $this->metaboxes[ $post_type ] ) ) {
			$this->metaboxes[ $post_type ]->save( $post_id );
		}
		// After saving, trigger scheduling/close logic.
		$this->close_date_logic->maybe_schedule_or_close( $post_id );
	}

	/**
	 * Get supported post types for the metabox.
	 *
	 * @return array
	 */
	protected function get_supported_post_types(): array {
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		return array_filter(
			$post_types,
			static function ( $type ) {
				return post_type_supports( $type, 'comments' );
			}
		);
	}

	/**
	 * Get registered settings for the metabox.
	 *
	 * @return array
	 */
	protected function get_registered_settings() {
		$settings = array(
			array(
				'id'   => 'comments_date',
				'name' => esc_html__( 'Close comments on', 'autoclose' ),
				'type' => 'datetime',
				'desc' => esc_html__( 'Select the date/time to close comments.', 'autoclose' ),
			),
			array(
				'id'   => 'pings_date',
				'name' => esc_html__( 'Close pingbacks/trackbacks on', 'autoclose' ),
				'type' => 'datetime',
				'desc' => esc_html__( 'Select the date/time to close pingbacks/trackbacks.', 'autoclose' ),
			),
		);

		/**
		 * Filter array of registered settings for metabox.
		 *
		 * @param array $settings Registered settings.
		 */
		$settings = apply_filters( $this->prefix . '_metabox_settings', $settings );

		return $settings;
	}
}
