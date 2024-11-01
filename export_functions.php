<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Export users to CSV.
 */
function wpepcsv_export_users_toCSV_gwl($user_ids) 
{
	// Check for current user privileges 
	if( !current_user_can( 'manage_options' ) ){ return false; }

	// Check if we are in WP-Admin
	if( !is_admin() ){ return false; }

	ob_start();
	$domain = $_SERVER['SERVER_NAME']; // GetServer Name
	$filename = 'users-' . $domain . '-' . time() . '.csv'; // FileName

	$data_rows = array();
	global $wpdb;
	$user_ids = implode(',', $user_ids);
	$sql = 'SELECT ID,display_name,user_email,user_login FROM '.$wpdb->users.' WHERE ID IN ('.$user_ids.')';
	$header_row = array(
		'ID',
		'FirstName',
		'LastName',
		'Email',
		'UserName',
		'Role'
	);
	$users = $wpdb->get_results( $sql, 'ARRAY_A' );
	if (!empty($users)) 
	{
		foreach ( $users as $user ) 
		{
			$user_meta  = get_userdata($user['ID']);
			$user_roles = $user_meta->roles;
			$user_roles = implode(',', $user_roles);
			$row = array(
		    $user['ID'],		
			$user_meta->first_name,
			$user_meta->last_name,
			$user['user_email'],
			$user['user_login'],
			$user_roles
			);
			$data_rows[] = $row;
		}
	}

	$fh = @fopen( 'php://output', 'w' );
	fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Content-Description: File Transfer' );
	header( 'Content-type: text/csv' );
	header( "Content-Disposition: attachment; filename={$filename}" );
	header( 'Expires: 0' );
	header( 'Pragma: public' );
	fputcsv( $fh, $header_row );

	if(!empty($data_rows)) 
	{
		foreach ( $data_rows as $data_row )
		{
			fputcsv( $fh, $data_row );
		}
	}

	fclose( $fh );
	ob_end_flush();
	die();
}

/**
 * Export posts to CSV.
 */
function wpepcsv_export_posts_toCSV_gwl($post_ids)
{
	// Check for current user privileges 
	if( !current_user_can( 'manage_options' ) ){ return false; }

	// Check if we are in WP-Admin
	if( !is_admin() ){ return false; }

	ob_start();
	$domain = $_SERVER['SERVER_NAME'];  // GetServer Name
	$filename = 'posts-' . $domain . '-' . time() . '.csv'; // FileName

	$data_rows = array();
	global $wpdb;
	$post_ids = implode(',', $post_ids);
	$sql = 'SELECT ID,post_title,post_status,post_content,post_date,post_modified FROM '.$wpdb->posts.' WHERE ID IN ('.$post_ids.') AND post_type = "post"';
	$header_row = array(
		'ID',
		'Post Title',
		'Post Status',
		'Post Content',
		'Post Date',
		'Post Modified'
	);
	$posts = $wpdb->get_results( $sql, 'ARRAY_A' );
	if (!empty($posts)) 
	{
		foreach ( $posts as $post ) 
		{
			$row = array(
			$post['ID'],	
			$post['post_title'],
			$post['post_status'],
			wp_strip_all_tags($post['post_content']),
			$post['post_date'],
			$post['post_modified']
			);
			$data_rows[] = $row;
		}
	}

	$fh = @fopen( 'php://output', 'w' );
	fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Content-Description: File Transfer' );
	header( 'Content-type: text/csv' );
	header( "Content-Disposition: attachment; filename={$filename}" );
	header( 'Expires: 0' );
	header( 'Pragma: public' );
	fputcsv( $fh, $header_row );

	if(!empty($data_rows)) 
	{
		foreach ( $data_rows as $data_row )
		{
			fputcsv( $fh, $data_row );
		}
	}

	fclose( $fh );
	ob_end_flush();
	die();
}

/**
 * Export products to CSV.
 */
