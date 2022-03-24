<div id="overlay">
  <div class="cv-spinner">
	<span class="spinner_order"></span>
  </div>
</div>
<div class="container-fluid list_client_id mt-5">
	<div class="row flex-head-order">
		<div class="col-4">
			<p class="h1"><?php echo __( 'List Order', 'order_sandbox' ); ?></p>
		</div>
		<div class="col-4">
			<ul class="nav nav-pills">
				<?php
				if ( isset( $_GET['time'] ) ) {
					$time = $_GET['time'];
				} else {
					$time = '';
				};
				?>
				<li class="nav-item">
					<a class="nav-link nav-time <?php echo $time == 0 ? 'active' : ''; ?>" href="<?php echo $admin_url . '&time=0'; ?>">All time</a>
				</li>
				<li class="nav-item">
					<a class="nav-link nav-time <?php echo $time == 1 ? 'active' : ''; ?>" href="<?php echo $admin_url . '&time=1'; ?>">To Day</a>
				</li>
				<li class="nav-item">
					<a class="nav-link nav-time <?php echo $time == 2 ? 'active' : ''; ?>" href="<?php echo $admin_url . '&time=2'; ?>">Yesterday</a>
				</li>
				<li class="nav-item">
					<a class="nav-link nav-time <?php echo $time == 7 ? 'active' : ''; ?>" href="<?php echo $admin_url . '&time=7'; ?>">Last Week</a>
				</li>
				<li class="nav-item">
					<a class="nav-link nav-time <?php echo $time == 30 ? 'active' : ''; ?>" href="<?php echo $admin_url . '&time=30'; ?>">Last Month</a>
				</li>
			</ul>
		</div>
		<div class="col-4">
			<div class="input-group">
				<div class="input-group-prepend">
					<?php
					if ( isset( $_GET['key_search'] ) ) {
						$key_search = $_GET['key_search'];
					} else {
						$key_search = '';
					};
					?>
					<select class="custom_search" id="key_search">
						<option value="order_id" <?php echo $key_search == 'order_id' ? 'selected' : ''; ?>> Order id </option>     
						<option value="product_id" <?php echo $key_search == 'product_id' ? 'selected' : ''; ?>>Product Id </option> 
						<option value="shipping_name" <?php echo $key_search == 'shipping_name' ? 'selected' : ''; ?>> User Name </option>  
					</select>
				</div>
				<?php
				if ( isset( $_GET['val_search'] ) ) {
					$val_search = $_GET['val_search'];
				} else {
					$val_search = '';
				};
				?>
				<input type="text" class="form-control" placeholder="Enter the key..." name="val_search" value="<?php echo $val_search ? $val_search : ''; ?>">
				<button type="button" class="btn btn-dark btn-search_order"><i class="fa fa-search" aria-hidden="true"></i></button>
			</div> 
		</div>
	</div>
	<div class="count-order mb-4">
		<span>Total Order: <span style="font-weight: bold; font-size:15px;"><?php echo $total_sql; ?></span></span>
	</div>

	<table class="table table-striped">
		<thead>
			<tr>
				<th scope="col"><?php echo __( 'STT', 'order_sandbox' ); ?></th>
				<th scope="col"><?php echo __( 'Desgin name', 'order_sandbox' ); ?></th>
				<th scope="col"><?php echo __( 'Campaign', 'order_sandbox' ); ?></th>
				<th scope="col"><?php echo __( 'App Name', 'order_sandbox' ); ?></th>
				<th scope="col"><?php echo __( 'Date', 'order_sandbox' ); ?></th>
				<th scope="col" style="display:none">Order ID</th>
				<th scope="col">
					<?php echo __( 'Days to Fulfill', 'order_sandbox' ); ?>
					<span class="short_day"><i class="fa fa-caret-up" aria-hidden="true"></i></span>
				</th>    
				<th scope="col" style="display:none"><?php echo __( 'View Product (SKU)', 'order_sandbox' ); ?></th>
				<th scope="col"><?php echo __( 'Product Name', 'order_sandbox' ); ?></th>
				<th scope="col" class="show-pc" >
					<?php echo __( 'Image', 'order_sandbox' ); ?>
					<span class="show-pc" style="color:red"><i class="fa fa-caret-right" aria-hidden="true"></i></span>
				</th>
				<th class="hidden-pc" scope="col"><?php echo __( 'Variation', 'order_sandbox' ); ?></th>
				<th class="hidden-pc" scope="col"><?php echo __( 'Currency Code', 'order_sandbox' ); ?></th>
				<th class="hidden-pc" scope="col"><?php echo __( 'Price', 'order_sandbox' ); ?></th>
				<th class="hidden-pc" scope="col"><?php echo __( 'Cost', 'order_sandbox' ); ?></th>
				<th class="hidden-pc" scope="col"><?php echo __( 'Shipping', 'order_sandbox' ); ?></th>
				<th class="hidden-pc" scope="col"><?php echo __( 'Shipping Cost', 'order_sandbox' ); ?></th>
				<th class="hidden-pc" scope="col"><?php echo __( 'Quantity', 'order_sandbox' ); ?></th>
				<th class="hidden-pc" scope="col"><?php echo __( 'Total Cost', 'order_sandbox' ); ?></th>
				<th class="hidden-pc" scope="col"><?php echo __( 'Warehouse Name (Warehouse Id)', 'order_sandbox' ); ?></th>
				<th scope="col"><?php echo __( 'Ship to', 'order_sandbox' ); ?></th>
				<!-- <th scope="col"><?php // echo __( 'Open Tickets', 'order_sandbox' ); ?></th> -->
				<th scope="col" class="show-note">Note</th>
				<th scope="col"><?php echo __( 'NoteCC', 'order_sandbox' ); ?></th>
				<th scope="col"><?php echo __( 'Tracking Number', 'order_sandbox' ); ?></th>
				<th scope="col"><?php echo __( 'Tracking Provider', 'order_sandbox' ); ?></th>
				<th scope="col"><?php echo __( 'Country Code', 'order_sandbox' ); ?></th>
				<th scope="col"><?php echo __( 'Actions', 'order_sandbox' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$currency_symbols = mpo_currency_symbols();
		$i                = 1;
	
		global $wpdb;
		foreach ( $data as $value ) {

			$symbols        = array_key_exists( $value->currency_code, $currency_symbols ) ? $currency_symbols[ $value->currency_code ] : '';
			$query_app_name = $wpdb->get_results( "SELECT name_app FROM {$wpdb->prefix}mpo_config WHERE client_id = '{$value->client_id}'" );
			$time_fill      = human_time_diff( time(), strtotime( $value->shipped_date ) );

			$class_time_fill = 'alert-warning';
			$array_time_fill = explode( ' ', $time_fill );

			if ( $array_time_fill[0] <= 24 && $array_time_fill[1] == 'hours' ) {
				$class_time_fill = 'alert-danger';
			};
			if ( strtotime( $value->shipped_date ) < time() ) {
				$time_fill       = '0 hours';
				$class_time_fill = 'alert-danger';
			}

			?>
			
			<tr class="row-tk">
				<td><?php echo $i; ?></td>
				<td class="row_design_name">
					<select name="design_name" class="design_name" style="width:200px">
						<option value="<?php echo $value->design_name ?? ''; ?>" selected="selected"><?php echo $value->design_name ?? ''; ?></option>
					</select>
					<button type="button" class="btn btn-info push_design" style="margin-top:10px">Push</button>               
				</td>
				<td scope="row">
					<?php if ( empty( $value->camp_id ) ) { ?>
						<button type="button" class="btn btn-info create_camp"><?php echo __( 'Create ', 'order_sandbox' ); ?></button>
						<?php
					} else {
						$camp_name = $wpdb->get_results( "SELECT campaign_name FROM {$wpdb->prefix}mpo_campaign WHERE camp_id  = '{$value->camp_id}'" );
						?>
						<a href="<?php echo admin_url() . '/admin.php?page=mpo_list_campaign&camp_id=' . $value->camp_id; ?>" target="_blank"><?php echo $camp_name[0]->campaign_name; ?></a>
					<?php } ?>
				</td>
				<td class="access_token" style="display:none"><?php echo $value->access_token; ?></td>
				<td class="product_id_camp" style="display:none"><?php echo $value->product_id_camp; ?></td>
				<td scope="row"><?php echo ! empty( $query_app_name[0] ) ? $query_app_name[0]->name_app : ''; ?></td>
				<td scope="row">
					<div><b>Date : </b><?php echo $value->order_time; ?></div>
					<div><b>OrderID : </b><?php echo $value->order_id; ?></div>
					<div><b>SKU : </b><?php echo $value->product_id; ?></div>
				</td>
				<td class="order_id" style="display:none;"><?php echo $value->order_id; ?></td>
				<td class="day_to_ful ful_<?php echo $i; ?>">
					<span class="<?php echo $class_time_fill; ?>">
						<?php echo $time_fill; ?>
					</span>
				</td>
				<td class="product_sku" style="display:none"><?php echo $value->product_id; ?></td>
				<td class="product_name"><?php echo $value->product_name; ?></td>
				<td class="product_img"><img src="<?php echo $value->product_image_url; ?>" alt="" width="60" height="60"></td>
				<td class="hidden-pc">size: <span class="product_size"><?php echo $value->size; ?></span>, color: <span class="product_color"><?php echo $value->color; ?></span> </td>
				<td class="order_currency_code hidden-pc"><?php echo $value->currency_code; ?></td>
				<td class="hidden-pc" class="product_price"><?php echo $value->price . $symbols; ?></td>
				<td class="hidden-pc"><?php echo $value->cost . $symbols; ?></td>
				<td class="hidden-pc"><?php echo $value->shipping . $symbols; ?></td>
				<td class="hidden-pc"><?php echo $value->shipping_cost . $symbols; ?></td>
				<td class="order_quantity hidden-pc"><?php echo $value->quantity; ?></td>
				<td class="hidden-pc"><?php echo $value->order_total . $symbols; ?></td>
				<td class="hidden-pc"><?php echo $value->warehouse_name; ?>, <?php echo $value->warehouse_id; ?> </td>
				<td>
					<div class="content-shiping">
						<p class="view_shiping">View</a>
						<p class="shiping_name" style="display:none"><?php echo $value->shipping_name; ?></p>
						<p class="shipping_phone" style="display:none"><?php echo $value->shipping_phone; ?></p>
						<p class="shipping_country" style="display:none"><?php echo $value->shipping_country; ?></p>
						<p class="shipping_zipcode" style="display:none"><?php echo $value->shipping_zipcode; ?></p>
						<p class="shipping_address_1" style="display:none"><?php echo $value->shipping_address_1; ?></p>
						<p class="shipping_address_2" style="display:none"><?php echo $value->shipping_address_2; ?></p>
						<p class="shipping_state" style="display:none"><?php echo $value->shipping_state; ?></p>
						<p class="shipping_city" style="display:none"><?php echo $value->shipping_city; ?></p>
					</div>
					<?php echo $value->shipping_name; ?></td>
				<td class="row_order_note">
					<span class="icon_note"><i class="fa fa-pencil-square" aria-hidden="true"></i></span>
					<textarea class="order_note" name="order_note" cols="20">
						<?php echo $value->custom_note; ?>
					</textarea>
				</td>
				<td class="row_order_note note_cc">
					<span class="icon_note_cc"><i class="fa fa-pencil-square" aria-hidden="true"></i></span>
					<textarea class="order_note_cc" name="order_note_cc" cols="20">
						<?php echo $value->custom_note_cc; ?>
					</textarea>
				</td>
				<td >        
					<input id="track_id" type="text" class="form-control" placeholder="Tracking id " value="<?php echo $value->tracking_number ? $value->tracking_number : ''; ?>">
				</td>
				<td>        
					<input id="track_provider" type="text" class="form-control" placeholder="Provider " value="<?php echo $value->tracking_provider ? $value->tracking_provider : ''; ?>">
				</td>
				<td>        
					<input id="country_code" type="text" class="form-control" placeholder="Code" value="<?php echo $value->country_code ? $value->country_code : ''; ?>">
				</td>
				<td>
					<div class="d-flex justify-content-between">
						<button type="button" class="btn btn-info mr-2 submit_tracking theproblem" data-toggle="tooltip" data-placement="top" title="<?php echo __( 'Update Tracking Number', 'order_sandbox' ); ?>"><i class="fa fa-map-o" aria-hidden="true"></i></button>
						<button type="button" class="btn btn-info mr-2 push_merchant theproblem" data-toggle="tooltip" data-placement="top" title="<?php echo 'Create order merchant'; ?>"><i class="fa fa-home" aria-hidden="true"></i></button>
						<button type="button"  class="btn btn-info export_csv theproblem" data-toggle="tooltip" data-placement="top" title="<?php echo __( 'Export CSV', 'order_sandbox' ); ?>"><i class="fa fa-share-square-o" aria-hidden="true"></i></button>
					</div>
				</td>
				<input type="hidden" value="<?php echo get_option( 'token_mer' ) ? get_option( 'token_mer' ) : ''; ?>" name="token_mer">
			</tr>
		<?php $i++;} ?>
		</tbody>
	</table>
	<nav class="mt-5">
		 <ul class="pagination">
			<?php for ( $i = 1;$i <= $total_pages;$i++ ) : ?>
				<?php if ( $i == $pageno ) : ?>
					<li class="page-item active">
						<a class="page-link" href="#"><?php echo $i; ?><span class="sr-only">(current)</span></a>
					</li>
				<?php else : ?>
					<li class="page-item"><a class="page-link" href="<?php echo $admin_url; ?>&pageno=<?php echo $i; ?>" title="<?php echo $i; ?>"><?php echo $i; ?></a></li>
				<?php endif ?>
			<?php endfor; ?>
		</ul>
	</nav>
</div>
