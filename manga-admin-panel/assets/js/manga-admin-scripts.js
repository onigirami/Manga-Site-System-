/**
 * Manga Admin Panel Scripts
 * JavaScript functionality for the manga management interface
 */

(function($) {
    'use strict';
    
    // Initialize the application when document is ready
    $(document).ready(function() {
        MangaAdmin.init();
    });
    
    // Main application object
    const MangaAdmin = {
        // Initialize the application
        init: function() {
            this.initTabs();
            this.initForms();
            this.initUploaders();
            this.initPreview();
            this.initChapterManager();
            this.initScheduler();
            this.initCustomFields();
            this.initSearch();
            this.setupAjaxHandlers();
        },
        
        // Tab navigation functionality
        initTabs: function() {
            $('.manga-admin-tab').on('click', function() {
                const targetTab = $(this).data('tab');
                
                // Update active tab
                $('.manga-admin-tab').removeClass('active');
                $(this).addClass('active');
                
                // Show target tab content
                $('.manga-admin-tab-pane').removeClass('active');
                $('#' + targetTab).addClass('active');
                
                // Update URL hash
                window.location.hash = targetTab;
            });
            
            // Check for hash in URL and activate corresponding tab
            if (window.location.hash) {
                const tabId = window.location.hash.substring(1);
                $('.manga-admin-tab[data-tab="' + tabId + '"]').click();
            } else {
                // Activate first tab by default
                $('.manga-admin-tab:first').click();
            }
        },
        
        // Form handling
        initForms: function() {
            // Manga creation/edit form
            $('#manga-form').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitButton = $(this).find('button[type="submit"]');
                const originalText = submitButton.text();
                submitButton.prop('disabled', true).text(mangaAdminVars.i18n.saving);
                
                // Get form data
                const formData = new FormData(this);
                formData.append('action', 'manga_admin_save_manga');
                formData.append('nonce', mangaAdminVars.nonce);
                
                // Submit form via AJAX
                $.ajax({
                    url: mangaAdminVars.ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            MangaAdmin.showNotification('success', response.data.message);
                            
                            // Redirect after successful save if ID is provided
                            if (response.data.manga_id) {
                                setTimeout(function() {
                                    window.location.href = '?page=manga-admin&view=edit&id=' + response.data.manga_id;
                                }, 1000);
                            }
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
            
            // Chapter form
            $('#chapter-form').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitButton = $(this).find('button[type="submit"]');
                const originalText = submitButton.text();
                submitButton.prop('disabled', true).text(mangaAdminVars.i18n.saving);
                
                // Get form data
                const formData = new FormData(this);
                formData.append('action', 'manga_admin_save_chapter');
                formData.append('nonce', mangaAdminVars.nonce);
                
                // Submit form via AJAX
                $.ajax({
                    url: mangaAdminVars.ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            MangaAdmin.showNotification('success', response.data.message);
                            
                            // Refresh chapter list
                            MangaAdmin.loadChapterList(formData.get('manga_id'));
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
            
            // Custom fields form
            $('#custom-fields-form').on('submit', function(e) {
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
                    data: formData + '&action=manga_admin_save_custom_fields&nonce=' + mangaAdminVars.nonce,
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
            
            // Delete actions
            $(document).on('click', '.delete-manga', function(e) {
                e.preventDefault();
                
                if (confirm(mangaAdminVars.i18n.confirm_delete)) {
                    const mangaId = $(this).data('id');
                    
                    $.ajax({
                        url: mangaAdminVars.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'manga_admin_delete_manga',
                            manga_id: mangaId,
                            nonce: mangaAdminVars.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                MangaAdmin.showNotification('success', response.data.message);
                                // Remove item from list
                                $('#manga-card-' + mangaId).fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                MangaAdmin.showNotification('error', response.data.message);
                            }
                        },
                        error: function() {
                            MangaAdmin.showNotification('error', mangaAdminVars.i18n.error);
                        }
                    });
                }
            });
            
            // Delete chapter actions
            $(document).on('click', '.delete-chapter', function(e) {
                e.preventDefault();
                
                if (confirm(mangaAdminVars.i18n.confirm_delete)) {
                    const chapterId = $(this).data('id');
                    const mangaId = $(this).data('manga');
                    
                    $.ajax({
                        url: mangaAdminVars.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'manga_admin_delete_chapter',
                            chapter_id: chapterId,
                            manga_id: mangaId,
                            nonce: mangaAdminVars.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                MangaAdmin.showNotification('success', response.data.message);
                                // Remove chapter from list
                                $('#chapter-item-' + chapterId).fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                MangaAdmin.showNotification('error', response.data.message);
                            }
                        },
                        error: function() {
                            MangaAdmin.showNotification('error', mangaAdminVars.i18n.error);
                        }
                    });
                }
            });
        },
        
        // File uploaders
        initUploaders: function() {
            // Cover image uploader
            if ($('#cover-image-upload').length) {
                $('#cover-image-upload').on('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            $('#cover-image-preview').html('<img src="' + e.target.result + '" alt="Cover Preview" />');
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Chapter images uploader
            if ($('#chapter-images-upload').length) {
                $('#chapter-images-upload').on('change', function() {
                    const files = this.files;
                    const uploadList = $('#chapter-upload-list');
                    uploadList.empty();
                    
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const item = $('<div class="manga-upload-item"></div>');
                        item.append('<span>' + file.name + '</span>');
                        item.append('<div class="manga-upload-progress"><div class="manga-upload-progress-bar" style="width:0%"></div></div>');
                        uploadList.append(item);
                    }
                });
            }
            
            // Drag and drop area
            $('.manga-file-upload').each(function() {
                const dropArea = $(this);
                const input = dropArea.find('input[type="file"]');
                
                // Prevent default behaviors
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropArea.on(eventName, function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                    });
                });
                
                // Highlight drop area on drag over
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropArea.on(eventName, function() {
                        dropArea.addClass('highlighted');
                    });
                });
                
                // Remove highlight on drag leave
                ['dragleave', 'drop'].forEach(eventName => {
                    dropArea.on(eventName, function() {
                        dropArea.removeClass('highlighted');
                    });
                });
                
                // Handle dropped files
                dropArea.on('drop', function(e) {
                    const dt = e.originalEvent.dataTransfer;
                    const files = dt.files;
                    input[0].files = files;
                    
                    // Trigger change event
                    input.trigger('change');
                });
                
                // Click to select files
                dropArea.on('click', function() {
                    input.click();
                });
            });
        },
        
        // Live preview functionality
        initPreview: function() {
            // Live preview toggle
            $('#toggle-preview').on('click', function() {
                const previewContainer = $('#manga-preview-container');
                
                if (previewContainer.is(':visible')) {
                    previewContainer.slideUp();
                    $(this).text('Show Preview');
                } else {
                    // Update preview content
                    MangaAdmin.updatePreview();
                    previewContainer.slideDown();
                    $(this).text('Hide Preview');
                }
            });
            
            // Update preview when form fields change
            $('.preview-field').on('input change', MangaAdmin.debounce(function() {
                if ($('#manga-preview-container').is(':visible')) {
                    MangaAdmin.updatePreview();
                }
            }, 500));
        },
        
        // Update preview content
        updatePreview: function() {
            const title = $('#manga_title').val() || 'Manga Title';
            const description = $('#manga_description').val() || 'No description available.';
            const status = $('#manga_status').val();
            
            let statusText = '';
            if (status === 'publish') {
                statusText = '<span class="chapter-status published">Published</span>';
            } else if (status === 'draft') {
                statusText = '<span class="chapter-status draft">Draft</span>';
            } else if (status === 'scheduled') {
                statusText = '<span class="chapter-status scheduled">Scheduled</span>';
            }
            
            const previewContent = `
                <h2>${title} ${statusText}</h2>
                <div class="manga-preview-description">${description}</div>
            `;
            
            $('#manga-preview-content').html(previewContent);
        },
        
        // Chapter management
        initChapterManager: function() {
            // Load chapters when manga is selected
            $('#manga_id').on('change', function() {
                const mangaId = $(this).val();
                if (mangaId) {
                    MangaAdmin.loadChapterList(mangaId);
                } else {
                    $('#chapter-list').html('<div class="manga-empty-state"><p class="manga-empty-text">Select a manga to view chapters</p></div>');
                }
            });
            
            // Edit chapter
            $(document).on('click', '.edit-chapter', function(e) {
                e.preventDefault();
                
                const chapterId = $(this).data('id');
                const mangaId = $(this).data('manga');
                
                // Load chapter data
                $.ajax({
                    url: mangaAdminVars.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'manga_admin_get_chapter',
                        chapter_id: chapterId,
                        manga_id: mangaId,
                        nonce: mangaAdminVars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Populate form with chapter data
                            $('#chapter_id').val(chapterId);
                            $('#chapter_title').val(response.data.title);
                            $('#chapter_number').val(response.data.number);
                            $('#chapter_status').val(response.data.status);
                            
                            // Show edit form
                            $('#chapter-form-container').slideDown();
                            $('#chapter-form-title').text('Edit Chapter');
                            
                            // Scroll to form
                            $('html, body').animate({
                                scrollTop: $('#chapter-form-container').offset().top - 50
                            }, 500);
                        } else {
                            MangaAdmin.showNotification('error', response.data.message);
                        }
                    },
                    error: function() {
                        MangaAdmin.showNotification('error', mangaAdminVars.i18n.error);
                    }
                });
            });
            
            // New chapter button
            $('#new-chapter').on('click', function() {
                // Reset form
                $('#chapter-form')[0].reset();
                $('#chapter_id').val('');
                
                // Show form
                $('#chapter-form-container').slideDown();
                $('#chapter-form-title').text('New Chapter');
                
                // Scroll to form
                $('html, body').animate({
                    scrollTop: $('#chapter-form-container').offset().top - 50
                }, 500);
            });
            
            // Cancel button
            $('#cancel-chapter').on('click', function(e) {
                e.preventDefault();
                $('#chapter-form-container').slideUp();
            });
        },
        
        // Load chapter list
        loadChapterList: function(mangaId) {
            const chapterList = $('#chapter-list');
            
            // Show loading
            chapterList.html('<div class="manga-loading"><div class="manga-spinner"></div> Loading chapters...</div>');
            
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
                        if (response.data.chapters.length > 0) {
                            let html = '';
                            
                            response.data.chapters.forEach(function(chapter) {
                                let statusClass = '';
                                let statusText = '';
                                
                                if (chapter.status === 'publish') {
                                    statusClass = 'published';
                                    statusText = 'Published';
                                } else if (chapter.status === 'draft') {
                                    statusClass = 'draft';
                                    statusText = 'Draft';
                                } else if (chapter.status === 'future') {
                                    statusClass = 'scheduled';
                                    statusText = 'Scheduled';
                                }
                                
                                html += `
                                    <div id="chapter-item-${chapter.id}" class="chapter-item">
                                        <div class="chapter-number">${chapter.number}</div>
                                        <div class="chapter-title">${chapter.title}</div>
                                        <div class="chapter-status ${statusClass}">${statusText}</div>
                                        <div class="chapter-actions">
                                            <button class="manga-btn manga-btn-secondary manga-btn-sm edit-chapter" data-id="${chapter.id}" data-manga="${mangaId}">Edit</button>
                                            <button class="manga-btn manga-btn-danger manga-btn-sm delete-chapter" data-id="${chapter.id}" data-manga="${mangaId}">Delete</button>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            chapterList.html(html);
                        } else {
                            chapterList.html('<div class="manga-empty-state"><p class="manga-empty-text">No chapters found</p></div>');
                        }
                    } else {
                        chapterList.html('<div class="manga-alert manga-alert-danger">' + response.data.message + '</div>');
                    }
                },
                error: function() {
                    chapterList.html('<div class="manga-alert manga-alert-danger">' + mangaAdminVars.i18n.error + '</div>');
                }
            });
        },
        
        // Chapter scheduler
        initScheduler: function() {
            if (!$('#scheduler-tab').length) {
                return;
            }
            
            // Initialize datetime pickers
            $('.datetime-picker').each(function() {
                // Note: This is a placeholder. In reality, we'd use a real datetime picker library
                $(this).attr('type', 'datetime-local');
            });
            
            // Load scheduled chapters
            $('#load-schedule').on('click', function() {
                MangaAdmin.loadSchedule();
            });
            
            // Schedule form
            $('#schedule-form').on('submit', function(e) {
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
                    data: formData + '&action=manga_admin_schedule_chapter&nonce=' + mangaAdminVars.nonce,
                    success: function(response) {
                        if (response.success) {
                            MangaAdmin.showNotification('success', response.data.message);
                            
                            // Reload schedule
                            MangaAdmin.loadSchedule();
                            
                            // Reset form
                            $('#schedule-form')[0].reset();
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
        },
        
        // Load scheduled chapters
        loadSchedule: function() {
            const scheduleList = $('#schedule-list');
            
            // Show loading
            scheduleList.html('<div class="manga-loading"><div class="manga-spinner"></div> Loading scheduled chapters...</div>');
            
            // Load schedule via AJAX
            $.ajax({
                url: mangaAdminVars.ajaxurl,
                type: 'POST',
                data: {
                    action: 'manga_admin_get_schedule',
                    nonce: mangaAdminVars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.schedules.length > 0) {
                            let html = '';
                            
                            response.data.schedules.forEach(function(schedule) {
                                const isPast = new Date(schedule.date) < new Date();
                                const statusClass = isPast ? 'past' : 'future';
                                
                                html += `
                                    <div class="scheduler-item ${statusClass}">
                                        <div class="scheduler-header">
                                            <h4 class="scheduler-title">${schedule.manga}: ${schedule.chapter}</h4>
                                            <div class="scheduler-date">${schedule.date}</div>
                                        </div>
                                        <div class="scheduler-actions">
                                            <button class="manga-btn manga-btn-secondary manga-btn-sm edit-schedule" data-id="${schedule.id}">Edit</button>
                                            <button class="manga-btn manga-btn-danger manga-btn-sm delete-schedule" data-id="${schedule.id}">Delete</button>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            scheduleList.html(html);
                        } else {
                            scheduleList.html('<div class="manga-empty-state"><p class="manga-empty-text">No scheduled chapters found</p></div>');
                        }
                    } else {
                        scheduleList.html('<div class="manga-alert manga-alert-danger">' + response.data.message + '</div>');
                    }
                },
                error: function() {
                    scheduleList.html('<div class="manga-alert manga-alert-danger">' + mangaAdminVars.i18n.error + '</div>');
                }
            });
        },
        
        // Custom fields
        initCustomFields: function() {
            if (!$('#custom-fields-tab').length) {
                return;
            }
            
            // Load manga custom fields
            $('#manga_id_fields').on('change', function() {
                const mangaId = $(this).val();
                if (mangaId) {
                    MangaAdmin.loadCustomFields(mangaId);
                } else {
                    $('#custom-fields-container').html('<div class="manga-empty-state"><p class="manga-empty-text">Select a manga to view custom fields</p></div>');
                }
            });
            
            // Add new custom field
            $('#add-custom-field').on('click', function() {
                const fieldContainer = $('#custom-fields-list');
                const fieldCount = fieldContainer.find('.custom-field-group').length;
                
                const newField = `
                    <div class="custom-field-group">
                        <div class="manga-form-group">
                            <label class="manga-form-label">Field Name</label>
                            <input type="text" name="custom_fields[${fieldCount}][name]" class="manga-form-control" required>
                        </div>
                        <div class="manga-form-group">
                            <label class="manga-form-label">Field Value</label>
                            <input type="text" name="custom_fields[${fieldCount}][value]" class="manga-form-control">
                        </div>
                        <button type="button" class="manga-btn manga-btn-danger remove-field">Remove</button>
                    </div>
                `;
                
                fieldContainer.append(newField);
            });
            
            // Remove custom field
            $(document).on('click', '.remove-field', function() {
                $(this).closest('.custom-field-group').remove();
            });
        },
        
        // Load custom fields
        loadCustomFields: function(mangaId) {
            const fieldsContainer = $('#custom-fields-container');
            
            // Show loading
            fieldsContainer.html('<div class="manga-loading"><div class="manga-spinner"></div> Loading custom fields...</div>');
            
            // Load fields via AJAX
            $.ajax({
                url: mangaAdminVars.ajaxurl,
                type: 'POST',
                data: {
                    action: 'manga_admin_get_custom_fields',
                    manga_id: mangaId,
                    nonce: mangaAdminVars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        if (Object.keys(response.data.fields).length > 0) {
                            let html = '<div id="custom-fields-list">';
                            let index = 0;
                            
                            for (const [name, value] of Object.entries(response.data.fields)) {
                                html += `
                                    <div class="custom-field-group">
                                        <div class="manga-form-group">
                                            <label class="manga-form-label">Field Name</label>
                                            <input type="text" name="custom_fields[${index}][name]" value="${name}" class="manga-form-control" required>
                                        </div>
                                        <div class="manga-form-group">
                                            <label class="manga-form-label">Field Value</label>
                                            <input type="text" name="custom_fields[${index}][value]" value="${value}" class="manga-form-control">
                                        </div>
                                        <button type="button" class="manga-btn manga-btn-danger remove-field">Remove</button>
                                    </div>
                                `;
                                index++;
                            }
                            
                            html += '</div>';
                            html += '<button type="button" id="add-custom-field" class="manga-btn manga-btn-secondary">Add Field</button>';
                            html += '<button type="submit" class="manga-btn manga-btn-primary">Save Fields</button>';
                            
                            // Add form wrapping the fields
                            html = `
                                <form id="custom-fields-form">
                                    <input type="hidden" name="manga_id" value="${mangaId}">
                                    ${html}
                                </form>
                            `;
                            
                            fieldsContainer.html(html);
                        } else {
                            let html = `
                                <form id="custom-fields-form">
                                    <input type="hidden" name="manga_id" value="${mangaId}">
                                    <div id="custom-fields-list"></div>
                                    <button type="button" id="add-custom-field" class="manga-btn manga-btn-secondary">Add Field</button>
                                    <button type="submit" class="manga-btn manga-btn-primary">Save Fields</button>
                                </form>
                            `;
                            
                            fieldsContainer.html(html);
                        }
                        
                        // Reinitialize form handlers
                        MangaAdmin.initForms();
                    } else {
                        fieldsContainer.html('<div class="manga-alert manga-alert-danger">' + response.data.message + '</div>');
                    }
                },
                error: function() {
                    fieldsContainer.html('<div class="manga-alert manga-alert-danger">' + mangaAdminVars.i18n.error + '</div>');
                }
            });
        },
        
        // Search and filters
        initSearch: function() {
            // Search manga
            $('#manga-search').on('input', MangaAdmin.debounce(function() {
                const searchTerm = $(this).val();
                MangaAdmin.searchManga(searchTerm);
            }, 500));
            
            // Filter by status
            $('#manga-status-filter').on('change', function() {
                const status = $(this).val();
                MangaAdmin.filterMangaByStatus(status);
            });
        },
        
        // Search manga by title
        searchManga: function(term) {
            // Filter manga cards by title
            if (term === '') {
                $('.manga-card').show();
            } else {
                $('.manga-card').each(function() {
                    const title = $(this).find('.manga-card-title').text().toLowerCase();
                    if (title.includes(term.toLowerCase())) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
            
            // Check if no results
            const visibleItems = $('.manga-card:visible').length;
            if (visibleItems === 0) {
                if ($('#no-results').length === 0) {
                    $('.manga-grid').append('<div id="no-results" class="manga-empty-state"><p class="manga-empty-text">No manga found matching your search</p></div>');
                }
            } else {
                $('#no-results').remove();
            }
        },
        
        // Filter manga by status
        filterMangaByStatus: function(status) {
            if (status === 'all') {
                $('.manga-card').show();
            } else {
                $('.manga-card').each(function() {
                    const cardStatus = $(this).data('status');
                    if (cardStatus === status) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
            
            // Check if no results
            const visibleItems = $('.manga-card:visible').length;
            if (visibleItems === 0) {
                if ($('#no-results').length === 0) {
                    $('.manga-grid').append('<div id="no-results" class="manga-empty-state"><p class="manga-empty-text">No manga found with the selected status</p></div>');
                }
            } else {
                $('#no-results').remove();
            }
        },
        
        // Setup AJAX handlers
        setupAjaxHandlers: function() {
            // AJAX loading of manga list
            if ($('#manga-list-container').length) {
                MangaAdmin.loadMangaList();
            }
            
            // Ajaxify pagination
            $(document).on('click', '.manga-pagination a', function(e) {
                e.preventDefault();
                
                const page = $(this).data('page');
                MangaAdmin.loadMangaList(page);
            });
        },
        
        // Load manga list
        loadMangaList: function(page = 1) {
            const container = $('#manga-list-container');
            
            // Show loading
            container.html('<div class="manga-loading"><div class="manga-spinner"></div> Loading manga...</div>');
            
            // Load manga via AJAX
            $.ajax({
                url: mangaAdminVars.ajaxurl,
                type: 'POST',
                data: {
                    action: 'manga_admin_get_manga_list',
                    page: page,
                    nonce: mangaAdminVars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.manga.length > 0) {
                            let html = '<div class="manga-grid">';
                            
                            response.data.manga.forEach(function(manga) {
                                let statusClass = '';
                                let statusText = '';
                                
                                if (manga.status === 'publish') {
                                    statusClass = 'published';
                                    statusText = 'Published';
                                } else if (manga.status === 'draft') {
                                    statusClass = 'draft';
                                    statusText = 'Draft';
                                } else if (manga.status === 'future') {
                                    statusClass = 'scheduled';
                                    statusText = 'Scheduled';
                                }
                                
                                html += `
                                    <div id="manga-card-${manga.id}" class="manga-card" data-status="${manga.status}">
                                        <div class="manga-card-thumbnail">
                                            <img src="${manga.cover}" alt="${manga.title}">
                                            <div class="manga-card-status ${statusClass}">${statusText}</div>
                                        </div>
                                        <div class="manga-card-content">
                                            <div class="manga-card-header">
                                                <h3 class="manga-card-title">${manga.title}</h3>
                                                <div class="manga-card-actions">
                                                    <a href="#" class="manga-btn manga-btn-secondary manga-btn-icon manga-tooltip" data-tooltip="Edit" data-id="${manga.id}">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <a href="#" class="manga-btn manga-btn-danger manga-btn-icon manga-tooltip delete-manga" data-tooltip="Delete" data-id="${manga.id}">
                                                        <i class="feather-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="manga-card-meta">
                                                <span>${manga.chapters} chapters</span>
                                                <span>${manga.date}</span>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            html += '</div>';
                            
                            // Add pagination
                            if (response.data.total_pages > 1) {
                                html += '<div class="manga-pagination">';
                                
                                for (let i = 1; i <= response.data.total_pages; i++) {
                                    const activeClass = i === page ? 'active' : '';
                                    html += `<a href="#" class="manga-pagination-link ${activeClass}" data-page="${i}">${i}</a>`;
                                }
                                
                                html += '</div>';
                            }
                            
                            container.html(html);
                        } else {
                            container.html('<div class="manga-empty-state"><p class="manga-empty-text">No manga found</p></div>');
                        }
                    } else {
                        container.html('<div class="manga-alert manga-alert-danger">' + response.data.message + '</div>');
                    }
                },
                error: function() {
                    container.html('<div class="manga-alert manga-alert-danger">' + mangaAdminVars.i18n.error + '</div>');
                }
            });
        },
        
        // Show notification
        showNotification: function(type, message) {
            const alertClass = type === 'success' ? 'manga-alert-success' : 'manga-alert-danger';
            
            // Create notification
            const notification = $(`<div class="manga-alert ${alertClass}">${message}</div>`);
            
            // Append to container
            if ($('#manga-notifications').length === 0) {
                $('body').append('<div id="manga-notifications" style="position:fixed;top:20px;right:20px;z-index:9999;"></div>');
            }
            
            $('#manga-notifications').append(notification);
            
            // Auto-remove after delay
            setTimeout(function() {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        // Utility: Debounce function to limit frequency of function calls
        debounce: function(func, wait) {
            let timeout;
            return function() {
                const context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        }
    };
    
})(jQuery);