function wpepcsv_export_products_toCSV_gwl($product_ids) 
{
	// Check for current user privileges 
	if( !current_user_can( 'manage_options' ) ){ return false; }

	// Check if we are in WP-Admin
	if( !is_admin() ){ return false; }

	ob_start();
	$domain = $_SERVER['SERVER_NAME']; // GetServer Name
	$filename = 'products-' . $domain . '-' . time() . '.csv'; // FileName

	$data_rows = array();
	global $wpdb;
	$header_row = array(
		'ID',
		'Type',
		'Name',
		'Slug',
		'Category',
		'Date Created',
		'Date Modified',
		'Status',
		'SKU',
		'Link',
		'Price',
		'Regular Price',
		'Sale Price',
		'Total Sales',
		'Tax',
		'Stock',
		'Full Image URl'
	);

	if (!empty($product_ids)) 
	{
		$pro_cat = '';
		foreach ( $product_ids as $product_id ) 
		{
			$product = wc_get_product( $product_id );
			if( $term_lists = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names')) ){
				foreach ($term_lists as $term_list) {
				 $pro_cat .= $term_list.', ';	
				}
				$pro_cat = rtrim($pro_cat,", ");
			}
			else{
			$pro_cat = '';
			}
			$row = array(
			$product->get_id(),
			$product->get_type(),	
			$product->get_name(),
			$product->get_slug(),
			$pro_cat,
			$product->get_date_created(),
			$product->get_date_modified(),
			$product->get_status(),
			$product->get_sku(),
			get_permalink( $product->get_id() ),
			$product->get_price(),
			$product->get_regular_price(),
			$product->get_sale_price(),
			$product->get_total_sales(),
			$product->get_tax_status(),
			$product->get_stock_status(),
			get_the_post_thumbnail_url( $product->get_id(), 'full' )
			);
			$data_rows[] = $row;
		}
	}

	$fh = @fopen( 'php://output', 'w' );
	fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Content-Description: File Transfer' );
	header( 'Content-type: text/csv' );
	header( "Content-Disposition: attachment; filename={$filename}" );
	header( 'Expires: 0' );
	header( 'Pragma: public' );
	fputcsv( $fh, $header_row );

	if(!empty($data_rows)) 
	{
		foreach ( $data_rows as $data_row )
		{
			fputcsv( $fh, $data_row );
		}
	}

	fclose( $fh );
	ob_end_flush();
	die();
}

/**
 * Export orders to CSV.
 */
function wpepcsv_export_orders_toCSV_gwl($order_ids) 
{
	// Check for current user privileges 
	if( !current_user_can( 'manage_options' ) ){ return false; }

	// Check if we are in WP-Admin
	if( !is_admin() ){ return false; }

	ob_start();
	$domain = $_SERVER['SERVER_NAME']; // GetServer Name
	$filename = 'orders-' . $domain . '-' . time() . '.csv'; // FileName

	$data_rows = array();
	global $wpdb;
	$header_row = array(
		'ID',
		'Parent Id',
		'Order No.',
		'Product Id',
		'Product Name',
		'Status',
		'Currency',
		'Version',
		'Payment Method',
		'Payment Method Title',
		'Date Created',
		'Date Modified',
		'Total',
		'Discount Total',
		'Discount Tax',
		'Shipping Total',
		'Shipping Tax',
		'Cart Tax',
		'Total Tax',
		'Customer Id',
		'Billing FirstName',
		'Billing LastName',
		'Billing Company',
		'Billing Address_1',
		'Billing Address_2',
		'Billing City',
		'Billing State',
		'Billing Postcode',
		'Billing Country',
		'Billing Email',
		'Billing Phone',
		'Shipping FirstName',
		'Shipping LastName',
		'Shipping Company',
		'Shipping Address_1',
		'Shipping Address_2',
		'Shipping City',
		'Shipping State',
		'Shipping Postcode',
		'Shipping Country'
	);

	if (!empty($order_ids)) 
	{
		foreach ( $order_ids as $order_id ) 
		{
			// Get an instance of the WC_Order object
			$order = wc_get_order( $order_id );
			$order_data = $order->get_data(); // The Order data

			//getting all line items
			$product_ids = '';
			$pro_names = '';
	        foreach ($order->get_items() as $item_id => $item) 
	        {
	            $product_ids .= $item->get_product_id().', ';
	            $pro_names .= get_the_title($item->get_product_id()).', ';
	        }	
	        if (!empty($product_ids)) {
	        	$product_ids = rtrim($product_ids, ', ');
	        }
	        if (!empty($pro_names)) {
	        	$pro_names = rtrim($pro_names, ', ');
	        }
			$row = array(
				$order_data['id'],
				$order_data['parent_id'],
				$order->get_order_number(),
				$product_ids,
				$pro_names,
				$order_data['status'],
				$order_data['currency'],
				$order_data['version'],
				$order_data['payment_method'],
				$order_data['payment_method_title'],
				$order_data['date_created']->date('Y-m-d H:i:s'),
				$order_data['date_modified']->date('Y-m-d H:i:s'),
				$order_data['total'],
				$order_data['discount_total'],
				$order_data['discount_tax'],
				$order_data['shipping_total'],
				$order_data['shipping_tax'],
				$order_data['cart_tax'],
				$order_data['total_tax'],
				$order_data['customer_id'],
				$order_data['billing']['first_name'],
				$order_data['billing']['last_name'],
				$order_data['billing']['company'],
				$order_data['billing']['address_1'],
				$order_data['billing']['address_2'],
				$order_data['billing']['city'],
				$order_data['billing']['state'],
				$order_data['billing']['postcode'],
				$order_data['billing']['country'],
				$order_data['billing']['email'],
				$order_data['billing']['phone'],
				$order_data['shipping']['first_name'],
				$order_data['shipping']['last_name'],
				$order_data['shipping']['company'],
				$order_data['shipping']['address_1'],
				$order_data['shipping']['address_2'],
				$order_data['shipping']['city'],
				$order_data['shipping']['state'],
				$order_data['shipping']['postcode'],
				$order_data['shipping']['country']
			);
			$data_rows[] = $row;
		}
	}

	$fh = @fopen( 'php://output', 'w' );
	fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Content-Description: File Transfer' );
	header( 'Content-type: text/csv' );
	header( "Content-Disposition: attachment; filename={$filename}" );
	header( 'Expires: 0' );
	header( 'Pragma: public' );
	fputcsv( $fh, $header_row );

	if(!empty($data_rows)) 
	{
		foreach ( $data_rows as $data_row )
		{
			fputcsv( $fh, $data_row );
		}
	}

	fclose( $fh );
	ob_end_flush();
	die();	
}

