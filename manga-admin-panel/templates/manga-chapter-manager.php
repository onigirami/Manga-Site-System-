<?php
/**
 * Manga Chapter Manager Template
 * 
 * Interface for managing manga chapters
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get manga ID from query parameter
$manga_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get manga data
$manga = get_post($manga_id);

if (!$manga || $manga->post_type !== 'wp-manga') {
    echo '<div class="manga-alert manga-alert-danger">' . __('Manga not found.', 'manga-admin-panel') . '</div>';
    return;
}

// Get chapters
$chapters = array();
if (function_exists('madara_get_manga_chapters')) {
    $chapters = madara_get_manga_chapters($manga_id);
}
?>

<div class="manga-admin-header">
    <h2><?php echo sprintf(__('Managing Chapters: %s', 'manga-admin-panel'), esc_html($manga->post_title)); ?></h2>
    <div class="manga-admin-actions">
        <a href="<?php echo esc_url(remove_query_arg(array('view', 'id'))); ?>" class="manga-btn manga-btn-secondary"><?php _e('Back to Dashboard', 'manga-admin-panel'); ?></a>
        <button id="new-chapter" class="manga-btn manga-btn-primary"><?php _e('Add New Chapter', 'manga-admin-panel'); ?></button>
    </div>
</div>

<div class="manga-admin-tabs">
    <div class="manga-admin-tab active" data-tab="chapter-list"><?php _e('Chapters', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="bulk-upload"><?php _e('Bulk Upload', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="chapter-settings"><?php _e('Settings', 'manga-admin-panel'); ?></div>
</div>

<div class="manga-admin-content">
    <!-- Chapters List Tab -->
    <div class="manga-admin-tab-pane active" id="chapter-list">
        <div class="manga-search-bar">
            <input type="text" id="chapter-search" class="manga-search-input" placeholder="<?php _e('Search chapters...', 'manga-admin-panel'); ?>">
            <select id="chapter-status-filter" class="manga-filter-select">
                <option value="all"><?php _e('All Statuses', 'manga-admin-panel'); ?></option>
                <option value="publish"><?php _e('Published', 'manga-admin-panel'); ?></option>
                <option value="draft"><?php _e('Draft', 'manga-admin-panel'); ?></option>
                <option value="future"><?php _e('Scheduled', 'manga-admin-panel'); ?></option>
            </select>
        </div>
        
        <div id="chapter-form-container" style="display: none; margin-bottom: 30px; background-color: #f9f9f9; padding: 20px; border-radius: 5px;">
            <h3 id="chapter-form-title"><?php _e('New Chapter', 'manga-admin-panel'); ?></h3>
            
            <form id="chapter-form" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('manga_admin_save_chapter', 'chapter_nonce'); ?>
                <input type="hidden" id="chapter_id" name="chapter_id" value="">
                <input type="hidden" name="manga_id" value="<?php echo esc_attr($manga_id); ?>">
                
                <div class="manga-form-row" style="display: flex; gap: 20px;">
                    <div class="manga-form-group" style="flex: 1;">
                        <label for="chapter_title" class="manga-form-label"><?php _e('Chapter Title', 'manga-admin-panel'); ?> *</label>
                        <input type="text" id="chapter_title" name="chapter_title" class="manga-form-control" required>
                    </div>
                    
                    <div class="manga-form-group" style="width: 150px;">
                        <label for="chapter_number" class="manga-form-label"><?php _e('Chapter Number', 'manga-admin-panel'); ?> *</label>
                        <input type="text" id="chapter_number" name="chapter_number" class="manga-form-control" required>
                    </div>
                </div>
                
                <div class="manga-form-row" style="display: flex; gap: 20px;">
                    <div class="manga-form-group" style="flex: 1;">
                        <label for="chapter_status" class="manga-form-label"><?php _e('Status', 'manga-admin-panel'); ?></label>
                        <select id="chapter_status" name="chapter_status" class="manga-form-control">
                            <option value="publish"><?php _e('Published', 'manga-admin-panel'); ?></option>
                            <option value="draft"><?php _e('Draft', 'manga-admin-panel'); ?></option>
                        </select>
                    </div>
                    
                    <div class="manga-form-group" style="flex: 1;">
                        <label for="chapter_date" class="manga-form-label"><?php _e('Publication Date', 'manga-admin-panel'); ?></label>
                        <input type="datetime-local" id="chapter_date" name="chapter_date" class="manga-form-control">
                    </div>
                </div>
                
                <div class="manga-form-group">
                    <label class="manga-form-label"><?php _e('Chapter Images', 'manga-admin-panel'); ?></label>
                    <div class="manga-file-upload">
                        <input type="file" id="chapter-images-upload" name="chapter_images[]" accept="image/*" multiple style="display: none;">
                        <div class="manga-file-upload-icon">
                            <i class="feather-image"></i>
                        </div>
                        <p><?php _e('Click to upload multiple images or drag & drop', 'manga-admin-panel'); ?></p>
                        <p><small><?php _e('Images will be ordered alphabetically by filename. Use numbering in filenames for proper sequence.', 'manga-admin-panel'); ?></small></p>
                    </div>
                    
                    <div id="chapter-upload-list" class="manga-upload-list"></div>
                </div>
                
                <div class="manga-form-group">
                    <label for="chapter_warning" class="manga-form-label"><?php _e('Chapter Warning/Notice', 'manga-admin-panel'); ?></label>
                    <textarea id="chapter_warning" name="chapter_warning" class="manga-form-control" rows="2"></textarea>
                    <small><?php _e('Optional warning or notice that appears before the chapter content', 'manga-admin-panel'); ?></small>
                </div>
                
                <div class="manga-form-actions" style="margin-top: 20px;">
                    <button type="button" id="cancel-chapter" class="manga-btn manga-btn-secondary"><?php _e('Cancel', 'manga-admin-panel'); ?></button>
                    <button type="submit" class="manga-btn manga-btn-primary"><?php _e('Save Chapter', 'manga-admin-panel'); ?></button>
                </div>
            </form>
        </div>
        
        <div class="chapter-list" id="chapter-list">
            <?php if (!empty($chapters)): ?>
                <?php foreach ($chapters as $chapter): 
                    $chapter_id = $chapter['chapter_id'];
                    $chapter_slug = $chapter['chapter_slug'];
                    $chapter_name = $chapter['chapter_name'];
                    $chapter_status = get_post_status($chapter_id);
                    
                    $status_class = '';
                    $status_text = '';
                    
                    if ($chapter_status === 'publish') {
                        $status_class = 'published';
                        $status_text = __('Published', 'manga-admin-panel');
                    } elseif ($chapter_status === 'draft') {
                        $status_class = 'draft';
                        $status_text = __('Draft', 'manga-admin-panel');
                    } elseif ($chapter_status === 'future') {
                        $status_class = 'scheduled';
                        $status_text = __('Scheduled', 'manga-admin-panel');
                    }
                ?>
                    <div id="chapter-item-<?php echo esc_attr($chapter_id); ?>" class="chapter-item">
                        <div class="chapter-number"><?php echo esc_html($chapter_slug); ?></div>
                        <div class="chapter-title"><?php echo esc_html($chapter_name); ?></div>
                        <div class="chapter-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_text); ?></div>
                        <div class="chapter-actions">
                            <button class="manga-btn manga-btn-secondary manga-btn-sm edit-chapter" data-id="<?php echo esc_attr($chapter_id); ?>" data-manga="<?php echo esc_attr($manga_id); ?>"><?php _e('Edit', 'manga-admin-panel'); ?></button>
                            <button class="manga-btn manga-btn-danger manga-btn-sm delete-chapter" data-id="<?php echo esc_attr($chapter_id); ?>" data-manga="<?php echo esc_attr($manga_id); ?>"><?php _e('Delete', 'manga-admin-panel'); ?></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="manga-empty-state">
                    <div class="manga-empty-icon">
                        <i class="feather-book-open"></i>
                    </div>
                    <p class="manga-empty-text"><?php _e('No chapters found', 'manga-admin-panel'); ?></p>
                    <button id="empty-new-chapter" class="manga-btn manga-btn-primary"><?php _e('Add First Chapter', 'manga-admin-panel'); ?></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bulk Upload Tab -->
    <div class="manga-admin-tab-pane" id="bulk-upload">
        <div class="manga-alert manga-alert-info">
            <?php _e('Bulk upload allows you to upload multiple chapters at once. Each folder represents a single chapter.', 'manga-admin-panel'); ?>
        </div>
        
        <div class="manga-form-group">
            <label class="manga-form-label"><?php _e('Upload Chapters', 'manga-admin-panel'); ?></label>
            <div class="manga-file-upload">
                <input type="file" id="bulk-chapters-upload" name="bulk_chapters[]" webkitdirectory directory multiple style="display: none;">
                <div class="manga-file-upload-icon">
                    <i class="feather-folder"></i>
                </div>
                <p><?php _e('Click to upload chapter folders or drag & drop', 'manga-admin-panel'); ?></p>
                <p><small><?php _e('Each folder should contain all images for a single chapter.', 'manga-admin-panel'); ?></small></p>
            </div>
            
            <div class="manga-form-group" style="margin-top: 20px;">
                <label class="manga-form-label"><?php _e('Chapter Naming Format', 'manga-admin-panel'); ?></label>
                <select id="chapter-naming-format" class="manga-form-control">
                    <option value="folder_name"><?php _e('Use folder name as chapter name', 'manga-admin-panel'); ?></option>
                    <option value="chapter_number"><?php _e('Use "Chapter X" format (where X is folder name)', 'manga-admin-panel'); ?></option>
                    <option value="custom"><?php _e('Use custom format', 'manga-admin-panel'); ?></option>
                </select>
                
                <div id="custom-format-container" style="margin-top: 10px; display: none;">
                    <input type="text" id="custom-format" class="manga-form-control" placeholder="<?php _e('e.g., "Chapter {number}: {name}"', 'manga-admin-panel'); ?>">
                    <small><?php _e('Use {number} for chapter number and {name} for folder name', 'manga-admin-panel'); ?></small>
                </div>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Default Chapter Status', 'manga-admin-panel'); ?></label>
                <select id="bulk-chapter-status" class="manga-form-control">
                    <option value="publish"><?php _e('Published', 'manga-admin-panel'); ?></option>
                    <option value="draft"><?php _e('Draft', 'manga-admin-panel'); ?></option>
                </select>
            </div>
            
            <div id="bulk-upload-list" class="manga-upload-list" style="margin-top: 20px;"></div>
            
            <div class="manga-form-actions" style="margin-top: 20px;">
                <button id="start-bulk-upload" class="manga-btn manga-btn-primary" disabled><?php _e('Start Upload', 'manga-admin-panel'); ?></button>
            </div>
        </div>
    </div>
    
    <!-- Chapter Settings Tab -->
    <div class="manga-admin-tab-pane" id="chapter-settings">
        <form id="chapter-settings-form">
            <?php wp_nonce_field('manga_admin_save_chapter_settings', 'settings_nonce'); ?>
            <input type="hidden" name="manga_id" value="<?php echo esc_attr($manga_id); ?>">
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Reading Direction', 'manga-admin-panel'); ?></label>
                <select name="reading_direction" class="manga-form-control">
                    <option value="default"><?php _e('Use Site Default', 'manga-admin-panel'); ?></option>
                    <option value="ltr"><?php _e('Left to Right', 'manga-admin-panel'); ?></option>
                    <option value="rtl"><?php _e('Right to Left', 'manga-admin-panel'); ?></option>
                </select>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Chapter Naming Format', 'manga-admin-panel'); ?></label>
                <select name="chapter_naming" class="manga-form-control">
                    <option value="number"><?php _e('Number Only (e.g., "1", "2")', 'manga-admin-panel'); ?></option>
                    <option value="chapter_number"><?php _e('Chapter Number (e.g., "Chapter 1", "Chapter 2")', 'manga-admin-panel'); ?></option>
                    <option value="custom"><?php _e('Custom', 'manga-admin-panel'); ?></option>
                </select>
                
                <div class="custom-naming-field" style="margin-top: 10px; display: none;">
                    <input type="text" name="custom_naming" class="manga-form-control" placeholder="<?php _e('e.g., "Episode {number}"', 'manga-admin-panel'); ?>">
                    <small><?php _e('Use {number} as placeholder for chapter number', 'manga-admin-panel'); ?></small>
                </div>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Image Optimization', 'manga-admin-panel'); ?></label>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="optimize_images" value="1">
                        <?php _e('Automatically optimize uploaded images', 'manga-admin-panel'); ?>
                    </label>
                </div>
                
                <div class="optimization-settings" style="margin-top: 10px; padding-left: 20px;">
                    <div class="manga-form-row" style="display: flex; gap: 20px;">
                        <div class="manga-form-group" style="flex: 1;">
                            <label class="manga-form-label"><?php _e('Max Width', 'manga-admin-panel'); ?></label>
                            <input type="number" name="max_width" class="manga-form-control" value="1200">
                        </div>
                        
                        <div class="manga-form-group" style="flex: 1;">
                            <label class="manga-form-label"><?php _e('Quality', 'manga-admin-panel'); ?></label>
                            <input type="number" name="image_quality" class="manga-form-control" min="1" max="100" value="90">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Notification Settings', 'manga-admin-panel'); ?></label>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="notify_new_chapter" value="1">
                        <?php _e('Notify subscribers when a new chapter is published', 'manga-admin-panel'); ?>
                    </label>
                </div>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="social_share" value="1">
                        <?php _e('Auto share new chapters on social media', 'manga-admin-panel'); ?>
                    </label>
                </div>
            </div>
            
            <div class="manga-form-actions" style="margin-top: 20px;">
                <button type="submit" class="manga-btn manga-btn-primary"><?php _e('Save Settings', 'manga-admin-panel'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle custom naming format
    $('select[name="chapter_naming"]').on('change', function() {
        if ($(this).val() === 'custom') {
            $('.custom-naming-field').show();
        } else {
            $('.custom-naming-field').hide();
        }
    });
    
    // Toggle custom format for bulk upload
    $('#chapter-naming-format').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#custom-format-container').show();
        } else {
            $('#custom-format-container').hide();
        }
    });
    
    // Empty state new chapter button
    $('#empty-new-chapter').on('click', function() {
        $('#new-chapter').click();
    });
    
    // Bulk upload folder selection
    $('#bulk-chapters-upload').on('change', function() {
        const files = this.files;
        const uploadList = $('#bulk-upload-list');
        uploadList.empty();
        
        // Group files by directory
        const directories = {};
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const path = file.webkitRelativePath;
            const dirName = path.split('/')[0];
            
            if (!directories[dirName]) {
                directories[dirName] = {
                    name: dirName,
                    files: []
                };
            }
            
            directories[dirName].files.push(file);
        }
        
        // Display directories
        for (const dir in directories) {
            const item = $(`
                <div class="manga-upload-item">
                    <span><strong>${directories[dir].name}</strong> (${directories[dir].files.length} images)</span>
                </div>
            `);
            
            uploadList.append(item);
        }
        
        // Enable upload button if directories found
        if (Object.keys(directories).length > 0) {
            $('#start-bulk-upload').prop('disabled', false);
        } else {
            $('#start-bulk-upload').prop('disabled', true);
        }
    });
    
    // Chapter settings form
    $('#chapter-settings-form').on('submit', function(e) {
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
            data: formData + '&action=manga_admin_save_chapter_settings&nonce=' + mangaAdminVars.nonce,
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
    
    // Start bulk upload
    $('#start-bulk-upload').on('click', function() {
        // This would normally handle the bulk upload process
        // For demonstration, show a notification
        MangaAdmin.showNotification('success', '<?php _e('Bulk upload initiated. Please wait while your chapters are processed.', 'manga-admin-panel'); ?>');
    });
    
    // Chapter search functionality
    $('#chapter-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.chapter-item').each(function() {
            const title = $(this).find('.chapter-title').text().toLowerCase();
            const number = $(this).find('.chapter-number').text().toLowerCase();
            
            if (title.includes(searchTerm) || number.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Chapter status filter
    $('#chapter-status-filter').on('change', function() {
        const status = $(this).val();
        
        if (status === 'all') {
            $('.chapter-item').show();
        } else {
            $('.chapter-item').each(function() {
                const itemStatus = $(this).find('.chapter-status').attr('class').includes(status);
                
                if (itemStatus) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    });
});
</script>
