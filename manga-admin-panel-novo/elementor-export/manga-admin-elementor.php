<?php
/**
 * Manga Admin Elementor Export
 * 
 * Template file for Elementor integration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="elementor-manga-admin-template" 
     data-template-name="Manga Admin Panel"
     data-template-type="page"
     data-template-description="A complete manga management panel for privileged users to manage manga content."
     data-template-version="1.0">
    
    <!-- Elementor Template Structure -->
    <div class="elementor-section-wrap">
        
        <!-- Header Section -->
        <section class="elementor-section elementor-top-section elementor-element" data-element_type="section" data-settings='{"background_background":"classic"}'>
            <div class="elementor-container elementor-column-gap-default">
                <div class="elementor-row">
                    <div class="elementor-column elementor-col-100 elementor-top-column elementor-element" data-element_type="column">
                        <div class="elementor-column-wrap elementor-element-populated">
                            <div class="elementor-widget-wrap">
                                <div class="elementor-element elementor-widget elementor-widget-heading" data-element_type="widget" data-widget_type="heading.default">
                                    <div class="elementor-widget-container">
                                        <h1 class="elementor-heading-title elementor-size-default">Manga Admin Panel</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Main Content Section -->
        <section class="elementor-section elementor-top-section elementor-element" data-element_type="section">
            <div class="elementor-container elementor-column-gap-default">
                <div class="elementor-row">
                    <div class="elementor-column elementor-col-100 elementor-top-column elementor-element" data-element_type="column">
                        <div class="elementor-column-wrap elementor-element-populated">
                            <div class="elementor-widget-wrap">
                                <!-- Manga Admin Panel Widget -->
                                <div class="elementor-element elementor-widget elementor-widget-manga_admin_panel" data-element_type="widget" data-widget_type="manga_admin_panel.default">
                                    <div class="elementor-widget-container">
                                        <!-- Manga Admin Panel Widget Settings -->
                                        <div class="elementor-widget-settings" style="display:none;">
                                            <ul>
                                                <li data-setting="show_title" data-value="yes"></li>
                                                <li data-setting="title_text" data-value="Manga Admin Panel"></li>
                                                <li data-setting="default_tab" data-value="manga-list"></li>
                                                <li data-setting="access_level" data-value="manga_editor"></li>
                                                <li data-setting="primary_color" data-value="#ff6b6b"></li>
                                                <li data-setting="background_color" data-value="#f7f7f7"></li>
                                                <li data-setting="items_per_page" data-value="12"></li>
                                                <li data-setting="enable_cache" data-value="yes"></li>
                                                <li data-setting="cache_timeout" data-value="15"></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Manga Dashboard Widget -->
                                <div class="elementor-element elementor-widget elementor-widget-manga_dashboard" data-element_type="widget" data-widget_type="manga_dashboard.default">
                                    <div class="elementor-widget-container">
                                        <!-- Manga Dashboard Widget Settings -->
                                        <div class="elementor-widget-settings" style="display:none;">
                                            <ul>
                                                <li data-setting="show_title" data-value="yes"></li>
                                                <li data-setting="title_text" data-value="Meus Mangás"></li>
                                                <li data-setting="show_stats" data-value="yes"></li>
                                                <li data-setting="limit" data-value="10"></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Manga Upload Widget -->
                                <div class="elementor-element elementor-widget elementor-widget-manga_upload" data-element_type="widget" data-widget_type="manga_upload.default">
                                    <div class="elementor-widget-container">
                                        <!-- Manga Upload Widget Settings -->
                                        <div class="elementor-widget-settings" style="display:none;">
                                            <ul>
                                                <li data-setting="show_title" data-value="yes"></li>
                                                <li data-setting="title_text" data-value="Enviar Capítulos"></li>
                                                <li data-setting="enable_zip" data-value="yes"></li>
                                                <li data-setting="enable_bulk" data-value="yes"></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Footer Section -->
        <section class="elementor-section elementor-top-section elementor-element" data-element_type="section" data-settings='{"background_background":"classic"}'>
            <div class="elementor-container elementor-column-gap-default">
                <div class="elementor-row">
                    <div class="elementor-column elementor-col-100 elementor-top-column elementor-element" data-element_type="column">
                        <div class="elementor-column-wrap elementor-element-populated">
                            <div class="elementor-widget-wrap">
                                <div class="elementor-element elementor-widget elementor-widget-text-editor" data-element_type="widget" data-widget_type="text-editor.default">
                                    <div class="elementor-widget-container">
                                        <div class="elementor-text-editor elementor-clearfix">
                                            <p style="text-align: center;">© <?php echo date('Y'); ?> Manga Admin Panel - Powered by WordPress & Elementor</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
    </div>
</div>

<!-- Import Instructions -->
<div class="elementor-manga-admin-instructions" style="display:none;">
    <h2>Manga Admin Panel Template</h2>
    <p>This template provides a complete manga management interface for privileged users to manage manga content.</p>
    
    <h3>Requirements:</h3>
    <ul>
        <li>WordPress 5.6 or higher</li>
        <li>Elementor 3.0 or higher</li>
        <li>Manga Admin Panel plugin</li>
        <li>WP Manga Member Upload PRO</li>
        <li>WP Manga Chapter Scheduler</li>
        <li>WP Manga Custom Fields</li>
    </ul>
    
    <h3>Import Instructions:</h3>
    <ol>
        <li>Make sure all required plugins are installed and activated</li>
        <li>Import this template to Elementor</li>
        <li>Create a new page and apply this template</li>
        <li>Adjust widget settings as needed</li>
        <li>Update and publish the page</li>
    </ol>
</div>
