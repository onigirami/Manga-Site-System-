<?php
/**
 * Manga Scheduler Template
 * 
 * Interface for scheduling manga chapter releases
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if WP Manga Chapter Scheduler is active
if (!defined('WP_MANGA_CHAPTER_SCHEDULER_VERSION')) {
    echo '<div class="manga-alert manga-alert-danger">' . __('WP Manga Chapter Scheduler plugin is required for this feature.', 'manga-admin-panel') . '</div>';
    return;
}

// Get manga list for dropdown
$manga_list = manga_admin_get_manga_list();
?>

<div class="manga-admin-tabs">
    <div class="manga-admin-tab active" data-tab="upcoming-schedule"><?php _e('Upcoming Releases', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="new-schedule"><?php _e('Schedule New Release', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="schedule-history"><?php _e('Release History', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="schedule-settings"><?php _e('Settings', 'manga-admin-panel'); ?></div>
</div>

<div class="manga-admin-content">
    <!-- Upcoming Releases Tab -->
    <div class="manga-admin-tab-pane active" id="upcoming-schedule">
        <div class="manga-search-bar">
            <button id="load-schedule" class="manga-btn manga-btn-primary"><?php _e('Refresh Schedule', 'manga-admin-panel'); ?></button>
            <select id="schedule-manga-filter" class="manga-filter-select">
                <option value="all"><?php _e('All Manga', 'manga-admin-panel'); ?></option>
                <?php foreach ($manga_list as $manga) : ?>
                    <option value="<?php echo esc_attr($manga->ID); ?>"><?php echo esc_html($manga->post_title); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div id="schedule-list">
            <!-- Schedule will be loaded via AJAX -->
            <div class="manga-loading">
                <div class="manga-spinner"></div>
                <span><?php _e('Loading scheduled chapters...', 'manga-admin-panel'); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Schedule New Release Tab -->
    <div class="manga-admin-tab-pane" id="new-schedule">
        <form id="schedule-form" method="post">
            <?php wp_nonce_field('manga_admin_schedule_chapter', 'schedule_nonce'); ?>
            
            <div class="manga-form-group">
                <label for="schedule_manga_id" class="manga-form-label"><?php _e('Manga', 'manga-admin-panel'); ?> *</label>
                <select id="schedule_manga_id" name="manga_id" class="manga-form-control" required>
                    <option value=""><?php _e('Select Manga', 'manga-admin-panel'); ?></option>
                    <?php foreach ($manga_list as $manga) : ?>
                        <option value="<?php echo esc_attr($manga->ID); ?>"><?php echo esc_html($manga->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="manga-form-group schedule-chapter-container" style="display: none;">
                <label for="schedule_chapter_id" class="manga-form-label"><?php _e('Chapter to Schedule', 'manga-admin-panel'); ?> *</label>
                <select id="schedule_chapter_id" name="chapter_id" class="manga-form-control" required disabled>
                    <option value=""><?php _e('Select Manga First', 'manga-admin-panel'); ?></option>
                </select>
            </div>
            
            <div class="manga-form-group">
                <label for="schedule_date" class="manga-form-label"><?php _e('Publication Date & Time', 'manga-admin-panel'); ?> *</label>
                <input type="datetime-local" id="schedule_date" name="schedule_date" class="manga-form-control datetime-picker" required>
            </div>
            
            <div class="manga-form-group">
                <label for="schedule_title" class="manga-form-label"><?php _e('Custom Chapter Title (Optional)', 'manga-admin-panel'); ?></label>
                <input type="text" id="schedule_title" name="schedule_title" class="manga-form-control">
                <small><?php _e('Leave empty to use the default chapter title', 'manga-admin-panel'); ?></small>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Notification Options', 'manga-admin-panel'); ?></label>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="notify_subscribers" value="1" checked>
                        <?php _e('Notify subscribers when published', 'manga-admin-panel'); ?>
                    </label>
                </div>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="post_to_social" value="1">
                        <?php _e('Post to social media when published', 'manga-admin-panel'); ?>
                    </label>
                </div>
            </div>
            
            <div class="manga-form-group">
                <label for="schedule_note" class="manga-form-label"><?php _e('Internal Note (Optional)', 'manga-admin-panel'); ?></label>
                <textarea id="schedule_note" name="schedule_note" class="manga-form-control" rows="3"></textarea>
                <small><?php _e('This note is only visible to administrators and will not be published', 'manga-admin-panel'); ?></small>
            </div>
            
            <div class="manga-form-actions" style="margin-top: 30px;">
                <button type="submit" class="manga-btn manga-btn-primary"><?php _e('Schedule Chapter', 'manga-admin-panel'); ?></button>
            </div>
        </form>
    </div>
    
    <!-- Release History Tab -->
    <div class="manga-admin-tab-pane" id="schedule-history">
        <div class="manga-search-bar">
            <input type="text" id="history-search" class="manga-search-input" placeholder="<?php _e('Search history...', 'manga-admin-panel'); ?>">
            <select id="history-filter" class="manga-filter-select">
                <option value="all"><?php _e('All Time', 'manga-admin-panel'); ?></option>
                <option value="week"><?php _e('This Week', 'manga-admin-panel'); ?></option>
                <option value="month"><?php _e('This Month', 'manga-admin-panel'); ?></option>
                <option value="year"><?php _e('This Year', 'manga-admin-panel'); ?></option>
            </select>
        </div>
        
        <div class="manga-table-container">
            <table class="manga-table">
                <thead>
                    <tr>
                        <th><?php _e('Manga', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Chapter', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Scheduled Date', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Published Date', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Status', 'manga-admin-panel'); ?></th>
                    </tr>
                </thead>
                <tbody id="history-list">
                    <?php
                    $history = manga_admin_get_schedule_history();
                    if (!empty($history)) {
                        foreach ($history as $item) {
                            $status_class = '';
                            $status_text = '';
                            
                            switch ($item['status']) {
                                case 'published':
                                    $status_class = 'published';
                                    $status_text = __('Published', 'manga-admin-panel');
                                    break;
                                case 'cancelled':
                                    $status_class = 'draft';
                                    $status_text = __('Cancelled', 'manga-admin-panel');
                                    break;
                                case 'failed':
                                    $status_class = 'draft';
                                    $status_text = __('Failed', 'manga-admin-panel');
                                    break;
                            }
                            ?>
                            <tr>
                                <td><?php echo esc_html($item['manga_title']); ?></td>
                                <td><?php echo esc_html($item['chapter_title']); ?></td>
                                <td><?php echo esc_html($item['scheduled_date']); ?></td>
                                <td><?php echo esc_html($item['published_date']); ?></td>
                                <td><span class="chapter-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_text); ?></span></td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="5" style="text-align: center;"><?php _e('No history found.', 'manga-admin-panel'); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Schedule Settings Tab -->
    <div class="manga-admin-tab-pane" id="schedule-settings">
        <form id="schedule-settings-form" method="post">
            <?php wp_nonce_field('manga_admin_save_scheduler_settings', 'scheduler_settings_nonce'); ?>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Default Notification Settings', 'manga-admin-panel'); ?></label>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="default_notify_subscribers" value="1" checked>
                        <?php _e('Notify subscribers by default', 'manga-admin-panel'); ?>
                    </label>
                </div>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="default_post_to_social" value="1">
                        <?php _e('Post to social media by default', 'manga-admin-panel'); ?>
                    </label>
                </div>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Scheduling Time Zone', 'manga-admin-panel'); ?></label>
                <select name="schedule_timezone" class="manga-form-control">
                    <?php
                    $current_timezone = get_option('timezone_string');
                    $timezones = timezone_identifiers_list();
                    
                    foreach ($timezones as $timezone) {
                        echo '<option value="' . esc_attr($timezone) . '"' . selected($timezone, $current_timezone, false) . '>' . esc_html($timezone) . '</option>';
                    }
                    ?>
                </select>
                <small><?php _e('This setting affects all scheduled publications', 'manga-admin-panel'); ?></small>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Automatic Publishing', 'manga-admin-panel'); ?></label>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="enable_auto_publish" value="1" checked>
                        <?php _e('Enable automatic publishing of scheduled chapters', 'manga-admin-panel'); ?>
                    </label>
                </div>
                <small><?php _e('If disabled, chapters will remain as scheduled but won\'t be automatically published', 'manga-admin-panel'); ?></small>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Publication Failure Handling', 'manga-admin-panel'); ?></label>
                <select name="failure_handling" class="manga-form-control">
                    <option value="notify"><?php _e('Notify administrators only', 'manga-admin-panel'); ?></option>
                    <option value="retry"><?php _e('Retry once after 30 minutes', 'manga-admin-panel'); ?></option>
                    <option value="publish"><?php _e('Publish immediately without notification', 'manga-admin-panel'); ?></option>
                </select>
            </div>
            
            <div class="manga-form-group">
                <label for="schedule_email" class="manga-form-label"><?php _e('Notification Email', 'manga-admin-panel'); ?></label>
                <input type="email" id="schedule_email" name="schedule_email" class="manga-form-control" value="<?php echo esc_attr(get_option('admin_email')); ?>">
                <small><?php _e('Email address to receive notifications about scheduled publications', 'manga-admin-panel'); ?></small>
            </div>
            
            <div class="manga-form-actions" style="margin-top: 30px;">
                <button type="submit" class="manga-btn manga-btn-primary"><?php _e('Save Settings', 'manga-admin-panel'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Load scheduled chapters
    MangaAdmin.loadSchedule();
    
    // When manga is selected, load available chapters
    $('#schedule_manga_id').on('change', function() {
        const mangaId = $(this).val();
        
        if (!mangaId) {
            $('#schedule_chapter_id').html('<option value=""><?php _e('Select Manga First', 'manga-admin-panel'); ?></option>');
            $('#schedule_chapter_id').prop('disabled', true);
            $('.schedule-chapter-container').hide();
            return;
        }
        
        // Show loading
        $('#schedule_chapter_id').html('<option value=""><?php _e('Loading chapters...', 'manga-admin-panel'); ?></option>');
        $('#schedule_chapter_id').prop('disabled', true);
        $('.schedule-chapter-container').show();
        
        // Load chapters via AJAX
        $.ajax({
            url: mangaAdminVars.ajaxurl,
            type: 'POST',
            data: {
                action: 'manga_admin_get_chapters_for_schedule',
                manga_id: mangaId,
                nonce: mangaAdminVars.nonce
            },
            success: function(response) {
                if (response.success) {
                    let options = '<option value=""><?php _e('Select Chapter', 'manga-admin-panel'); ?></option>';
                    
                    response.data.chapters.forEach(function(chapter) {
                        options += `<option value="${chapter.id}">${chapter.name}</option>`;
                    });
                    
                    $('#schedule_chapter_id').html(options);
                    $('#schedule_chapter_id').prop('disabled', false);
                } else {
                    $('#schedule_chapter_id').html('<option value=""><?php _e('No chapters available', 'manga-admin-panel'); ?></option>');
                    $('#schedule_chapter_id').prop('disabled', true);
                }
            },
            error: function() {
                $('#schedule_chapter_id').html('<option value=""><?php _e('Error loading chapters', 'manga-admin-panel'); ?></option>');
                $('#schedule_chapter_id').prop('disabled', true);
            }
        });
    });
    
    // Filter history
    $('#history-filter').on('change', function() {
        const filter = $(this).val();
        filterHistory(filter);
    });
    
    // Search history
    $('#history-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('#history-list tr').each(function() {
            const mangaTitle = $(this).find('td:first-child').text().toLowerCase();
            const chapterTitle = $(this).find('td:nth-child(2)').text().toLowerCase();
            
            if (mangaTitle.includes(searchTerm) || chapterTitle.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Filter manga in schedule list
    $('#schedule-manga-filter').on('change', function() {
        const mangaId = $(this).val();
        
        if (mangaId === 'all') {
            $('.scheduler-item').show();
        } else {
            $('.scheduler-item').each(function() {
                if ($(this).data('manga-id') == mangaId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });
    
    // Schedule settings form
    $('#schedule-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitButton = $(this).find('button[type="submit"]');
        const originalText = submitButton.text();
        submitButton.prop('disabled', true).text(mangaAdminVars.i18n.saving);
        
        // Get form data
        const formData = $(this).serialize();
        
        // Submit form via AJAX
        $.ajax({
            url: mangaAdminVars.ajaxurl,
            type: 'POST',
            data: formData + '&action=manga_admin_save_scheduler_settings&nonce=' + mangaAdminVars.nonce,
            success: function(response) {
                if (response.success) {
                    MangaAdmin.showNotification('success', response.data.message);
                } else {
                    MangaAdmin.showNotification('error', response.data.message);
                }
            },
            error: function() {
                MangaAdmin.showNotification('error', mangaAdminVars.i18n.error);
            },
            complete: function() {
                // Restore button state
                submitButton.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Function to filter history by date range
    function filterHistory(filter) {
        // This is a simplified example
        // In a real implementation, we would fetch filtered data from server
        
        // For demonstration, show/hide rows
        if (filter === 'all') {
            $('#history-list tr').show();
        } else {
            $('#history-list tr').each(function() {
                // This is just a placeholder
                // In a real implementation, we would check actual dates
                const random = Math.random();
                
                if (filter === 'week' && random > 0.7) {
                    $(this).show();
                } else if (filter === 'month' && random > 0.4) {
                    $(this).show();
                } else if (filter === 'year' && random > 0.1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }
});
</script>
