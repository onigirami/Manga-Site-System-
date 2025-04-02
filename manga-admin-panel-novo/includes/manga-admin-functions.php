<?php
/**
 * Manga Admin Functions
 * 
 * Core functions for the manga admin panel
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get manga list that the current user can edit
 *
 * @return array Array of manga posts
 */
function manga_admin_get_manga_list() {
    $args = array(
        'post_type'      => 'wp-manga',
        'post_status'    => array('publish', 'draft', 'future'),
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    );
    
    // If user is not an admin, limit to their manga
    if (!current_user_can('administrator')) {
        $args['author'] = get_current_user_id();
    }
    
    $manga_query = new WP_Query($args);
    
    return $manga_query->posts;
}

/**
 * Get recent manga with latest chapter info
 *
 * @param int $limit Number of manga to retrieve
 * @return array Array of manga data
 */
function manga_admin_get_recent_manga($limit = 10) {
    $args = array(
        'post_type'      => 'wp-manga',
        'post_status'    => array('publish', 'draft', 'future'),
        'posts_per_page' => $limit,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    );
    
    // If user is not an admin, limit to their manga
    if (!current_user_can('administrator')) {
        $args['author'] = get_current_user_id();
    }
    
    $manga_query = new WP_Query($args);
    $manga_list = array();
    
    if ($manga_query->have_posts()) {
        while ($manga_query->have_posts()) {
            $manga_query->the_post();
            $manga_id = get_the_ID();
            
            // Get latest chapter
            $latest_chapter = array(
                'name' => __('No chapters', 'manga-admin-panel'),
                'date' => ''
            );
            
            if (function_exists('madara_get_manga_chapters')) {
                $chapters = madara_get_manga_chapters($manga_id, 1); // Get only the latest chapter
                
                if (!empty($chapters)) {
                    $chapter = reset($chapters); // Get first element
                    $latest_chapter = array(
                        'name' => $chapter['chapter_name'],
                        'date' => date_i18n(get_option('date_format'), strtotime($chapter['date']))
                    );
                }
            }
            
            // Get status text
            $status_text = '';
            switch (get_post_status()) {
                case 'publish':
                    $status_text = __('Published', 'manga-admin-panel');
                    break;
                case 'draft':
                    $status_text = __('Draft', 'manga-admin-panel');
                    break;
                case 'future':
                    $status_text = __('Scheduled', 'manga-admin-panel');
                    break;
                default:
                    $status_text = ucfirst(get_post_status());
                    break;
            }
            
            $manga_list[] = array(
                'id'             => $manga_id,
                'title'          => get_the_title(),
                'latest_chapter' => $latest_chapter['name'],
                'updated_date'   => get_the_modified_date(),
                'status'         => get_post_status(),
                'status_text'    => $status_text
            );
        }
        wp_reset_postdata();
    }
    
    return $manga_list;
}

/**
 * Get user statistics for manga
 *
 * @param int $user_id User ID
 * @return array User statistics
 */
function manga_admin_get_user_stats($user_id) {
    $args = array(
        'post_type'      => 'wp-manga',
        'post_status'    => array('publish', 'draft', 'future'),
        'posts_per_page' => -1,
        'author'         => $user_id,
    );
    
    // If user is an admin, show all manga stats
    if (current_user_can('administrator')) {
        unset($args['author']);
    }
    
    $manga_query = new WP_Query($args);
    
    $total_manga = $manga_query->found_posts;
    $published_manga = 0;
    $draft_manga = 0;
    $total_chapters = 0;
    $top_manga = array();
    
    if ($manga_query->have_posts()) {
        while ($manga_query->have_posts()) {
            $manga_query->the_post();
            $manga_id = get_the_ID();
            
            // Count by status
            switch (get_post_status()) {
                case 'publish':
                    $published_manga++;
                    break;
                case 'draft':
                    $draft_manga++;
                    break;
            }
            
            // Count chapters
            $chapters_count = 0;
            if (function_exists('madara_get_manga_chapters')) {
                $chapters = madara_get_manga_chapters($manga_id);
                $chapters_count = count($chapters);
                $total_chapters += $chapters_count;
            }
            
            // Get views
            $views = intval(get_post_meta($manga_id, '_wp_manga_views', true));
            
            // Get likes/ratings
            $likes = intval(get_post_meta($manga_id, '_wp_manga_likes', true));
            
            // Add to top manga list
            $top_manga[] = array(
                'id'       => $manga_id,
                'title'    => get_the_title(),
                'views'    => $views,
                'likes'    => $likes,
                'chapters' => $chapters_count
            );
        }
        wp_reset_postdata();
    }
    
    // Sort top manga by views
    usort($top_manga, function($a, $b) {
        return $b['views'] - $a['views'];
    });
    
    // Limit to top 5
    $top_manga = array_slice($top_manga, 0, 5);
    
    return array(
        'total_manga'     => $total_manga,
        'published_manga' => $published_manga,
        'draft_manga'     => $draft_manga,
        'total_chapters'  => $total_chapters,
        'top_manga'       => $top_manga
    );
}

