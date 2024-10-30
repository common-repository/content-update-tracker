<?php
// Action Hooks for WordPress Admin

add_action('admin_menu', 'cutp_last_updated_dashboard_menu');

function cutp_last_updated_dashboard_menu() {
    if (current_user_can('administrator')) {
        add_menu_page(
            'Content Update Tracker',
            'Content Update Tracker',
            'manage_options',
            'content_update_tracker',
            'cutp_content_update_tracker_page'
        );
    }
}


// Function to render the dashboard page
function cutp_content_update_tracker_page() {
    if (!current_user_can('administrator')) {
        return;
    }
    
    global $wpdb;


    //Include CSS for table styling

    echo '
    <div class="notes_edit_overlay">
        <div class="notes_editor_body">
            <div class="notes_title"></div><!-- /.notes_title -->
            <div class="notes_contet">
                <textarea name=""   cols="30" rows="10" id="notes_body"></textarea><!-- /#.notes_body -->
            </div><!-- /.notes_title -->
            <div class="save_notice_action">
                <button id="save_note_action" class="">Save</button><!-- /#save_note_action -->
                <button id="cancel_note_action" class="">Cancel</button><!-- /#save_note_action -->
            </div><!-- /.save_notice_action -->
        </div><!-- /.notes_editor_body -->
    </div><!-- /.notes_edit_overlay -->
    ';
   
    echo '<h1>Content Update Tracker</h1>';

    // Info box
    echo '<div class="info-box">';
    echo '<div class="col_container col_3">';
    echo '<div class="column">';
    echo '<h2>Article Counts by Category</h2>';

    $excluded_ids   = get_posts([
        'post_type' => [ 'post', 'page' ],
        'post_status' => 'publish',
        'showposts' => -1,
        'orderby' => 'modified',
        'order' => 'ASC',
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => 'cutp_post_excluded',
                'value' => '1',
            ]
        ]
    ]);

    if( count( $excluded_ids ) == 0 ){
        $excluded_ids = [0];
    }
    
    $green_count = count($wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE ID NOT IN (%s)  AND post_type IN ('post', 'page') AND post_status = 'publish' AND post_modified >= DATE_SUB(NOW(), INTERVAL 3 MONTH)", implode( ',', $excluded_ids ) )));
    $yellow_count = count($wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE ID NOT IN (%s)  AND  post_type IN ('post', 'page') AND post_status = 'publish' AND post_modified >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND post_modified < DATE_SUB(NOW(), INTERVAL 3 MONTH)", implode( ',', $excluded_ids ) )));
    $orange_count = count($wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE ID NOT IN (%s)  AND  post_type IN ('post', 'page') AND post_status = 'publish' AND post_modified >= DATE_SUB(NOW(), INTERVAL 12 MONTH) AND post_modified < DATE_SUB(NOW(), INTERVAL 6 MONTH)", implode( ',', $excluded_ids ) )));
    $red_count = count($wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE ID NOT IN (%s)  AND  post_type IN ('post', 'page') AND post_status = 'publish' AND post_modified < DATE_SUB(NOW(), INTERVAL 12 MONTH)", implode( ',', $excluded_ids ) )));

    $total_summ = $green_count + $yellow_count + $orange_count + $red_count;

  
    $green_out_value = round(($green_count / ( $total_summ )) * 100, 2);
    echo '<p><strong>GREEN:</strong> Updated in the last 3 months: ' . esc_html($green_count) . ' articles (' . esc_html($green_out_value) . '%)</p>';

    $yellow_out_value = round(($yellow_count / ( $total_summ )) * 100, 2);
    echo '<p><strong>YELLOW:</strong> Updated between 3 and 6 months ago: ' . esc_html($yellow_count) . ' articles (' . esc_html($yellow_out_value) . '%)</p>';

    $orange_out_value = round(($orange_count / ( $total_summ )) * 100, 2);
    echo '<p><strong>ORANGE:</strong> Updated between 6 and 12 months ago: ' . esc_html($orange_count) . ' articles (' . esc_html($orange_out_value) . '%)</p>';

    $red_out_value = round(($red_count / ( $total_summ )) * 100, 2);
    echo '<p><strong>RED:</strong> Not updated for over 12 months: ' . esc_html($red_count) . ' articles (' . esc_html($red_out_value) . '%)</p>';
    

    echo '</div>';
    echo '<div class="column">
    <input type="hidden" id="pie_values" value="'.wp_json_encode( [ $green_out_value, $yellow_out_value, $orange_out_value, $red_out_value ] ).'" />
        <div style="width:200px; height:200px;margin: auto;">
            <canvas id="myChart"   ></canvas>
        </div>
       
    ';
    echo '</div>';
    echo '<div class="column filter_title_block">
    <div class="cell_block">';
     // Filter by title
    echo '<label for="filter-Color">Filter by title: </label>';
    echo '<input type="text" id="title_search" placeholder="Enter title">';
    echo '</div></div>';
    echo '</div>';

    // Export Buttons
    echo '<button onclick="exportCSV(\'all\')" style="font-size: 18px; margin-left: 10px;">Export All</button>';
    echo '<button onclick="exportCSV(\'green\')" style="font-size: 18px; margin-left: 10px;">Export Green</button>';
    echo '<button onclick="exportCSV(\'yellow\')" style="font-size: 18px; margin-left: 10px;">Export Yellow</button>';
    echo '<button onclick="exportCSV(\'orange\')" style="font-size: 18px; margin-left: 10px;">Export Orange</button>';
    echo '<button onclick="exportCSV(\'red\')" style="font-size: 18px; margin-left: 10px;">Export Red</button>';

    echo '<br/><button onclick="export_clone(\'all\')" style="font-size: 18px; margin-left: 10px;">Export Excel All</button>';
    echo '<button onclick="export_clone(\'green\')" style="font-size: 18px; margin-left: 10px;">Export Excel Green</button>';
    echo '<button onclick="export_clone(\'yellow\')" style="font-size: 18px; margin-left: 10px;">Export Excel Yellow</button>';
    echo '<button onclick="export_clone(\'orange\')" style="font-size: 18px; margin-left: 10px;">Export Excel Orange</button>';
    echo '<button onclick="export_clone(\'red\')" style="font-size: 18px; margin-left: 10px;">Export Excel Red</button>';


    echo '
 
    
    <div class="hidden_excel">    
    <table id="excel_export" style="display: none;">
        <thead>
        </thead>
        <tbody></tbody>
    </table>
    </div>';

    // Filter by color dropdown
    echo '<label for="filter-Color">Filter by color: </label>';
    echo '<select id="filter-Color">
            <option value="all">All</option>
            <option value="green">Green</option>
            <option value="yellow">Yellow</option>
            <option value="orange">Orange</option>
            <option value="red">Red</option>
          </select>';
   
    
    // Table
    echo '
    <div class="tab_navi_cont">
        <div class="single_nav_tab tab_nav_1 active" data-id="1">
            Posts List
        </div>
        <div class="single_nav_tab tab_nav_2" data-id="2">
            Excluded List
        </div>
    </div><!-- /.tab_navi_cont -->
    <div class="table_container">
    <div class="single_tab tab_included tab_1">
    <table id="lastUpdated-Table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Edit</th> <!-- New Column for the Visit Link -->
                    <th>Visit</th> <!-- New Column for the Visit Link -->
                    
                    <th>Last Updated</th>
                    <th>Notes</th>
                    <th>Author</th>
                    <th>Word Count</th>
                    <th>Action</th> <!-- New Column for the Updated Button -->
                </tr>
            </thead>
            <tbody>';
    
    // Fetch and display posts
    /*
    $posts = $wpdb->get_results("SELECT ID, post_title, post_author, post_modified, post_content FROM {$wpdb->prefix}posts WHERE post_type IN ('post', 'page') AND post_status = 'publish' ORDER BY post_modified ASC ");
    */
     
    $posts = get_posts([
        'post_type' => [ 'post', 'page' ],
        'post_status' => 'publish',
        'showposts' => -1,
        'orderby' => 'modified',
        'order' => 'ASC',
        'fields' => 'ids',
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'cutp_post_excluded',
                'value' => '0',
            ],
            [
                'key' => 'cutp_post_excluded',
                'value' => '',
                'compare' => 'NOT EXISTS'
            ]
        ]
    ]);
    
    
 
    foreach ($posts as $post) {
        $post = get_post( $post );
        $last_updated = new DateTime($post->post_modified);
        $current_date = new DateTime();
        $interval = $last_updated->diff($current_date);
        $months = $interval->m + ($interval->y * 12);
        $word_count = str_word_count(strip_tags($post->post_content));

        $changed_words_count =  cutp_get_changed_words_count( $post->ID );

        if ($months < 3) {
            $color = 'green';
        } elseif ($months >= 3 && $months < 6) {
            $color = 'yellow';
        } elseif ($months >= 6 && $months < 12) {
            $color = 'orange';
        } elseif ($months >= 12) {
            $color = 'red';
        } else {
            $color = '';
        }

        // Check if the post has not been updated for at least 12 months
        $post_meta = get_post_meta($post->ID, 'last_updated_date', true);
        $last_updated_date = $post_meta ? new DateTime($post_meta) : null;
        if ($color === 'red' && (!$last_updated_date || $last_updated_date < $current_date->modify('-12 months'))) {
            // Update the last_updated_date metadata
            update_post_meta($post->ID, 'last_updated_date', $current_date->format('Y-m-d H:i:s'));
        }

        echo "<tr class='" . esc_attr($color) . "'>
                <td class='title_cell'>
                <span class='title_line'>" .  htmlspecialchars_decode( $post->post_title )  . "</span>
                <div class='exclude_post action_button' data-id='".esc_html( $post->ID )."'>X</div>
                </td>
                
                <td><a class='edit_url' href='" . esc_url(admin_url( 'post.php?post='.$post->ID.'&action=edit' ))  . "' target='_blank'>Edit</a></td> <!-- New Visit Link -->
                <td><a class='visit_url' href='" . esc_url(get_permalink($post->ID)) . "' target='_blank'>Visit</a></td>
                <td>" . esc_html($post->post_modified) . "</td>
                <td><a href='#' class='view_note' data-id='".$post->ID."'><i class='dashicons dashicons-edit'></i></a></td>
                <td>" . get_user_by( 'ID', $post->post_author)->user_login . "</td>
                <td class='counter_cell' data-value='".esc_html($word_count)."'  data-change='".strip_tags( $changed_words_count )."'>" . esc_html($word_count).( $changed_words_count ? ' ('.$changed_words_count.')' : '' ) . "</td>
                <td><button class='updateBtn' data-post-id='" . esc_attr($post->ID) . "'>Updated</button></td>
              </tr>";
    }

    echo '</tbody></table>
    </div>
    <div class="single_tab tab_excluded  tab_2">
    <table id="excluded-Table" class="">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Edit</th> <!-- New Column for the Visit Link -->
                    <th>Visit</th> <!-- New Column for the Visit Link -->
                    
                    <th>Last Updated</th>
                    <th>Notes</th>
                    <th>Author</th>
                    <th>Word Count</th>
                    <th>Action</th> <!-- New Column for the Updated Button -->
                </tr>
            </thead>
            <tbody>';
    
    // Fetch and display posts
    //$posts = $wpdb->get_results("SELECT ID, post_title, post_author, post_modified, post_content FROM {$wpdb->prefix}posts WHERE post_type IN ('post', 'page') AND post_status = 'publish' ORDER BY post_modified DESC");
    
    $posts = get_posts([
        'post_type' => [ 'post', 'page' ],
        'post_status' => 'publish',
        'showposts' => -1,
        'orderby' => 'modified',
        'order' => 'ASC',
        'meta_query' => [
            [
                'key' => 'cutp_post_excluded',
                'value' => '1',
            ]
        ]
    ]);
    

    foreach ($posts as $post) {
        $last_updated = new DateTime($post->post_modified);
        $current_date = new DateTime();
        $interval = $last_updated->diff($current_date);
        $months = $interval->m + ($interval->y * 12);
        $word_count = str_word_count(strip_tags($post->post_content));

        if ($months < 3) {
            $color = 'green';
        } elseif ($months >= 3 && $months < 6) {
            $color = 'yellow';
        } elseif ($months >= 6 && $months < 12) {
            $color = 'orange';
        } elseif ($months >= 12) {
            $color = 'red';
        } else {
            $color = '';
        }

        // Check if the post has not been updated for at least 12 months
        $post_meta = get_post_meta($post->ID, 'last_updated_date', true);
        $last_updated_date = $post_meta ? new DateTime($post_meta) : null;
        if ($color === 'red' && (!$last_updated_date || $last_updated_date < $current_date->modify('-12 months'))) {
            // Update the last_updated_date metadata
            update_post_meta($post->ID, 'last_updated_date', $current_date->format('Y-m-d H:i:s'));
        }

        echo "<tr class='" . esc_attr($color) . "'>
                <td class='title_cell'>
                " . esc_html($post->post_title) . "
                <div class='include_post action_button' data-id='".esc_html( $post->ID )."'>X</div>
                </td>
                
                <td><a class='edit_url' href='" . esc_url(admin_url( 'post.php?post='.$post->ID.'&action=edit' )) . "' target='_blank'>edit</a></td> <!-- New Visit Link -->
                <td><a class='visit_url' href='" . esc_url(get_permalink($post->ID)) . "' target='_blank'>Visit</a></td> <!-- New Visit Link -->
                <td>" . esc_html($post->post_modified) . "</td>
                <td><a href='#' class='view_note' data-id='".$post->ID."'><i class='dashicons dashicons-edit'></i></a></td>
                <td>" . get_user_by( 'ID', $post->post_author)->user_login . "</td>
                <td data-value='".esc_html($word_count)."' data-change='".strip_tags( $changed_words_count )."'>" . esc_html($word_count).( $changed_words_count ? ' ('.esc_html( $changed_words_count ).')' : '' ). "</td>
                <td><button class='updateBtn' data-post-id='" . esc_attr($post->ID) . "'>Updated</button></td> <!-- New Updated Button -->
              </tr>";
    }

    echo '</tbody></table>
    </div>
    </div><!-- /.table_container -->
    ';

    
}


function cutp_get_red_category_posts() {
    // Query posts that moved from orange to red category since the last notification
    global $wpdb;
    $last_notification_date = strtotime('-1 week'); // Adjust as needed
    $query = $wpdb->prepare("SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_type IN ('post', 'page') AND post_status = 'publish' AND post_modified > FROM_UNIXTIME(%d) AND post_modified <= NOW() AND post_modified >= DATE_SUB(NOW(), INTERVAL 12 MONTH)", $last_notification_date);
    return $wpdb->get_results($query);
}


/**
 * get chnaged words count
 * */
function cutp_get_changed_words_count( $post_id ){
    $all_items =  wp_get_post_revisions( $post_id ) ;
 
    if( is_array( $all_items ) ){
          array_shift($all_items);
         if( count($all_items) > 0 ){
             
             $last_item = reset( $all_items );
             $last_version_words_count = str_word_count(strip_tags( $last_item->post_content  ) );
    
             $current_version_words_count = str_word_count(strip_tags( get_post( $post_id )->post_content ) );
 
             $difference = $current_version_words_count - $last_version_words_count;
      
             if( $difference > 0 ){
                $difference_out = '<span class="color_green">+'.esc_html($difference).'</span>';
             }
             if( $difference < 0 ){
                $difference_out = '<span class="color_red">'.esc_html($difference).'</span>';
             }
             if( $difference == 0 ){
                $difference_out = '';
             }
             return $difference_out;
         }else{
             return false;
         }
    }else{
         return false;
    }
}


add_action('init', function(){
    //var_dump( cutp_get_changed_words_count( 1235 ) );
});