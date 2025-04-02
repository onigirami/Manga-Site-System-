<?php
/**
 * Template Name: Manga Admin Dashboard
 * 
 * Main dashboard for the manga admin panel
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check user access
if (!function_exists('manga_admin_panel_has_access') || !manga_admin_panel_has_access()) {
    wp_redirect(home_url());
    exit;
}

get_header();

// Check if specific view is requested
$view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'dashboard';
$manga_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>

<div class="manga-admin-container">
    
    <div class="manga-admin-header">
        <h1 class="manga-admin-title"><?php _e('Manga Admin Panel', 'manga-admin-panel'); ?></h1>
        <div class="manga-admin-actions">
            <a href="<?php echo esc_url(add_query_arg('view', 'create', remove_query_arg('id'))); ?>" class="manga-btn manga-btn-primary">
                <?php _e('Add New Manga', 'manga-admin-panel'); ?>
            </a>
        </div>
    </div>
    
    <?php
    // Load specific view based on request
    switch ($view) {
        case 'create':
            include MANGA_ADMIN_PANEL_PATH . 'templates/manga-create-edit.php';
            break;
            
        case 'edit':
            if ($manga_id > 0) {
                include MANGA_ADMIN_PANEL_PATH . 'templates/manga-create-edit.php';
            } else {
                echo '<div class="manga-alert manga-alert-danger">' . __('Invalid manga ID.', 'manga-admin-panel') . '</div>';
                include MANGA_ADMIN_PANEL_PATH . 'templates/manga-dashboard.php';
            }
            break;
            
        case 'chapters':
            if ($manga_id > 0) {
                include MANGA_ADMIN_PANEL_PATH . 'templates/manga-chapter-manager.php';
            } else {
                echo '<div class="manga-alert manga-alert-danger">' . __('Invalid manga ID.', 'manga-admin-panel') . '</div>';
                include MANGA_ADMIN_PANEL_PATH . 'templates/manga-dashboard.php';
            }
            break;
            
        case 'scheduler':
            include MANGA_ADMIN_PANEL_PATH . 'templates/manga-scheduler.php';
            break;
            
        case 'custom-fields':
            include MANGA_ADMIN_PANEL_PATH . 'templates/manga-custom-fields.php';
            break;
            
        case 'file-manager':
            include MANGA_ADMIN_PANEL_PATH . 'templates/manga-file-manager.php';
            break;
            
        default:
            include MANGA_ADMIN_PANEL_PATH . 'templates/manga-dashboard.php';
            break;
    }
    ?>
    
</div>

<?php get_footer(); ?>
