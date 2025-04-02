<?php
/**
 * Manga Admin AJAX Handlers
 * 
 * AJAX handlers for manga admin panel functionality
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize AJAX handlers
 */
function manga_admin_ajax_init() {
    // Manga listing and management
    add_action('wp_ajax_manga_admin_get_manga_list', 'manga_admin_ajax_get_manga_list');
    add_action('wp_ajax_manga_admin_save_manga', 'manga_admin_ajax_save_manga');
    add_action('wp_ajax_manga_admin_delete_manga', 'manga_admin_ajax_delete_manga');
    add_action('wp_ajax_manga_admin_add_term', 'manga_admin_ajax_add_term');
    add_action('wp_ajax_manga_admin_remove_gallery_image', 'manga_admin_ajax_remove_gallery_image');
    
    // Chapter management
    add_action('wp_ajax_manga_admin_get_chapters', 'manga_admin_ajax_get_chapters');
    add_action('wp_ajax_manga_admin_get_chapter', 'manga_admin_ajax_get_chapter');
    add_action('wp_ajax_manga_admin_save_chapter', 'manga_admin_ajax_save_chapter');
    add_action('wp_ajax_manga_admin_delete_chapter', 'manga_admin_ajax_delete_chapter');
    add_action('wp_ajax_manga_admin_save_chapter_settings', 'manga_admin_ajax_save_chapter_settings');
    
    // Scheduler
    add_action('wp_ajax_manga_admin_get_schedule', 'manga_admin_ajax_get_schedule');
    add_action('wp_ajax_manga_admin_get_chapters_for_schedule', 'manga_admin_ajax_get_chapters_for_schedule');
    add_action('wp_ajax_manga_admin_schedule_chapter', 'manga_admin_ajax_schedule_chapter');
    add_action('wp_ajax_manga_admin_save_scheduler_settings', 'manga_admin_ajax_save_scheduler_settings');
    
    // Custom fields
    add_action('wp_ajax_manga_admin_get_custom_fields', 'manga_admin_ajax_get_custom_fields');
    add_action('wp_ajax_manga_admin_save_custom_fields', 'manga_admin_ajax_save_custom_fields');
    add_action('wp_ajax_manga_admin_save_global_fields', 'manga_admin_ajax_save_global_fields');
    add_action('wp_ajax_manga_admin_save_field_settings', 'manga_admin_ajax_save_field_settings');
    
    // File management
    add_action('wp_ajax_manga_admin_get_chapter_files', 'manga_admin_ajax_get_chapter_files');
    add_action('wp_ajax_manga_admin_save_file_settings', 'manga_admin_ajax_save_file_settings');
    add_action('wp_ajax_manga_admin_clear_cache', 'manga_admin_ajax_clear_cache');
    
    // Dashboard data
    add_action('wp_ajax_manga_admin_get_recent_manga', 'manga_admin_ajax_get_recent_manga');
    add_action('wp_ajax_manga_admin_get_statistics', 'manga_admin_ajax_get_statistics');
}
add_action('init', 'manga_admin_ajax_init');

/**
 * Get manga list
 */
function manga_admin_ajax_get_manga_list() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to access this feature.', 'manga-admin-panel')]);
    }
    
    // Get page number
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    if ($page < 1) {
        $page = 1;
    }
    
    // Items per page
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;
    if ($per_page < 1) {
        $per_page = 12;
    }
    
    // Query args
    $args = array(
        'post_type'      => 'wp-manga',
        'post_status'    => array('publish', 'draft', 'future'),
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    );
    
    // Filter by status if provided
    if (isset($_POST['status']) && $_POST['status'] !== 'all') {
        $args['post_status'] = sanitize_text_field($_POST['status']);
    }
    
    // Filter by search term if provided
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $args['s'] = sanitize_text_field($_POST['search']);
    }
    
    // If user is not an admin, limit to their manga
    if (!current_user_can('administrator')) {
        $args['author'] = get_current_user_id();
    }
    
    // Run the query
    $manga_query = new WP_Query($args);
    $manga_items = array();
    
    // Process results
    if ($manga_query->have_posts()) {
        while ($manga_query->have_posts()) {
            $manga_query->the_post();
            $manga_id = get_the_ID();
            
            // Get chapters count
            $chapters_count = 0;
            if (function_exists('madara_get_manga_chapters')) {
                $chapters = madara_get_manga_chapters($manga_id);
                $chapters_count = count($chapters);
            }
            
            // Get thumbnail
            $cover_url = get_the_post_thumbnail_url($manga_id, 'medium');
            if (!$cover_url) {
                $cover_url = MANGA_ADMIN_PANEL_URL . 'assets/images/no-cover.svg';
            }
            
            // Get status for display
            $status = get_post_status($manga_id);
            
            // Add to results
            $manga_items[] = array(
                'id'       => $manga_id,
                'title'    => get_the_title(),
                'cover'    => $cover_url,
                'status'   => $status,
                'chapters' => $chapters_count,
                'date'     => get_the_modified_date()
            );
        }
    }
    
    // Return data
    wp_send_json_success([
        'manga'       => $manga_items,
        'total_pages' => $manga_query->max_num_pages,
        'current_page' => $page
    ]);
}

