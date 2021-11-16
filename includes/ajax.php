<?php

class ManagerOrderAjax {
    
    private static $instance;

    public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

    public function __construct(){
        self::init();
        //ok
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

        // add_action( 'wp_ajax_remove_product_mpo', array( $this, 'remove_product_mpo' ));
		// add_action( 'wp_ajax_nopriv_remove_product_mpo', array( $this, 'remove_product_mpo'));

        add_action( 'wp_ajax_save_note_config_app_mpo', array( $this, 'save_note_config_app_mpo' ));
		add_action( 'wp_ajax_nopriv_save_note_config_app_mpo', array( $this, 'save_note_config_app_mpo'));

        add_action( 'wp_ajax_create_campaign_mpo', array( $this, 'create_campaign_mpo' ));
		add_action( 'wp_ajax_nopriv_create_campaign_mpo', array( $this, 'create_campaign_mpo'));

        add_action( 'wp_ajax_get_campaign_by_token_mpo', array( $this, 'get_campaign_by_token_mpo' ));
		add_action( 'wp_ajax_nopriv_get_campaign_by_token_mpo', array( $this, 'get_campaign_by_token_mpo'));

        add_action( 'wp_ajax_update_campaign_mpo', array( $this, 'update_campaign_mpo' ));
		add_action( 'wp_ajax_nopriv_update_campaign_mpo', array( $this, 'update_campaign_mpo'));

        //
        add_action('update_new_order_mpo',array($this,'auto_update_new_order_mpo'));
        //wp_schedule_single_event( time() + 900, 'update_new_order_mpo' );

        //
        add_action('update_status_order_mpo',array($this,'auto_update_status_order_mpo'));
        wp_schedule_single_event( time() + 1800, 'update_status_order_mpo' );

        //
    
        //
        add_action('remove_product_mpo', array($this,'start_remove_product_merchant'),10,4);
        //
        add_action('get_campaign_mpo', array($this,'auto_get_campaign_mpo'));
        wp_schedule_single_event( time() + 3600, 'get_campaign_mpo' );

        // upload token after 30 days

        add_action('schedule_refesh_token_new', array($this,'refesh_token_new'),10,3);

        //upload product after upload csv
        add_action( 'wp_ajax_start_upload_product_merchant', array( $this, 'start_upload_product_merchant' ));
		add_action( 'wp_ajax_nopriv_start_upload_product_merchant', array( $this, 'start_upload_product_merchant'));

        // remove product after upload csv
        add_action( 'wp_ajax_auto_romove_product_merchant', array( $this, 'auto_romove_product_merchant' ));
		add_action( 'wp_ajax_nopriv_auto_romove_product_merchant', array( $this, 'auto_romove_product_merchant'));

        add_action( 'wp_ajax_save_messages_after_upload_product', array( $this, 'save_messages_after_upload_product' ));
		add_action( 'wp_ajax_nopriv_save_messages_after_upload_product', array( $this, 'save_messages_after_upload_product'));

        add_action( 'wp_ajax_push_order_action', array( $this, 'push_order_action_callback' ));
		add_action( 'wp_ajax_nopriv_push_order_action', array( $this, 'push_order_action_callback'));
	}
    public function push_order_action_callback(){

        $data = isset($_POST['data']) ? $_POST['data'] : '';
        wp_send_json_success($data);
    }
    /**
     * Refesh token new
     */
    public function refesh_token_new($refesh_token,$client_id,$client_secret){

        global $wpdb;

        $api_endpoint = 'https://merchant.wish.com/api/v3/oauth/refresh_token';

        $request = array(
            'client_id'=>$client_id,
            'client_secret'=>$client_secret,
            'refresh_token'=>$refesh_token,
            'grant_type'=>'refresh_token',
        );

        $data = $this->request_manager_order($api_endpoint,$request,'GET');

        
            $expiry_time = $data->data->expiry_time;

            $token = $data->data->access_token;
    
            $wpdb->update($wpdb->prefix . 'mpo_config', array('access_token'=>$token , 'refesh_token'=>$refesh_token , 'expiry_time'=>$expiry_time) ,array( 'client_id' => $client_id ));
    
            wp_schedule_single_event( time() + 30 * 86400, 'schedule_refesh_token_new',array($refesh_token,$client_id,$client_secret));
        
        
    }

    
    /**
     * Save data store end get code before get token
     */
    public function sent_client_id(){

        global $wpdb;

        $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : '';
        $client_secret = isset($_POST['client_secret']) ? $_POST['client_secret'] : '';
        $redirect_uri = isset($_POST['redirect_uri']) ? $_POST['redirect_uri'] : '';
        $name_app = isset($_POST['name_app']) ? $_POST['name_app'] : '';

            $wpdb->replace($wpdb->prefix . 'mpo_config', array(
                'name_app'=>$name_app,
                'client_id' => $client_id,
                'client_secret' => $client_secret ,
                'redirect_uri' => $redirect_uri,
            ));
        
        die();
    }

