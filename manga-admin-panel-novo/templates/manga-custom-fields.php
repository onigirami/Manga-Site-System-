<?php
/**
 * Manga Custom Fields Template
 * 
 * Interface for managing custom fields for manga
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if WP Manga Custom Fields is active
if (!defined('WP_MANGA_CUSTOM_FIELDS_VERSION')) {
    echo '<div class="manga-alert manga-alert-danger">' . __('WP Manga Custom Fields plugin is required for this feature.', 'manga-admin-panel') . '</div>';
    return;
}

// Get manga list for dropdown
$manga_list = manga_admin_get_manga_list();

// Get available field types
$field_types = array(
    'text' => __('Text', 'manga-admin-panel'),
    'textarea' => __('Text Area', 'manga-admin-panel'),
    'number' => __('Number', 'manga-admin-panel'),
    'select' => __('Select Dropdown', 'manga-admin-panel'),
    'checkbox' => __('Checkbox', 'manga-admin-panel'),
    'radio' => __('Radio Buttons', 'manga-admin-panel'),
    'url' => __('URL', 'manga-admin-panel'),
    'date' => __('Date', 'manga-admin-panel'),
    'color' => __('Color Picker', 'manga-admin-panel'),
);

// Get global fields (retrieved from WP Manga Custom Fields if available)
$global_fields = manga_admin_get_global_custom_fields();
?>

<div class="manga-admin-tabs">
    <div class="manga-admin-tab active" data-tab="manga-fields"><?php _e('Manga Custom Fields', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="global-fields"><?php _e('Global Fields', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="field-settings"><?php _e('Settings', 'manga-admin-panel'); ?></div>
</div>

<div class="manga-admin-content">
    <!-- Manga Custom Fields Tab -->
    <div class="manga-admin-tab-pane active" id="manga-fields">
        <div class="manga-form-group">
            <label for="manga_id_fields" class="manga-form-label"><?php _e('Select Manga', 'manga-admin-panel'); ?></label>
            <select id="manga_id_fields" name="manga_id" class="manga-form-control">
                <option value=""><?php _e('Select a manga', 'manga-admin-panel'); ?></option>
                <?php foreach ($manga_list as $manga) : ?>
                    <option value="<?php echo esc_attr($manga->ID); ?>"><?php echo esc_html($manga->post_title); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div id="custom-fields-container">
            <div class="manga-empty-state">
                <div class="manga-empty-icon">
                    <i class="feather-list"></i>
                </div>
                <p class="manga-empty-text"><?php _e('Select a manga to view and edit custom fields', 'manga-admin-panel'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Global Fields Tab -->
    <div class="manga-admin-tab-pane" id="global-fields">
        <div class="manga-alert manga-alert-info">
            <?php _e('Global fields are available for all manga. Add fields here that you want to use across multiple manga.', 'manga-admin-panel'); ?>
        </div>
        
        <form id="global-fields-form" method="post">
            <?php wp_nonce_field('manga_admin_save_global_fields', 'global_fields_nonce'); ?>
            
            <div id="global-fields-list">
                <?php if (!empty($global_fields)) : ?>
                    <?php foreach ($global_fields as $index => $field) : ?>
                        <div class="custom-field-group">
                            <div class="manga-form-row" style="display: flex; gap: 20px;">
                                <div class="manga-form-group" style="flex: 1;">
                                    <label class="manga-form-label"><?php _e('Field ID', 'manga-admin-panel'); ?></label>
                                    <input type="text" name="global_fields[<?php echo $index; ?>][id]" value="<?php echo esc_attr($field['id']); ?>" class="manga-form-control" required>
                                    <small><?php _e('Unique identifier for the field. Use lowercase letters, numbers, and underscores.', 'manga-admin-panel'); ?></small>
                                </div>
                                
                                <div class="manga-form-group" style="flex: 1;">
                                    <label class="manga-form-label"><?php _e('Field Label', 'manga-admin-panel'); ?></label>
                                    <input type="text" name="global_fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr($field['label']); ?>" class="manga-form-control" required>
                                    <small><?php _e('User-friendly label for the field.', 'manga-admin-panel'); ?></small>
                                </div>
                            </div>
                            
                            <div class="manga-form-row" style="display: flex; gap: 20px;">
                                <div class="manga-form-group" style="flex: 1;">
                                    <label class="manga-form-label"><?php _e('Field Type', 'manga-admin-panel'); ?></label>
                                    <select name="global_fields[<?php echo $index; ?>][type]" class="manga-form-control field-type-select">
                                        <?php foreach ($field_types as $type_value => $type_label) : ?>
                                            <option value="<?php echo esc_attr($type_value); ?>" <?php selected($field['type'], $type_value); ?>><?php echo esc_html($type_label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="manga-form-group" style="flex: 1;">
                                    <label class="manga-form-label"><?php _e('Default Value', 'manga-admin-panel'); ?></label>
                                    <input type="text" name="global_fields[<?php echo $index; ?>][default]" value="<?php echo esc_attr($field['default']); ?>" class="manga-form-control">
                                </div>
                                
                                <div class="manga-form-group" style="width: 100px;">
                                    <label class="manga-form-label"><?php _e('Required', 'manga-admin-panel'); ?></label>
                                    <div class="manga-checkbox-item" style="margin-top: 10px;">
                                        <label>
                                            <input type="checkbox" name="global_fields[<?php echo $index; ?>][required]" value="1" <?php checked(!empty($field['required']), true); ?>>
                                            <?php _e('Yes', 'manga-admin-panel'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="field-options-container" style="<?php echo ($field['type'] == 'select' || $field['type'] == 'radio') ? '' : 'display: none;'; ?>">
                                <div class="manga-form-group">
                                    <label class="manga-form-label"><?php _e('Options (one per line)', 'manga-admin-panel'); ?></label>
                                    <textarea name="global_fields[<?php echo $index; ?>][options]" class="manga-form-control" rows="3"><?php echo esc_textarea(!empty($field['options']) ? implode("\n", $field['options']) : ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="manga-form-group">
                                <label class="manga-form-label"><?php _e('Description', 'manga-admin-panel'); ?></label>
                                <input type="text" name="global_fields[<?php echo $index; ?>][description]" value="<?php echo esc_attr(!empty($field['description']) ? $field['description'] : ''); ?>" class="manga-form-control">
                                <small><?php _e('Help text to explain the field.', 'manga-admin-panel'); ?></small>
                            </div>
                            
                            <button type="button" class="manga-btn manga-btn-danger remove-global-field"><?php _e('Remove Field', 'manga-admin-panel'); ?></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="manga-form-actions" style="margin-top: 20px;">
                <button type="button" id="add-global-field" class="manga-btn manga-btn-secondary"><?php _e('Add Field', 'manga-admin-panel'); ?></button>
                <button type="submit" class="manga-btn manga-btn-primary"><?php _e('Save Global Fields', 'manga-admin-panel'); ?></button>
            </div>
        </form>
    </div>
    
    <!-- Field Settings Tab -->
    <div class="manga-admin-tab-pane" id="field-settings">
        <form id="field-settings-form" method="post">
            <?php wp_nonce_field('manga_admin_save_field_settings', 'field_settings_nonce'); ?>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Field Display Location', 'manga-admin-panel'); ?></label>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="display_locations[]" value="manga_single" checked>
                        <?php _e('Manga Single Page', 'manga-admin-panel'); ?>
                    </label>
                </div>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="display_locations[]" value="manga_archive">
                        <?php _e('Manga Archive/List Page', 'manga-admin-panel'); ?>
                    </label>
                </div>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="display_locations[]" value="chapter_page">
                        <?php _e('Chapter Reading Page', 'manga-admin-panel'); ?>
                    </label>
                </div>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Custom Fields Display Style', 'manga-admin-panel'); ?></label>
                <select name="fields_display_style" class="manga-form-control">
                    <option value="table"><?php _e('Table Layout', 'manga-admin-panel'); ?></option>
                    <option value="list"><?php _e('List Layout', 'manga-admin-panel'); ?></option>
                    <option value="grid"><?php _e('Grid Layout', 'manga-admin-panel'); ?></option>
                    <option value="inline"><?php _e('Inline Layout', 'manga-admin-panel'); ?></option>
                </select>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Custom Fields Section Title', 'manga-admin-panel'); ?></label>
                <input type="text" name="fields_section_title" class="manga-form-control" value="<?php echo esc_attr(get_option('wp_manga_custom_fields_title', __('Additional Information', 'manga-admin-panel'))); ?>">
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Search Integration', 'manga-admin-panel'); ?></label>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="search_integration" value="1">
                        <?php _e('Include custom fields in manga search', 'manga-admin-panel'); ?>
                    </label>
                </div>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Filter Integration', 'manga-admin-panel'); ?></label>
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" name="filter_integration" value="1">
                        <?php _e('Allow filtering manga by custom fields', 'manga-admin-panel'); ?>
                    </label>
                </div>
                
                <div class="filterable-fields" style="margin-top: 10px; padding-left: 20px;">
                    <label class="manga-form-label"><?php _e('Filterable Fields', 'manga-admin-panel'); ?></label>
                    <select name="filterable_fields[]" class="manga-form-control" multiple style="height: 150px;">
                        <?php if (!empty($global_fields)) : ?>
                            <?php foreach ($global_fields as $field) : ?>
                                <option value="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['label']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small><?php _e('Hold Ctrl/Cmd to select multiple fields', 'manga-admin-panel'); ?></small>
                </div>
            </div>
            
            <div class="manga-form-actions" style="margin-top: 30px;">
                <button type="submit" class="manga-btn manga-btn-primary"><?php _e('Save Settings', 'manga-admin-panel'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle field type change to show/hide options
    $(document).on('change', '.field-type-select', function() {
        const fieldType = $(this).val();
        const optionsContainer = $(this).closest('.custom-field-group').find('.field-options-container');
        
        if (fieldType === 'select' || fieldType === 'radio') {
            optionsContainer.show();
        } else {
            optionsContainer.hide();
        }
    });
    
    // Add new global field
    $('#add-global-field').on('click', function() {
        const fieldIndex = $('#global-fields-list .custom-field-group').length;
        
        const newField = `
            <div class="custom-field-group">
                <div class="manga-form-row" style="display: flex; gap: 20px;">
                    <div class="manga-form-group" style="flex: 1;">
                        <label class="manga-form-label"><?php _e('Field ID', 'manga-admin-panel'); ?></label>
                        <input type="text" name="global_fields[${fieldIndex}][id]" class="manga-form-control" required>
                        <small><?php _e('Unique identifier for the field. Use lowercase letters, numbers, and underscores.', 'manga-admin-panel'); ?></small>
                    </div>
                    
                    <div class="manga-form-group" style="flex: 1;">
                        <label class="manga-form-label"><?php _e('Field Label', 'manga-admin-panel'); ?></label>
                        <input type="text" name="global_fields[${fieldIndex}][label]" class="manga-form-control" required>
                        <small><?php _e('User-friendly label for the field.', 'manga-admin-panel'); ?></small>
                    </div>
                </div>
                
                <div class="manga-form-row" style="display: flex; gap: 20px;">
                    <div class="manga-form-group" style="flex: 1;">
                        <label class="manga-form-label"><?php _e('Field Type', 'manga-admin-panel'); ?></label>
                        <select name="global_fields[${fieldIndex}][type]" class="manga-form-control field-type-select">
                            <?php foreach ($field_types as $type_value => $type_label) : ?>
                                <option value="<?php echo esc_attr($type_value); ?>"><?php echo esc_html($type_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="manga-form-group" style="flex: 1;">
                        <label class="manga-form-label"><?php _e('Default Value', 'manga-admin-panel'); ?></label>
                        <input type="text" name="global_fields[${fieldIndex}][default]" class="manga-form-control">
                    </div>
                    
                    <div class="manga-form-group" style="width: 100px;">
                        <label class="manga-form-label"><?php _e('Required', 'manga-admin-panel'); ?></label>
                        <div class="manga-checkbox-item" style="margin-top: 10px;">
                            <label>
                                <input type="checkbox" name="global_fields[${fieldIndex}][required]" value="1">
                                <?php _e('Yes', 'manga-admin-panel'); ?>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="field-options-container" style="display: none;">
                    <div class="manga-form-group">
                        <label class="manga-form-label"><?php _e('Options (one per line)', 'manga-admin-panel'); ?></label>
                        <textarea name="global_fields[${fieldIndex}][options]" class="manga-form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="manga-form-group">
                    <label class="manga-form-label"><?php _e('Description', 'manga-admin-panel'); ?></label>
                    <input type="text" name="global_fields[${fieldIndex}][description]" class="manga-form-control">
                    <small><?php _e('Help text to explain the field.', 'manga-admin-panel'); ?></small>
                </div>
                
                <button type="button" class="manga-btn manga-btn-danger remove-global-field"><?php _e('Remove Field', 'manga-admin-panel'); ?></button>
            </div>
        `;
        
        $('#global-fields-list').append(newField);
    });
    
    // Remove global field
    $(document).on('click', '.remove-global-field', function() {
        if (confirm(mangaAdminVars.i18n.confirm_delete)) {
            $(this).closest('.custom-field-group').remove();
        }
    });
    
    // Global fields form submission
    $('#global-fields-form').on('submit', function(e) {
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
            data: formData + '&action=manga_admin_save_global_fields&nonce=' + mangaAdminVars.nonce,
            success: function(response) {
                if (response.success) {
                    MangaAdmin.showNotification('success', response.data.message);
                    
                    // Update filterable fields dropdown
                    updateFilterableFields(response.data.fields);
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
    
    // Field settings form submission
    $('#field-settings-form').on('submit', function(e) {
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
            data: formData + '&action=manga_admin_save_field_settings&nonce=' + mangaAdminVars.nonce,
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
    
    // Update filterable fields dropdown
    function updateFilterableFields(fields) {
        const filterableFields = $('select[name="filterable_fields[]"]');
        let options = '';
        
        for (let i = 0; i < fields.length; i++) {
            options += `<option value="${fields[i].id}">${fields[i].label}</option>`;
        }
        
        filterableFields.html(options);
    }
});
</script>