/**
 * Get recent activity for the manga admin
 *
 * @param int $limit Number of activities to retrieve
 * @return array Recent activity data
 */
function manga_admin_get_recent_activity($limit = 10) {
    $activities = array();
    
    // Check if we can use WP Manga activity log
    if (function_exists('wp_manga_get_activity_log')) {
        $log_items = wp_manga_get_activity_log($limit);
        
        if (!empty($log_items)) {
            foreach ($log_items as $item) {
                $activities[] = array(
                    'message' => $item['message'],
                    'date'    => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['date']))
                );
            }
            
            return $activities;
        }
    }
    
    // Fallback to recent manga changes
    $args = array(
        'post_type'      => 'wp-manga',
        'post_status'    => array('publish', 'draft', 'future'),
        'posts_per_page' => $limit,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    );
    
    $manga_query = new WP_Query($args);
    
    if ($manga_query->have_posts()) {
        while ($manga_query->have_posts()) {
            $manga_query->the_post();
            
            $message = sprintf(
                __('Manga "%s" was updated', 'manga-admin-panel'),
                get_the_title()
            );
            
            $activities[] = array(
                'message' => $message,
                'date'    => get_the_modified_date(get_option('date_format') . ' ' . get_option('time_format'))
            );
        }
        wp_reset_postdata();
    }
    
    return $activities;
}

/**
 * Get scheduled chapters
 *
 * @return array Scheduled chapters data
 */
function manga_admin_get_schedule_history() {
    // Check if WP Manga Chapter Scheduler is active
    if (!defined('WP_MANGA_CHAPTER_SCHEDULER_VERSION')) {
        return array();
    }
    
    // This is a placeholder function - in a real implementation,
    // this would query the scheduler's database table
    $history = array();
    
    // Sample data
    $history[] = array(
        'manga_title'    => 'Sample Manga 1',
        'chapter_title'  => 'Chapter 10',
        'scheduled_date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime('-2 days')),
        'published_date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime('-2 days')),
        'status'         => 'published'
    );
    
    $history[] = array(
        'manga_title'    => 'Sample Manga 2',
        'chapter_title'  => 'Chapter 5',
        'scheduled_date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime('-1 week')),
        'published_date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime('-1 week')),
        'status'         => 'published'
    );
    
    $history[] = array(
        'manga_title'    => 'Sample Manga 3',
        'chapter_title'  => 'Chapter 8',
        'scheduled_date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime('-3 days')),
        'published_date' => '',
        'status'         => 'cancelled'
    );
    
    return $history;
}

/**
 * Get global custom fields
 *
 * @return array Custom fields data
 */
function manga_admin_get_global_custom_fields() {
    // Check if WP Manga Custom Fields is active
    if (!defined('WP_MANGA_CUSTOM_FIELDS_VERSION')) {
        return array();
    }
    
    // This is a placeholder function - in a real implementation,
    // this would get the fields from the plugin's database or settings
    $fields = array();
    
    // Sample data
    $fields[] = array(
        'id'          => 'manga_year',
        'label'       => __('Year of Release', 'manga-admin-panel'),
        'type'        => 'number',
        'default'     => date('Y'),
        'required'    => false,
        'description' => __('The year the manga was first released', 'manga-admin-panel')
    );
    
    $fields[] = array(
        'id'          => 'manga_rating',
        'label'       => __('Age Rating', 'manga-admin-panel'),
        'type'        => 'select',
        'default'     => 'all',
        'required'    => true,
        'options'     => array('all', '13+', '16+', '18+', 'mature'),
        'description' => __('Age rating for the manga content', 'manga-admin-panel')
    );
    
    $fields[] = array(
        'id'          => 'manga_publisher',
        'label'       => __('Publisher', 'manga-admin-panel'),
        'type'        => 'text',
        'default'     => '',
        'required'    => false,
        'description' => __('The publisher of the manga', 'manga-admin-panel')
    );
    
    $fields[] = array(
        'id'          => 'manga_is_translated',
        'label'       => __('Translated Work', 'manga-admin-panel'),
        'type'        => 'checkbox',
        'default'     => '1',
        'required'    => false,
        'description' => __('Is this a translated work?', 'manga-admin-panel')
    );
    
    return $fields;
}