    /**
     * Get token store
     * 
     */

    public function get_access_token(){

        global $wpdb;

        $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : '';
        $client_secret = isset($_POST['client_secret']) ? $_POST['client_secret'] : '';
        $redirect_uri = isset($_POST['redirect_uri']) ? $_POST['redirect_uri'] : '';
        $code = isset($_POST['code']) ? $_POST['code'] : '';

        $request = array(
            'client_id'=>$client_id,
            'client_secret'=>$client_secret,
            'code'=>$code,
            'grant_type'=>'authorization_code',
            'redirect_uri'=>$redirect_uri,
        );

        $api_endpoint = 'https://merchant.wish.com/api/v3/oauth/access_token';
        $parsed_response = $this->request_manager_order($api_endpoint,$request, 'GET');
        
        
            $token = $parsed_response->data->access_token;
            $refesh_token = $parsed_response->data->refresh_token;
            $expiry_time = $parsed_response->data->expiry_time;

            $wpdb->update($wpdb->prefix . 'mpo_config', array('access_token'=>$token , 'refesh_token'=>$refesh_token , 'expiry_time'=>$expiry_time) ,array( 'client_id' => $client_id ));

            wp_schedule_single_event( time() + 30 * 86400, 'schedule_refesh_token_new',array($refesh_token,$client_id,$client_secret));
            
            wp_send_json_success($parsed_response);
        

        die();
    }  

    /**
     * Get list order
     * 
     */
    public function get_list_order() {
        
        $token = isset($_POST['token']) ? $_POST['token'] : '';
        $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : '';
        $data = $this->request_list_order_mpo( $token, $client_id );

        
        wp_send_json_success($data);
        

        die();
    }


    public function request_list_order_mpo($token, $client_id){
        
        $request = array(
            'access_token'=> $token,
        );
        
        $api_endpoint = 'https://merchant.wish.com/api/v2/order/multi-get';
        
        $respons = $this->request_manager_order($api_endpoint,$request , 'GET');

     
        $this->update_db_order_mpo($respons , $token , $client_id);
        

        return $respons;

    }
    public function get_order_paginate_mpo($url, $token, $client_id){

        $json = file_get_contents($url);

        $obj = json_decode($json);

        return $this->update_db_order_mpo($obj , $token , $client_id);
    }


