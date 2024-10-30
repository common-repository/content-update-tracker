<?php
/**
 * Register meta box(es).
 */
add_action( 'add_meta_boxes', 'cutp_return_meta_box_callback' );
function cutp_return_meta_box_callback() {
	add_meta_box( 'cutp-meta-box-id', __( 'Notes', '' ), 'cutp_my_display_callback', 'post' );
	add_meta_box( 'cutp-meta-box-id', __( 'Notes', '' ), 'cutp_my_display_callback', 'page' );
}


/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function cutp_my_display_callback( $post ) {
	// Display code/markup goes here. Don't forget to include nonces!
	
	$out = '
	'.wp_nonce_field( '_cutp_notes_action', '_cutp_notes_nonce', false, false ).'
	
	<textarea name="_cutp_notes" id="_cutp_notes" class="large-text" rows="10">'.esc_html( get_post_meta( $post->ID, '_cutp_notes', true ) ).'</textarea>
	<div class="save_row">
	<button class="button button-primary button-large" type="button" id="editor_save_notes">Save</button><!-- / -->
	</div><!-- /.save_row -->
	';
	echo $out;
}

/**
 * Save meta box content.
 *
 * @param int $post_id Post ID
 */
add_action( 'save_post', 'cutp_save_meta_box' );
function cutp_save_meta_box( $post_id ) {
	// Save logic goes here. Don't forget to include nonce checks!

	if( isset( $_POST['_cutp_notes_nonce'] ) ){
		if( wp_verify_nonce( $_POST['_cutp_notes_nonce'], '_cutp_notes_action' ) ){	 
			update_post_meta( $post_id, '_cutp_notes', sanitize_text_field( $_POST['_cutp_notes']  ) );
		}
	}
	
}
