<?php
/**
 * Manga Create/Edit Template
 * 
 * Form for creating or editing manga
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get manga data if editing
$manga = null;
$is_edit = isset($_GET['id']) && intval($_GET['id']) > 0;

if ($is_edit) {
    $manga_id = intval($_GET['id']);
    $manga = get_post($manga_id);
    
    if (!$manga || $manga->post_type !== 'wp-manga') {
        echo '<div class="manga-alert manga-alert-danger">' . __('Manga not found.', 'manga-admin-panel') . '</div>';
        return;
    }
}

// Get manga taxonomies
$genres = get_terms(array(
    'taxonomy' => 'wp-manga-genre',
    'hide_empty' => false,
));

$tags = get_terms(array(
    'taxonomy' => 'wp-manga-tag',
    'hide_empty' => false,
));

$authors = get_terms(array(
    'taxonomy' => 'wp-manga-author',
    'hide_empty' => false,
));

$artists = get_terms(array(
    'taxonomy' => 'wp-manga-artist',
    'hide_empty' => false,
));

// Get existing terms if editing
$manga_genres = array();
$manga_tags = array();
$manga_authors = array();
$manga_artists = array();

if ($is_edit) {
    $manga_genres = wp_get_post_terms($manga_id, 'wp-manga-genre', array('fields' => 'ids'));
    $manga_tags = wp_get_post_terms($manga_id, 'wp-manga-tag', array('fields' => 'ids'));
    $manga_authors = wp_get_post_terms($manga_id, 'wp-manga-author', array('fields' => 'ids'));
    $manga_artists = wp_get_post_terms($manga_id, 'wp-manga-artist', array('fields' => 'ids'));
}

// Get manga meta data
$manga_status = '';
$manga_adult = false;
$manga_alternative = '';

if ($is_edit) {
    $manga_status = get_post_meta($manga_id, '_wp_manga_status', true);
    $manga_adult = get_post_meta($manga_id, '_wp_manga_adult', true) == 'yes';
    $manga_alternative = get_post_meta($manga_id, '_wp_manga_alternative', true);
}
?>

<div class="manga-admin-tabs">
    <div class="manga-admin-tab active" data-tab="manga-info"><?php _e('Manga Information', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="manga-content"><?php _e('Content', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="manga-categories"><?php _e('Categories & Tags', 'manga-admin-panel'); ?></div>
    <div class="manga-admin-tab" data-tab="manga-images"><?php _e('Images', 'manga-admin-panel'); ?></div>
</div>

<div class="manga-admin-content">
    <form id="manga-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('manga_admin_save_manga', 'manga_admin_nonce'); ?>
        <input type="hidden" name="manga_id" value="<?php echo $is_edit ? esc_attr($manga_id) : ''; ?>">
        
        <!-- Manga Information Tab -->
        <div class="manga-admin-tab-pane active" id="manga-info">
            <div class="manga-form-group">
                <label for="manga_title" class="manga-form-label"><?php _e('Title', 'manga-admin-panel'); ?> *</label>
                <input type="text" id="manga_title" name="manga_title" class="manga-form-control preview-field" value="<?php echo $is_edit ? esc_attr($manga->post_title) : ''; ?>" required>
            </div>
            
            <div class="manga-form-group">
                <label for="manga_alternative" class="manga-form-label"><?php _e('Alternative Title(s)', 'manga-admin-panel'); ?></label>
                <input type="text" id="manga_alternative" name="manga_alternative" class="manga-form-control" value="<?php echo esc_attr($manga_alternative); ?>">
                <small><?php _e('Separate multiple titles with commas', 'manga-admin-panel'); ?></small>
            </div>
            
            <div class="manga-form-group">
                <label for="manga_description" class="manga-form-label"><?php _e('Description', 'manga-admin-panel'); ?></label>
                <textarea id="manga_description" name="manga_description" class="manga-form-control preview-field" rows="5"><?php echo $is_edit ? esc_textarea($manga->post_content) : ''; ?></textarea>
            </div>
            
            <div class="manga-form-row" style="display: flex; gap: 20px;">
                <div class="manga-form-group" style="flex: 1;">
                    <label for="manga_status" class="manga-form-label"><?php _e('Status', 'manga-admin-panel'); ?></label>
                    <select id="manga_status" name="manga_status" class="manga-form-control">
                        <option value="ongoing" <?php selected($manga_status, 'ongoing'); ?>><?php _e('Ongoing', 'manga-admin-panel'); ?></option>
                        <option value="completed" <?php selected($manga_status, 'completed'); ?>><?php _e('Completed', 'manga-admin-panel'); ?></option>
                        <option value="canceled" <?php selected($manga_status, 'canceled'); ?>><?php _e('Canceled', 'manga-admin-panel'); ?></option>
                        <option value="on-hold" <?php selected($manga_status, 'on-hold'); ?>><?php _e('On Hold', 'manga-admin-panel'); ?></option>
                    </select>
                </div>
                
                <div class="manga-form-group" style="flex: 1;">
                    <label for="manga_post_status" class="manga-form-label"><?php _e('Publication Status', 'manga-admin-panel'); ?></label>
                    <select id="manga_post_status" name="manga_post_status" class="manga-form-control preview-field">
                        <option value="publish" <?php echo $is_edit && $manga->post_status == 'publish' ? 'selected' : ''; ?>><?php _e('Published', 'manga-admin-panel'); ?></option>
                        <option value="draft" <?php echo $is_edit && $manga->post_status == 'draft' ? 'selected' : ''; ?>><?php _e('Draft', 'manga-admin-panel'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="manga-form-group">
                <label class="manga-form-label">
                    <input type="checkbox" name="manga_adult" value="yes" <?php checked($manga_adult, true); ?>>
                    <?php _e('Mark as Adult Content (18+)', 'manga-admin-panel'); ?>
                </label>
            </div>
        </div>
        
        <!-- Manga Content Tab -->
        <div class="manga-admin-tab-pane" id="manga-content">
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Manga Excerpt', 'manga-admin-panel'); ?></label>
                <textarea name="manga_excerpt" class="manga-form-control" rows="3"><?php echo $is_edit ? esc_textarea($manga->post_excerpt) : ''; ?></textarea>
                <small><?php _e('A short summary that appears in search results and listings', 'manga-admin-panel'); ?></small>
            </div>
            
            <?php
            // If using WYSIWYG editor
            if (class_exists('_WP_Editors')) {
                $content = $is_edit ? $manga->post_content : '';
                $editor_id = 'manga_content_editor';
                $settings = array(
                    'textarea_name' => 'manga_content',
                    'textarea_rows' => 10,
                    'media_buttons' => true,
                );
                wp_editor($content, $editor_id, $settings);
            } else {
                // Fallback to regular textarea
                echo '<div class="manga-form-group">';
                echo '<label class="manga-form-label">' . __('Manga Content', 'manga-admin-panel') . '</label>';
                echo '<textarea name="manga_content" class="manga-form-control" rows="10">' . ($is_edit ? esc_textarea($manga->post_content) : '') . '</textarea>';
                echo '</div>';
            }
            ?>
            
            <div class="manga-preview-toggle" style="margin-top: 20px; text-align: right;">
                <button type="button" id="toggle-preview" class="manga-btn manga-btn-secondary"><?php _e('Show Preview', 'manga-admin-panel'); ?></button>
            </div>
            
            <div id="manga-preview-container" class="manga-preview-container" style="display: none;">
                <div class="manga-preview-header">
                    <h3 class="manga-preview-title"><?php _e('Preview', 'manga-admin-panel'); ?></h3>
                    <button type="button" id="close-preview" class="manga-btn manga-btn-sm manga-btn-secondary"><?php _e('Close', 'manga-admin-panel'); ?></button>
                </div>
                <div id="manga-preview-content" class="manga-preview-content"></div>
            </div>
        </div>
        
        <!-- Categories & Tags Tab -->
        <div class="manga-admin-tab-pane" id="manga-categories">
            <div class="manga-form-row" style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div class="manga-form-group" style="flex: 1; min-width: 250px;">
                    <label class="manga-form-label"><?php _e('Genres', 'manga-admin-panel'); ?></label>
                    <div class="manga-checkbox-group" style="height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                        <?php foreach ($genres as $genre) : ?>
                            <div class="manga-checkbox-item">
                                <label>
                                    <input type="checkbox" name="manga_genres[]" value="<?php echo esc_attr($genre->term_id); ?>" <?php checked(in_array($genre->term_id, $manga_genres), true); ?>>
                                    <?php echo esc_html($genre->name); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="manga-form-group" style="flex: 1; min-width: 250px;">
                    <label class="manga-form-label"><?php _e('Tags', 'manga-admin-panel'); ?></label>
                    <div class="manga-checkbox-group" style="height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                        <?php foreach ($tags as $tag) : ?>
                            <div class="manga-checkbox-item">
                                <label>
                                    <input type="checkbox" name="manga_tags[]" value="<?php echo esc_attr($tag->term_id); ?>" <?php checked(in_array($tag->term_id, $manga_tags), true); ?>>
                                    <?php echo esc_html($tag->name); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 10px;">
                        <input type="text" id="new_tag" class="manga-form-control" placeholder="<?php _e('Add new tag', 'manga-admin-panel'); ?>">
                        <button type="button" id="add_tag" class="manga-btn manga-btn-secondary" style="margin-top: 5px;"><?php _e('Add', 'manga-admin-panel'); ?></button>
                    </div>
                </div>
            </div>
            
            <div class="manga-form-row" style="display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap;">
                <div class="manga-form-group" style="flex: 1; min-width: 250px;">
                    <label class="manga-form-label"><?php _e('Authors', 'manga-admin-panel'); ?></label>
                    <div class="manga-checkbox-group" style="height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                        <?php foreach ($authors as $author) : ?>
                            <div class="manga-checkbox-item">
                                <label>
                                    <input type="checkbox" name="manga_authors[]" value="<?php echo esc_attr($author->term_id); ?>" <?php checked(in_array($author->term_id, $manga_authors), true); ?>>
                                    <?php echo esc_html($author->name); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 10px;">
                        <input type="text" id="new_author" class="manga-form-control" placeholder="<?php _e('Add new author', 'manga-admin-panel'); ?>">
                        <button type="button" id="add_author" class="manga-btn manga-btn-secondary" style="margin-top: 5px;"><?php _e('Add', 'manga-admin-panel'); ?></button>
                    </div>
                </div>
                
                <div class="manga-form-group" style="flex: 1; min-width: 250px;">
                    <label class="manga-form-label"><?php _e('Artists', 'manga-admin-panel'); ?></label>
                    <div class="manga-checkbox-group" style="height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                        <?php foreach ($artists as $artist) : ?>
                            <div class="manga-checkbox-item">
                                <label>
                                    <input type="checkbox" name="manga_artists[]" value="<?php echo esc_attr($artist->term_id); ?>" <?php checked(in_array($artist->term_id, $manga_artists), true); ?>>
                                    <?php echo esc_html($artist->name); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 10px;">
                        <input type="text" id="new_artist" class="manga-form-control" placeholder="<?php _e('Add new artist', 'manga-admin-panel'); ?>">
                        <button type="button" id="add_artist" class="manga-btn manga-btn-secondary" style="margin-top: 5px;"><?php _e('Add', 'manga-admin-panel'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Images Tab -->
        <div class="manga-admin-tab-pane" id="manga-images">
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Cover Image', 'manga-admin-panel'); ?></label>
                <div class="manga-file-upload">
                    <input type="file" id="cover-image-upload" name="cover_image" accept="image/*" style="display: none;">
                    <div class="manga-file-upload-icon">
                        <i class="feather-upload"></i>
                    </div>
                    <p><?php _e('Click to upload or drag & drop', 'manga-admin-panel'); ?></p>
                </div>
                
                <div id="cover-image-preview" style="margin-top: 15px; max-width: 200px;">
                    <?php 
                    if ($is_edit) {
                        $thumbnail_id = get_post_thumbnail_id($manga_id);
                        if ($thumbnail_id) {
                            $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'medium');
                            echo '<img src="' . esc_url($thumbnail_url) . '" alt="Cover Preview" />';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <?php if (class_exists('WP_MANGA')): ?>
            <div class="manga-form-group">
                <label class="manga-form-label"><?php _e('Manga Gallery', 'manga-admin-panel'); ?></label>
                <p><?php _e('Upload additional manga images for the gallery.', 'manga-admin-panel'); ?></p>
                
                <div class="manga-file-upload">
                    <input type="file" id="gallery-images-upload" name="gallery_images[]" accept="image/*" multiple style="display: none;">
                    <div class="manga-file-upload-icon">
                        <i class="feather-images"></i>
                    </div>
                    <p><?php _e('Click to upload multiple images or drag & drop', 'manga-admin-panel'); ?></p>
                </div>
                
                <div id="gallery-images-preview" style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php 
                    // Show existing gallery images if editing
                    if ($is_edit && function_exists('get_manga_gallery')) {
                        $gallery = get_manga_gallery($manga_id);
                        if ($gallery && !empty($gallery)) {
                            foreach ($gallery as $image) {
                                echo '<div class="gallery-image-item" style="width: 100px; height: 100px; position: relative;">';
                                echo '<img src="' . esc_url($image['url']) . '" alt="" style="width: 100%; height: 100%; object-fit: cover;">';
                                echo '<button type="button" class="remove-gallery-image manga-btn manga-btn-danger manga-btn-sm" data-id="' . esc_attr($image['id']) . '" style="position: absolute; top: 5px; right: 5px; padding: 2px 5px;">×</button>';
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="manga-form-actions" style="margin-top: 30px; text-align: right;">
            <a href="<?php echo esc_url(remove_query_arg(array('view', 'id'))); ?>" class="manga-btn manga-btn-secondary"><?php _e('Cancel', 'manga-admin-panel'); ?></a>
            <button type="submit" class="manga-btn manga-btn-primary"><?php echo $is_edit ? __('Update Manga', 'manga-admin-panel') : __('Create Manga', 'manga-admin-panel'); ?></button>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle new tag/author/artist creation
    $('#add_tag').on('click', function() {
        const newTag = $('#new_tag').val().trim();
        if (newTag !== '') {
            addNewTerm(newTag, 'wp-manga-tag', 'manga_tags');
        }
    });
    
    $('#add_author').on('click', function() {
        const newAuthor = $('#new_author').val().trim();
        if (newAuthor !== '') {
            addNewTerm(newAuthor, 'wp-manga-author', 'manga_authors');
        }
    });
    
    $('#add_artist').on('click', function() {
        const newArtist = $('#new_artist').val().trim();
        if (newArtist !== '') {
            addNewTerm(newArtist, 'wp-manga-artist', 'manga_artists');
        }
    });
    
    function addNewTerm(termName, taxonomy, inputName) {
        $.ajax({
            url: mangaAdminVars.ajaxurl,
            type: 'POST',
            data: {
                action: 'manga_admin_add_term',
                term_name: termName,
                taxonomy: taxonomy,
                nonce: mangaAdminVars.nonce
            },
            success: function(response) {
                if (response.success) {
                    const termId = response.data.term_id;
                    const termName = response.data.term_name;
                    
                    // Add new checkbox
                    const checkboxHtml = `
                        <div class="manga-checkbox-item">
                            <label>
                                <input type="checkbox" name="${inputName}[]" value="${termId}" checked>
                                ${termName}
                            </label>
                        </div>
                    `;
                    
                    // Add to the appropriate checkbox group
                    $(`.manga-checkbox-group input[name="${inputName}[]"]`).first().closest('.manga-checkbox-group').append(checkboxHtml);
                    
                    // Clear input
                    $(`#new_${taxonomy.replace('wp-manga-', '')}`).val('');
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert(mangaAdminVars.i18n.error);
            }
        });
    }
    
    // Close preview button
    $('#close-preview').on('click', function() {
        $('#manga-preview-container').slideUp();
        $('#toggle-preview').text('<?php _e('Show Preview', 'manga-admin-panel'); ?>');
    });
    
    // Gallery image preview
    $('#gallery-images-upload').on('change', function() {
        const files = this.files;
        const previewContainer = $('#gallery-images-preview');
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const imageItem = $(`
                    <div class="gallery-image-item" style="width: 100px; height: 100px; position: relative;">
                        <img src="${e.target.result}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                        <button type="button" class="remove-preview-image manga-btn manga-btn-danger manga-btn-sm" style="position: absolute; top: 5px; right: 5px; padding: 2px 5px;">×</button>
                    </div>
                `);
                
                previewContainer.append(imageItem);
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    // Remove gallery preview image
    $(document).on('click', '.remove-preview-image', function() {
        $(this).closest('.gallery-image-item').remove();
    });
    
    // Remove existing gallery image
    $(document).on('click', '.remove-gallery-image', function() {
        const imageId = $(this).data('id');
        const button = $(this);
        
        if (confirm(mangaAdminVars.i18n.confirm_delete)) {
            $.ajax({
                url: mangaAdminVars.ajaxurl,
                type: 'POST',
                data: {
                    action: 'manga_admin_remove_gallery_image',
                    image_id: imageId,
                    manga_id: '<?php echo $is_edit ? esc_js($manga_id) : ''; ?>',
                    nonce: mangaAdminVars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        button.closest('.gallery-image-item').remove();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(mangaAdminVars.i18n.error);
                }
            });
        }
    });
});
</script>