    public function update_db_order_mpo($respons , $token , $client_id){

        global $wpdb;

        $data = $respons->data ?? array();
        $list_order = array();

        $arr_order = $wpdb->get_results("SELECT DISTINCT order_id FROM {$wpdb->prefix}mpo_order");

        foreach($arr_order as $value){
            $list_order[] = $value->order_id;
        }

        foreach($data as $value){
            if(!in_array($value->Order->order_id , $list_order)){
                $wpdb->replace($wpdb->prefix . 'mpo_order', array(
                    'order_id' => $value->Order->order_id,
                    'client_id'=> $client_id,
                    'access_token' => $token,
                    'order_time' => $value->Order->order_time,
                    'hours_to_fulfill' => $value->Order->hours_to_fulfill,
                    'transaction_id' => $value->Order->transaction_id,
                    'product_id' => $value->Order->sku,
                    'product_name' => $value->Order->product_name,
                    'product_image_url'=>$value->Order->product_image_url,
                    'size' => $value->Order->size,
                    'color' => $value->Order->color,
                    'currency_code' => $value->Order->currency_code,
                    'price' => $value->Order->price,
                    'status_order'=>$value->Order->state,
                    'cost' => $value->Order->cost,
                    'shipping' => $value->Order->shipping,
                    'shipping_cost' => $value->Order->shipping_cost,
                    'quantity' => $value->Order->quantity,
                    'order_total' => $value->Order->order_total,
                    'warehouse_name' => $value->Order->MerchantWarehouseDetails->merchant_warehouse_name,
                    'warehouse_id' => $value->Order->MerchantWarehouseDetails->merchant_warehouse_id,
                    'shipping_name' => $value->Order->ShippingDetail->name,
                    'shipping_country' => $value->Order->ShippingDetail->country,
                    'shipping_phone' => $value->Order->ShippingDetail->phone_number,
                    'shipping_zipcode' => $value->Order->ShippingDetail->zipcode,
                    'shipping_address_1' => $value->Order->ShippingDetail->street_address1,
                    'shipping_address_2' => $value->Order->ShippingDetail->street_address2,
                    'shipping_state' => $value->Order->ShippingDetail->state,
                    'shipping_city' => $value->Order->ShippingDetail->city,
                    'shipped_date' => $value->Order->shipped_date,
                    'tracking_confirmed' => $value->Order->tracking_confirmed,
                    'product_id_camp'=>$value->Order->product_id,
                ));
            }else{
                $wpdb->update($wpdb->prefix.'mpo_order',
                  array('access_token' => $token) ,
                  array('order_id' => $value->Order->order_id));   
          }
        };
        if( $respons->paging !=""){
            $this->get_order_paginate_mpo($respons->paging->next , $token , $client_id);
        }
    }

    
    /**
     * Upadte tracking ID
     * 
     */

    public function update_tracking_id(){
        
        global $wpdb;

        $order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';
        $track_id = isset($_POST['track_id']) ? $_POST['track_id'] : '';
        $track_provider = isset($_POST['track_provider']) ? $_POST['track_provider'] : '';
        $country_code = isset($_POST['country_code']) ? $_POST['country_code'] : '';

        $api_endpoint = 'https://merchant.wish.com/api/v2/order/fulfill-one';

        $token = $wpdb->get_var("SELECT access_token FROM {$wpdb->prefix}mpo_order WHERE order_id = '{$order_id}'");

        $request= array(
            'access_token' =>$token,
            'id'=>$order_id,
            'tracking_provider' => $track_provider,
            'tracking_number' => $track_id,
            'origin_country_code'=> $country_code,
        );
        $respon = $this->request_manager_order( $api_endpoint, $request , 'POST');
        
        $new_order = $this->request_update_order_mpo( $order_id , $token );
        $data = $new_order->data;
        $status_order = $data->Order->state;
        $shipped_date= $data->Order->shipped_date;

        $wpdb->update($wpdb->prefix.'mpo_order',
                array('tracking_number' => $track_id, 
                'tracking_provider' => $track_provider ,
                'country_code' => $country_code , 
                'status_order'=>$status_order ,
                'shipped_date'=>$shipped_date) ,
                array( 'order_id' => $order_id ));
        
        wp_send_json_success($respon);

        die();
    }

    
    /**
     * Update status order
     * 
     */

    public function auto_update_status_order_mpo(){

        global $wpdb;

        $list_order = $wpdb->get_results("SELECT DISTINCT access_token , order_id FROM {$wpdb->prefix}mpo_order WHERE status_order IS NOT NULL OR status_order != ''");

        foreach($list_order as $value){
            $respon = $this->request_update_order_mpo( $value->order_id, $value->access_token);
            $data = $respon->data;
            $order_id = $data->Order->order_id;
            $status_order = $data->Order->state;
            $shipped_date= $data->Order->shipped_date;
            $this->update_status_db_order_mpo($status_order , $shipped_date , $order_id);
            
            
        }
    }

