/**
 * Manga Admin Elementor Widget JavaScript
 * JS for the Elementor integrated manga admin panel
 */

(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        // Check if we're in Elementor editor
        const isElementorEditor = typeof elementor !== 'undefined';
        
        // Initialize the manga admin panel
        if (!isElementorEditor) {
            ElementorMangaAdmin.init();
        } else {
            // Show a preview in the editor
            ElementorMangaAdmin.initEditorPreview();
        }
    });
    
    // Main application object for Elementor integration
    const ElementorMangaAdmin = {
        // Initialize the application
        init: function() {
            // Initialize tabs
            this.initTabs();
            
            // Initialize the manga loading functions
            this.initMangaLoading();
            
            // Initialize search and filters
            this.initSearch();
            
            // Set up notification system
            this.initNotifications();
        },
        
        // Initialize editor preview
        initEditorPreview: function() {
            // Show sample content in Elementor editor
            $('.manga-admin-container').each(function() {
                const container = $(this);
                
                // Show sample manga cards
                if (container.find('#manga-list-container').length) {
                    let sampleHtml = '<div class="manga-grid">';
                    
                    for (let i = 1; i <= 6; i++) {
                        sampleHtml += `
                            <div class="manga-card" data-status="publish">
                                <div class="manga-card-thumbnail">
                                    <img src="https://via.placeholder.com/200x300" alt="Sample Manga ${i}">
                                    <div class="manga-card-status published">Published</div>
                                </div>
                                <div class="manga-card-content">
                                    <div class="manga-card-header">
                                        <h3 class="manga-card-title">Sample Manga ${i}</h3>
                                        <div class="manga-card-actions">
                                            <a href="#" class="manga-btn manga-btn-secondary manga-btn-icon manga-tooltip" data-tooltip="Edit">
                                                <i class="eicon-edit"></i>
                                            </a>
                                            <a href="#" class="manga-btn manga-btn-danger manga-btn-icon manga-tooltip" data-tooltip="Delete">
                                                <i class="eicon-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="manga-card-meta">
                                        <span>${Math.floor(Math.random() * 30) + 1} chapters</span>
                                        <span>Today</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    sampleHtml += '</div>';
                    container.find('#manga-list-container').html(sampleHtml);
                }
                
                // Show sample recent manga
                if (container.find('#recently-updated-list').length) {
                    let recentHtml = '';
                    
                    for (let i = 1; i <= 5; i++) {
                        const statuses = ['published', 'draft', 'scheduled'];
                        const randomStatus = statuses[Math.floor(Math.random() * statuses.length)];
                        const statusText = randomStatus.charAt(0).toUpperCase() + randomStatus.slice(1);
                        
                        recentHtml += `
                            <tr>
                                <td><strong>Sample Manga ${i}</strong></td>
                                <td>Chapter ${Math.floor(Math.random() * 30) + 1}</td>
                                <td>${i} day${i !== 1 ? 's' : ''} ago</td>
                                <td><span class="chapter-status ${randomStatus}">${statusText}</span></td>
                                <td>
                                    <a href="#" class="manga-btn manga-btn-secondary manga-btn-sm">Edit</a>
                                    <a href="#" class="manga-btn manga-btn-primary manga-btn-sm">Chapters</a>
                                </td>
                            </tr>
                        `;
                    }
                    
                    container.find('#recently-updated-list').html(recentHtml);
                }
                
                // Show sample statistics
                if (container.find('#statistics-container').length) {
                    let statsHtml = `
                        <div class="manga-stats-grid">
                            <div class="manga-stat-card">
                                <h3 style="margin-top: 0;">Total Manga</h3>
                                <div class="manga-stat-value">15</div>
                            </div>
                            
                            <div class="manga-stat-card">
                                <h3 style="margin-top: 0;">Total Chapters</h3>
                                <div class="manga-stat-value">247</div>
                            </div>
                            
                            <div class="manga-stat-card">
                                <h3 style="margin-top: 0;">Published</h3>
                                <div class="manga-stat-value">12</div>
                            </div>
                            
                            <div class="manga-stat-card">
                                <h3 style="margin-top: 0;">Drafts</h3>
                                <div class="manga-stat-value">3</div>
                            </div>
                        </div>
                        
                        <div class="manga-recent-activity" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <h3>Recent Activity</h3>
                            <ul style="list-style: none; padding: 0;">
                                <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                    <div style="display: flex; justify-content: space-between;">
                                        <span>Sample Manga 1 was updated</span>
                                        <span style="color: #a5b1c2;">Today</span>
                                    </div>
                                </li>
                                <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                    <div style="display: flex; justify-content: space-between;">
                                        <span>New chapter added to Sample Manga 2</span>
                                        <span style="color: #a5b1c2;">1 day ago</span>
                                    </div>
                                </li>
                                <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                    <div style="display: flex; justify-content: space-between;">
                                        <span>Sample Manga 3 was created</span>
                                        <span style="color: #a5b1c2;">2 days ago</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    `;
                    
                    container.find('#statistics-container').html(statsHtml);
                }
            });
        },
        
        // Initialize tabs
        initTabs: function() {
            $('.manga-admin-tab').on('click', function() {
                const targetTab = $(this).data('tab');
                
                // Update active tab
                $('.manga-admin-tab').removeClass('active');
                $(this).addClass('active');
                
                // Show target tab content
                $('.manga-admin-tab-pane').removeClass('active');
                $('#' + targetTab).addClass('active');
                
                // Load data for the active tab if needed
                if (targetTab === 'manga-list') {
                    ElementorMangaAdmin.loadMangaList();
                } else if (targetTab === 'recently-updated') {
                    ElementorMangaAdmin.loadRecentManga();
                } else if (targetTab === 'statistics') {
                    ElementorMangaAdmin.loadStatistics();
                }
            });
        },
        
        // Initialize manga loading functions
        initMangaLoading: function() {
            // Load data for the default active tab
            const activeTab = $('.manga-admin-tab.active').data('tab');
            
            if (activeTab === 'manga-list') {
                this.loadMangaList();
            } else if (activeTab === 'recently-updated') {
                this.loadRecentManga();
            } else if (activeTab === 'statistics') {
                this.loadStatistics();
            }
        },
        
        // Load manga list
        loadMangaList: function(page = 1) {
            const container = $('#manga-list-container');
            
            // Show loading
            container.html('<div class="manga-loading"><div class="manga-spinner"></div> <span>Loading manga...</span></div>');
            
            // Get filters
            const searchTerm = $('#manga-search').val();
            const statusFilter = $('#manga-status-filter').val();
            
            // Get items per page
            const itemsPerPage = $('.manga-admin-container').data('items-per-page') || 12;
            
            // Load manga via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'manga_admin_get_manga_list',
                    nonce: mangaAdminVars.nonce,
                    page: page,
                    per_page: itemsPerPage,
                    search: searchTerm,
                    status: statusFilter
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.manga.length > 0) {
                            let html = '<div class="manga-grid">';
                            
                            response.data.manga.forEach(function(manga) {
                                html += `
                                    <div id="manga-card-${manga.id}" class="manga-card" data-status="${manga.status}">
                                        <div class="manga-card-thumbnail">
                                            <img src="${manga.cover}" alt="${manga.title}">
                                            <div class="manga-card-status ${manga.status === 'publish' ? 'published' : (manga.status === 'future' ? 'scheduled' : 'draft')}">
                                                ${manga.status === 'publish' ? 'Published' : (manga.status === 'future' ? 'Scheduled' : 'Draft')}
                                            </div>
                                        </div>
                                        <div class="manga-card-content">
                                            <div class="manga-card-header">
                                                <h3 class="manga-card-title">${manga.title}</h3>
                                                <div class="manga-card-actions">
                                                    <a href="${mangaAdminVars.adminUrl}?view=edit&id=${manga.id}" class="manga-btn manga-btn-secondary manga-btn-icon manga-tooltip" data-tooltip="Edit">
                                                        <i class="eicon-edit"></i>
                                                    </a>
                                                    <a href="#" class="manga-btn manga-btn-danger manga-btn-icon manga-tooltip delete-manga" data-tooltip="Delete" data-id="${manga.id}">
                                                        <i class="eicon-trash"></i>
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
                                    const activeClass = i === response.data.current_page ? 'active' : '';
                                    html += `<a href="#" class="manga-pagination-link ${activeClass}" data-page="${i}">${i}</a>`;
                                }
                                
                                html += '</div>';
                            }
                            
                            container.html(html);
                            
                            // Add pagination event handlers
                            $('.manga-pagination-link').on('click', function(e) {
                                e.preventDefault();
                                const page = $(this).data('page');
                                ElementorMangaAdmin.loadMangaList(page);
                            });
                            
                            // Add delete manga handlers
                            $('.delete-manga').on('click', function(e) {
                                e.preventDefault();
                                ElementorMangaAdmin.deleteManga($(this).data('id'));
                            });
                        } else {
                            container.html('<div class="manga-empty-state"><div class="manga-empty-icon"><i class="eicon-library-books"></i></div><p class="manga-empty-text">No manga found</p></div>');
                        }
                    } else {
                        container.html(`<div class="manga-alert manga-alert-danger">${response.data.message}</div>`);
                    }
                },
                error: function() {
                    container.html(`<div class="manga-alert manga-alert-danger">An error occurred while loading manga.</div>`);
                }
            });
        },
        
        // Load recent manga
        loadRecentManga: function() {
            const container = $('#recently-updated-list');
            
            // Show loading
            container.html('<tr><td colspan="5" class="manga-loading-cell"><div class="manga-loading"><div class="manga-spinner"></div> <span>Loading recent manga...</span></div></td></tr>');
            
            // Load recent manga via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'manga_admin_get_recent_manga',
                    nonce: mangaAdminVars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.recent_manga.length > 0) {
                            let html = '';
                            
                            response.data.recent_manga.forEach(function(manga) {
                                let statusClass = '';
                                
                                if (manga.status === 'publish') {
                                    statusClass = 'published';
                                } else if (manga.status === 'draft') {
                                    statusClass = 'draft';
                                } else if (manga.status === 'future') {
                                    statusClass = 'scheduled';
                                }
                                
                                html += `
                                    <tr>
                                        <td><strong>${manga.title}</strong></td>
                                        <td>${manga.latest_chapter}</td>
                                        <td>${manga.updated_date}</td>
                                        <td><span class="chapter-status ${statusClass}">${manga.status_text}</span></td>
                                        <td>
                                            <a href="${mangaAdminVars.adminUrl}?view=edit&id=${manga.id}" class="manga-btn manga-btn-secondary manga-btn-sm">Edit</a>
                                            <a href="${mangaAdminVars.adminUrl}?view=chapters&id=${manga.id}" class="manga-btn manga-btn-primary manga-btn-sm">Chapters</a>
                                        </td>
                                    </tr>
                                `;
                            });
                            
                            container.html(html);
                        } else {
                            container.html('<tr><td colspan="5" style="text-align: center;">No recently updated manga found.</td></tr>');
                        }
                    } else {
                        container.html(`<tr><td colspan="5"><div class="manga-alert manga-alert-danger">${response.data.message}</div></td></tr>`);
                    }
                },
                error: function() {
                    container.html(`<tr><td colspan="5"><div class="manga-alert manga-alert-danger">An error occurred while loading recent manga.</div></td></tr>`);
                }
            });
        },
        
        // Load statistics
        loadStatistics: function() {
            const container = $('#statistics-container');
            
            // Show loading
            container.html('<div class="manga-loading"><div class="manga-spinner"></div> <span>Loading statistics...</span></div>');
            
            // Load statistics via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'manga_admin_get_statistics',
                    nonce: mangaAdminVars.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const stats = response.data.stats;
                        const activity = response.data.activity;
                        
                        let html = `
                            <div class="manga-stats-grid">
                                <div class="manga-stat-card">
                                    <h3 style="margin-top: 0;">Total Manga</h3>
                                    <div class="manga-stat-value">${stats.total_manga}</div>
                                </div>
                                
                                <div class="manga-stat-card">
                                    <h3 style="margin-top: 0;">Total Chapters</h3>
                                    <div class="manga-stat-value">${stats.total_chapters}</div>
                                </div>
                                
                                <div class="manga-stat-card">
                                    <h3 style="margin-top: 0;">Published</h3>
                                    <div class="manga-stat-value">${stats.published_manga}</div>
                                </div>
                                
                                <div class="manga-stat-card">
                                    <h3 style="margin-top: 0;">Drafts</h3>
                                    <div class="manga-stat-value">${stats.draft_manga}</div>
                                </div>
                            </div>
                        `;
                        
                        // Activity section
                        html += `
                            <div class="manga-recent-activity" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 20px;">
                                <h3>Recent Activity</h3>
                                <ul style="list-style: none; padding: 0;">
                        `;
                        
                        if (activity && activity.length > 0) {
                            activity.forEach(function(item) {
                                html += `
                                    <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                        <div style="display: flex; justify-content: space-between;">
                                            <span>${item.message}</span>
                                            <span style="color: #a5b1c2;">${item.date}</span>
                                        </div>
                                    </li>
                                `;
                            });
                        } else {
                            html += `<li style="padding: 10px 0;">No recent activity found.</li>`;
                        }
                        
                        html += `
                                </ul>
                            </div>
                        `;
                        
                        // Top manga section if available
                        if (stats.top_manga && stats.top_manga.length > 0) {
                            html += `
                                <div class="manga-popular" style="background-color: #fff; border-radius: 5px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 20px;">
                                    <h3>Most Popular Manga</h3>
                                    <table class="manga-table">
                                        <thead>
                                            <tr>
                                                <th>Manga</th>
                                                <th>Views</th>
                                                <th>Likes</th>
                                                <th>Chapters</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;
                            
                            stats.top_manga.forEach(function(manga) {
                                html += `
                                    <tr>
                                        <td><strong>${manga.title}</strong></td>
                                        <td>${manga.views}</td>
                                        <td>${manga.likes}</td>
                                        <td>${manga.chapters}</td>
                                    </tr>
                                `;
                            });
                            
                            html += `
                                        </tbody>
                                    </table>
                                </div>
                            `;
                        }
                        
                        container.html(html);
                    } else {
                        container.html(`<div class="manga-alert manga-alert-danger">${response.data.message}</div>`);
                    }
                },
                error: function() {
                    container.html(`<div class="manga-alert manga-alert-danger">An error occurred while loading statistics.</div>`);
                }
            });
        },
        
        // Delete manga
        deleteManga: function(mangaId) {
            if (confirm(mangaAdminVars.i18n.confirm_delete)) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'manga_admin_delete_manga',
                        manga_id: mangaId,
                        nonce: mangaAdminVars.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            ElementorMangaAdmin.showNotification('success', response.data.message);
                            // Remove item from list
                            $('#manga-card-' + mangaId).fadeOut(300, function() {
                                $(this).remove();
                                
                                // If no more items, show empty state
                                if ($('.manga-card').length === 0) {
                                    $('#manga-list-container').html('<div class="manga-empty-state"><div class="manga-empty-icon"><i class="eicon-library-books"></i></div><p class="manga-empty-text">No manga found</p></div>');
                                }
                            });
                        } else {
                            ElementorMangaAdmin.showNotification('error', response.data.message);
                        }
                    },
                    error: function() {
                        ElementorMangaAdmin.showNotification('error', 'An error occurred while deleting the manga.');
                    }
                });
            }
        },
        
        // Initialize search and filters
        initSearch: function() {
            // Search manga
            $('#manga-search').on('input', this.debounce(function() {
                const searchTerm = $(this).val();
                if (searchTerm.length >= 2 || searchTerm.length === 0) {
                    ElementorMangaAdmin.loadMangaList(1);
                }
            }, 500));
            
            // Filter by status
            $('#manga-status-filter').on('change', function() {
                ElementorMangaAdmin.loadMangaList(1);
            });
        },
        
        // Initialize notifications system
        initNotifications: function() {
            // Create container if it doesn't exist
            if ($('#manga-notifications').length === 0) {
                $('body').append('<div id="manga-notifications"></div>');
            }
        },
        
        // Show notification
        showNotification: function(type, message) {
            const alertClass = type === 'success' ? 'manga-alert-success' : 'manga-alert-danger';
            
            // Create notification
            const notification = $(`<div class="manga-alert ${alertClass}">${message}</div>`);
            
            // Append to container
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
    
    // Add to global scope for external access
    window.MangaAdmin = ElementorMangaAdmin;
    
})(jQuery);
