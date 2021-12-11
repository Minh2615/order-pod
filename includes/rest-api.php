<?php
/**
 * Use for register REST-API.
 */
class LP_Manager_Order_Rest_Controller {
	private static $_instance = null;

	public $namespace = 'mpo-order';

	public function __construct() {
		header( 'Access-Control-Allow-Origin: *' );
		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	public function register_endpoints() {

		register_rest_route(
			$this->namespace,
			'/remove-product',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'remove_product' ),
				'permission_callback' => '__return_true',
			)
		);

	}

	/**
	 * Create design
	 */
	public function remove_product( $request ) {

		$response         = new stdClass();
		$response->status = 'error';

		$title_product   = ! empty( $request['mpo_title_product'] ) ? $request['mpo_title_product'] : '';
		$author_design   = ! empty( $request['mpo_author'] ) ? $request['mpo_author'] : '';
		$order_id        = ! empty( $request['mpo_order_id'] ) ? $request['mpo_order_id'] : '';
		$mpo_img_product = ! empty( $request['mpo_img_product'] ) ? $request['mpo_img_product'] : '';

		try {
			if ( empty( $title_product ) ) {
				throw new Exception( esc_html__( 'No Post ID param.' ) );
			}
			foreach ( $list_sku as $value ) {
				$point_remove = 'https://merchant.wish.com/api/v2/product/remove';

				$request = array(
					'access_token' => $token,
					'parent_sku'   => $value->parent_sku,
				);

				$respon = $this->request_manager_order( $point_remove, $request, 'POST' );

				$wpdb->delete( $wpdb->prefix . 'mpo_product', array( 'parent_sku' => $value->parent_sku ), array( '%s' ) );
			}
		} catch ( Exception $e ) {
			$response->message = $e->getMessage();
		}

		return rest_ensure_response( $response );
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
}

LP_Manager_Order_Rest_Controller::instance();
