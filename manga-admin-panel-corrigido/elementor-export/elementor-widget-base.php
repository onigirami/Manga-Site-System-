<?php
/**
 * Classe base para widgets do Elementor
 * Define funcionalidades comuns que serão estendidas por widgets específicos
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe base para todos os widgets do Manga Admin Panel
 */
abstract class Manga_Admin_Elementor_Widget_Base extends \Elementor\Widget_Base {
    
    /**
     * Obter categoria do widget
     *
     * @return array Lista de categorias do widget
     */
    public function get_categories() {
        return ['manga-admin'];
    }
    
    /**
     * Obter scripts dependentes
     *
     * @return array Lista de scripts dependentes
     */
    public function get_script_depends() {
        return ['manga-admin-elementor'];
    }
    
    /**
     * Obter estilos dependentes
     *
     * @return array Lista de estilos dependentes
     */
    public function get_style_depends() {
        return ['manga-admin-elementor'];
    }
    
    /**
     * Registrar controles comuns para todos os widgets
     */
    protected function register_common_controls() {
        // Seção de Layout
        $this->start_controls_section(
            'section_layout',
            [
                'label' => __('Layout', 'manga-admin-panel'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_responsive_control(
            'content_alignment',
            [
                'label' => __('Alinhamento', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Esquerda', 'manga-admin-panel'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Centro', 'manga-admin-panel'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Direita', 'manga-admin-panel'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .manga-container' => 'text-align: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Seção de Estilos
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Estilos', 'manga-admin-panel'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'primary_color',
            [
                'label' => __('Cor Primária', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ff6b6b',
                'selectors' => [
                    '{{WRAPPER}} .manga-btn-primary' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-profile-item-status' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-profile-stat-number' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-profile-tab.active' => 'border-color: {{VALUE}} !important; color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-reader-nav-btn' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_control(
            'secondary_color',
            [
                'label' => __('Cor Secundária', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#576574',
                'selectors' => [
                    '{{WRAPPER}} .manga-btn-secondary' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-profile-status-select' => 'border-color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_control(
            'accent_color',
            [
                'label' => __('Cor de Destaque', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#4b7bec',
                'selectors' => [
                    '{{WRAPPER}} .manga-btn-accent' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-login-register a' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-chapters-info-link' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_control(
            'text_color',
            [
                'label' => __('Cor do Texto', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .manga-profile-name' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-profile-item-title' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-reader-title h1' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-form-control' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_control(
            'background_color',
            [
                'label' => __('Cor de Fundo', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f7f7f7',
                'selectors' => [
                    '{{WRAPPER}} .manga-profile-container' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-reader-container' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-chapters-container' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_control(
            'card_color',
            [
                'label' => __('Cor dos Cards', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .manga-profile-item' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-profile-tab' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-profile-stat-item' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-card' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .manga-modal-content' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Seção de Tipografia
        $this->start_controls_section(
            'section_typography',
            [
                'label' => __('Tipografia', 'manga-admin-panel'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Títulos', 'manga-admin-panel'),
                'selector' => '{{WRAPPER}} .manga-profile-name, {{WRAPPER}} .manga-profile-item-title, {{WRAPPER}} .manga-reader-title h1',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography',
                'label' => __('Conteúdo', 'manga-admin-panel'),
                'selector' => '{{WRAPPER}} .manga-profile-item-details, {{WRAPPER}} .manga-reader-content, {{WRAPPER}} .manga-form-control',
            ]
        );
        
        $this->end_controls_section();
        
        // Seção de Espaçamento
        $this->start_controls_section(
            'section_spacing',
            [
                'label' => __('Espaçamento', 'manga-admin-panel'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding do Container', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .manga-profile-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    '{{WRAPPER}} .manga-reader-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    '{{WRAPPER}} .manga-chapters-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
                'default' => [
                    'top' => '20',
                    'right' => '20',
                    'bottom' => '20',
                    'left' => '20',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );
        
        $this->add_responsive_control(
            'item_margin',
            [
                'label' => __('Margem entre Itens', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 5,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} .manga-profile-item' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
                    '{{WRAPPER}} .manga-reader-webtoon-image' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Renderizar estilos CSS inline para variáveis
     *
     * @param array $settings Configurações do widget
     * @return string Estilos CSS inline
     */
    protected function render_css_variables($settings) {
        // Obter cores dos settings ou usar padrões
        $primary_color = !empty($settings['primary_color']) ? $settings['primary_color'] : '#ff6b6b';
        $secondary_color = !empty($settings['secondary_color']) ? $settings['secondary_color'] : '#576574';
        $accent_color = !empty($settings['accent_color']) ? $settings['accent_color'] : '#4b7bec';
        $text_color = !empty($settings['text_color']) ? $settings['text_color'] : '#333333';
        $background_color = !empty($settings['background_color']) ? $settings['background_color'] : '#f7f7f7';
        $card_color = !empty($settings['card_color']) ? $settings['card_color'] : '#ffffff';
        
        // Criar CSS inline
        $css = "
        <style>
            :root {
                --manga-primary-color: {$primary_color};
                --manga-secondary-color: {$secondary_color};
                --manga-accent-color: {$accent_color};
                --manga-text-color: {$text_color};
                --manga-background-color: {$background_color};
                --manga-card-color: {$card_color};
            }
        </style>
        ";
        
        return $css;
    }
}