    public function update_status_db_order_mpo ($status_order, $shipped_date , $order_id ){
        global $wpdb;

        $wpdb->update($wpdb->prefix.'mpo_order',
                array('status_order'=>$status_order ,
                    'shipped_date'=>$shipped_date),
                array( 'order_id' => $order_id ));
    }

    public function request_update_order_mpo($order_id , $token){
        $api_endpoint = 'https://merchant.wish.com/api/v2/order';
        $request= array(
            'access_token' =>$token,
            'id'=>$order_id,
        );
        $respon = $this->request_manager_order($api_endpoint, $request , 'GET');

        return $respon;
    }

    public function remove_app_config(){
        global $wpdb;

        $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : '';
        $token = isset($_POST['token']) ? $_POST['token'] : '';

        $update_config = $wpdb->delete($wpdb->prefix.'mpo_config',array('client_id'=>$client_id));
        $update_order = $wpdb->delete($wpdb->prefix.'mpo_order',array('client_id'=>$client_id));
        $update_product = $wpdb->delete($wpdb->prefix.'mpo_product',array('access_token'=>$token));

        wp_send_json_success($update_config);
        
        die();
    }

    public function auto_update_new_order_mpo(){
        global $wpdb;

        $list_token = $wpdb->get_results("SELECT DISTINCT access_token , client_id FROM {$wpdb->prefix}mpo_config");
        foreach($list_token as $value){
            $this->request_list_order_mpo($value->access_token, $value->client_id);
        }
    }   

    /**
     * Remove products
     * Sẽ custom lại (13-5-2021)
     */
    public function start_remove_product_merchant($offset , $limit ,$name_file,$token){
        global $wpdb;

        $list_sku = $wpdb->get_results("SELECT DISTINCT * FROM {$wpdb->prefix}mpo_sku_product WHERE name_file='{$name_file}' AND access_token = '{$token}' LIMIT {$offset} , {$limit}");

        foreach($list_sku as $value){
            $point_remove = 'https://merchant.wish.com/api/v2/product/remove';

            $request= array(
                'access_token' =>$token,
                'parent_sku'=>$value->parent_sku,
            );
    
            $respon = $this->request_manager_order($point_remove, $request , 'POST');
            
            $wpdb->delete( $wpdb->prefix.'mpo_product' , array('parent_sku'=>$value->parent_sku ), array('%s') );
        }

        wp_send_json_success($respon);

        die();
    }
    
    /**
     * Upload products
     * 
     */
    public function start_upload_product_merchant(){

        $token = isset($_POST['access_token']) ? $_POST['access_token'] : '';
        $api_product = 'https://merchant.wish.com/api/v2/product/add';
        $api_variable = 'https://merchant.wish.com/api/v2/variant/add';

        $list_product = json_decode(stripslashes($_POST['data_csv']));

        foreach($list_product as $key => $value){

            if($key > 0){
                if($value[1] == $value[0]){
                    $request = array(
                        'name'=>$value[4],
                        'description'=>$value[13],
                        'tags'=>$value[11],
                        'sku'=>$value[1],
                        'color'=>$value[8],
                        'size'=>$value[9],
                        'inventory'=>$value[10],
                        'price'=>$value[14],
                        'localized_currency_code'=> $value[12],
                        'shipping_time'=>$value[17],
                        'main_image'=>$value[19],
                        'parent_sku'=>$value[0],
                        'landing_page_url'=>$value[18],
                        'upc'=>$value[2],
                        'declared_name'=>$value[5],
                        'declared_local_name'=>$value[6],
                        'pieces'=>$value[7],
                        'access_token'=>$token,
                        'shipping'=>$value[16],
                    );
                    $arr_request = array(
                        'method'     => 'POST',
                        'headers'     => array(),
                        'body'       => $request,
                        'timeout'    => 70,
                        'sslverify'  => false,
                    );
                    $respon = wp_remote_post( $api_product , $arr_request );
                }else{
                    $new_request = array(
                        'description'=>$value[13],
                        'tags'=>$value[11],
                        'sku'=>$value[1],
                        'color'=>$value[8],
                        'size'=>$value[9],
                        'inventory'=>$value[10],
                        'price'=>$value[14],
                        'localized_currency_code'=> $value[12],
                        'shipping_time'=>$value[17],
                        'main_image'=>$value[19],
                        'parent_sku'=>$value[0],
                        'landing_page_url'=>$value[18],
                        'upc'=>$value[2],
                        'declared_name'=>$value[5],
                        'declared_local_name'=>$value[6],
                        'pieces'=>$value[7],
                        'access_token'=>$token,
                    );
                    $arr_request = array(
                        'method'     => 'POST',
                        'headers'     => array(),
                        'body'       => $new_request,
                        'timeout'    => 70,
                        'sslverify'  => false,
                    );
        
                    $respon =  wp_remote_post( $api_variable , $arr_request );                
                }
            }
        }
        return $respon;

    }