/**
 * Save manga
 */
function manga_admin_ajax_save_manga() {
    // Check nonce
    if (!isset($_POST['manga_admin_nonce']) || !wp_verify_nonce($_POST['manga_admin_nonce'], 'manga_admin_save_manga')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to save manga.', 'manga-admin-panel')]);
    }
    
    // Call function to save manga
    $result = manga_admin_save_manga($_POST);
    
    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    } else {
        wp_send_json_success([
            'message' => $result['message'],
            'manga_id' => $result['manga_id']
        ]);
    }
}

/**
 * Delete manga
 */
function manga_admin_ajax_delete_manga() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to delete manga.', 'manga-admin-panel')]);
    }
    
    // Check if manga ID is provided
    if (!isset($_POST['manga_id']) || empty($_POST['manga_id'])) {
        wp_send_json_error(['message' => __('No manga ID provided.', 'manga-admin-panel')]);
    }
    
    $manga_id = intval($_POST['manga_id']);
    
    // Check if manga exists and is the correct post type
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        wp_send_json_error(['message' => __('Invalid manga ID.', 'manga-admin-panel')]);
    }
    
    // Check if user has permission to delete this specific manga
    if (!current_user_can('administrator') && $manga->post_author != get_current_user_id()) {
        wp_send_json_error(['message' => __('You do not have permission to delete this manga.', 'manga-admin-panel')]);
    }
    
    // Delete manga
    $result = wp_delete_post($manga_id, true);
    
    if (!$result) {
        wp_send_json_error(['message' => __('Failed to delete manga.', 'manga-admin-panel')]);
    }
    
    wp_send_json_success(['message' => __('Manga deleted successfully.', 'manga-admin-panel')]);
}

/**
 * Add a taxonomy term
 */
function manga_admin_ajax_add_term() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to add terms.', 'manga-admin-panel')]);
    }
    
    // Check required fields
    if (!isset($_POST['term_name']) || empty($_POST['term_name']) || !isset($_POST['taxonomy']) || empty($_POST['taxonomy'])) {
        wp_send_json_error(['message' => __('Term name and taxonomy are required.', 'manga-admin-panel')]);
    }
    
    $term_name = sanitize_text_field($_POST['term_name']);
    $taxonomy = sanitize_key($_POST['taxonomy']);
    
    // Validate taxonomy
    $valid_taxonomies = ['wp-manga-genre', 'wp-manga-tag', 'wp-manga-author', 'wp-manga-artist'];
    if (!in_array($taxonomy, $valid_taxonomies)) {
        wp_send_json_error(['message' => __('Invalid taxonomy.', 'manga-admin-panel')]);
    }
    
    // Create term
    $term_id = manga_admin_create_term($term_name, $taxonomy);
    
    if (is_wp_error($term_id)) {
        wp_send_json_error(['message' => $term_id->get_error_message()]);
    }
    
    wp_send_json_success([
        'term_id' => $term_id,
        'term_name' => $term_name,
        'message' => __('Term added successfully.', 'manga-admin-panel')
    ]);
}

/**
 * Remove a gallery image
 */
function manga_admin_ajax_remove_gallery_image() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to remove images.', 'manga-admin-panel')]);
    }
    
    // Check required fields
    if (!isset($_POST['image_id']) || empty($_POST['image_id']) || !isset($_POST['manga_id']) || empty($_POST['manga_id'])) {
        wp_send_json_error(['message' => __('Image ID and manga ID are required.', 'manga-admin-panel')]);
    }
    
    $image_id = intval($_POST['image_id']);
    $manga_id = intval($_POST['manga_id']);
    
    // Check if manga exists and is the correct post type
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        wp_send_json_error(['message' => __('Invalid manga ID.', 'manga-admin-panel')]);
    }
    
    // Check if user has permission to edit this manga
    if (!current_user_can('administrator') && $manga->post_author != get_current_user_id()) {
        wp_send_json_error(['message' => __('You do not have permission to edit this manga.', 'manga-admin-panel')]);
    }
    
    // Check if image exists and is attached to this manga
    $attachment = get_post($image_id);
    if (!$attachment || $attachment->post_type !== 'attachment' || $attachment->post_parent != $manga_id) {
        wp_send_json_error(['message' => __('Invalid image ID or image not attached to this manga.', 'manga-admin-panel')]);
    }
    
    // Delete the attachment
    $result = wp_delete_attachment($image_id, true);
    
    if (!$result) {
        wp_send_json_error(['message' => __('Failed to delete image.', 'manga-admin-panel')]);
    }
    
    wp_send_json_success(['message' => __('Image deleted successfully.', 'manga-admin-panel')]);
}

