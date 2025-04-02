<?php
/**
 * Manga Dashboard Template
 * 
 * Main dashboard view showing manga list and statistics
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get user statistics
$user_id = get_current_user_id();
$manga_stats = manga_admin_get_user_stats($user_id);
?>

<div class="manga-admin-tabs">
    <div class="manga-admin-tab active" data-tab="manga-list"><?php _e('My Manga', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="recently-updated"><?php _e('Recently Updated', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="statistics"><?php _e('Statistics', 'manga-admin-panel'); ?></div>
</div>

<div class="manga-admin-content">
    <!-- Manga List Tab -->
    <div class="manga-admin-tab-pane active" id="manga-list">
        <div class="manga-search-bar">
            <input type="text" id="manga-search" class="manga-search-input" placeholder="<?php _e('Search manga...', 'manga-admin-panel'); ?>">
            <select id="manga-status-filter" class="manga-filter-select">
                <option value="all"><?php _e('All Statuses', 'manga-admin-panel'); ?></option>
                <option value="publish"><?php _e('Published', 'manga-admin-panel'); ?></option>
                <option value="draft"><?php _e('Draft', 'manga-admin-panel'); ?></option>
                <option value="scheduled"><?php _e('Scheduled', 'manga-admin-panel'); ?></option>
            </select>
        </div>
        
        <div id="manga-list-container">
            <!-- Content will be loaded via AJAX -->
            <div class="manga-loading">
                <div class="manga-spinner"></div>
                <span><?php _e('Loading manga...', 'manga-admin-panel'); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Recently Updated Tab -->
    <div class="manga-admin-tab-pane" id="recently-updated">
        <div class="manga-table-container">
            <table class="manga-table">
                <thead>
                    <tr>
                        <th><?php _e('Manga', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Latest Chapter', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Updated', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Status', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Actions', 'manga-admin-panel'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recent_manga = manga_admin_get_recent_manga();
                    if (!empty($recent_manga)) {
                        foreach ($recent_manga as $manga) {
                            $manga_url = add_query_arg(array('view' => 'edit', 'id' => $manga['id']));
                            $chapters_url = add_query_arg(array('view' => 'chapters', 'id' => $manga['id']));
                            
                            $status_class = '';
                            switch ($manga['status']) {
                                case 'publish':
                                    $status_class = 'published';
                                    break;
                                case 'draft':
                                    $status_class = 'draft';
                                    break;
                                case 'future':
                                    $status_class = 'scheduled';
                                    break;
                            }
                            ?>
                            <tr>
                                <td><strong><?php echo esc_html($manga['title']); ?></strong></td>
                                <td><?php echo esc_html($manga['latest_chapter']); ?></td>
                                <td><?php echo esc_html($manga['updated_date']); ?></td>
                                <td><span class="chapter-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($manga['status_text']); ?></span></td>
                                <td>
                                    <a href="<?php echo esc_url($manga_url); ?>" class="manga-btn manga-btn-secondary manga-btn-sm"><?php _e('Edit', 'manga-admin-panel'); ?></a>
                                    <a href="<?php echo esc_url($chapters_url); ?>" class="manga-btn manga-btn-primary manga-btn-sm"><?php _e('Chapters', 'manga-admin-panel'); ?></a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="5" style="text-align: center;"><?php _e('No recently updated manga found.', 'manga-admin-panel'); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Statistics Tab -->
    <div class="manga-admin-tab-pane" id="statistics">
        <div class="manga-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="manga-stat-card" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;"><?php _e('Total Manga', 'manga-admin-panel'); ?></h3>
                <div class="manga-stat-value" style="font-size: 36px; font-weight: bold; color: #ff6b6b;"><?php echo esc_html($manga_stats['total_manga']); ?></div>
            </div>
            
            <div class="manga-stat-card" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;"><?php _e('Total Chapters', 'manga-admin-panel'); ?></h3>
                <div class="manga-stat-value" style="font-size: 36px; font-weight: bold; color: #ff6b6b;"><?php echo esc_html($manga_stats['total_chapters']); ?></div>
            </div>
            
            <div class="manga-stat-card" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;"><?php _e('Published', 'manga-admin-panel'); ?></h3>
                <div class="manga-stat-value" style="font-size: 36px; font-weight: bold; color: #1dd1a1;"><?php echo esc_html($manga_stats['published_manga']); ?></div>
            </div>
            
            <div class="manga-stat-card" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;"><?php _e('Drafts', 'manga-admin-panel'); ?></h3>
                <div class="manga-stat-value" style="font-size: 36px; font-weight: bold; color: #a5b1c2;"><?php echo esc_html($manga_stats['draft_manga']); ?></div>
            </div>
        </div>
        
        <div class="manga-recent-activity" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3><?php _e('Recent Activity', 'manga-admin-panel'); ?></h3>
            <ul style="list-style: none; padding: 0;">
                <?php
                $recent_activity = manga_admin_get_recent_activity();
                if (!empty($recent_activity)) {
                    foreach ($recent_activity as $activity) {
                        echo '<li style="padding: 10px 0; border-bottom: 1px solid #eee;">';
                        echo '<div style="display: flex; justify-content: space-between;">';
                        echo '<span>' . esc_html($activity['message']) . '</span>';
                        echo '<span style="color: #a5b1c2;">' . esc_html($activity['date']) . '</span>';
                        echo '</div>';
                        echo '</li>';
                    }
                } else {
                    echo '<li style="padding: 10px 0;">' . __('No recent activity found.', 'manga-admin-panel') . '</li>';
                }
                ?>
            </ul>
        </div>
        
        <?php if (!empty($manga_stats['top_manga'])) : ?>
        <div class="manga-popular" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 20px;">
            <h3><?php _e('Most Popular Manga', 'manga-admin-panel'); ?></h3>
            <table class="manga-table">
                <thead>
                    <tr>
                        <th><?php _e('Manga', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Views', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Likes', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Chapters', 'manga-admin-panel'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($manga_stats['top_manga'] as $manga) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($manga['title']); ?></strong></td>
                        <td><?php echo esc_html($manga['views']); ?></td>
                        <td><?php echo esc_html($manga['likes']); ?></td>
                        <td><?php echo esc_html($manga['chapters']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Load manga list when the document is ready
        MangaAdmin.loadMangaList();
    });
</script>