/**
 * Get storage statistics
 *
 * @return array Storage statistics data
 */
function manga_admin_get_storage_stats() {
    // This is a placeholder function - in a real implementation,
    // this would calculate actual storage usage
    
    $stats = array(
        'total_used'     => '1.24 GB',
        'total_files'    => 3254,
        'total_chapters' => 186,
        'storage_limit'  => '5 GB',
        'usage_percent'  => 24.8
    );
    
    return $stats;
}

/**
 * Save manga post
 *
 * @param array $data Manga data to save
 * @return array|WP_Error Success/error info
 */
function manga_admin_save_manga($data) {
    // Validate required fields
    if (empty($data['manga_title'])) {
        return new WP_Error('missing_title', __('Manga title is required.', 'manga-admin-panel'));
    }
    
    // Setup post data
    $post_args = array(
        'post_title'    => sanitize_text_field($data['manga_title']),
        'post_content'  => isset($data['manga_content']) ? wp_kses_post($data['manga_content']) : '',
        'post_excerpt'  => isset($data['manga_excerpt']) ? sanitize_textarea_field($data['manga_excerpt']) : '',
        'post_status'   => isset($data['manga_post_status']) ? sanitize_text_field($data['manga_post_status']) : 'draft',
        'post_type'     => 'wp-manga',
    );
    
    // Update existing post or create new one
    if (!empty($data['manga_id'])) {
        $post_args['ID'] = intval($data['manga_id']);
        $manga_id = wp_update_post($post_args);
        $is_update = true;
    } else {
        $post_args['post_author'] = get_current_user_id();
        $manga_id = wp_insert_post($post_args);
        $is_update = false;
    }
    
    // Check for errors
    if (is_wp_error($manga_id)) {
        return $manga_id;
    }
    
    // Save manga meta data
    if (isset($data['manga_status'])) {
        update_post_meta($manga_id, '_wp_manga_status', sanitize_text_field($data['manga_status']));
    }
    
    if (isset($data['manga_alternative'])) {
        update_post_meta($manga_id, '_wp_manga_alternative', sanitize_text_field($data['manga_alternative']));
    }
    
    if (isset($data['manga_adult'])) {
        update_post_meta($manga_id, '_wp_manga_adult', 'yes');
    } else {
        update_post_meta($manga_id, '_wp_manga_adult', 'no');
    }
    
    // Handle taxonomy terms
    if (!empty($data['manga_genres'])) {
        wp_set_object_terms($manga_id, array_map('intval', $data['manga_genres']), 'wp-manga-genre');
    }
    
    if (!empty($data['manga_tags'])) {
        wp_set_object_terms($manga_id, array_map('intval', $data['manga_tags']), 'wp-manga-tag');
    }
    
    if (!empty($data['manga_authors'])) {
        wp_set_object_terms($manga_id, array_map('intval', $data['manga_authors']), 'wp-manga-author');
    }
    
    if (!empty($data['manga_artists'])) {
        wp_set_object_terms($manga_id, array_map('intval', $data['manga_artists']), 'wp-manga-artist');
    }
    
    // Handle cover image
    if (!empty($_FILES['cover_image']) && !empty($_FILES['cover_image']['tmp_name'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $attachment_id = media_handle_upload('cover_image', $manga_id);
        
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($manga_id, $attachment_id);
        }
    }
    
    // Return success data
    return array(
        'success' => true,
        'manga_id' => $manga_id,
        'is_update' => $is_update,
        'message' => $is_update 
            ? __('Manga updated successfully.', 'manga-admin-panel') 
            : __('Manga created successfully.', 'manga-admin-panel')
    );
}

/**
 * Save manga chapter
 *
 * @param array $data Chapter data to save
 * @return array|WP_Error Success/error info
 */
function manga_admin_save_chapter($data) {
    // Validate required fields
    if (empty($data['manga_id'])) {
        return new WP_Error('missing_manga', __('Manga ID is required.', 'manga-admin-panel'));
    }
    
    if (empty($data['chapter_title'])) {
        return new WP_Error('missing_title', __('Chapter title is required.', 'manga-admin-panel'));
    }
    
    if (empty($data['chapter_number'])) {
        return new WP_Error('missing_number', __('Chapter number is required.', 'manga-admin-panel'));
    }
    
    $manga_id = intval($data['manga_id']);
    $chapter_id = !empty($data['chapter_id']) ? intval($data['chapter_id']) : 0;
    $chapter_title = sanitize_text_field($data['chapter_title']);
    $chapter_number = sanitize_text_field($data['chapter_number']);
    $chapter_status = !empty($data['chapter_status']) ? sanitize_text_field($data['chapter_status']) : 'draft';
    $chapter_warning = !empty($data['chapter_warning']) ? sanitize_textarea_field($data['chapter_warning']) : '';
    
    // Check if manga exists
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        return new WP_Error('invalid_manga', __('Invalid manga ID.', 'manga-admin-panel'));
    }
    
    // Save chapter
    // This is a placeholder - in a real implementation, we'd call the WP Manga function
    // to create or update a chapter
    if (function_exists('wp_manga_create_chapter')) {
        $chapter_data = array(
            'post_id'        => $manga_id,
            'chapter_name'   => $chapter_title,
            'chapter_name_extend' => '',
            'chapter_slug'   => $chapter_number,
            'chapter_status' => $chapter_status,
            'chapter_warning' => $chapter_warning,
        );
        
        if ($chapter_id) {
            // Update existing chapter
            $result = wp_manga_update_chapter($chapter_id, $chapter_data);
            $is_update = true;
        } else {
            // Create new chapter
            $result = wp_manga_create_chapter($chapter_data);
            $chapter_id = $result;
            $is_update = false;
        }
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Handle chapter images
        if (!empty($_FILES['chapter_images'])) {
            // Process and save images
            // This would need to use the WP Manga functions to handle images correctly
        }
        
        // Return success data
        return array(
            'success' => true,
            'chapter_id' => $chapter_id,
            'is_update' => $is_update,
            'message' => $is_update 
                ? __('Chapter updated successfully.', 'manga-admin-panel') 
                : __('Chapter created successfully.', 'manga-admin-panel')
        );
    } else {
        // Fallback if WP Manga functions aren't available
        return new WP_Error('missing_dependency', __('WP Manga functions are not available.', 'manga-admin-panel'));
    }
}