/**
 * Get chapters for a manga
 */
function manga_admin_ajax_get_chapters() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to access this feature.', 'manga-admin-panel')]);
    }
    
    // Check if manga ID is provided
    if (!isset($_POST['manga_id']) || empty($_POST['manga_id'])) {
        wp_send_json_error(['message' => __('No manga ID provided.', 'manga-admin-panel')]);
    }
    
    $manga_id = intval($_POST['manga_id']);
    
    // Check if manga exists and is the correct post type
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        wp_send_json_error(['message' => __('Invalid manga ID.', 'manga-admin-panel')]);
    }
    
    // Get chapters
    $chapters = array();
    if (function_exists('madara_get_manga_chapters')) {
        $raw_chapters = madara_get_manga_chapters($manga_id);
        
        foreach ($raw_chapters as $chapter) {
            $chapters[] = array(
                'id'     => $chapter['chapter_id'],
                'number' => $chapter['chapter_slug'],
                'title'  => $chapter['chapter_name'],
                'status' => get_post_status($chapter['chapter_id'])
            );
        }
    }
    
    wp_send_json_success([
        'chapters' => $chapters,
        'manga_title' => $manga->post_title
    ]);
}

/**
 * Get a single chapter
 */
function manga_admin_ajax_get_chapter() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to access this feature.', 'manga-admin-panel')]);
    }
    
    // Check required fields
    if (!isset($_POST['chapter_id']) || empty($_POST['chapter_id']) || !isset($_POST['manga_id']) || empty($_POST['manga_id'])) {
        wp_send_json_error(['message' => __('Chapter ID and manga ID are required.', 'manga-admin-panel')]);
    }
    
    $chapter_id = intval($_POST['chapter_id']);
    $manga_id = intval($_POST['manga_id']);
    
    // Get chapter data
    // This would typically come from WP Manga's functions, but we'll use a placeholder
    if (function_exists('wp_manga_get_chapter')) {
        $chapter = wp_manga_get_chapter($chapter_id, $manga_id);
        
        if (!$chapter) {
            wp_send_json_error(['message' => __('Chapter not found.', 'manga-admin-panel')]);
        }
        
        wp_send_json_success([
            'id'     => $chapter_id,
            'number' => $chapter['chapter_slug'],
            'title'  => $chapter['chapter_name'],
            'status' => get_post_status($chapter_id),
            'warning' => get_post_meta($chapter_id, '_wp_manga_chapter_warning', true)
        ]);
    } else {
        // Fallback if WP Manga functions aren't available
        // This is just a simple implementation for demo purposes
        $chapter_post = get_post($chapter_id);
        
        if (!$chapter_post) {
            wp_send_json_error(['message' => __('Chapter not found.', 'manga-admin-panel')]);
        }
        
        // Get chapter meta
        $chapter_number = get_post_meta($chapter_id, '_wp_manga_chapter_number', true);
        $chapter_warning = get_post_meta($chapter_id, '_wp_manga_chapter_warning', true);
        
        wp_send_json_success([
            'id'     => $chapter_id,
            'number' => $chapter_number ?: '0',
            'title'  => $chapter_post->post_title,
            'status' => $chapter_post->post_status,
            'warning' => $chapter_warning
        ]);
    }
}

/**
 * Save chapter
 */
function manga_admin_ajax_save_chapter() {
    // Check nonce
    if (!isset($_POST['chapter_nonce']) || !wp_verify_nonce($_POST['chapter_nonce'], 'manga_admin_save_chapter')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to save chapters.', 'manga-admin-panel')]);
    }
    
    // Call function to save chapter
    $result = manga_admin_save_chapter($_POST);
    
    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    } else {
        wp_send_json_success([
            'message' => $result['message'],
            'chapter_id' => $result['chapter_id']
        ]);
    }
}

/**
 * Delete chapter
 */