    public function save_messages_after_upload_product(){
        global $wpdb;

        $name_file = isset($_POST['name_file']) ? $_POST['name_file'] : '';
        $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : '';
        $name_store = isset($_POST['name_store']) ? $_POST['name_store'] : '';
        
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $now = new DateTime();
        $mess = 'Upload File: '. $name_file .' Success by: '. $name_store . ' at : ' . $now->format('Y-m-d H:i:s');

        $update_note = $wpdb->update($wpdb->prefix.'mpo_config', array('mess_upload' => $mess) ,array( 'client_id' => $client_id ));

        die();

    }
    public function save_note_config_app_mpo(){

        global $wpdb;

        $note_order_app = isset($_POST['note_order_app']) ? $_POST['note_order_app'] : '';

        $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : '';

        $update_note = $wpdb->update($wpdb->prefix.'mpo_config', array('note_app' => $note_order_app) ,array( 'client_id' => $client_id ));

        wp_send_json_success($update_note);

        die();
    }


    /**
     * Create campaign
     * 
     */

    public function create_campaign_mpo(){
        global $wpdb;

        $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
        $campaign_name = isset($_POST['campaign_name']) ? $_POST['campaign_name'] : '';
        $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
        $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
        $max_budget = isset($_POST['max_budget']) ? $_POST['max_budget'] : '';
        $merchant_budget = isset($_POST['merchant_budget']) ? $_POST['merchant_budget'] : '';
        $scheduled_add_budget_amount = isset($_POST['scheduled_add_budget_amount']) ? $_POST['scheduled_add_budget_amount'] : '';
        $scheduled_add_budget_days = isset($_POST['scheduled_add_budget_days']) ? $_POST['scheduled_add_budget_days'] : '';
        $currency_code = isset($_POST['currency_code']) ? $_POST['currency_code'] : '';
        $token = isset($_POST['token']) ? $_POST['token'] : '';

        $end_date_fm = date('Y-m-d\TH:i:s\Z', strtotime($end_date));
        $start_date_fm = date('Y-m-d\TH:i:s\Z', strtotime($start_date));
        $point = 'https://merchant.wish.com/api/v3/product_boost/campaigns';

        $response = wp_remote_post( $point , array(
            'method'     => 'POST',
            'headers'     => array(
                'authorization' => 'Bearer '.$token ,
                'Content-Type' => 'application/json',
            ),
            'body'       => "{\"auto_renew\":true,\"campaign_name\":\"{$campaign_name}\",\"end_at\":\"{$end_date_fm}\",\"intense_boost\":true,\"max_budget\":{\"amount\":{$max_budget},\"currency_code\":\"{$currency_code}\"},\"merchant_budget\":{\"amount\":{$merchant_budget},\"currency_code\":\"{$currency_code}\"},\"products\":[{\"product_id\":\"{$product_id}\"}],\"scheduled_add_budget_amount\":{\"amount\":{$scheduled_add_budget_amount},\"currency_code\":\"{$currency_code}\"},\"scheduled_add_budget_days\":[0],\"start_at\":\"{$start_date_fm}\"}",
            'timeout'    => 70,
            'sslverify'  => false,
            'data_format' => 'body',
        ) );

        $parsed_response = json_decode( $response['body'] );
        
        $data = $parsed_response->data;

        $this->insert_data_campaign_mpo($data , $token);

        wp_send_json_success($parsed_response);

        die();
    }
    
 
    public function get_campaign_by_token_mpo(){

        $token = isset($_POST['token']) ? $_POST['token'] : '';
  
        $parsed_response = $this->get_data_campaign_mpo($token);

        wp_send_json_success($parsed_response);

        die();
    }

