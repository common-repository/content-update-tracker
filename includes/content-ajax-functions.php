<?php
// ajax processing
add_action('wp_ajax_exclude_post', 'cutp_exclude_post');
add_action('wp_ajax_nopriv_exclude_post', 'cutp_exclude_post');

function cutp_exclude_post(){
	global $current_user, $wpdb;
	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
	 
		$post_id = (int)$_POST['id'];
		$action_type = sanitize_text_field( $_POST['action_type'] );
	 
        if( $action_type == 'include' ){
            update_post_meta( $post_id, 'cutp_post_excluded', 0 );
        }
        if( $action_type == 'exclude' ){
            update_post_meta( $post_id, 'cutp_post_excluded', 1 );
        }
		
	 
		echo json_encode([ 'result' => 'success' ]);
	}
	die();
}


// ajax view note
add_action('wp_ajax_view_note', 'cutp_view_note');
add_action('wp_ajax_nopriv_view_note', 'cutp_view_note');

function cutp_view_note(){
	global $current_user, $wpdb;
	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
	 
		$post_id = (int)$_POST['id'];
        $post_data = get_post( $post_id );
		echo json_encode([ 'result' => 'success', 'content' => get_post_meta( $post_id, '_cutp_notes', true ), 'title' => $post_data->post_title ]);
	}
	die();
}
// ajax save note
add_action('wp_ajax_save_note', 'cutp_save_notee');
add_action('wp_ajax_nopriv_save_note', 'cutp_save_notee');

function cutp_save_notee(){
	global $current_user, $wpdb;
	if( check_ajax_referer( 'ajax_call_nonce', 'security') ){
	 
		$post_id = (int)$_POST['id'];
		$post_content =   $_POST['content']  ;
        update_post_meta( $post_id, '_cutp_notes', $post_content );
		echo json_encode([ 'result' => 'success', 'cont' => $post_content, 'id' =>  $post_id,'new' => get_post_meta( $post_id, '_cutp_notes', true ) ]);
	}
	die();
}