function manga_admin_ajax_delete_chapter() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to delete chapters.', 'manga-admin-panel')]);
    }
    
    // Check required fields
    if (!isset($_POST['chapter_id']) || empty($_POST['chapter_id']) || !isset($_POST['manga_id']) || empty($_POST['manga_id'])) {
        wp_send_json_error(['message' => __('Chapter ID and manga ID are required.', 'manga-admin-panel')]);
    }
    
    $chapter_id = intval($_POST['chapter_id']);
    $manga_id = intval($_POST['manga_id']);
    
    // Verify manga owner
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        wp_send_json_error(['message' => __('Invalid manga ID.', 'manga-admin-panel')]);
    }
    
    if (!current_user_can('administrator') && $manga->post_author != get_current_user_id()) {
        wp_send_json_error(['message' => __('You do not have permission to delete chapters for this manga.', 'manga-admin-panel')]);
    }
    
    // Delete chapter
    // This would typically use WP Manga's functions, but we'll use a placeholder
    if (function_exists('wp_manga_delete_chapter')) {
        $result = wp_manga_delete_chapter($chapter_id, $manga_id);
        
        if (!$result) {
            wp_send_json_error(['message' => __('Failed to delete chapter.', 'manga-admin-panel')]);
        }
        
        wp_send_json_success(['message' => __('Chapter deleted successfully.', 'manga-admin-panel')]);
    } else {
        // Fallback
        $chapter_post = get_post($chapter_id);
        
        if (!$chapter_post) {
            wp_send_json_error(['message' => __('Chapter not found.', 'manga-admin-panel')]);
        }
        
        $result = wp_delete_post($chapter_id, true);
        
        if (!$result) {
            wp_send_json_error(['message' => __('Failed to delete chapter.', 'manga-admin-panel')]);
        }
        
        wp_send_json_success(['message' => __('Chapter deleted successfully.', 'manga-admin-panel')]);
    }
}

/**
 * Save chapter settings
 */
function manga_admin_ajax_save_chapter_settings() {
    // Check nonce
    if (!isset($_POST['settings_nonce']) || !wp_verify_nonce($_POST['settings_nonce'], 'manga_admin_save_chapter_settings')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to save settings.', 'manga-admin-panel')]);
    }
    
    // Check manga ID
    if (!isset($_POST['manga_id']) || empty($_POST['manga_id'])) {
        wp_send_json_error(['message' => __('Manga ID is required.', 'manga-admin-panel')]);
    }
    
    $manga_id = intval($_POST['manga_id']);
    
    // Verify manga owner
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        wp_send_json_error(['message' => __('Invalid manga ID.', 'manga-admin-panel')]);
    }
    
    if (!current_user_can('administrator') && $manga->post_author != get_current_user_id()) {
        wp_send_json_error(['message' => __('You do not have permission to save settings for this manga.', 'manga-admin-panel')]);
    }
    
    // Process settings
    // Reading direction
    if (isset($_POST['reading_direction'])) {
        update_post_meta($manga_id, '_wp_manga_reading_direction', sanitize_text_field($_POST['reading_direction']));
    }
    
    // Chapter naming
    if (isset($_POST['chapter_naming'])) {
        update_post_meta($manga_id, '_wp_manga_chapter_naming', sanitize_text_field($_POST['chapter_naming']));
        
        if ($_POST['chapter_naming'] === 'custom' && isset($_POST['custom_naming'])) {
            update_post_meta($manga_id, '_wp_manga_custom_naming', sanitize_text_field($_POST['custom_naming']));
        }
    }
    
    // Image optimization
    if (isset($_POST['optimize_images'])) {
        update_post_meta($manga_id, '_wp_manga_optimize_images', 1);
        
        if (isset($_POST['max_width'])) {
            update_post_meta($manga_id, '_wp_manga_image_max_width', intval($_POST['max_width']));
        }
        
        if (isset($_POST['image_quality'])) {
            update_post_meta($manga_id, '_wp_manga_image_quality', intval($_POST['image_quality']));
        }
    } else {
        update_post_meta($manga_id, '_wp_manga_optimize_images', 0);
    }
    
    // Notification settings
    if (isset($_POST['notify_new_chapter'])) {
        update_post_meta($manga_id, '_wp_manga_notify_new_chapter', 1);
    } else {
        update_post_meta($manga_id, '_wp_manga_notify_new_chapter', 0);
    }
    
    if (isset($_POST['social_share'])) {
        update_post_meta($manga_id, '_wp_manga_social_share', 1);
    } else {
        update_post_meta($manga_id, '_wp_manga_social_share', 0);
    }
    
    wp_send_json_success(['message' => __('Chapter settings saved successfully.', 'manga-admin-panel')]);
}

/**
 * Get scheduled chapters
 */