/**
 * Custom function to check integer value.
 */
function wpepcsv_is_integer($input)
{
    return(ctype_digit(strval($input)));
}

/**
 * Function to display top selling products.
 */
function gwl_wpepcsv_top_selling_pro( $atts ) 
{
	$atts = shortcode_atts( array(
		'limit' => '10'
	), $atts);
	$total_pro = $atts['limit'];
	if (wpepcsv_is_integer($total_pro) || $total_pro == '-1')
	{
		if($total_pro == '-1') 
		{
			$qry_str = '';
		}
		else
		{
			$qry_str = ' LIMIT 0,'.$total_pro;
		}	
		global $wpdb;
		$print_text = '';
	    $tbl_post = $wpdb->prefix."posts";
	    $tbl_postmeta = $wpdb->prefix."postmeta";
	    $top_products = $wpdb->get_results("SELECT $tbl_post.ID,$tbl_post.post_title,$tbl_postmeta.meta_value FROM $tbl_post INNER JOIN $tbl_postmeta ON $tbl_post.ID = $tbl_postmeta.post_id WHERE $tbl_post.post_status = 'publish' AND $tbl_post.post_type = 'product' AND $tbl_postmeta.meta_key='total_sales' AND NOT ($tbl_postmeta.meta_value = 0) ORDER BY CAST($tbl_postmeta.meta_value AS unsigned) DESC$qry_str");
	    $print_text .= '<div style="overflow-x:auto;" class="wpepcsvtotal_sale short_display"><table>';
	        $print_text .= '<tr><th align="left">'.__( 'Product Name', 'wpepcsv' ).'</th><th>'.__( 'Total Sale', 'wpepcsv' ).'</th></tr>';
	        if(!empty($top_products)) 
	        {
	        	foreach ($top_products as $top_product) 
		        {
		          $top_pro_arr[] = $top_product->ID;
		          $print_text .= '<tr>';
		            $print_text .= '<td><a href="'.get_permalink( $top_product->ID ).'" title="'.$top_product->post_title.'">'.$top_product->post_title.'</a></td>';
		            $print_text .= '<td>'.$top_product->meta_value.'</td>';
		          $print_text .= '</tr>';
		        }
	        }
	    $print_text .= '</table></div>';
		return $print_text;
	}
}
