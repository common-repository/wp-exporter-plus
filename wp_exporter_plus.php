<?php
/*
Plugin Name: WP Exporter Plus
Plugin URI: https://www.galaxyweblinks.com/
Description: This plugin provides functionality to export posts, users, products, orders, top 10 selling products data in CSV.
Version: 3.3
Text Domain: wpepcsv
Author: Galaxy Weblinks
Author URI: https://www.galaxyweblinks.com/
License:GPL2
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include(plugin_dir_path( __FILE__ ).'export_functions.php'); // included export_functions file.

/**
 * Register Custom JS.
 */
function gwl_wpepcsv_register_script() 
{
  wp_register_style ( 'gwl_wpepcsv_custom_style', plugins_url('/css/custom-style.css', __FILE__), false, '1.0', 'all' );
  wp_enqueue_style  ( 'gwl_wpepcsv_custom_style' );
}
add_action('admin_enqueue_scripts', 'gwl_wpepcsv_register_script'); // Added script to WP admin
add_action('wp_enqueue_scripts', 'gwl_wpepcsv_register_script'); // Added script to frontend

add_filter( 'bulk_actions-users', 'gwl_wpepcsv_register_userexport_bulk_actions' ); // Export option added on users page
add_filter( 'handle_bulk_actions-users', 'gwl_wpepcsv_userexport_bulk_action_handler', 10, 3 ); // Users export option handler

add_filter( 'bulk_actions-edit-post', 'gwl_wpepcsv_register_postexport_bulk_actions' ); // Export option added on posts page
add_filter( 'handle_bulk_actions-edit-post', 'gwl_wpepcsv_postexport_bulk_action_handler', 10, 3 ); // Post export option handler

/**
 * Check woocommerce plugin is exists and activated.
 */
if ( class_exists( 'WooCommerce' ) ) 
{
  add_filter( 'bulk_actions-edit-product', 'gwl_wpepcsv_register_productexport_bulk_actions' ); // Export and Set sale price option added on products page
  add_filter( 'handle_bulk_actions-edit-product', 'gwl_wpepcsv_productexport_bulk_action_handler', 10, 3 ); // Post export option handler

  add_filter( 'bulk_actions-edit-shop_order', 'gwl_wpepcsv_register_orderexport_bulk_actions' ); // Export option added on orders page
  add_filter( 'handle_bulk_actions-edit-shop_order', 'gwl_wpepcsv_orderexport_bulk_action_handler', 10, 3 ); // Order export option handler

  //fired before displaying widgets and after loading widgets API library.
  add_action("wp_dashboard_setup", "gwl_wpepcsv_display_custom_tsp_dashboard");
  //allows logged in users to submit requests.
  add_action( 'admin_post_add_wpepcsv', 'gwl_wpepcsv_prefix_admin_add_wpepcsv' );

  // add shortcode to display top selling products.
  add_shortcode( 'wpepcsv_top_selling_pro', 'gwl_wpepcsv_top_selling_pro' );
}

/**
 * Added bulk action to users list page.
 */
function gwl_wpepcsv_register_userexport_bulk_actions($bulk_actions) 
{
  $bulk_actions['gwl_userexport_to_csv'] = __( 'Export to CSV', 'gwl_userexport_to_csv');
  return $bulk_actions;
}
/**
 * Users bulk action handler.
 */ 
function gwl_wpepcsv_userexport_bulk_action_handler( $redirect_to, $doaction, $user_ids ) 
{
  if ( $doaction !== 'gwl_userexport_to_csv' ) 
  {
    return $redirect_to;
  }
  wpepcsv_export_users_toCSV_gwl($user_ids); // Users export function
}

/**
 * Added bulk action to posts list page.
 */
function gwl_wpepcsv_register_postexport_bulk_actions($bulk_actions) 
{
  $bulk_actions['gwl_postexport_to_csv'] = __( 'Export to CSV', 'gwl_postexport_to_csv');
  return $bulk_actions;
}
/**
 * Posts bulk action handler.
 */  
function gwl_wpepcsv_postexport_bulk_action_handler( $redirect_to, $doaction, $post_ids ) 
{
  if ( $doaction !== 'gwl_postexport_to_csv' ) 
  {
    return $redirect_to;
  }
  wpepcsv_export_posts_toCSV_gwl($post_ids); // Posts export function
}

/**
 * Added bulk actions to products list page.
 */