function manga_admin_ajax_get_schedule() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to access this feature.', 'manga-admin-panel')]);
    }
    
    // Check if WP Manga Chapter Scheduler is active
    if (!defined('WP_MANGA_CHAPTER_SCHEDULER_VERSION')) {
        wp_send_json_error(['message' => __('WP Manga Chapter Scheduler is not active.', 'manga-admin-panel')]);
    }
    
    // Get schedules
    // This would typically use the scheduler's functions, but we'll use a placeholder
    $schedules = array();
    
    // Sample data - in a real implementation this would come from the database
    $current_time = current_time('timestamp');
    
    $schedules[] = array(
        'id' => 1,
        'manga' => 'Sample Manga 1',
        'manga_id' => 1001,
        'chapter' => 'Chapter 5',
        'chapter_id' => 2001,
        'date' => date('Y-m-d H:i:s', $current_time + 86400) // Tomorrow
    );
    
    $schedules[] = array(
        'id' => 2,
        'manga' => 'Sample Manga 2',
        'manga_id' => 1002,
        'chapter' => 'Chapter 10',
        'chapter_id' => 2002,
        'date' => date('Y-m-d H:i:s', $current_time + 172800) // Two days from now
    );
    
    $schedules[] = array(
        'id' => 3,
        'manga' => 'Sample Manga 3',
        'manga_id' => 1003,
        'chapter' => 'Chapter 15',
        'chapter_id' => 2003,
        'date' => date('Y-m-d H:i:s', $current_time + 259200) // Three days from now
    );
    
    wp_send_json_success(['schedules' => $schedules]);
}

/**
 * Get chapters available for scheduling
 */
function manga_admin_ajax_get_chapters_for_schedule() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to access this feature.', 'manga-admin-panel')]);
    }
    
    // Check manga ID
    if (!isset($_POST['manga_id']) || empty($_POST['manga_id'])) {
        wp_send_json_error(['message' => __('Manga ID is required.', 'manga-admin-panel')]);
    }
    
    $manga_id = intval($_POST['manga_id']);
    
    // Verify manga
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        wp_send_json_error(['message' => __('Invalid manga ID.', 'manga-admin-panel')]);
    }
    
    // Get chapters that are in draft status
    $chapters = array();
    if (function_exists('madara_get_manga_chapters')) {
        $raw_chapters = madara_get_manga_chapters($manga_id);
        
        foreach ($raw_chapters as $chapter) {
            $chapter_id = $chapter['chapter_id'];
            $status = get_post_status($chapter_id);
            
            // Only include draft chapters
            if ($status === 'draft') {
                $chapters[] = array(
                    'id'   => $chapter_id,
                    'name' => $chapter['chapter_slug'] . ': ' . $chapter['chapter_name']
                );
            }
        }
    }
    
    wp_send_json_success([
        'chapters' => $chapters,
        'manga_title' => $manga->post_title
    ]);
}

/**
 * Schedule a chapter
 */
function manga_admin_ajax_schedule_chapter() {
    // Check nonce
    if (!isset($_POST['schedule_nonce']) || !wp_verify_nonce($_POST['schedule_nonce'], 'manga_admin_schedule_chapter')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to schedule chapters.', 'manga-admin-panel')]);
    }
    
    // Check if WP Manga Chapter Scheduler is active
    if (!defined('WP_MANGA_CHAPTER_SCHEDULER_VERSION')) {
        wp_send_json_error(['message' => __('WP Manga Chapter Scheduler is not active.', 'manga-admin-panel')]);
    }
    
    // Process form data
    $result = manga_admin_schedule_chapter($_POST);
    
    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    } else {
        wp_send_json_success([
            'message' => __('Chapter scheduled successfully.', 'manga-admin-panel'),
            'schedule_id' => $result['schedule_id']
        ]);
    }
}

/**
 * Save scheduler settings
 */
function manga_admin_ajax_save_scheduler_settings() {
    // Check nonce
    if (!isset($_POST['scheduler_settings_nonce']) || !wp_verify_nonce($_POST['scheduler_settings_nonce'], 'manga_admin_save_scheduler_settings')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to save settings.', 'manga-admin-panel')]);
    }
    
    // Check if WP Manga Chapter Scheduler is active
    if (!defined('WP_MANGA_CHAPTER_SCHEDULER_VERSION')) {
        wp_send_json_error(['message' => __('WP Manga Chapter Scheduler is not active.', 'manga-admin-panel')]);
    }
    
    // Save settings
    // Default notification settings
    if (isset($_POST['default_notify_subscribers'])) {
        update_option('wp_manga_scheduler_default_notify', 1);
    } else {
        update_option('wp_manga_scheduler_default_notify', 0);
    }
    
    if (isset($_POST['default_post_to_social'])) {
        update_option('wp_manga_scheduler_default_social', 1);
    } else {
        update_option('wp_manga_scheduler_default_social', 0);
    }
    
    // Timezone
    if (isset($_POST['schedule_timezone']) && !empty($_POST['schedule_timezone'])) {
        update_option('wp_manga_scheduler_timezone', sanitize_text_field($_POST['schedule_timezone']));
    }
    
    // Auto publishing
    if (isset($_POST['enable_auto_publish'])) {
        update_option('wp_manga_scheduler_auto_publish', 1);
    } else {
        update_option('wp_manga_scheduler_auto_publish', 0);
    }
    
    // Failure handling
    if (isset($_POST['failure_handling']) && in_array($_POST['failure_handling'], ['notify', 'retry', 'publish'])) {
        update_option('wp_manga_scheduler_failure_handling', sanitize_text_field($_POST['failure_handling']));
    }
    
    // Notification email
    if (isset($_POST['schedule_email']) && is_email($_POST['schedule_email'])) {
        update_option('wp_manga_scheduler_notification_email', sanitize_email($_POST['schedule_email']));
    }
    
    wp_send_json_success(['message' => __('Scheduler settings saved successfully.', 'manga-admin-panel')]);
}