/**
 * Save custom fields for manga
 *
 * @param int $manga_id Manga post ID
 * @param array $fields Custom fields data
 * @return bool Success/failure
 */
function manga_admin_save_custom_fields($manga_id, $fields) {
    // Check if WP Manga Custom Fields is active
    if (!defined('WP_MANGA_CUSTOM_FIELDS_VERSION')) {
        return false;
    }
    
    // Validate manga ID
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        return false;
    }
    
    // Save fields
    // This is a placeholder - in a real implementation, we'd use the 
    // WP Manga Custom Fields functions to save the data
    foreach ($fields as $field) {
        if (!empty($field['name']) && isset($field['value'])) {
            $meta_key = sanitize_key('_wp_manga_custom_' . $field['name']);
            update_post_meta($manga_id, $meta_key, sanitize_text_field($field['value']));
        }
    }
    
    return true;
}

/**
 * Schedule a chapter for future publication
 *
 * @param array $data Schedule data
 * @return array|WP_Error Success/error info
 */
function manga_admin_schedule_chapter($data) {
    // Check if WP Manga Chapter Scheduler is active
    if (!defined('WP_MANGA_CHAPTER_SCHEDULER_VERSION')) {
        return new WP_Error('missing_dependency', __('WP Manga Chapter Scheduler is not active.', 'manga-admin-panel'));
    }
    
    // Validate required fields
    if (empty($data['manga_id'])) {
        return new WP_Error('missing_manga', __('Manga ID is required.', 'manga-admin-panel'));
    }
    
    if (empty($data['chapter_id'])) {
        return new WP_Error('missing_chapter', __('Chapter ID is required.', 'manga-admin-panel'));
    }
    
    if (empty($data['schedule_date'])) {
        return new WP_Error('missing_date', __('Schedule date is required.', 'manga-admin-panel'));
    }
    
    $manga_id = intval($data['manga_id']);
    $chapter_id = intval($data['chapter_id']);
    $schedule_date = sanitize_text_field($data['schedule_date']);
    $custom_title = !empty($data['schedule_title']) ? sanitize_text_field($data['schedule_title']) : '';
    $notify_subscribers = !empty($data['notify_subscribers']) ? true : false;
    $post_to_social = !empty($data['post_to_social']) ? true : false;
    
    // This is a placeholder - in a real implementation, we'd use the 
    // WP Manga Chapter Scheduler functions to schedule the chapter
    if (function_exists('wp_manga_schedule_chapter')) {
        $schedule_data = array(
            'manga_id'          => $manga_id,
            'chapter_id'        => $chapter_id,
            'date'              => $schedule_date,
            'custom_title'      => $custom_title,
            'notify_subscribers' => $notify_subscribers,
            'post_to_social'    => $post_to_social,
        );
        
        $result = wp_manga_schedule_chapter($schedule_data);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return array(
            'success' => true,
            'schedule_id' => $result,
            'message' => __('Chapter scheduled successfully.', 'manga-admin-panel')
        );
    } else {
        // Fallback if function isn't available
        return new WP_Error('missing_function', __('WP Manga scheduling function is not available.', 'manga-admin-panel'));
    }
}

