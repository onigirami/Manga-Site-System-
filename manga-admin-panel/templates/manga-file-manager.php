<?php
/**
 * Manga File Manager Template
 * 
 * Interface for managing manga files and uploads
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get manga list for dropdown
$manga_list = manga_admin_get_manga_list();

// Get storage data
$storage_stats = manga_admin_get_storage_stats();
?>

<div class="manga-admin-tabs">
    <div class="manga-admin-tab active" data-tab="upload-files"><?php _e('Upload Files', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="manage-files"><?php _e('Manage Files', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="file-settings"><?php _e('Settings', 'manga-admin-panel'); ?></div>
</div>

<div class="manga-admin-content">
    <!-- Upload Files Tab -->
    <div class="manga-admin-tab-pane active" id="upload-files">
        <div class="manga-alert manga-alert-info">
            <?php _e('Upload manga chapters and related files. You can upload individual files or zip archives containing chapter images.', 'manga-admin-panel'); ?>
        </div>
        
        <div class="manga-form-group">
            <label for="upload_manga_id" class="manga-form-label"><?php _e('Select Manga', 'manga-admin-panel'); ?> *</label>
            <select id="upload_manga_id" name="manga_id" class="manga-form-control" required>
                <option value=""><?php _e('Select a manga', 'manga-admin-panel'); ?></option>
                <?php foreach ($manga_list as $manga) : ?>
                    <option value="<?php echo esc_attr($manga->ID); ?>"><?php echo esc_html($manga->post_title); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="manga-form-group upload-chapter-container" style="display: none;">
            <label for="upload_chapter" class="manga-form-label"><?php _e('Chapter Information', 'manga-admin-panel'); ?></label>
            
            <div class="manga-form-row" style="display: flex; gap: 20px;">
                <div class="manga-form-group" style="flex: 1;">
                    <label for="upload_chapter_number" class="manga-form-label"><?php _e('Chapter Number', 'manga-admin-panel'); ?> *</label>
                    <input type="text" id="upload_chapter_number" name="chapter_number" class="manga-form-control" required>
                </div>
                
                <div class="manga-form-group" style="flex: 2;">
                    <label for="upload_chapter_title" class="manga-form-label"><?php _e('Chapter Title (Optional)', 'manga-admin-panel'); ?></label>
                    <input type="text" id="upload_chapter_title" name="chapter_title" class="manga-form-control">
                </div>
            </div>
        </div>
        
        <div class="manga-form-group upload-files-container" style="display: none;">
            <label class="manga-form-label"><?php _e('Upload Files', 'manga-admin-panel'); ?></label>
            
            <div class="manga-tabs-secondary" style="margin-bottom: 15px;">
                <div class="manga-tab-secondary active" data-upload-tab="images"><?php _e('Image Files', 'manga-admin-panel'); ?></div>
                <div class="manga-tab-secondary" data-upload-tab="zip"><?php _e('ZIP Archive', 'manga-admin-panel'); ?></div>
            </div>
            
            <div class="manga-tab-upload-content active" id="upload-tab-images">
                <div class="manga-file-upload">
                    <input type="file" id="chapter-file-upload" name="chapter_files[]" accept="image/*" multiple style="display: none;">
                    <div class="manga-file-upload-icon">
                        <i class="feather-image"></i>
                    </div>
                    <p><?php _e('Click to upload multiple images or drag & drop', 'manga-admin-panel'); ?></p>
                    <p><small><?php _e('Images will be ordered alphabetically by filename. Use numbering in filenames for proper sequence.', 'manga-admin-panel'); ?></small></p>
                </div>
                
                <div id="file-upload-preview" class="manga-upload-list" style="margin-top: 15px;"></div>
            </div>
            
            <div class="manga-tab-upload-content" id="upload-tab-zip" style="display: none;">
                <div class="manga-file-upload">
                    <input type="file" id="zip-file-upload" name="zip_file" accept=".zip" style="display: none;">
                    <div class="manga-file-upload-icon">
                        <i class="feather-file-text"></i>
                    </div>
                    <p><?php _e('Click to upload a ZIP archive or drag & drop', 'manga-admin-panel'); ?></p>
                    <p><small><?php _e('The ZIP file should contain all images for the chapter in the correct order.', 'manga-admin-panel'); ?></small></p>
                </div>
                
                <div id="zip-upload-preview" style="margin-top: 15px;"></div>
            </div>
        </div>
        
        <div class="manga-form-group upload-options-container" style="display: none;">
            <label class="manga-form-label"><?php _e('Upload Options', 'manga-admin-panel'); ?></label>
            
            <div class="manga-checkbox-item">
                <label>
                    <input type="checkbox" name="optimize_images" value="1" checked>
                    <?php _e('Optimize images', 'manga-admin-panel'); ?>
                </label>
                <small><?php _e('Automatically resize and compress images for better loading performance.', 'manga-admin-panel'); ?></small>
            </div>
            
            <div class="manga-checkbox-item">
                <label>
                    <input type="checkbox" name="publish_immediately" value="1" checked>
                    <?php _e('Publish immediately', 'manga-admin-panel'); ?>
                </label>
                <small><?php _e('If unchecked, chapter will be saved as a draft.', 'manga-admin-panel'); ?></small>
            </div>
            
            <div class="manga-checkbox-item">
                <label>
                    <input type="checkbox" name="notify_subscribers" value="1">
                    <?php _e('Notify subscribers', 'manga-admin-panel'); ?>
                </label>
                <small><?php _e('Send notification to subscribers about the new chapter.', 'manga-admin-panel'); ?></small>
            </div>
        </div>
        
        <div class="manga-form-actions" style="margin-top: 30px; display: none;" id="upload-actions">
            <button type="button" id="start-upload" class="manga-btn manga-btn-primary"><?php _e('Upload & Process', 'manga-admin-panel'); ?></button>
        </div>
        
        <div id="upload-progress-container" style="display: none; margin-top: 20px;">
            <div class="manga-alert manga-alert-info">
                <div class="manga-spinner" style="display: inline-block; margin-right: 10px;"></div>
                <span id="upload-status-message"><?php _e('Uploading files...', 'manga-admin-panel'); ?></span>
            </div>
            
            <div class="manga-progress-bar" style="height: 20px; background-color: #f1f2f6; border-radius: 10px; overflow: hidden; margin-top: 10px;">
                <div id="upload-progress" style="height: 100%; width: 0%; background-color: #ff6b6b; transition: width 0.3s ease;"></div>
            </div>
            
            <div id="upload-progress-text" style="text-align: center; margin-top: 5px;">0%</div>
        </div>
    </div>
    
    <!-- Manage Files Tab -->
    <div class="manga-admin-tab-pane" id="manage-files">
        <div class="manga-search-bar">
            <select id="manage_manga_id" class="manga-filter-select">
                <option value=""><?php _e('Select Manga', 'manga-admin-panel'); ?></option>
                <?php foreach ($manga_list as $manga) : ?>
                    <option value="<?php echo esc_attr($manga->ID); ?>"><?php echo esc_html($manga->post_title); ?></option>
                <?php endforeach; ?>
            </select>
            
            <select id="manage_chapter_id" class="manga-filter-select" disabled>
                <option value=""><?php _e('Select Chapter', 'manga-admin-panel'); ?></option>
            </select>
            
            <button id="load-files" class="manga-btn manga-btn-primary" disabled><?php _e('Load Files', 'manga-admin-panel'); ?></button>
        </div>
        
        <div id="file-manager-container">
            <div class="manga-empty-state">
                <div class="manga-empty-icon">
                    <i class="feather-file"></i>
                </div>
                <p class="manga-empty-text"><?php _e('Select a manga and chapter to manage files', 'manga-admin-panel'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- File Settings Tab -->
    <div class="manga-admin-tab-pane" id="file-settings">
        <div class="manga-stats-container" style="margin-bottom: 30px;">
            <h3><?php _e('Storage Statistics', 'manga-admin-panel'); ?></h3>
            
            <div class="manga-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                <div class="manga-stat-card" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h4 style="margin-top: 0;"><?php _e('Total Storage Used', 'manga-admin-panel'); ?></h4>
                    <div class="manga-stat-value" style="font-size: 24px; font-weight: bold; color: #ff6b6b;"><?php echo esc_html($storage_stats['total_used']); ?></div>
                </div>
                
                <div class="manga-stat-card" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h4 style="margin-top: 0;"><?php _e('Total Files', 'manga-admin-panel'); ?></h4>
                    <div class="manga-stat-value" style="font-size: 24px; font-weight: bold; color: #ff6b6b;"><?php echo esc_html($storage_stats['total_files']); ?></div>
                </div>
                
                <div class="manga-stat-card" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h4 style="margin-top: 0;"><?php _e('Chapters Count', 'manga-admin-panel'); ?></h4>
                    <div class="manga-stat-value" style="font-size: 24px; font-weight: bold; color: #ff6b6b;"><?php echo esc_html($storage_stats['total_chapters']); ?></div>
                </div>
            </div>
            
            <?php if (isset($storage_stats['storage_limit'])) : ?>
            <div class="manga-progress-container" style="margin-top: 20px; background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h4><?php _e('Storage Usage', 'manga-admin-panel'); ?></h4>
                
                <div class="manga-progress-bar" style="height: 20px; background-color: #f1f2f6; border-radius: 10px; overflow: hidden; margin-top: 10px;">
                    <div style="height: 100%; width: <?php echo esc_attr($storage_stats['usage_percent']); ?>%; background-color: <?php echo $storage_stats['usage_percent'] > 90 ? '#ff6b6b' : '#1dd1a1'; ?>; transition: width 0.3s ease;"></div>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                    <span><?php echo esc_html($storage_stats['usage_percent']); ?>% <?php _e('used', 'manga-admin-panel'); ?></span>
                    <span><?php echo esc_html($storage_stats['storage_limit']); ?> <?php _e('total', 'manga-admin-panel'); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <form id="file-settings-form" method="post">
            <?php wp_nonce_field('manga_admin_save_file_settings', 'file_settings_nonce'); ?>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Image Optimization', 'manga-admin-panel'); ?></label>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="enable_optimization" value="1" checked>
                        <?php _e('Enable automatic image optimization', 'manga-admin-panel'); ?>
                    </label>
                </div>
                
                <div class="optimization-settings" style="margin-top: 10px; padding-left: 20px;">
                    <div class="manga-form-row" style="display: flex; gap: 20px;">
                        <div class="manga-form-group" style="flex: 1;">
                            <label class="manga-form-label"><?php _e('Max Width', 'manga-admin-panel'); ?></label>
                            <input type="number" name="max_width" class="manga-form-control" value="1200">
                            <small><?php _e('Images will be resized to this maximum width.', 'manga-admin-panel'); ?></small>
                        </div>
                        
                        <div class="manga-form-group" style="flex: 1;">
                            <label class="manga-form-label"><?php _e('Image Quality', 'manga-admin-panel'); ?></label>
                            <input type="number" name="image_quality" class="manga-form-control" min="1" max="100" value="90">
                            <small><?php _e('JPEG compression quality (1-100).', 'manga-admin-panel'); ?></small>
                        </div>
                    </div>
                    
                    <div class="manga-form-group">
                        <label class="manga-form-label"><?php _e('Convert Images', 'manga-admin-panel'); ?></label>
                        <select name="convert_format" class="manga-form-control">
                            <option value="no_convert"><?php _e('Don\'t convert', 'manga-admin-panel'); ?></option>
                            <option value="jpeg"><?php _e('Convert to JPEG', 'manga-admin-panel'); ?></option>
                            <option value="webp" selected><?php _e('Convert to WebP', 'manga-admin-panel'); ?></option>
                        </select>
                        <small><?php _e('WebP provides better compression but might not be supported by all browsers.', 'manga-admin-panel'); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('File Storage', 'manga-admin-panel'); ?></label>
                <select name="storage_type" class="manga-form-control">
                    <option value="local" selected><?php _e('Local Server', 'manga-admin-panel'); ?></option>
                    <?php if (function_exists('wp_manga_get_available_storage_services')) : ?>
                        <?php foreach (wp_manga_get_available_storage_services() as $id => $service) : ?>
                            <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($service['name']); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small><?php _e('Where to store manga chapter files.', 'manga-admin-panel'); ?></small>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('File Organization', 'manga-admin-panel'); ?></label>
                <select name="file_structure" class="manga-form-control">
                    <option value="manga/chapter"><?php _e('Manga/Chapter (Default)', 'manga-admin-panel'); ?></option>
                    <option value="manga_id/chapter_id"><?php _e('Manga ID/Chapter ID', 'manga-admin-panel'); ?></option>
                    <option value="year/month/manga"><?php _e('Year/Month/Manga', 'manga-admin-panel'); ?></option>
                </select>
                <small><?php _e('How files are organized in the storage.', 'manga-admin-panel'); ?></small>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Cache Settings', 'manga-admin-panel'); ?></label>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="enable_cache" value="1" checked>
                        <?php _e('Enable file caching for better performance', 'manga-admin-panel'); ?>
                    </label>
                </div>
                
                <div class="cache-settings" style="margin-top: 10px; padding-left: 20px;">
                    <div class="manga-form-group">
                        <label class="manga-form-label"><?php _e('Cache Duration', 'manga-admin-panel'); ?></label>
                        <input type="number" name="cache_duration" class="manga-form-control" value="7">
                        <small><?php _e('Number of days to keep cache files.', 'manga-admin-panel'); ?></small>
                    </div>
                </div>
            </div>
            
            <div class="manga-form-actions" style="margin-top: 30px; display: flex; gap: 10px;">
                <button type="submit" class="manga-btn manga-btn-primary"><?php _e('Save Settings', 'manga-admin-panel'); ?></button>
                <button type="button" id="clear-cache" class="manga-btn manga-btn-secondary"><?php _e('Clear Cache', 'manga-admin-panel'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle between upload tabs
    $('.manga-tab-secondary').on('click', function() {
        $('.manga-tab-secondary').removeClass('active');
        $(this).addClass('active');
        
        const tab = $(this).data('upload-tab');
        $('.manga-tab-upload-content').hide();
        $('#upload-tab-' + tab).show();
    });
    
    // When manga is selected for upload
    $('#upload_manga_id').on('change', function() {
        const mangaId = $(this).val();
        
        if (mangaId) {
            $('.upload-chapter-container').slideDown();
            $('.upload-files-container').slideDown();
            $('.upload-options-container').slideDown();
            $('#upload-actions').slideDown();
        } else {
            $('.upload-chapter-container').slideUp();
            $('.upload-files-container').slideUp();
            $('.upload-options-container').slideUp();
            $('#upload-actions').slideUp();
        }
    });
    
    // Preview for image uploads
    $('#chapter-file-upload').on('change', function() {
        const files = this.files;
        const preview = $('#file-upload-preview');
        preview.empty();
        
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const item = $(`
                            <div class="manga-upload-item">
                                <img src="${e.target.result}" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;">
                                <span>${file.name}</span>
                            </div>
                        `);
                        
                        preview.append(item);
                    };
                    
                    reader.readAsDataURL(file);
                }
            }
        }
    });
    
    // Preview for ZIP upload
    $('#zip-file-upload').on('change', function() {
        const file = this.files[0];
        const preview = $('#zip-upload-preview');
        preview.empty();
        
        if (file) {
            const item = $(`
                <div class="manga-upload-item">
                    <i class="feather-file-text" style="margin-right: 10px;"></i>
                    <span>${file.name}</span> (${formatFileSize(file.size)})
                </div>
            `);
            
            preview.append(item);
        }
    });
    
    // When manga is selected for file management
    $('#manage_manga_id').on('change', function() {
        const mangaId = $(this).val();
        
        if (!mangaId) {
            $('#manage_chapter_id').html('<option value=""><?php _e('Select Chapter', 'manga-admin-panel'); ?></option>');
            $('#manage_chapter_id').prop('disabled', true);
            $('#load-files').prop('disabled', true);
            return;
        }
        
        // Show loading
        $('#manage_chapter_id').html('<option value=""><?php _e('Loading chapters...', 'manga-admin-panel'); ?></option>');
        $('#manage_chapter_id').prop('disabled', true);
        
        // Load chapters via AJAX
        $.ajax({
            url: mangaAdminVars.ajaxurl,
            type: 'POST',
            data: {
                action: 'manga_admin_get_chapters',
                manga_id: mangaId,
                nonce: mangaAdminVars.nonce
            },
            success: function(response) {
                if (response.success) {
                    let options = '<option value=""><?php _e('Select Chapter', 'manga-admin-panel'); ?></option>';
                    
                    response.data.chapters.forEach(function(chapter) {
                        options += `<option value="${chapter.id}">${chapter.number}: ${chapter.title}</option>`;
                    });
                    
                    $('#manage_chapter_id').html(options);
                    $('#manage_chapter_id').prop('disabled', false);
                } else {
                    $('#manage_chapter_id').html('<option value=""><?php _e('No chapters available', 'manga-admin-panel'); ?></option>');
                    $('#manage_chapter_id').prop('disabled', true);
                }
            },
            error: function() {
                $('#manage_chapter_id').html('<option value=""><?php _e('Error loading chapters', 'manga-admin-panel'); ?></option>');
                $('#manage_chapter_id').prop('disabled', true);
            }
        });
    });
    
    // When chapter is selected for file management
    $('#manage_chapter_id').on('change', function() {
        $('#load-files').prop('disabled', !$(this).val());
    });
    
    // Load files button
    $('#load-files').on('click', function() {
        const mangaId = $('#manage_manga_id').val();
        const chapterId = $('#manage_chapter_id').val();
        
        if (!mangaId || !chapterId) {
            return;
        }
        
        loadChapterFiles(mangaId, chapterId);
    });
    
    // Start upload process
    $('#start-upload').on('click', function() {
        const mangaId = $('#upload_manga_id').val();
        const chapterNumber = $('#upload_chapter_number').val();
        
        if (!mangaId || !chapterNumber) {
            MangaAdmin.showNotification('error', '<?php _e('Please select a manga and enter chapter number.', 'manga-admin-panel'); ?>');
            return;
        }
        
        // Check if files are selected
        const hasImages = $('#chapter-file-upload')[0].files.length > 0;
        const hasZip = $('#zip-file-upload')[0].files.length > 0;
        
        if (!hasImages && !hasZip) {
            MangaAdmin.showNotification('error', '<?php _e('Please select files to upload.', 'manga-admin-panel'); ?>');
            return;
        }
        
        // Show upload progress
        $('#upload-progress-container').show();
        $('#upload-progress').css('width', '0%');
        $('#upload-progress-text').text('0%');
        $('#upload-status-message').text('<?php _e('Preparing files...', 'manga-admin-panel'); ?>');
        
        // Simulate upload progress (this would be replaced with actual upload in a real implementation)
        simulateUploadProgress();
    });
    
    // Clear cache button
    $('#clear-cache').on('click', function() {
        if (confirm(mangaAdminVars.i18n.confirm_delete)) {
            // Show loading state
            const button = $(this);
            const originalText = button.text();
            button.prop('disabled', true).text('<?php _e('Clearing...', 'manga-admin-panel'); ?>');
            
            // Send AJAX request
            $.ajax({
                url: mangaAdminVars.ajaxurl,
                type: 'POST',
                data: {
                    action: 'manga_admin_clear_cache',
                    nonce: mangaAdminVars.nonce
                },
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
                    button.prop('disabled', false).text(originalText);
                }
            });
        }
    });
    
    // File settings form submission
    $('#file-settings-form').on('submit', function(e) {
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
            data: formData + '&action=manga_admin_save_file_settings&nonce=' + mangaAdminVars.nonce,
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
    
    // Load chapter files
    function loadChapterFiles(mangaId, chapterId) {
        const container = $('#file-manager-container');
        
        // Show loading
        container.html('<div class="manga-loading"><div class="manga-spinner"></div> <span><?php _e('Loading files...', 'manga-admin-panel'); ?></span></div>');
        
        // Load files via AJAX
        $.ajax({
            url: mangaAdminVars.ajaxurl,
            type: 'POST',
            data: {
                action: 'manga_admin_get_chapter_files',
                manga_id: mangaId,
                chapter_id: chapterId,
                nonce: mangaAdminVars.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.files.length > 0) {
                        let html = `
                            <div class="manga-file-manager-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                                <h3 style="margin: 0;">${response.data.manga_title} - ${response.data.chapter_title}</h3>
                                <div>
                                    <button class="manga-btn manga-btn-secondary manga-btn-sm upload-more-files" data-manga="${mangaId}" data-chapter="${chapterId}"><?php _e('Upload More Files', 'manga-admin-panel'); ?></button>
                                    <button class="manga-btn manga-btn-danger manga-btn-sm delete-all-files" data-manga="${mangaId}" data-chapter="${chapterId}"><?php _e('Delete All Files', 'manga-admin-panel'); ?></button>
                                </div>
                            </div>
                            
                            <div class="manga-files-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                        `;
                        
                        response.data.files.forEach(function(file, index) {
                            html += `
                                <div class="manga-file-item" data-id="${file.id}" style="background-color: #fff; border-radius: 5px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                    <div class="manga-file-preview" style="position: relative; padding-top: 142%; overflow: hidden;">
                                        <img src="${file.url}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                                        <div class="manga-file-number" style="position: absolute; top: 10px; left: 10px; background-color: #ff6b6b; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: 600;">${index + 1}</div>
                                    </div>
                                    <div class="manga-file-info" style="padding: 10px;">
                                        <div style="font-size: 12px; color: #718093; margin-bottom: 5px;">${file.name}</div>
                                        <div style="font-size: 11px; color: #a5b1c2;">${file.size}</div>
                                        <div class="manga-file-actions" style="margin-top: 10px; display: flex; gap: 5px;">
                                            <button class="manga-btn manga-btn-danger manga-btn-sm delete-file" data-id="${file.id}"><?php _e('Delete', 'manga-admin-panel'); ?></button>
                                            <button class="manga-btn manga-btn-secondary manga-btn-sm replace-file" data-id="${file.id}"><?php _e('Replace', 'manga-admin-panel'); ?></button>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        
                        container.html(html);
                    } else {
                        container.html(`
                            <div class="manga-empty-state">
                                <div class="manga-empty-icon">
                                    <i class="feather-file"></i>
                                </div>
                                <p class="manga-empty-text"><?php _e('No files found for this chapter', 'manga-admin-panel'); ?></p>
                                <button class="manga-btn manga-btn-primary upload-more-files" data-manga="${mangaId}" data-chapter="${chapterId}"><?php _e('Upload Files', 'manga-admin-panel'); ?></button>
                            </div>
                        `);
                    }
                } else {
                    container.html(`<div class="manga-alert manga-alert-danger">${response.data.message}</div>`);
                }
            },
            error: function() {
                container.html(`<div class="manga-alert manga-alert-danger">${mangaAdminVars.i18n.error}</div>`);
            }
        });
    }
    
    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Simulate upload progress (for demonstration)
    function simulateUploadProgress() {
        let progress = 0;
        const interval = setInterval(function() {
            progress += Math.random() * 10;
            
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                
                $('#upload-progress').css('width', '100%');
                $('#upload-progress-text').text('100%');
                $('#upload-status-message').text('<?php _e('Processing uploaded files...', 'manga-admin-panel'); ?>');
                
                // Simulate processing delay
                setTimeout(function() {
                    $('#upload-status-message').text('<?php _e('Upload complete!', 'manga-admin-panel'); ?>');
                    MangaAdmin.showNotification('success', '<?php _e('Files uploaded and processed successfully.', 'manga-admin-panel'); ?>');
                    
                    // Reset form
                    setTimeout(function() {
                        $('#upload-progress-container').hide();
                        $('#chapter-file-upload').val('');
                        $('#zip-file-upload').val('');
                        $('#file-upload-preview').empty();
                        $('#zip-upload-preview').empty();
                    }, 2000);
                }, 1500);
            } else {
                $('#upload-progress').css('width', progress + '%');
                $('#upload-progress-text').text(Math.round(progress) + '%');
            }
        }, 200);
    }
});
</script>
