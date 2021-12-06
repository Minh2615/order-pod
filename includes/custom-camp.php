<?php

class CustomCamPaign {

	private static $instance;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	public function __construct() {

		self::init();
	}

	public function init() {
		$this->show_list_camp_mpo();

	}

	public function show_list_camp_mpo() {

		global $wpdb;

		$token = isset( $_GET['token'] ) ? $_GET['token'] : '';

		$camp_id = '';

		if ( isset( $_GET['camp_id'] ) && isset( $_GET['camp_id'] ) ) {
			$camp_id = ' WHERE camp_id =' . '"' . $_GET['camp_id'] . '"' . '';
		}

		if ( isset( $_GET['pageno'] ) ) {
			$pageno = $_GET['pageno'];
		} else {
			$pageno = 1;
		}

		$param_kv = '';
		if ( isset( $_GET['val_search'] ) && isset( $_GET['key_search'] ) ) {
			$param_kv = 'AND ' . $_GET['key_search'] . '=' . '"' . $_GET['val_search'] . '"' . '';
		}

		$records_per_page = 50;

		$offset = ( $pageno - 1 ) * $records_per_page;

		$admin_url = admin_url() . '/admin.php?page=mpo_list_campaign';

		if ( ! empty( $token ) ) {
			$admin_url .= '&token=' . $token;

			$query_total = $wpdb->prepare( "SELECT count(camp_id) FROM {$wpdb->prefix}mpo_campaign WHERE access_token = %s AND state_camp <> 'ENDED'", $token );
			print_r( $query_total );

			$total_sql = $wpdb->get_var( $query_total );

			$total_pages = ceil( $total_sql / $records_per_page );

			$query_data = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mpo_campaign WHERE access_token = %s  AND state_camp <> 'ENDED' LIMIT %d , %d", $token, $offset, $records_per_page );

			$data = $wpdb->get_results( $query_data );

		} else {

			$total_sql = $wpdb->get_var( "SELECT count(camp_id) FROM {$wpdb->prefix}mpo_campaign $camp_id WHERE state_camp <> 'ENDED'" );

			$total_pages = ceil( $total_sql / $records_per_page );

			$query_data = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mpo_campaign $camp_id WHERE state_camp <> 'ENDED'  LIMIT %d , %d", $offset, $records_per_page );

			$data = $wpdb->get_results( $query_data );

		}

		mpo_get_templage(
			'list-camp.php',
			array(
				'data'        => $data,
				'total_pages' => $total_pages,
				'pageno'      => $pageno,
				'admin_url'   => $admin_url,
			)
		);

	}

}

$custom_camp = CustomCamPaign::instance();