    public function get_data_campaign_mpo($token){

        $point = 'https://merchant.wish.com/api/v3/product_boost/campaigns';

        $response = wp_remote_post( $point , array(
            'method'     => 'GET',
            'headers'     => array(
                'authorization' => 'Bearer '.$token ,
                'Content-Type' => 'application/json',
            ),
            'timeout'    => 70,
            'sslverify'  => false,
            'data_format' => 'body',
        ) );

        $parsed_response = json_decode( $response['body'] );

        $data = $parsed_response->data;
        
        if( ! empty($data) && $data->message == "" ){
            foreach($data as $value){   
                $this->insert_data_campaign_mpo($value , $token);
            }
        }

        return $parsed_response;
    }

    public function insert_data_campaign_mpo($data , $token){
        global $wpdb;
        
        $list_camp = array();

        $arr_camp = $wpdb->get_results("SELECT camp_id FROM {$wpdb->prefix}mpo_campaign");

        foreach($arr_camp as $value){
            $list_camp[] = $value->camp_id;
        }
        
        $arr_insert = array(
            'campaign_name'=>$data->campaign_name,
            'auto_renew'=>$data->auto_renew,
            'access_token' => $token,
            'amount_bonus_budget'=> $data->bonus_budget->amount,
            'currency_code'=> $data->products[0]->enrollment_fee->currency_code,
            'bonus_budget_spend'=> $data->bonus_budget_spend->amount,
            'end_at'=> $data->end_at,
            'amount_gmv'=> $data->gmv->amount,
            'camp_id'=> $data->id,
            'intense_boost'=> $data->intense_boost,
            'is_automated_campaign'=> $data->is_automated_campaign,
            'amount_max_budget'=> $data->max_budget->amount,
            'merchant_budget'=> $data->merchant_budget->amount,
            'merchant_id'=> $data->merchant_id,
            'amount_min_spend'=> $data->min_spend->amount,
            'paid_impressions'=> $data->paid_impressions,
            'product_id'=> $data->products[0]->product_id,
            'keywords' => $data->products[0]->keywords,
            'amount_enrollment_fee'=>$data->products[0]->enrollment_fee->amount,
            'is_maxboost'=> $data->products[0]->is_maxboost,
            'sales'=>$data->sales,
            'scheduled_add_budget_amount'=> $data->scheduled_add_budget_amount->amount,
            'scheduled_add_budget_days'=> $data->scheduled_add_budget_days,
            'start_at'=> $data->start_at,
            'state_camp'=> $data->state,
            'total_campaign_spend'=>$data->total_campaign_spend->amount,
            'total_enrollment_fees'=>$data->total_enrollment_fees->amount,
            'total_impression_fees_charged'=>$data->total_impression_fees_charged->amount,
            'total_impressions'=>$data->total_impressions,
            'type_camp'=>$data->type,
            'updated_at'=>$data->updated_at,
       );
        if(!in_array($data->id , $list_camp)){
            $wpdb->insert($wpdb->prefix . 'mpo_campaign',$arr_insert);
            $wpdb->update($wpdb->prefix . 'mpo_order',array('camp_id'=>$data->id) , array('camp_id'=>$data->id));
        }else{
            $wpdb->update($wpdb->prefix . 'mpo_campaign',$arr_insert, array('camp_id'=>$data->id));
            $wpdb->update($wpdb->prefix . 'mpo_order',array('camp_id'=>$data->id) , array('camp_id'=>$data->id));
        }

    }


