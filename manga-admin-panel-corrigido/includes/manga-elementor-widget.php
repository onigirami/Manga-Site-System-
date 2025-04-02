<?php
/**
 * Integração com Elementor
 * 
 * Este arquivo registra os widgets personalizados do Manga Admin Panel
 * para uso dentro do Elementor Page Builder.
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe para gerenciar integração com Elementor
 */
class Manga_Admin_Elementor {
    
    /**
     * Instância única (singleton)
     */
    private static $instance = null;
    
    /**
     * Obtém instância única da classe
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Construtor privado para singleton
     */
    private function __construct() {
        // Verificar se o Elementor está ativo
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        // Registrar categoria de widgets
        add_action('elementor/elements/categories_registered', array($this, 'register_widget_category'));
        
        // Registrar widgets
        add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets'));
        
        // Registrar scripts e estilos
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_styles'));
        add_action('elementor/frontend/after_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Adicionar controles personalizados
        add_action('elementor/controls/controls_registered', array($this, 'register_controls'));
    }
    
    /**
     * Registrar categoria personalizada para widgets
     */
    public function register_widget_category($elements_manager) {
        $elements_manager->add_category(
            'manga-admin',
            [
                'title' => __('Manga Admin', 'manga-admin-panel'),
                'icon' => 'fa fa-book',
            ]
        );
    }
    
    /**
     * Registrar widgets personalizados
     */
    public function register_widgets() {
        // Verificar se estamos usando Elementor 3.5+
        if (version_compare(ELEMENTOR_VERSION, '3.5.0', '>=')) {
            $widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
        } else {
            // Versões anteriores do Elementor
            $widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
        }
        
        // Incluir arquivos base
        require_once MANGA_ADMIN_PANEL_PATH . 'elementor-export/elementor-widget-base.php';
        
        // Registrar widgets individuais
        $this->register_widgets_files($widgets_manager);
    }
    
    /**
     * Registrar arquivos de widgets individuais
     */
    private function register_widgets_files($widgets_manager) {
        // Lista de widgets
        $widgets = array(
            'manga-admin-panel-widget',
            'manga-upload-widget',
            'manga-reader-widget',
            'manga-display-widget',
            'manga-user-profile-widget'
        );
        
        // Incluir e registrar cada widget
        foreach ($widgets as $widget) {
            $file_path = MANGA_ADMIN_PANEL_PATH . 'elementor-export/widgets/' . $widget . '.php';
            
            if (file_exists($file_path)) {
                require_once $file_path;
                
                // Obter nome da classe do arquivo
                $class_name = $this->get_widget_class_name($widget);
                
                if (class_exists($class_name)) {
                    if (method_exists($widgets_manager, 'register')) {
                        // Elementor 3.5+
                        $widgets_manager->register(new $class_name());
                    } else {
                        // Elementor < 3.5
                        $widgets_manager->register_widget_type(new $class_name());
                    }
                }
            }
        }
    }
    
    /**
     * Obter nome da classe a partir do nome do arquivo
     */
    private function get_widget_class_name($widget_slug) {
        $parts = explode('-', $widget_slug);
        $parts = array_map('ucfirst', $parts);
        return 'Manga_' . implode('_', $parts);
    }
    
    /**
     * Enfileirar estilos para o Elementor
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'manga-admin-elementor',
            MANGA_ADMIN_PANEL_URL . 'elementor-export/assets/manga-admin-elementor.css',
            array(),
            MANGA_ADMIN_PANEL_VERSION
        );
    }
    
    /**
     * Enfileirar scripts para o Elementor
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'manga-admin-elementor',
            MANGA_ADMIN_PANEL_URL . 'elementor-export/assets/manga-admin-elementor.js',
            array('jquery'),
            MANGA_ADMIN_PANEL_VERSION,
            true
        );
    }
    
    /**
     * Registrar controles personalizados para o Elementor
     */
    public function register_controls($controls_manager) {
        // Aqui poderíamos adicionar controles personalizados se necessário
    }
}

// Inicializar a integração com Elementor
Manga_Admin_Elementor::get_instance();