/**
 * Get custom fields for a manga
 */
function manga_admin_ajax_get_custom_fields() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to access this feature.', 'manga-admin-panel')]);
    }
    
    // Check if WP Manga Custom Fields is active
    if (!defined('WP_MANGA_CUSTOM_FIELDS_VERSION')) {
        wp_send_json_error(['message' => __('WP Manga Custom Fields is not active.', 'manga-admin-panel')]);
    }
    
    // Check manga ID
    if (!isset($_POST['manga_id']) || empty($_POST['manga_id'])) {
        wp_send_json_error(['message' => __('Manga ID is required.', 'manga-admin-panel')]);
    }
    
    $manga_id = intval($_POST['manga_id']);
    
    // Verify manga
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        wp_send_json_error(['message' => __('Invalid manga ID.', 'manga-admin-panel')]);
    }
    
    // Get custom fields
    $fields = array();
    
    // This would typically use the custom fields plugin's functions, but we'll use a placeholder
    // Get all post meta that starts with _wp_manga_custom_
    $meta_keys = get_post_custom_keys($manga_id);
    
    if ($meta_keys) {
        foreach ($meta_keys as $key) {
            if (strpos($key, '_wp_manga_custom_') === 0) {
                $field_name = str_replace('_wp_manga_custom_', '', $key);
                $field_value = get_post_meta($manga_id, $key, true);
                
                $fields[$field_name] = $field_value;
            }
        }
    }
    
    // If there are global fields defined but not yet set for this manga, include those too
    $global_fields = manga_admin_get_global_custom_fields();
    
    foreach ($global_fields as $field) {
        if (!isset($fields[$field['id']])) {
            $fields[$field['id']] = $field['default'];
        }
    }
    
    wp_send_json_success([
        'fields' => $fields,
        'manga_title' => $manga->post_title
    ]);
}

/**
 * Save custom fields for a manga
 */
function manga_admin_ajax_save_custom_fields() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to save custom fields.', 'manga-admin-panel')]);
    }
    
    // Check if WP Manga Custom Fields is active
    if (!defined('WP_MANGA_CUSTOM_FIELDS_VERSION')) {
        wp_send_json_error(['message' => __('WP Manga Custom Fields is not active.', 'manga-admin-panel')]);
    }
    
    // Check manga ID
    if (!isset($_POST['manga_id']) || empty($_POST['manga_id'])) {
        wp_send_json_error(['message' => __('Manga ID is required.', 'manga-admin-panel')]);
    }
    
    $manga_id = intval($_POST['manga_id']);
    
    // Verify manga owner
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        wp_send_json_error(['message' => __('Invalid manga ID.', 'manga-admin-panel')]);
    }
    
    if (!current_user_can('administrator') && $manga->post_author != get_current_user_id()) {
        wp_send_json_error(['message' => __('You do not have permission to save custom fields for this manga.', 'manga-admin-panel')]);
    }
    
    // Process fields
    $fields = array();
    
    if (isset($_POST['custom_fields']) && is_array($_POST['custom_fields'])) {
        foreach ($_POST['custom_fields'] as $field) {
            if (!empty($field['name'])) {
                $fields[] = array(
                    'name'  => sanitize_text_field($field['name']),
                    'value' => isset($field['value']) ? sanitize_text_field($field['value']) : ''
                );
            }
        }
    }
    
    // Save fields
    $result = manga_admin_save_custom_fields($manga_id, $fields);
    
    if (!$result) {
        wp_send_json_error(['message' => __('Failed to save custom fields.', 'manga-admin-panel')]);
    }
    
    wp_send_json_success(['message' => __('Custom fields saved successfully.', 'manga-admin-panel')]);
}

/**
 * Save global custom fields
 */
