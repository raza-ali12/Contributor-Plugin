<?php
/*
Plugin Name: Multi Author Plugin
Description: Display multiple authors on a post.
Version: 1.0
Author: Raza Ali
*/

// Add metabox for contributors on the post editor page
function multi_author_add_metabox() {
    add_meta_box(
        'multi_author_contributors',
        'Contributors',
        'multi_author_contributors_metabox',
        'post',
        'side',
        'default'
    );
}
add_action('admin_enqueue_scripts', 'multi_author_add_metabox');



function multi_author_contributors_metabox($post) {
    // Get saved contributors for the post
    $contributors = get_post_meta($post->ID, '_contributors', true);

    // Get all users
    $users = get_users();

    // Display checkbox for each user
    foreach ($users as $user) {
        $checked = in_array($user->ID, (array) $contributors) ? 'checked' : '';
        echo '<label><input type="checkbox" name="contributors[]" value="' . esc_attr($user->ID) . '" ' . esc_attr($checked) . ' />' . esc_html($user->display_name) . '</label><br>';
    }
}

// Save contributors when the post is saved
function multi_author_save_contributors($post_id) {
    if (isset($_POST['contributors'])) {
        $contributors = array_map('intval', $_POST['contributors']);
        update_post_meta($post_id, '_contributors', $contributors);
    } else {
        delete_post_meta($post_id, '_contributors');
    }
}

add_action('add_meta_boxes', 'multi_author_add_metabox');
add_action('save_post', 'multi_author_save_contributors');

// Display contributors at the end of the post
function multi_author_display_contributors($content) {
    if (is_single()) {
        $contributors = get_post_meta(get_the_ID(), '_contributors', true);

        if ($contributors) {
            $output = '<div class="contributors-box"><h3>Contributors:</h3><ul class="contributors-list">';
            foreach ($contributors as $contributor_id) {
                $user_info = get_userdata($contributor_id);
                $output .= '<li><a class="contributor-link" href="' . esc_url(get_author_posts_url($contributor_id)) . '">' . get_avatar($contributor_id, 32) . esc_html($user_info->display_name) . '</a></li>';
            }
            $output .= '</ul></div>';
            $content .= $output;
        }
    }
    return $content;
}
add_filter('the_content', 'multi_author_display_contributors');
