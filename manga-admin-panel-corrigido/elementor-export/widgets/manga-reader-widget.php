<?php
/**
 * Widget do Elementor para o Leitor de Mangá
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe do widget do leitor de mangá
 */
class Manga_Reader_Widget extends Manga_Admin_Elementor_Widget_Base {
    
    /**
     * Obter nome do widget
     *
     * @return string Nome do widget
     */
    public function get_name() {
        return 'manga_reader';
    }
    
    /**
     * Obter título do widget
     *
     * @return string Título do widget
     */
    public function get_title() {
        return __('Leitor de Mangá', 'manga-admin-panel');
    }
    
    /**
     * Obter ícone do widget
     *
     * @return string Ícone do widget
     */
    public function get_icon() {
        return 'eicon-book-open';
    }
    
    /**
     * Obter palavras-chave do widget
     *
     * @return array Palavras-chave do widget
     */
    public function get_keywords() {
        return ['manga', 'leitor', 'capítulo', 'leitura', 'webtoon', 'comics'];
    }
    
    /**
     * Registrar controles do widget
     */
    protected function register_controls() {
        // Controles comuns
        $this->register_common_controls();
        
        // Seção de conteúdo específica
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Configurações do Leitor', 'manga-admin-panel'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'manga_id',
            [
                'label' => __('ID do Mangá', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __('Opcional. Se não fornecido, o ID será obtido da URL.', 'manga-admin-panel'),
                'default' => 0,
                'min' => 0,
            ]
        );
        
        $this->add_control(
            'chapter_id',
            [
                'label' => __('ID do Capítulo', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => __('Opcional. Se não fornecido, o ID será obtido da URL.', 'manga-admin-panel'),
                'default' => 0,
                'min' => 0,
            ]
        );
        
        $this->add_control(
            'default_mode',
            [
                'label' => __('Modo de Visualização Padrão', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'pagination',
                'options' => [
                    'pagination' => __('Paginado', 'manga-admin-panel'),
                    'webtoon' => __('Lista Corrida', 'manga-admin-panel'),
                ],
            ]
        );
        
        $this->add_control(
            'show_chapter_list',
            [
                'label' => __('Mostrar Lista de Capítulos', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sim', 'manga-admin-panel'),
                'label_off' => __('Não', 'manga-admin-panel'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_comments',
            [
                'label' => __('Mostrar Comentários', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sim', 'manga-admin-panel'),
                'label_off' => __('Não', 'manga-admin-panel'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        
        $this->add_control(
            'show_header',
            [
                'label' => __('Mostrar Cabeçalho', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Sim', 'manga-admin-panel'),
                'label_off' => __('Não', 'manga-admin-panel'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->end_controls_section();
        
        // Seção de estilos específicos
        $this->start_controls_section(
            'section_reader_style',
            [
                'label' => __('Estilos do Leitor', 'manga-admin-panel'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'brightness_default',
            [
                'label' => __('Brilho Padrão', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 50,
                        'max' => 150,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
            ]
        );
        
        $this->add_control(
            'page_border_radius',
            [
                'label' => __('Arredondamento das Imagens', 'manga-admin-panel'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .manga-reader-pages' => 'border-radius: {{SIZE}}{{UNIT}} !important;',
                    '{{WRAPPER}} .manga-reader-webtoon-image img' => 'border-radius: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'page_box_shadow',
                'label' => __('Sombra das Imagens', 'manga-admin-panel'),
                'selector' => '{{WRAPPER}} .manga-reader-pages, {{WRAPPER}} .manga-reader-webtoon-image img',
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Renderizar o widget
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Mostrar variáveis CSS
        echo $this->render_css_variables($settings);
        
        // Extrair configurações
        $manga_id = !empty($settings['manga_id']) ? intval($settings['manga_id']) : 0;
        $chapter_id = !empty($settings['chapter_id']) ? intval($settings['chapter_id']) : 0;
        $default_mode = !empty($settings['default_mode']) ? $settings['default_mode'] : 'pagination';
        $show_chapter_list = !empty($settings['show_chapter_list']) && $settings['show_chapter_list'] === 'yes';
        $show_comments = !empty($settings['show_comments']) && $settings['show_comments'] === 'yes';
        $show_header = !empty($settings['show_header']) && $settings['show_header'] === 'yes';
        $brightness_default = !empty($settings['brightness_default']['size']) ? $settings['brightness_default']['size'] : 100;
        
        // Definir variáveis para o template
        set_query_var('manga_id', $manga_id);
        set_query_var('chapter_id', $chapter_id);
        set_query_var('default_mode', $default_mode);
        set_query_var('show_chapter_list', $show_chapter_list);
        set_query_var('show_comments', $show_comments);
        set_query_var('show_header', $show_header);
        set_query_var('user_brightness', $brightness_default);
        
        // Verificar se estamos no editor do Elementor
        $is_edit_mode = \Elementor\Plugin::instance()->editor->is_edit_mode();
        
        // Adicionar wrapper específico para o elementor
        echo '<div class="manga-elementor-widget manga-reader-widget">';
        
        if ($is_edit_mode && !$manga_id) {
            echo '<div class="manga-elementor-preview-notice">';
            echo '<i class="fas fa-info-circle"></i> ';
            echo esc_html__('Visualização do Leitor de Mangá. Para ver o conteúdo real, configure um ID de mangá ou visualize no frontend.', 'manga-admin-panel');
            echo '</div>';
            
            // Mostrar visualização simplificada no editor
            ?>
            <div class="manga-reader-container elementor-preview">
                <div class="manga-reader-header">
                    <div class="manga-reader-title">
                        <h1><?php echo esc_html__('Título do Mangá', 'manga-admin-panel'); ?></h1>
                        <h2><?php echo esc_html__('Capítulo 1', 'manga-admin-panel'); ?></h2>
                    </div>
                    <div class="manga-reader-controls">
                        <div class="manga-reader-setting-group">
                            <label><?php echo esc_html__('Modo de Visualização:', 'manga-admin-panel'); ?></label>
                            <select>
                                <option><?php echo esc_html__('Paginado', 'manga-admin-panel'); ?></option>
                                <option><?php echo esc_html__('Lista Corrida', 'manga-admin-panel'); ?></option>
                            </select>
                        </div>
                        <div class="manga-reader-setting-group">
                            <label><?php echo esc_html__('Brilho:', 'manga-admin-panel'); ?></label>
                            <input type="range" min="50" max="150" value="100">
                            <span>100%</span>
                        </div>
                    </div>
                </div>
                <div class="manga-reader-content">
                    <div class="manga-reader-pages">
                        <img src="https://via.placeholder.com/800x1200.png?text=Exemplo+de+Página" alt="<?php echo esc_attr__('Exemplo de página', 'manga-admin-panel'); ?>">
                    </div>
                </div>
            </div>
            <?php
        } else {
            // Carregar o template real
            if ($chapter_id) {
                // Leitor de capítulo
                include MANGA_ADMIN_PANEL_PATH . 'templates/manga-modern-reader.php';
            } else {
                // Lista de capítulos
                include MANGA_ADMIN_PANEL_PATH . 'templates/manga-chapter-list.php';
            }
        }
        
        echo '</div>'; // Fecha .manga-elementor-widget
    }
    
    /**
     * Renderizar conteúdo em HTML puro (para versões não-JS)
     */
    protected function content_template() {
        ?>
        <# var isEditMode = elementorFrontend.isEditMode(); #>
        
        <div class="manga-elementor-widget manga-reader-widget">
            <# if (isEditMode && !settings.manga_id) { #>
                <div class="manga-elementor-preview-notice">
                    <i class="fas fa-info-circle"></i> 
                    <?php echo esc_html__('Visualização do Leitor de Mangá. Para ver o conteúdo real, configure um ID de mangá ou visualize no frontend.', 'manga-admin-panel'); ?>
                </div>
                
                <div class="manga-reader-container elementor-preview">
                    <div class="manga-reader-header">
                        <div class="manga-reader-title">
                            <h1><?php echo esc_html__('Título do Mangá', 'manga-admin-panel'); ?></h1>
                            <h2><?php echo esc_html__('Capítulo 1', 'manga-admin-panel'); ?></h2>
                        </div>
                        <div class="manga-reader-controls">
                            <div class="manga-reader-setting-group">
                                <label><?php echo esc_html__('Modo de Visualização:', 'manga-admin-panel'); ?></label>
                                <select>
                                    <option><?php echo esc_html__('Paginado', 'manga-admin-panel'); ?></option>
                                    <option><?php echo esc_html__('Lista Corrida', 'manga-admin-panel'); ?></option>
                                </select>
                            </div>
                            <div class="manga-reader-setting-group">
                                <label><?php echo esc_html__('Brilho:', 'manga-admin-panel'); ?></label>
                                <input type="range" min="50" max="150" value="100">
                                <span>100%</span>
                            </div>
                        </div>
                    </div>
                    <div class="manga-reader-content">
                        <div class="manga-reader-pages">
                            <img src="https://via.placeholder.com/800x1200.png?text=Exemplo+de+Página" alt="<?php echo esc_attr__('Exemplo de página', 'manga-admin-panel'); ?>">
                        </div>
                    </div>
                </div>
            <# } else { #>
                <div class="manga-elementor-loading">
                    <i class="fas fa-spinner fa-spin"></i> <?php echo esc_html__('Carregando leitor de mangá...', 'manga-admin-panel'); ?>
                </div>
            <# } #>
        </div>
        <?php
    }
}