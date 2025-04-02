<?php
/**
 * Manga Admin Elementor Widget
 * 
 * Elementor widget for the manga admin panel
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if Elementor is active
if (!did_action('elementor/loaded')) {
    return;
}

/**
 * Manga Admin Elementor Widget class
 */
class Manga_Admin_Elementor_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     *
     * @return string Widget name
     */
    public function get_name() {
        return 'manga_admin_panel';
    }

    /**
     * Get widget title
     *
     * @return string Widget title
     */
    public function get_title() {
        return __('Manga Admin Panel', 'manga-admin-panel');
    }

    /**
     * Get widget icon
     *
     * @return string Widget icon
     */
    public function get_icon() {
        return 'eicon-book';
    }

    /**
     * Get widget categories
     *
     * @return array Widget categories
     */
    public function get_categories() {
        return ['general'];
    }

    /**
     * Get widget keywords
     *
     * @return array Widget keywords
     */
    public function get_keywords() {
        return ['manga', 'admin', 'panel', 'comic', 'manage'];
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'manga-admin-panel'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => __('Show Title', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'manga-admin-panel'),
                'label_off' => __('No', 'manga-admin-panel'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'title_text',
            [
                'label' => __('Title', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Manga Admin Panel', 'manga-admin-panel'),
                'placeholder' => __('Enter your title', 'manga-admin-panel'),
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'default_tab',
            [
                'label' => __('Default Tab', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'manga-list',
                'options' => [
                    'manga-list' => __('Manga List', 'manga-admin-panel'),
                    'recently-updated' => __('Recently Updated', 'manga-admin-panel'),
                    'statistics' => __('Statistics', 'manga-admin-panel'),
                ],
            ]
        );

        $this->add_control(
            'access_level',
            [
                'label' => __('Access Level', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'editor',
                'options' => [
                    'editor' => __('Editor & Admin', 'manga-admin-panel'),
                    'admin' => __('Admin Only', 'manga-admin-panel'),
                    'manga_editor' => __('Manga Editor Role', 'manga-admin-panel'),
                    'custom' => __('Custom Capability', 'manga-admin-panel'),
                ],
            ]
        );

        $this->add_control(
            'custom_capability',
            [
                'label' => __('Custom Capability', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'edit_manga',
                'placeholder' => __('Enter custom capability', 'manga-admin-panel'),
                'condition' => [
                    'access_level' => 'custom',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'manga-admin-panel'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .manga-admin-title' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Title Typography', 'manga-admin-panel'),
                'selector' => '{{WRAPPER}} .manga-admin-title',
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'primary_color',
            [
                'label' => __('Primary Color', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ff6b6b',
                'selectors' => [
                    '{{WRAPPER}} .manga-btn-primary' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .manga-admin-tab.active' => 'border-bottom-color: {{VALUE}}; color: {{VALUE}}',
                    '{{WRAPPER}} .manga-spinner' => 'border-top-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => __('Background Color', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f7f7f7',
                'selectors' => [
                    '{{WRAPPER}} .manga-admin-container' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => __('Border Radius', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .manga-admin-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'label' => __('Box Shadow', 'manga-admin-panel'),
                'selector' => '{{WRAPPER}} .manga-admin-container',
            ]
        );

        $this->end_controls_section();

        // Advanced Section
        $this->start_controls_section(
            'advanced_section',
            [
                'label' => __('Advanced', 'manga-admin-panel'),
                'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
            ]
        );

        $this->add_control(
            'items_per_page',
            [
                'label' => __('Items Per Page', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 4,
                'max' => 60,
                'step' => 4,
            ]
        );

        $this->add_control(
            'enable_cache',
            [
                'label' => __('Enable Cache', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'manga-admin-panel'),
                'label_off' => __('No', 'manga-admin-panel'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'cache_timeout',
            [
                'label' => __('Cache Timeout (minutes)', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 15,
                'min' => 1,
                'max' => 1440,
                'condition' => [
                    'enable_cache' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'custom_css',
            [
                'label' => __('Custom CSS', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::CODE,
                'language' => 'css',
                'rows' => 10,
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Check if user has access to the panel
     *
     * @param string $access_level Access level setting
     * @param string $custom_capability Custom capability if needed
     * @return bool Whether user has access
     */
    private function has_access($access_level, $custom_capability = '') {
        $user = wp_get_current_user();

        switch ($access_level) {
            case 'admin':
                return current_user_can('administrator');
            
            case 'editor':
                return current_user_can('administrator') || current_user_can('editor');
            
            case 'manga_editor':
                return current_user_can('administrator') || current_user_can('editor') || 
                       current_user_can('manga_editor');
            
            case 'custom':
                return current_user_can($custom_capability);
            
            default:
                return current_user_can('administrator') || current_user_can('editor');
        }
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        // Check user access
        if (!$this->has_access($settings['access_level'], $settings['custom_capability'])) {
            echo '<div class="manga-admin-restricted">';
            echo __('You do not have permission to access this panel.', 'manga-admin-panel');
            echo '</div>';
            return;
        }

        // Add custom CSS if provided
        if (!empty($settings['custom_css'])) {
            echo '<style>' . esc_html($settings['custom_css']) . '</style>';
        }

        // Get items per page setting
        $items_per_page = absint($settings['items_per_page']);
        if ($items_per_page < 4) {
            $items_per_page = 12; // Fallback to default
        }

        // Main container
        echo '<div class="manga-admin-container" data-items-per-page="' . esc_attr($items_per_page) . '">';

        // Title if enabled
        if ($settings['show_title'] === 'yes' && !empty($settings['title_text'])) {
            echo '<div class="manga-admin-header">';
            echo '<h1 class="manga-admin-title">' . esc_html($settings['title_text']) . '</h1>';
            echo '<div class="manga-admin-actions">';
            echo '<a href="' . esc_url(add_query_arg('view', 'create', remove_query_arg('id'))) . '" class="manga-btn manga-btn-primary">';
            echo __('Add New Manga', 'manga-admin-panel');
            echo '</a>';
            echo '</div>';
            echo '</div>';
        }

        // Tab navigation
        echo '<div class="manga-admin-tabs">';
        
        $tabs = [
            'manga-list' => __('My Manga', 'manga-admin-panel'),
            'recently-updated' => __('Recently Updated', 'manga-admin-panel'),
            'statistics' => __('Statistics', 'manga-admin-panel'),
        ];

        foreach ($tabs as $tab_id => $tab_label) {
            $active_class = ($tab_id === $settings['default_tab']) ? 'active' : '';
            echo '<div class="manga-admin-tab ' . esc_attr($active_class) . '" data-tab="' . esc_attr($tab_id) . '">' . esc_html($tab_label) . '</div>';
        }
        
        echo '</div>';

        // Tab content
        echo '<div class="manga-admin-content">';
        
        // Manga List Tab
        $manga_list_active = ($settings['default_tab'] === 'manga-list') ? 'active' : '';
        echo '<div class="manga-admin-tab-pane ' . esc_attr($manga_list_active) . '" id="manga-list">';
        
        echo '<div class="manga-search-bar">';
        echo '<input type="text" id="manga-search" class="manga-search-input" placeholder="' . esc_attr__('Search manga...', 'manga-admin-panel') . '">';
        
        echo '<select id="manga-status-filter" class="manga-filter-select">';
        echo '<option value="all">' . esc_html__('All Statuses', 'manga-admin-panel') . '</option>';
        echo '<option value="publish">' . esc_html__('Published', 'manga-admin-panel') . '</option>';
        echo '<option value="draft">' . esc_html__('Draft', 'manga-admin-panel') . '</option>';
        echo '<option value="scheduled">' . esc_html__('Scheduled', 'manga-admin-panel') . '</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div id="manga-list-container">';
        echo '<div class="manga-loading">';
        echo '<div class="manga-spinner"></div>';
        echo '<span>' . esc_html__('Loading manga...', 'manga-admin-panel') . '</span>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        
        // Recently Updated Tab
        $recently_updated_active = ($settings['default_tab'] === 'recently-updated') ? 'active' : '';
        echo '<div class="manga-admin-tab-pane ' . esc_attr($recently_updated_active) . '" id="recently-updated">';
        
        echo '<div class="manga-table-container">';
        echo '<table class="manga-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . esc_html__('Manga', 'manga-admin-panel') . '</th>';
        echo '<th>' . esc_html__('Latest Chapter', 'manga-admin-panel') . '</th>';
        echo '<th>' . esc_html__('Updated', 'manga-admin-panel') . '</th>';
        echo '<th>' . esc_html__('Status', 'manga-admin-panel') . '</th>';
        echo '<th>' . esc_html__('Actions', 'manga-admin-panel') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody id="recently-updated-list">';
        
        // This will be populated via AJAX
        echo '<tr><td colspan="5" class="manga-loading-cell">';
        echo '<div class="manga-loading">';
        echo '<div class="manga-spinner"></div>';
        echo '<span>' . esc_html__('Loading recent manga...', 'manga-admin-panel') . '</span>';
        echo '</div>';
        echo '</td></tr>';
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        echo '</div>';
        
        // Statistics Tab
        $statistics_active = ($settings['default_tab'] === 'statistics') ? 'active' : '';
        echo '<div class="manga-admin-tab-pane ' . esc_attr($statistics_active) . '" id="statistics">';
        
        echo '<div id="statistics-container">';
        echo '<div class="manga-loading">';
        echo '<div class="manga-spinner"></div>';
        echo '<span>' . esc_html__('Loading statistics...', 'manga-admin-panel') . '</span>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        
        echo '</div>'; // End manga-admin-content
        echo '</div>'; // End manga-admin-container

        // Add JS to initialize the widget
        echo '<script>
            jQuery(document).ready(function($) {
                // Initialize tabs
                $(".manga-admin-tab").on("click", function() {
                    const targetTab = $(this).data("tab");
                    
                    // Update active tab
                    $(".manga-admin-tab").removeClass("active");
                    $(this).addClass("active");
                    
                    // Show target tab content
                    $(".manga-admin-tab-pane").removeClass("active");
                    $("#" + targetTab).addClass("active");
                    
                    // Load data for the active tab if needed
                    if (targetTab === "manga-list") {
                        MangaAdmin.loadMangaList();
                    } else if (targetTab === "recently-updated") {
                        MangaAdmin.loadRecentManga();
                    } else if (targetTab === "statistics") {
                        MangaAdmin.loadStatistics();
                    }
                });
                
                // Load data for the default active tab
                const defaultTab = "' . esc_js($settings['default_tab']) . '";
                if (defaultTab === "manga-list") {
                    MangaAdmin.loadMangaList();
                } else if (defaultTab === "recently-updated") {
                    MangaAdmin.loadRecentManga();
                } else if (defaultTab === "statistics") {
                    MangaAdmin.loadStatistics();
                }
            });
        </script>';
    }

    /**
     * Render widget plain content
     * 
     * No plain content for this widget
     */
    protected function content_template() {
        // Dynamic content - empty
    }
}

// Register the Manga Admin Elementor Widget
add_action('elementor/widgets/register', function($widgets_manager) {
    $widgets_manager->register(new Manga_Admin_Elementor_Widget());
});