function manga_admin_ajax_save_global_fields() {
    // Check nonce
    if (!isset($_POST['global_fields_nonce']) || !wp_verify_nonce($_POST['global_fields_nonce'], 'manga_admin_save_global_fields')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to save global fields.', 'manga-admin-panel')]);
    }
    
    // Check if WP Manga Custom Fields is active
    if (!defined('WP_MANGA_CUSTOM_FIELDS_VERSION')) {
        wp_send_json_error(['message' => __('WP Manga Custom Fields is not active.', 'manga-admin-panel')]);
    }
    
    // Process fields
    $fields = array();
    
    if (isset($_POST['global_fields']) && is_array($_POST['global_fields'])) {
        foreach ($_POST['global_fields'] as $field) {
            if (!empty($field['id']) && !empty($field['label']) && !empty($field['type'])) {
                $field_data = array(
                    'id'          => sanitize_key($field['id']),
                    'label'       => sanitize_text_field($field['label']),
                    'type'        => sanitize_key($field['type']),
                    'default'     => isset($field['default']) ? sanitize_text_field($field['default']) : '',
                    'required'    => isset($field['required']) ? 1 : 0,
                    'description' => isset($field['description']) ? sanitize_text_field($field['description']) : ''
                );
                
                // Process options for select and radio types
                if (($field['type'] === 'select' || $field['type'] === 'radio') && isset($field['options'])) {
                    $options = explode("\n", $field['options']);
                    $field_data['options'] = array_map('trim', $options);
                }
                
                $fields[] = $field_data;
            }
        }
    }
    
    // Save fields to database
    update_option('wp_manga_custom_fields_global', $fields);
    
    wp_send_json_success([
        'message' => __('Global fields saved successfully.', 'manga-admin-panel'),
        'fields' => $fields
    ]);
}

/**
 * Save custom fields settings
 */
function manga_admin_ajax_save_field_settings() {
    // Check nonce
    if (!isset($_POST['field_settings_nonce']) || !wp_verify_nonce($_POST['field_settings_nonce'], 'manga_admin_save_field_settings')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to save settings.', 'manga-admin-panel')]);
    }
    
    // Check if WP Manga Custom Fields is active
    if (!defined('WP_MANGA_CUSTOM_FIELDS_VERSION')) {
        wp_send_json_error(['message' => __('WP Manga Custom Fields is not active.', 'manga-admin-panel')]);
    }
    
    // Save settings
    // Display locations
    $display_locations = isset($_POST['display_locations']) && is_array($_POST['display_locations']) 
        ? array_map('sanitize_key', $_POST['display_locations']) 
        : array('manga_single');
    
    update_option('wp_manga_custom_fields_locations', $display_locations);
    
    // Display style
    if (isset($_POST['fields_display_style']) && in_array($_POST['fields_display_style'], ['table', 'list', 'grid', 'inline'])) {
        update_option('wp_manga_custom_fields_style', sanitize_key($_POST['fields_display_style']));
    }
    
    // Section title
    if (isset($_POST['fields_section_title'])) {
        update_option('wp_manga_custom_fields_title', sanitize_text_field($_POST['fields_section_title']));
    }
    
    // Search integration
    if (isset($_POST['search_integration'])) {
        update_option('wp_manga_custom_fields_search', 1);
    } else {
        update_option('wp_manga_custom_fields_search', 0);
    }
    
    // Filter integration
    if (isset($_POST['filter_integration'])) {
        update_option('wp_manga_custom_fields_filter', 1);
        
        // Filterable fields
        if (isset($_POST['filterable_fields']) && is_array($_POST['filterable_fields'])) {
            update_option('wp_manga_custom_fields_filterable', array_map('sanitize_key', $_POST['filterable_fields']));
        }
    } else {
        update_option('wp_manga_custom_fields_filter', 0);
    }
    
    wp_send_json_success(['message' => __('Field settings saved successfully.', 'manga-admin-panel')]);
}

/**
 * Get chapter files
 */
