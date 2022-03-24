<?php

class ManagerOrderAjax {

	private static $instance;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		self::init();
		// ok
	}

	public function init() {

		add_action( 'wp_ajax_sent_client_id', array( $this, 'sent_client_id' ) );
		add_action( 'wp_ajax_nopriv_sent_client_id', array( $this, 'sent_client_id' ) );

		add_action( 'wp_ajax_get_access_token', array( $this, 'get_access_token' ) );
		add_action( 'wp_ajax_nopriv_get_access_token', array( $this, 'get_access_token' ) );

		add_action( 'wp_ajax_get_list_order', array( $this, 'get_list_order' ) );
		add_action( 'wp_ajax_nopriv_get_list_order', array( $this, 'get_list_order' ) );

		add_action( 'wp_ajax_update_tracking_id', array( $this, 'update_tracking_id' ) );
		add_action( 'wp_ajax_nopriv_update_tracking_id', array( $this, 'update_tracking_id' ) );

		add_action( 'wp_ajax_remove_app_config', array( $this, 'remove_app_config' ) );
		add_action( 'wp_ajax_nopriv_remove_app_config', array( $this, 'remove_app_config' ) );

		add_action( 'wp_ajax_save_note_order_mpo', array( $this, 'save_note_order_mpo' ) );
		add_action( 'wp_ajax_nopriv_save_note_order_mpo', array( $this, 'save_note_order_mpo' ) );

		add_action( 'wp_ajax_save_note_order_cc_mpo', array( $this, 'save_note_order_cc_mpo' ) );
		add_action( 'wp_ajax_nopriv_save_note_order_cc_mpo', array( $this, 'save_note_order_cc_mpo' ) );

		add_action( 'wp_ajax_start_remove_product_merchant', array( $this, 'start_remove_product_merchant' ) );
		add_action( 'wp_ajax_nopriv_start_remove_product_merchant', array( $this, 'start_remove_product_merchant' ) );

		add_action( 'wp_ajax_save_note_config_app_mpo', array( $this, 'save_note_config_app_mpo' ) );
		add_action( 'wp_ajax_nopriv_save_note_config_app_mpo', array( $this, 'save_note_config_app_mpo' ) );

		add_action( 'wp_ajax_create_campaign_mpo', array( $this, 'create_campaign_mpo' ) );
		add_action( 'wp_ajax_nopriv_create_campaign_mpo', array( $this, 'create_campaign_mpo' ) );

		add_action( 'wp_ajax_get_campaign_by_token_mpo', array( $this, 'get_campaign_by_token_mpo' ) );
		add_action( 'wp_ajax_nopriv_get_campaign_by_token_mpo', array( $this, 'get_campaign_by_token_mpo' ) );

		add_action( 'wp_ajax_update_campaign_mpo', array( $this, 'update_campaign_mpo' ) );
		add_action( 'wp_ajax_nopriv_update_campaign_mpo', array( $this, 'update_campaign_mpo' ) );

		add_action( 'update_new_order_mpo', array( $this, 'auto_update_new_order_mpo' ) );
		wp_schedule_single_event( time() + 3600, 'update_new_order_mpo' );

		add_action( 'update_status_order_mpo', array( $this, 'auto_update_status_order_mpo' ) );
		wp_schedule_single_event( time() + 3600, 'update_status_order_mpo' );

		add_action( 'get_campaign_mpo', array( $this, 'auto_get_campaign_mpo' ) );
		wp_schedule_single_event( time() + 3600, 'get_campaign_mpo' );

		add_action( 'schedule_remove_product', array( $this, 'auto_remove_product_wish' ), 10, 1 );
		// upload token after 30 days

		add_action( 'schedule_refesh_token_new', array( $this, 'refesh_token_new' ), 10, 3 );

		// upload product after upload csv
		add_action( 'wp_ajax_start_upload_product_merchant', array( $this, 'start_upload_product_merchant' ) );
		add_action( 'wp_ajax_nopriv_start_upload_product_merchant', array( $this, 'start_upload_product_merchant' ) );

		// remove product after upload csv
		add_action( 'wp_ajax_auto_romove_product_merchant', array( $this, 'auto_romove_product_merchant' ) );
		add_action( 'wp_ajax_nopriv_auto_romove_product_merchant', array( $this, 'auto_romove_product_merchant' ) );

		add_action( 'wp_ajax_save_messages_after_upload_product', array( $this, 'save_messages_after_upload_product' ) );
		add_action( 'wp_ajax_nopriv_save_messages_after_upload_product', array( $this, 'save_messages_after_upload_product' ) );

		add_action( 'wp_ajax_push_order_action', array( $this, 'push_order_action_callback' ) );
		add_action( 'wp_ajax_nopriv_push_order_action', array( $this, 'push_order_action_callback' ) );

		// save design name
		add_action( 'wp_ajax_save_desgin_name', array( $this, 'save_desgin_name' ) );
		add_action( 'wp_ajax_nopriv_save_desgin_name', array( $this, 'save_desgin_name' ) );

		// create order merchant
		add_action( 'wp_ajax_create_order_merchant', array( $this, 'create_order_merchant' ) );
		add_action( 'wp_ajax_nopriv_create_order_merchant', array( $this, 'create_order_merchant' ) );

		add_action( 'wp_ajax_remove_product', array( $this, 'remove_product_ajax' ) );
		add_action( 'wp_ajax_nopriv_remove_product', array( $this, 'remove_product_ajax' ) );
	}

	public function remove_product_ajax( $token ) {
		if ( empty( $token ) ) {
			$token = isset( $_POST['token'] ) ? $_POST['token'] : '';
		}
		$api_endpoint = 'https://merchant.wish.com/api/v3/products/?limit=10';
		$response     = wp_remote_request(
			$api_endpoint,
			array(
				'method'  => 'GET',
				'headers' => array(
					// 'Authorization' => 'Bearer 76bdda41b40f45bbb5fdbe72a48628e5',
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
			)
		);

		$data = json_decode( $response['body'] );

		$this->remove_product_schedule( $token, $data );

		wp_send_json_success( $data );
	}

	public function auto_remove_product_wish( $token ) {
		return $this->remove_product( $token );
	}

	public function remove_product_schedule( $token, $data ) {

		if ( ! empty( $data ) && ! empty( $data->data ) ) {
			foreach ( $data->data as $key => $value ) {
				$api_endpoint = 'https://merchant.wish.com/api/v3/products/' . $value->id;
				$response     = wp_remote_request(
					$api_endpoint,
					array(
						'method'  => 'DELETE',
						'headers' => array(
							// 'Authorization' => 'Bearer 76bdda41b40f45bbb5fdbe72a48628e5',
							'Authorization' => 'Bearer ' . $token,
							'Content-Type'  => 'application/json',
						),
					)
				);
			}
			wp_schedule_single_event( time() + 60, 'schedule_remove_product', array( $token ) );
		}

	}

	/**
	 * create order merchant
	 */
	public function create_order_merchant() {
		$token = isset( $_POST['token_mer'] ) ? $_POST['token_mer'] : '';

		if ( $token ) {
			update_option( 'token_mer', $token );
		}

		$args  = array(
			'order_id'      => isset( $_POST['order_id'] ) ? $_POST['order_id'] : '',
			'shipping_info' => array(
				'full_name' => isset( $_POST['shiping_name'] ) ? $_POST['shiping_name'] : '',
				'address_1' => isset( $_POST['shipping_address_1'] ) ? $_POST['shipping_address_1'] : '',
				'address_2' => isset( $_POST['shipping_address_2'] ) ? $_POST['shipping_address_2'] : '',
				'city'      => isset( $_POST['shipping_city'] ) ? $_POST['shipping_city'] : '',
				'state'     => isset( $_POST['shipping_state'] ) ? $_POST['shipping_state'] : '',
				'postcode'  => isset( $_POST['shipping_zipcode'] ) ? $_POST['shipping_zipcode'] : '',
				'country'   => isset( $_POST['shipping_country'] ) ? $_POST['shipping_country'] : '',
				'phone'     => isset( $_POST['shipping_phone'] ) ? $_POST['shipping_phone'] : '',
			),
			'tax'           => isset( $_POST['tax'] ) ? $_POST['tax'] : '',
			'items'         => array(
				array(
					'name'       => isset( $_POST['product_name'] ) ? $_POST['product_name'] : '',
					'product_id' => isset( $_POST['product_id'] ) ? $_POST['product_id'] : '',
					'sku'        => isset( $_POST['product_sku'] ) ? $_POST['product_sku'] : '',
					'quantity'   => isset( $_POST['product_qty'] ) ? $_POST['product_qty'] : '',
					'price'      => isset( $_POST['product_price'] ) ? $_POST['product_price'] : '',
					'image'      => isset( $_POST['product_image'] ) ? $_POST['product_image'] : '',
					'attributes' => array(
						array(
							'name'   => 'Color',
							'option' => isset( $_POST['product_color'] ) ? $_POST['product_color'] : '',
						),
						array(
							'name'   => 'Size',
							'option' => isset( $_POST['product_size'] ) ? $_POST['product_size'] : '',
						),
					),
				),
			),
		);
		$point = 'https://bo-iwgep5r.merchize.com/bo-api/order/external/orders/';

		$response = wp_remote_post(
			$point,
			array(
				'method'      => 'POST',
				'headers'     => array(
					'authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'        => json_encode( $args ),
				'timeout'     => 70,
				'sslverify'   => false,
				'data_format' => 'body',
			)
		);

		$parsed_response = json_decode( $response['body'] );

		wp_send_json_success( $parsed_response );

		die();
	}

	/**
	 * insert order_id  merchant
	 */

	public function insert_order_id_merchant() {

	}

	/**
	 * save_desgin_name
	 */
	public function save_desgin_name() {

		global $wpdb;
		$respon         = new stdClass();
		$respon->status = '';

		$order_id  = isset( $_POST['order_id'] ) ? $_POST['order_id'] : '';
		$user_name = isset( $_POST['user_name'] ) ? $_POST['user_name'] : '';
		$result    = $wpdb->get_var( $wpdb->prepare( "SELECT design_name FROM  `{$wpdb->prefix}mpo_order` WHERE order_id = %s ", $order_id ) );

		if ( $result == $user_name ) {
			$respon->status  = 'fail';
			$respon->message = 'Design Name already exist';
		} else {
			$wpdb->update( $wpdb->prefix . 'mpo_order', array( 'design_name' => $user_name ), array( 'order_id' => $order_id ) );
			$respon->status  = 'success';
			$respon->message = '';

		}

		wp_send_json_success( $respon );
	}


	public function push_order_action_callback() {

		$data = isset( $_POST['data'] ) ? $_POST['data'] : '';
		wp_send_json_success( $data );
	}
	/**
	 * Refesh token new
	 */
	public function refesh_token_new( $refesh_token, $client_id, $client_secret ) {

		global $wpdb;

		$api_endpoint = 'https://merchant.wish.com/api/v3/oauth/refresh_token';

		$request = array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'refresh_token' => $refesh_token,
			'grant_type'    => 'refresh_token',
		);

		$data = $this->request_manager_order( $api_endpoint, $request, 'GET' );

		$expiry_time = $data->data->expiry_time;

		$token = $data->data->access_token;

		if ( $token ) {
			$wpdb->update(
				$wpdb->prefix . 'mpo_config',
				array(
					'access_token' => $token,
					'refesh_token' => $refesh_token,
					'expiry_time'  => $expiry_time,
				),
				array( 'client_id' => $client_id )
			);
			$wpdb->update(
				$wpdb->prefix . 'mpo_order',
				array(
					'access_token' => $token,
				),
				array( 'client_id' => $client_id )
			);
			wp_schedule_single_event( time() + 30 * 86400, 'schedule_refesh_token_new', array( $refesh_token, $client_id, $client_secret ) );
		} else {
			$this->refesh_token_new( $refesh_token, $client_id, $client_secret );
		}

	}


	/**
	 * Save data store end get code before get token
	 */
	public function sent_client_id() {

		global $wpdb;

		$client_id     = isset( $_POST['client_id'] ) ? $_POST['client_id'] : '';
		$client_secret = isset( $_POST['client_secret'] ) ? $_POST['client_secret'] : '';
		$redirect_uri  = isset( $_POST['redirect_uri'] ) ? $_POST['redirect_uri'] : '';
		$name_app      = isset( $_POST['name_app'] ) ? $_POST['name_app'] : '';

		$wpdb->replace(
			$wpdb->prefix . 'mpo_config',
			array(
				'name_app'      => $name_app,
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri'  => $redirect_uri,
			)
		);

		die();
	}

	/**
	 * Get token store
	 */

	public function get_access_token() {

		global $wpdb;

		$client_id     = isset( $_POST['client_id'] ) ? $_POST['client_id'] : '';
		$client_secret = isset( $_POST['client_secret'] ) ? $_POST['client_secret'] : '';
		$redirect_uri  = isset( $_POST['redirect_uri'] ) ? $_POST['redirect_uri'] : '';
		$code          = isset( $_POST['code'] ) ? $_POST['code'] : '';

		$request = array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'code'          => $code,
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => $redirect_uri,
		);

		$api_endpoint    = 'https://merchant.wish.com/api/v3/oauth/access_token';
		$parsed_response = $this->request_manager_order( $api_endpoint, $request, 'GET' );

		$token        = $parsed_response->data->access_token;
		$refesh_token = $parsed_response->data->refresh_token;
		$expiry_time  = $parsed_response->data->expiry_time;

		$wpdb->update(
			$wpdb->prefix . 'mpo_config',
			array(
				'access_token' => $token,
				'refesh_token' => $refesh_token,
				'expiry_time'  => $expiry_time,
			),
			array( 'client_id' => $client_id )
		);

		wp_schedule_single_event( time() + 30 * 86400, 'schedule_refesh_token_new', array( $refesh_token, $client_id, $client_secret ) );

		wp_send_json_success( $parsed_response );

		die();
	}

	/**
	 * Get list order
	 */
	public function get_list_order() {

		$token     = isset( $_POST['token'] ) ? $_POST['token'] : '';
		$client_id = isset( $_POST['client_id'] ) ? $_POST['client_id'] : '';
		$data      = $this->request_list_order_mpo( $token, $client_id );

		wp_send_json_success( $data );

		die();
	}


	public function request_list_order_mpo( $token, $client_id ) {

		$max_time = gmdate( 'Y-m-d\TH:i:s\Z', time() );
		$min_time = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( '-30 days', time() ) );

		$request = array(
			'released_at_min' => $min_time,
			'released_at_max' => $max_time,
			'updated_at_min'  => $min_time,
			'updated_at_max'  => $max_time,
			'limit'           => 200,
		);

		$point = 'https://merchant.wish.com/api/v3/orders';

		$response = wp_remote_request(
			$point,
			array(
				'method'      => 'GET',
				'headers'     => array(
					'authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'        => $request,
				'timeout'     => 70,
				'sslverify'   => false,
				'data_format' => 'body',
			)
		);

		$parsed_response = json_decode( $response['body'] );

		$this->update_db_order_mpo( $parsed_response, $token, $client_id );

		return $parsed_response;

	}
	public function get_order_paginate_mpo( $url, $token, $client_id ) {

		$json = file_get_contents( $url );

		$obj = json_decode( $json );

		return $this->update_db_order_mpo( $obj, $token, $client_id );
	}


	public function update_db_order_mpo( $respons, $token, $client_id ) {

		global $wpdb;

		$data       = $respons->data ?? array();
		$list_order = array();

		$arr_order = $wpdb->get_results( "SELECT DISTINCT order_id FROM {$wpdb->prefix}mpo_order WHERE order_time >= date_sub(now(), interval 30 day)" );

		foreach ( $arr_order as $value ) {
			$list_order[] = $value->order_id;
		}

		foreach ( $data as $value ) {
			if ( ! in_array( $value->id, $list_order ) ) {
				$wpdb->insert(
					$wpdb->prefix . 'mpo_order',
					array(
						'order_id'           => $value->id,
						'client_id'          => $client_id,
						'access_token'       => $token,
						'order_time'         => $value->released_at,
						// 'hours_to_fulfill'   => $value->fulfillment_requirements->expected_ship_time,
						'transaction_id'     => $value->transaction_id,
						'product_id'         => $value->product_information->sku,
						'product_name'       => $value->product_information->name,
						'product_image_url'  => $value->product_information->variation_image_url,
						'size'               => $value->product_information->size,
						'color'              => $value->product_information->color,
						'currency_code'      => $value->order_payment->general_payment_details->payment_total->currency_code,
						'price'              => $value->order_payment->general_payment_details->product_price->amount,
						'status_order'       => $value->state,
						'cost'               => $value->order_payment->general_payment_details->product_merchant_payment->amount,
						'shipping'           => $value->order_payment->general_payment_details->product_shipping_price->amount,
						'shipping_cost'      => $value->order_payment->general_payment_details->shipping_merchant_payment->amount,
						'quantity'           => $value->order_payment->general_payment_details->product_quantity,
						'order_total'        => $value->order_payment->general_payment_details->payment_total->amount,
						'warehouse_name'     => $value->warehouse_information->warehouse_name,
						'warehouse_id'       => $value->warehouse_information->warehouse_id,
						'shipping_name'      => $value->full_address->shipping_detail->name,
						'shipping_country'   => $value->full_address->shipping_detail->country_code,
						'shipping_phone'     => $value->full_address->shipping_detail->phone_number->number,
						'shipping_zipcode'   => $value->full_address->shipping_detail->zipcode,
						'shipping_address_1' => $value->full_address->shipping_detail->street_address1,
						'shipping_address_2' => $value->full_address->shipping_detail->street_address2,
						'shipping_state'     => $value->full_address->shipping_detail->state,
						'shipping_city'      => $value->full_address->shipping_detail->city,
						'shipped_date'       => $value->fulfillment_requirements->expected_ship_time,
						// 'tracking_confirmed' => $value->tracking_information->tracking_confirmed,
						'product_id_camp'    => $value->product_information->id,
						'tracking_number'    => $value->tracking_information[0]->tracking_number ?? '',
						'tracking_provider'  => $value->tracking_information[0]->shipping_provider->name ?? '',
						'country_code'       => $value->tracking_information[0]->origin_country ?? '',
					)
				);
			} else {
				$wpdb->update(
					$wpdb->prefix . 'mpo_order',
					array( 'access_token' => $token ),
					array( 'order_id' => $value->id )
				);
			}
		};
		if ( $respons->paging != '' ) {
			$this->get_order_paginate_mpo( $respons->paging->next, $token, $client_id );
		}
	}


	/**
	 * Upadte tracking ID
	 */

	public function update_tracking_id() {

		global $wpdb;

		$order_id       = isset( $_POST['order_id'] ) ? $_POST['order_id'] : '';
		$track_id       = isset( $_POST['track_id'] ) ? $_POST['track_id'] : '';
		$track_provider = isset( $_POST['track_provider'] ) ? $_POST['track_provider'] : '';
		$country_code   = isset( $_POST['country_code'] ) ? $_POST['country_code'] : '';

		$token = $wpdb->get_var(
			$wpdb->prepare(
				" SELECT access_token FROM {$wpdb->prefix}mpo_order WHERE order_id = %s",
				$order_id
			)
		);

		$point    = 'https://merchant.wish.com/api/v3/orders/' . $order_id . '/tracking';
		$response = wp_remote_request(
			$point,
			array(
				'method'      => 'PUT',
				'headers'     => array(
					'authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'        => "{\"origin_country\":\"{$country_code}\",\"shipping_provider\":\"{$track_provider}\",\"tracking_number\":\"{$track_id}\"}",
				'timeout'     => 70,
				'sslverify'   => false,
				'data_format' => 'body',
			)
		);

		$parsed_response = json_decode( $response['body'] );

		if ( empty( $parsed_response->message ) ) {
			$new_order    = $this->request_update_order_mpo( $order_id, $token );
			$data_new     = $new_order->data;
			$status_order = $data_new->state;
			$shipped_date = $data_new->fulfillment_requirements->expected_ship_time;

			$wpdb->update(
				$wpdb->prefix . 'mpo_order',
				array(
					'tracking_number'   => $track_id,
					'tracking_provider' => $track_provider,
					'country_code'      => $country_code,
					'status_order'      => $status_order,
					'shipped_date'      => $shipped_date,
				),
				array( 'order_id' => $order_id )
			);
		}

		wp_send_json_success( $parsed_response );

		die();
	}


	/**
	 * Update status order
	 */

	public function auto_update_status_order_mpo() {

		global $wpdb;

		$list_order = $wpdb->get_results( "SELECT DISTINCT access_token , order_id , status_order FROM {$wpdb->prefix}mpo_order WHERE status_order IS NOT NULL OR status_order != '' AND tracking_number IS NOT NULL OR status_order != '' " );

		foreach ( $list_order as $value ) {
			if ( $value->status_order != 'SHIPPED' ) {
				$new_order    = $this->request_update_order_mpo( $value->order_id, $value->access_token );
				$data_new     = $new_order->data;
				$order_id     = $data_new->id;
				$status_order = $data_new->state;
				$shipped_date = $data_new->fulfillment_requirements->expected_ship_time;

				$this->update_status_db_order_mpo( $status_order, $shipped_date, $order_id );
			}
		}
	}

	public function update_status_db_order_mpo( $status_order, $shipped_date, $order_id ) {
		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . 'mpo_order',
			array(
				'status_order' => $status_order,
				'shipped_date' => $shipped_date,
			),
			array( 'order_id' => $order_id )
		);
	}

	public function request_update_order_mpo( $order_id, $token ) {

		$point    = 'https://merchant.wish.com/api/v3/orders/' . $order_id . '';
		$request  = array(
			'id' => $order_id,
		);
		$response = wp_remote_request(
			$point,
			array(
				'method'      => 'GET',
				'headers'     => array(
					'authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'        => $request,
				'timeout'     => 70,
				'sslverify'   => false,
				'data_format' => 'body',
			)
		);

		$parsed_response = json_decode( $response['body'] );

		return $parsed_response;
	}

	public function remove_app_config() {
		global $wpdb;

		$client_id = isset( $_POST['client_id'] ) ? $_POST['client_id'] : '';
		$token     = isset( $_POST['token'] ) ? $_POST['token'] : '';

		$update_config  = $wpdb->delete( $wpdb->prefix . 'mpo_config', array( 'client_id' => $client_id ) );
		$update_order   = $wpdb->delete( $wpdb->prefix . 'mpo_order', array( 'client_id' => $client_id ) );
		$update_product = $wpdb->delete( $wpdb->prefix . 'mpo_product', array( 'access_token' => $token ) );

		wp_send_json_success( $update_config );

		die();
	}

	public function auto_update_new_order_mpo() {
		global $wpdb;

		$list_token = $wpdb->get_results( "SELECT DISTINCT access_token , client_id FROM {$wpdb->prefix}mpo_config" );
		foreach ( $list_token as $value ) {
			$this->request_list_order_mpo( $value->access_token, $value->client_id );
		}
	}

	/**
	 * Remove products
	 */
	public function start_remove_product_merchant() {
		global $wpdb;
		$list_product = json_decode( stripslashes( $_POST['data_csv'] ) );
		$token        = $_POST['access_token'];

		foreach ( $list_product as $key => $value ) {
			$point_remove = 'https://merchant.wish.com/api/v2/product/remove';

			if ( $key > 0 ) {

				$request = array(
					'access_token' => $token,
					'parent_sku'   => $value[5],
				);

				$respon = $this->request_manager_order( $point_remove, $request, 'POST' );
			}
		}

		wp_send_json_success( $respon );

		die();
	}

	/**
	 * Upload products
	 */
	public function start_upload_product_merchant() {

		$token        = isset( $_POST['access_token'] ) ? $_POST['access_token'] : '';
		$api_product  = 'https://merchant.wish.com/api/v2/product/add';
		$api_variable = 'https://merchant.wish.com/api/v2/variant/add';

		$list_product = json_decode( stripslashes( $_POST['data_csv'] ) );

		foreach ( $list_product as $key => $value ) {

			if ( $key > 0 ) {
				if ( $value[1] == $value[0] ) {
					$request     = array(
						'name'                    => $value[4],
						'description'             => $value[13],
						'tags'                    => $value[11],
						'sku'                     => $value[1],
						'color'                   => $value[8],
						'size'                    => $value[9],
						'inventory'               => $value[10],
						'price'                   => $value[14],
						'localized_currency_code' => $value[12],
						'shipping_time'           => $value[17],
						'main_image'              => $value[19],
						'parent_sku'              => $value[0],
						'landing_page_url'        => $value[18],
						'upc'                     => $value[2],
						'declared_name'           => $value[5],
						'declared_local_name'     => $value[6],
						'pieces'                  => $value[7],
						'access_token'            => $token,
						'shipping'                => $value[16],
					);
					$arr_request = array(
						'method'    => 'POST',
						'headers'   => array(),
						'body'      => $request,
						'timeout'   => 70,
						'sslverify' => false,
					);
					$respon      = wp_remote_post( $api_product, $arr_request );
				} else {
					$new_request = array(
						'description'             => $value[13],
						'tags'                    => $value[11],
						'sku'                     => $value[1],
						'color'                   => $value[8],
						'size'                    => $value[9],
						'inventory'               => $value[10],
						'price'                   => $value[14],
						'localized_currency_code' => $value[12],
						'shipping_time'           => $value[17],
						'main_image'              => $value[19],
						'parent_sku'              => $value[0],
						'landing_page_url'        => $value[18],
						'upc'                     => $value[2],
						'declared_name'           => $value[5],
						'declared_local_name'     => $value[6],
						'pieces'                  => $value[7],
						'access_token'            => $token,
					);
					$arr_request = array(
						'method'    => 'POST',
						'headers'   => array(),
						'body'      => $new_request,
						'timeout'   => 70,
						'sslverify' => false,
					);

					$respon = wp_remote_post( $api_variable, $arr_request );
				}
			}
		}
		return $respon;

	}

	public function save_messages_after_upload_product() {
		global $wpdb;

		$name_file  = isset( $_POST['name_file'] ) ? $_POST['name_file'] : '';
		$client_id  = isset( $_POST['client_id'] ) ? $_POST['client_id'] : '';
		$name_store = isset( $_POST['name_store'] ) ? $_POST['name_store'] : '';

		// date_default_timezone_set( 'Asia/Ho_Chi_Minh' );
		$now  = new DateTime();
		$mess = 'Upload File: ' . $name_file . ' Success by: ' . $name_store . ' at : ' . $now->format( 'Y-m-d H:i:s' );

		$update_note = $wpdb->update( $wpdb->prefix . 'mpo_config', array( 'mess_upload' => $mess ), array( 'client_id' => $client_id ) );

		die();

	}
	public function save_note_config_app_mpo() {

		global $wpdb;

		$note_order_app = isset( $_POST['note_order_app'] ) ? $_POST['note_order_app'] : '';

		$client_id = isset( $_POST['client_id'] ) ? $_POST['client_id'] : '';

		$update_note = $wpdb->update( $wpdb->prefix . 'mpo_config', array( 'note_app' => $note_order_app ), array( 'client_id' => $client_id ) );

		wp_send_json_success( $update_note );

		die();
	}


	/**
	 * Create campaign
	 */

	public function create_campaign_mpo() {
		global $wpdb;

		$product_id                  = isset( $_POST['product_id'] ) ? $_POST['product_id'] : '';
		$campaign_name               = isset( $_POST['campaign_name'] ) ? $_POST['campaign_name'] : '';
		$end_date                    = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';
		$start_date                  = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
		$max_budget                  = isset( $_POST['max_budget'] ) ? $_POST['max_budget'] : '';
		$merchant_budget             = isset( $_POST['merchant_budget'] ) ? $_POST['merchant_budget'] : '';
		$scheduled_add_budget_amount = isset( $_POST['scheduled_add_budget_amount'] ) ? $_POST['scheduled_add_budget_amount'] : '';
		$scheduled_add_budget_days   = isset( $_POST['scheduled_add_budget_days'] ) ? $_POST['scheduled_add_budget_days'] : '';
		$currency_code               = isset( $_POST['currency_code'] ) ? $_POST['currency_code'] : '';
		$token                       = isset( $_POST['token'] ) ? $_POST['token'] : '';

		$end_date_fm   = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $end_date ) );
		$start_date_fm = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $start_date ) );
		$point         = 'https://merchant.wish.com/api/v3/product_boost/campaigns';

		$response = wp_remote_post(
			$point,
			array(
				'method'      => 'POST',
				'headers'     => array(
					'authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'        => "{\"auto_renew\":true,\"campaign_name\":\"{$campaign_name}\",\"end_at\":\"{$end_date_fm}\",\"intense_boost\":true,\"max_budget\":{\"amount\":{$max_budget},\"currency_code\":\"{$currency_code}\"},\"merchant_budget\":{\"amount\":{$merchant_budget},\"currency_code\":\"{$currency_code}\"},\"products\":[{\"product_id\":\"{$product_id}\"}],\"scheduled_add_budget_amount\":{\"amount\":{$scheduled_add_budget_amount},\"currency_code\":\"{$currency_code}\"},\"scheduled_add_budget_days\":[0],\"start_at\":\"{$start_date_fm}\"}",
				'timeout'     => 70,
				'sslverify'   => false,
				'data_format' => 'body',
			)
		);

		$parsed_response = json_decode( $response['body'] );

		$data = $parsed_response->data;

		$this->insert_data_campaign_mpo( $data, $token );

		wp_send_json_success( $parsed_response );

		die();
	}


	public function get_campaign_by_token_mpo() {

		$token = isset( $_POST['token'] ) ? $_POST['token'] : '';

		$parsed_response = $this->get_data_campaign_mpo( $token );

		wp_send_json_success( $parsed_response );

		die();
	}

	public function get_data_campaign_mpo( $token ) {

		$point = 'https://merchant.wish.com/api/v3/product_boost/campaigns';

		$response = wp_remote_post(
			$point,
			array(
				'method'      => 'GET',
				'headers'     => array(
					'authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'timeout'     => 70,
				'sslverify'   => false,
				'data_format' => 'body',
			)
		);

		$parsed_response = json_decode( $response['body'] );

		$data = $parsed_response->data;

		if ( ! empty( $data ) && $data->message == '' ) {
			foreach ( $data as $value ) {
				$this->insert_data_campaign_mpo( $value, $token );
			}
		}

		return $parsed_response;
	}

	public function insert_data_campaign_mpo( $data, $token ) {
		global $wpdb;

		$list_camp = array();

		$arr_camp = $wpdb->get_results( "SELECT camp_id FROM {$wpdb->prefix}mpo_campaign" );

		foreach ( $arr_camp as $value ) {
			$list_camp[] = $value->camp_id;
		}

		$arr_insert = array(
			'campaign_name'                 => $data->campaign_name,
			'auto_renew'                    => $data->auto_renew,
			'access_token'                  => $token,
			'amount_bonus_budget'           => $data->bonus_budget->amount,
			'currency_code'                 => $data->products[0]->enrollment_fee->currency_code,
			'bonus_budget_spend'            => $data->bonus_budget_spend->amount,
			'end_at'                        => $data->end_at,
			'amount_gmv'                    => $data->gmv->amount,
			'camp_id'                       => $data->id,
			'intense_boost'                 => $data->intense_boost,
			'is_automated_campaign'         => $data->is_automated_campaign,
			'amount_max_budget'             => $data->max_budget->amount,
			'merchant_budget'               => $data->merchant_budget->amount,
			'merchant_id'                   => $data->merchant_id,
			'amount_min_spend'              => $data->min_spend->amount,
			'paid_impressions'              => $data->paid_impressions,
			'product_id'                    => $data->products[0]->product_id,
			'keywords'                      => $data->products[0]->keywords,
			'amount_enrollment_fee'         => $data->products[0]->enrollment_fee->amount,
			'is_maxboost'                   => $data->products[0]->is_maxboost,
			'sales'                         => $data->sales,
			'scheduled_add_budget_amount'   => $data->scheduled_add_budget_amount->amount,
			'scheduled_add_budget_days'     => $data->scheduled_add_budget_days,
			'start_at'                      => $data->start_at,
			'state_camp'                    => $data->state,
			'total_campaign_spend'          => $data->total_campaign_spend->amount,
			'total_enrollment_fees'         => $data->total_enrollment_fees->amount,
			'total_impression_fees_charged' => $data->total_impression_fees_charged->amount,
			'total_impressions'             => $data->total_impressions,
			'type_camp'                     => $data->type,
			'updated_at'                    => $data->updated_at,
		);
		if ( ! in_array( $data->id, $list_camp ) ) {
			$wpdb->insert( $wpdb->prefix . 'mpo_campaign', $arr_insert );
			$wpdb->update( $wpdb->prefix . 'mpo_order', array( 'camp_id' => $data->id ), array( 'camp_id' => $data->id ) );
		} else {
			$wpdb->update( $wpdb->prefix . 'mpo_campaign', $arr_insert, array( 'camp_id' => $data->id ) );
			$wpdb->update( $wpdb->prefix . 'mpo_order', array( 'camp_id' => $data->id ), array( 'camp_id' => $data->id ) );
		}

	}


	public function update_campaign_mpo() {

		$camp_id                     = isset( $_POST['camp_id'] ) ? $_POST['camp_id'] : '';
		$product_id                  = isset( $_POST['product_id'] ) ? $_POST['product_id'] : '';
		$campaign_name               = isset( $_POST['campaign_name'] ) ? $_POST['campaign_name'] : '';
		$end_date                    = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';
		$start_date                  = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
		$max_budget                  = isset( $_POST['max_budget'] ) ? $_POST['max_budget'] : '';
		$merchant_budget             = isset( $_POST['merchant_budget'] ) ? $_POST['merchant_budget'] : '';
		$scheduled_add_budget_amount = isset( $_POST['scheduled_add_budget_amount'] ) ? $_POST['scheduled_add_budget_amount'] : '';
		$scheduled_add_budget_days   = isset( $_POST['scheduled_add_budget_days'] ) ? $_POST['scheduled_add_budget_days'] : '';
		$currency_code               = isset( $_POST['currency_code'] ) ? $_POST['currency_code'] : '';
		$camp_renew                  = isset( $_POST['camp_renew'] ) ? $_POST['camp_renew'] : '';
		$token                       = isset( $_POST['token'] ) ? $_POST['token'] : '';
		$state_camp                  = isset( $_POST['state_camp'] ) ? $_POST['state_camp'] : '';

		$end_date_fm   = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $end_date ) );
		$start_date_fm = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $start_date ) );
		$point         = 'https://merchant.wish.com/api/v3/product_boost/campaigns/' . $camp_id;

		$response = wp_remote_post(
			$point,
			array(
				'method'      => 'PUT',
				'headers'     => array(
					'authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
				),
				'body'        => "{\"auto_renew\":{$camp_renew},\"campaign_name\":\"{$campaign_name}\",\"end_at\":\"{$end_date_fm}\",\"intense_boost\":true,\"max_budget\":{\"amount\":{$max_budget},\"currency_code\":\"{$currency_code}\"},\"merchant_budget\":{\"amount\":{$merchant_budget},\"currency_code\":\"{$currency_code}\"},\"products\":[{\"product_id\":\"{$product_id}\"}],\"scheduled_add_budget_amount\":{\"amount\":{$scheduled_add_budget_amount},\"currency_code\":\"{$currency_code}\"},\"scheduled_add_budget_days\":[0],\"start_at\":\"{$start_date_fm}\" , \"state\":\"{$state_camp}\"}",
				'timeout'     => 70,
				'sslverify'   => false,
				'data_format' => 'body',
			)
		);

		$parsed_response = json_decode( $response['body'] );

		$data = $parsed_response->data;

		$this->insert_data_campaign_mpo( $data, $token );

		wp_send_json_success( $parsed_response );

		die();

	}

	public function auto_get_campaign_mpo() {
		global $wpdb;

		$list_token = $wpdb->get_results( "SELECT access_token FROM {$wpdb->prefix}mpo_config" );

		foreach ( $list_token as $value ) {
			$token = $value->access_token;
			$this->get_data_campaign_mpo( $token );
		}

	}


	// end campaing

	public function request_manager_order( $api_endpoint, $request, $method ) {
		$response = wp_remote_request(
			$api_endpoint,
			array(
				'method'      => $method ? $method : 'GET',
				'headers'     => array(),
				'body'        => $request,
				'timeout'     => 70,
				'sslverify'   => false,
				'data_format' => 'body',
			)
		);

		$parsed_response = json_decode( $response['body'] );

		return $parsed_response;
	}

	public function save_note_order_mpo() {

		global $wpdb;

		$order_id   = isset( $_POST['order_id'] ) ? $_POST['order_id'] : '';
		$note_order = isset( $_POST['note_order'] ) ? $_POST['note_order'] : '';

		$update_note = $wpdb->update( $wpdb->prefix . 'mpo_order', array( 'custom_note' => $note_order ), array( 'order_id' => $order_id ) );

		wp_send_json_success( $update_note );

		die();
	}

	public function save_note_order_cc_mpo() {

		global $wpdb;

		$order_id      = isset( $_POST['order_id'] ) ? $_POST['order_id'] : '';
		$note_order_cc = isset( $_POST['note_order_cc'] ) ? $_POST['note_order_cc'] : '';

		$update_note = $wpdb->update( $wpdb->prefix . 'mpo_order', array( 'custom_note_cc' => $note_order_cc ), array( 'order_id' => $order_id ) );

		wp_send_json_success( $update_note );
		die();
	}



}

$mpo_ajax = ManagerOrderAjax::instance();