function gwl_wpepcsv_register_productexport_bulk_actions($bulk_actions) 
{
  $bulk_actions['gwl_productexport_to_csv'] = __( 'Export to CSV', 'gwl_productexport_to_csv');
  return $bulk_actions;
}
/**
 * Product bulk action handler.
 */ 
function gwl_wpepcsv_productexport_bulk_action_handler( $redirect_to, $doaction, $product_ids ) 
{
  if ( $doaction !== 'gwl_productexport_to_csv' ) 
  {
    return $redirect_to;
  }
  wpepcsv_export_products_toCSV_gwl($product_ids); // Products export function
}

/**
 * Added bulk actions to orders list page.
 */
function gwl_wpepcsv_register_orderexport_bulk_actions($bulk_actions) 
{
  $bulk_actions['gwl_orderexport_to_csv'] = __( 'Export to CSV', 'gwl_orderexport_to_csv');
  return $bulk_actions;
}
/**
 * Order bulk action handler.
 */
function gwl_wpepcsv_orderexport_bulk_action_handler( $redirect_to, $doaction, $order_ids ) 
{
  if ( $doaction !== 'gwl_orderexport_to_csv' ) 
  {
    return $redirect_to;
  }
  wpepcsv_export_orders_toCSV_gwl($order_ids);  // Orders export function
}

/**
 * Function to create a dashboard widget.
 */
function gwl_wpepcsv_display_custom_tsp_dashboard()
{
    //this function is used to create a dashboard widget.
    wp_add_dashboard_widget("gwlwpepcsv", "Top Selling Products", "gwl_display_top_selling_products");
}

/**
 * Callback to display the content in the widget.
 */
function gwl_display_top_selling_products()
{
  echo sprintf('<h3>%s</h3>',__( 'Top 10 Selling Products', 'wpepcsv'));
  global $wpdb;
  $tbl_post = $wpdb->prefix."posts";
  $tbl_postmeta = $wpdb->prefix."postmeta";
  $top_products = $wpdb->get_results("SELECT $tbl_post.ID,$tbl_post.post_title,$tbl_postmeta.meta_value FROM $tbl_post INNER JOIN $tbl_postmeta ON $tbl_post.ID = $tbl_postmeta.post_id WHERE $tbl_post.post_status = 'publish' AND $tbl_post.post_type = 'product' AND $tbl_postmeta.meta_key='total_sales' AND NOT ($tbl_postmeta.meta_value = 0) ORDER BY CAST($tbl_postmeta.meta_value AS unsigned) DESC LIMIT 0,10");
  if(!empty($top_products)) 
  {
    $top_pro_arr = array();
    echo '<form name="export_csv_form" action="'.admin_url('admin-post.php').'" method="post">';
      echo '<div style="overflow-x:auto;" class="wpepcsvtotal_sale"><table>';
        echo '<tr><th>'.__( 'Product Name', 'wpepcsv' ).'</th><th>'.__( 'Total Sale', 'wpepcsv' ).'</th></tr>';
        foreach ($top_products as $top_product) 
        {
          $top_pro_arr[] = $top_product->ID;
          echo '<input type="hidden" name="wpepcsv_data[]" value="'.$top_product->ID.'">';
          echo '<tr>';
            if(!empty($top_product->post_title)) 
            {
              echo '<td><a href="'.get_permalink( $top_product->ID ).'" title="'.$top_product->post_title.'">'.$top_product->post_title.'</a></td>';
            }
            if(!empty($top_product->meta_value)) 
            {
              echo '<td>'.$top_product->meta_value.'</td>';
            }
          echo '</tr>';
        }
        $img_path = plugin_dir_url( __FILE__ ).'images/csv.png';
      echo '</table></div>';
      echo '<input type="hidden" name="action" value="add_wpepcsv">';
      echo '<p><b>'.__( 'Export To CSV', 'wpepcsv' ).'</b> <img src="'.$img_path.'" alt="file-img" title="file image">: '.sprintf('<input type="%s" value="%s" class="%s">',__( 'submit', 'wpepcsv'),__( 'Export', 'wpepcsv'),__( 'button', 'wpepcsv')).'</p>';
    echo '</form>';
  }
}

/**
 * Admin post handler function.
 */
function gwl_wpepcsv_prefix_admin_add_wpepcsv()
{
  if (!empty($_REQUEST['wpepcsv_data'])) 
  {
    wpepcsv_export_products_toCSV_gwl($_REQUEST['wpepcsv_data']); // Top Products export function.
  }
}