function manga_admin_ajax_get_chapter_files() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to access this feature.', 'manga-admin-panel')]);
    }
    
    // Check required fields
    if (!isset($_POST['manga_id']) || empty($_POST['manga_id']) || !isset($_POST['chapter_id']) || empty($_POST['chapter_id'])) {
        wp_send_json_error(['message' => __('Manga ID and chapter ID are required.', 'manga-admin-panel')]);
    }
    
    $manga_id = intval($_POST['manga_id']);
    $chapter_id = intval($_POST['chapter_id']);
    
    // Verify manga and chapter
    $manga = get_post($manga_id);
    $chapter = get_post($chapter_id);
    
    if (!$manga || $manga->post_type !== 'wp-manga') {
        wp_send_json_error(['message' => __('Invalid manga ID.', 'manga-admin-panel')]);
    }
    
    if (!$chapter) {
        wp_send_json_error(['message' => __('Invalid chapter ID.', 'manga-admin-panel')]);
    }
    
    // Get files
    // This would typically use WP Manga's functions, but we'll use a placeholder
    $files = array();
    
    if (function_exists('wp_manga_get_chapter_images')) {
        $chapter_images = wp_manga_get_chapter_images($chapter_id);
        
        if (!empty($chapter_images)) {
            foreach ($chapter_images as $index => $image) {
                $files[] = array(
                    'id'   => $image['id'],
                    'name' => basename($image['src']),
                    'url'  => $image['src'],
                    'size' => $image['size']
                );
            }
        }
    } else {
        // Fallback - generate sample data
        for ($i = 1; $i <= 10; $i++) {
            $files[] = array(
                'id'   => $i,
                'name' => 'page-' . sprintf('%02d', $i) . '.jpg',
                'url'  => 'https://via.placeholder.com/800x1200.jpg?text=Page+' . $i,
                'size' => '125KB'
            );
        }
    }
    
    wp_send_json_success([
        'files' => $files,
        'manga_title' => $manga->post_title,
        'chapter_title' => $chapter->post_title
    ]);
}

/**
 * Save file settings
 */
function manga_admin_ajax_save_file_settings() {
    // Check nonce
    if (!isset($_POST['file_settings_nonce']) || !wp_verify_nonce($_POST['file_settings_nonce'], 'manga_admin_save_file_settings')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to save settings.', 'manga-admin-panel')]);
    }
    
    // Save settings
    // Image optimization
    if (isset($_POST['enable_optimization'])) {
        update_option('wp_manga_image_optimization', 1);
        
        // Max width
        if (isset($_POST['max_width']) && intval($_POST['max_width']) > 0) {
            update_option('wp_manga_image_max_width', intval($_POST['max_width']));
        }
        
        // Image quality
        if (isset($_POST['image_quality']) && intval($_POST['image_quality']) >= 1 && intval($_POST['image_quality']) <= 100) {
            update_option('wp_manga_image_quality', intval($_POST['image_quality']));
        }
        
        // Convert format
        if (isset($_POST['convert_format']) && in_array($_POST['convert_format'], ['no_convert', 'jpeg', 'webp'])) {
            update_option('wp_manga_image_convert', sanitize_key($_POST['convert_format']));
        }
    } else {
        update_option('wp_manga_image_optimization', 0);
    }
    
    // Storage type
    if (isset($_POST['storage_type'])) {
        update_option('wp_manga_storage_type', sanitize_key($_POST['storage_type']));
    }
    
    // File structure
    if (isset($_POST['file_structure']) && in_array($_POST['file_structure'], ['manga/chapter', 'manga_id/chapter_id', 'year/month/manga'])) {
        update_option('wp_manga_file_structure', sanitize_key($_POST['file_structure']));
    }
    
    // Cache settings
    if (isset($_POST['enable_cache'])) {
        update_option('wp_manga_enable_cache', 1);
        
        // Cache duration
        if (isset($_POST['cache_duration']) && intval($_POST['cache_duration']) > 0) {
            update_option('wp_manga_cache_duration', intval($_POST['cache_duration']));
        }
    } else {
        update_option('wp_manga_enable_cache', 0);
    }
    
    wp_send_json_success(['message' => __('File settings saved successfully.', 'manga-admin-panel')]);
}

/**
 * Clear cache
 */
function manga_admin_ajax_clear_cache() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to clear cache.', 'manga-admin-panel')]);
    }
    
    // Clear cache
    $result = manga_admin_clear_cache();
    
    if (!$result['success']) {
        wp_send_json_error(['message' => __('Failed to clear cache.', 'manga-admin-panel')]);
    }
    
    wp_send_json_success(['message' => __('Cache cleared successfully.', 'manga-admin-panel')]);
}

/**
 * Get recent manga for dashboard
 */
function manga_admin_ajax_get_recent_manga() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to access this feature.', 'manga-admin-panel')]);
    }
    
    // Get recent manga
    $recent_manga = manga_admin_get_recent_manga(10);
    
    wp_send_json_success(['recent_manga' => $recent_manga]);
}

/**
 * Get statistics for dashboard
 */
function manga_admin_ajax_get_statistics() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Security check failed.', 'manga-admin-panel')]);
    }
    
    // Check permissions
    if (!manga_admin_panel_has_access()) {
        wp_send_json_error(['message' => __('You do not have permission to access this feature.', 'manga-admin-panel')]);
    }
    
    // Get user ID
    $user_id = get_current_user_id();
    
    // Get statistics
    $stats = manga_admin_get_user_stats($user_id);
    $recent_activity = manga_admin_get_recent_activity(10);
    
    wp_send_json_success([
        'stats' => $stats,
        'activity' => $recent_activity
    ]);
}
