<?php

class HistoryOrder {

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
		$this->show_order_complete_mpo();
	}

	public function show_order_complete_mpo() {

		global $wpdb;

		$short_by = 'DESC';

		if ( isset( $_GET['pageno'] ) ) {
			$pageno = $_GET['pageno'];
		} else {
			$pageno = 1;
		}

		$records_per_page = 50;

		$offset = ( $pageno - 1 ) * $records_per_page;

		$admin_url = admin_url() . 'admin.php?page=mpo_order_history';

		if ( ! empty( $_GET['shortby'] ) ) {
			$admin_url .= '&shortby=' . $_GET['shortby'];
		}

		if ( ! empty( $_GET['time'] ) ) {
			$admin_url .= '&time=' . $_GET['time'];
		}
		if ( ! empty( $_GET['val_search'] ) ) {
			$admin_url .= '&val_search=' . $_GET['val_search'];
		}
		if ( ! empty( $_GET['key_search'] ) ) {
			$admin_url .= '&key_search=' . $_GET['key_search'];
		}

		$param_kv = '';
		if ( isset( $_GET['val_search'] ) && isset( $_GET['key_search'] ) ) {
			$param_kv = 'AND ' . $_GET['key_search'] . '=' . '"' . $_GET['val_search'] . '"' . '';
		}
		// custom time
		$today     = date( 'Y-m-d' );
		$yesterday = date( 'Y-m-d', strtotime( '-1 days' ) );

		$time = isset( $_GET['time'] ) ? $_GET['time'] : '';

		if ( $time == 1 ) {
			$param_time = 'order_time >= "' . $today . '"';
		} elseif ( $time == 0 ) {
			$param_time = 'order_time <= date_sub(now(), interval 0 day)';
		} elseif ( $time == 2 ) {
			$param_time = 'order_time BETWEEN "' . $yesterday . '" and "' . $today . '"';
		} elseif ( $time == 7 ) {
			$param_time = 'order_time >= date_sub(now(), interval 7 day)';
		} elseif ( $time == 30 ) {
			$param_time = 'order_time >= date_sub(now(), interval 30 day)';
		}
		// end time
		$total_pages_sql = $wpdb->get_var( "SELECT count(order_id) FROM {$wpdb->prefix}mpo_order WHERE status_order IN ('SHIPPED', 'PROCESSING','REFUNDED') AND {$param_time} {$param_kv}" );

		$total_pages = ceil( $total_pages_sql / $records_per_page );

		$query_data = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mpo_order WHERE status_order IN ('SHIPPED','PROCESSING','REFUNDED') AND {$param_time} {$param_kv} ORDER BY order_time {$short_by} LIMIT %d , %d", $offset, $records_per_page );

		$data = $wpdb->get_results( $query_data );

		mpo_get_templage(
			'list-history.php',
			array(
				'data'        => $data,
				'total_pages' => $total_pages,
				'pageno'      => $pageno,
				'admin_url'   => $admin_url,
				'total'       => $total_pages_sql,
			)
		);

	}

}

$history_order = HistoryOrder::instance();