    public function update_campaign_mpo(){

        $camp_id = isset($_POST['camp_id']) ? $_POST['camp_id'] : '';
        $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
        $campaign_name = isset($_POST['campaign_name']) ? $_POST['campaign_name'] : '';
        $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
        $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
        $max_budget = isset($_POST['max_budget']) ? $_POST['max_budget'] : '';
        $merchant_budget = isset($_POST['merchant_budget']) ? $_POST['merchant_budget'] : '';
        $scheduled_add_budget_amount = isset($_POST['scheduled_add_budget_amount']) ? $_POST['scheduled_add_budget_amount'] : '';
        $scheduled_add_budget_days = isset($_POST['scheduled_add_budget_days']) ? $_POST['scheduled_add_budget_days'] : '';
        $currency_code = isset($_POST['currency_code']) ? $_POST['currency_code'] : '';
        $camp_renew = isset($_POST['camp_renew']) ? $_POST['camp_renew'] : '';
        $token = isset($_POST['token']) ? $_POST['token'] : '';
        $state_camp = isset($_POST['state_camp']) ? $_POST['state_camp'] : '';

        $end_date_fm = date('Y-m-d\TH:i:s\Z', strtotime($end_date));
        $start_date_fm = date('Y-m-d\TH:i:s\Z', strtotime($start_date));
        $point = 'https://merchant.wish.com/api/v3/product_boost/campaigns/'.$camp_id;

        $response = wp_remote_post( $point , array(
            'method'     => 'PUT',
            'headers'     => array(
                'authorization' => 'Bearer '.$token ,
                'Content-Type' => 'application/json',
            ),
            'body'       => "{\"auto_renew\":{$camp_renew},\"campaign_name\":\"{$campaign_name}\",\"end_at\":\"{$end_date_fm}\",\"intense_boost\":true,\"max_budget\":{\"amount\":{$max_budget},\"currency_code\":\"{$currency_code}\"},\"merchant_budget\":{\"amount\":{$merchant_budget},\"currency_code\":\"{$currency_code}\"},\"products\":[{\"product_id\":\"{$product_id}\"}],\"scheduled_add_budget_amount\":{\"amount\":{$scheduled_add_budget_amount},\"currency_code\":\"{$currency_code}\"},\"scheduled_add_budget_days\":[0],\"start_at\":\"{$start_date_fm}\" , \"state\":\"{$state_camp}\"}",
            'timeout'    => 70,
            'sslverify'  => false,
            'data_format' => 'body',
        ) );

        $parsed_response = json_decode( $response['body'] );
        
        $data = $parsed_response->data;

        $this->insert_data_campaign_mpo($data , $token);

        wp_send_json_success($parsed_response);

        die();
        
    }

    public function auto_get_campaign_mpo(){
        global $wpdb;

        $list_token = $wpdb->get_results("SELECT access_token FROM {$wpdb->prefix}mpo_config");
        
        foreach($list_token as $value){
            $token = $value->access_token;
            $this->get_data_campaign_mpo($token);
        }
        
    }


    // end campaing

    public function request_manager_order($api_endpoint , $request , $method){
        $response = wp_remote_request( $api_endpoint , array(
            'method'     => $method ? $method : 'GET',
            'headers'     => array(),
            'body'       => $request,
            'timeout'    => 70,
            'sslverify'  => false,
            'data_format' => 'body',
        ) );

        $parsed_response = json_decode( $response['body'] );

        return $parsed_response;
    }

    public function save_note_order_mpo(){
        
        global $wpdb;

        $order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';
        $note_order = isset($_POST['note_order']) ? $_POST['note_order'] : '';

        $update_note = $wpdb->update($wpdb->prefix.'mpo_order', array('custom_note' => $note_order) ,array( 'order_id' => $order_id ));
        
        wp_send_json_success($update_note);

        die();
    }

    public function save_note_order_cc_mpo(){
        
        global $wpdb;

        $order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';
        $note_order_cc = isset($_POST['note_order_cc']) ? $_POST['note_order_cc'] : '';

        $update_note = $wpdb->update($wpdb->prefix.'mpo_order', array('custom_note_cc' => $note_order_cc) ,array( 'order_id' => $order_id ));
        
        wp_send_json_success($update_note);
        die();
    }

    
    
}

$mpo_ajax = ManagerOrderAjax::instance();