/**
 * Upload and process manga chapter files
 *
 * @param array $data Upload data
 * @return array|WP_Error Success/error info
 */
function manga_admin_upload_chapter_files($data) {
    // Validate required fields
    if (empty($data['manga_id'])) {
        return new WP_Error('missing_manga', __('Manga ID is required.', 'manga-admin-panel'));
    }
    
    if (empty($data['chapter_number'])) {
        return new WP_Error('missing_chapter', __('Chapter number is required.', 'manga-admin-panel'));
    }
    
    $manga_id = intval($data['manga_id']);
    $chapter_number = sanitize_text_field($data['chapter_number']);
    $chapter_title = !empty($data['chapter_title']) ? sanitize_text_field($data['chapter_title']) : sprintf(__('Chapter %s', 'manga-admin-panel'), $chapter_number);
    $optimize_images = !empty($data['optimize_images']) ? true : false;
    $publish_immediately = !empty($data['publish_immediately']) ? true : false;
    
    // Check if manga exists
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        return new WP_Error('invalid_manga', __('Invalid manga ID.', 'manga-admin-panel'));
    }
    
    // Process uploaded files
    // This is a placeholder - in a real implementation, we'd process the files
    // using WP Manga Member Upload functions
    
    // Handle direct image uploads
    if (!empty($_FILES['chapter_files'])) {
        // Process image files
    }
    
    // Handle ZIP upload
    if (!empty($_FILES['zip_file'])) {
        // Process ZIP file
    }
    
    // Create chapter if it doesn't exist
    // This would use the WP Manga functions to create or update a chapter
    
    return array(
        'success' => true,
        'message' => __('Files uploaded and processed successfully.', 'manga-admin-panel')
    );
}

/**
 * Clear files cache
 *
 * @return array Success/error info
 */
function manga_admin_clear_cache() {
    // This is a placeholder - in a real implementation, we'd clear the cache
    // of manga files, possibly using WP Manga functions if available
    
    return array(
        'success' => true,
        'message' => __('Cache cleared successfully.', 'manga-admin-panel')
    );
}

/**
 * Create a term and return its ID
 *
 * @param string $name Term name
 * @param string $taxonomy Taxonomy name
 * @return int|WP_Error Term ID or error
 */
function manga_admin_create_term($name, $taxonomy) {
    if (empty($name) || empty($taxonomy)) {
        return new WP_Error('invalid_term', __('Invalid term data.', 'manga-admin-panel'));
    }
    
    // Check if term exists
    $existing_term = term_exists($name, $taxonomy);
    if ($existing_term) {
        return $existing_term['term_id'];
    }
    
    // Create new term
    $result = wp_insert_term($name, $taxonomy);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    return $result['term_id'];
}
