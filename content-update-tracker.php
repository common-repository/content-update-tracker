<?php
/**
 * Plugin Name: Content Update Tracker
 * Description: Effortlessly monitor content update-related data like the "Last Updated Date" for your WordPress posts and pages in a user-friendly dashboard, all exportable with a convenient 1-click export.
 * Version: 1.0
 * Author: Marcel Iseli
 * License:      GPL2
*  License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 */

 add_action( 'wp_enqueue_scripts', 'cutp_my_plugin_enqueue_scripts' );
function cutp_my_plugin_enqueue_scripts() {
  // Register your CSS file.
  wp_register_style('content-update-tracker-style', plugins_url('assets/css/style.css', __FILE__));
    // Enqueue CSS file
    wp_enqueue_style( 'content-update-tracker-style' );
  // Register your JS file.
  wp_register_script('content-update-tracker-script', plugins_url('assets/js/script.js', __FILE__));

  wp_enqueue_style( 'content-update-tracker-script' );
}


add_action('admin_enqueue_scripts', 'cutp_my_plugin_enqueue_admin_scripts');
function cutp_my_plugin_enqueue_admin_scripts() {
    // Register your CSS file.
    wp_register_style('content-update-tracker-admin-style', plugins_url('assets/css/style.css', __FILE__));
    // Enqueue CSS file.
    wp_enqueue_style('content-update-tracker-admin-style');

    // add  chart 
    wp_register_script( 'content-update-tracker-admin-chart', plugins_url('assets/js/chart.js', __FILE__)  );
    wp_enqueue_script( 'content-update-tracker-admin-chart' );
    
    wp_register_script( 'content-update-tracker-excel-export', 'https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js'
/*plugins_url('assets/js/xlsx.js', __FILE__)*/  );
    wp_enqueue_script( 'content-update-tracker-excel-export' );

    wp_register_script( 'content-update-tracker-admin-chartjs-plugin-labels', plugins_url('assets/js/datalabels.js', __FILE__) );
    wp_enqueue_script( 'content-update-tracker-admin-chartjs-plugin-labels' );
 
    // Register your JS file.
    wp_register_script('content-update-tracker-admin-script', plugins_url('assets/js/script.js', __FILE__));

    // localize
    wp_localize_script( 'content-update-tracker-admin-script', 'cump_local_data', [ 'nonce' => wp_create_nonce( 'ajax_call_nonce' ), 'ajaxurl' => admin_url('admin-ajax.php') ] );

    // Enqueue JS file.
    wp_enqueue_script('content-update-tracker-admin-script');
	

}

require_once plugin_dir_path(__FILE__) . 'includes/content-update-function.php';
require_once plugin_dir_path(__FILE__) . 'includes/content-ajax-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/content-meta-box.php';

