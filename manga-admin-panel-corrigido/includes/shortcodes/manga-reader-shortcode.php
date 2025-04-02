<?php
/**
 * Manga Reader Shortcode
 * Implementa funcionalidades adicionais para o shortcode do leitor de mangá
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe para o Shortcode do Leitor de Mangá
 */
class Manga_Reader_Shortcode {
    
    /**
     * Construtor
     */
    public function __construct() {
        // Adicionar scripts e estilos específicos para o leitor
        add_action('wp_enqueue_scripts', array($this, 'enqueue_reader_assets'));
        
        // Adicionar AJAX handlers para o leitor
        add_action('wp_ajax_manga_reader_save_preferences', array($this, 'save_reader_preferences'));
        add_action('wp_ajax_nopriv_manga_reader_save_preferences', array($this, 'save_reader_preferences'));
        
        // Salvar progresso de leitura para usuários logados
        add_action('wp_ajax_manga_reader_save_progress', array($this, 'save_reading_progress'));
    }
    
    /**
     * Registrar e carregar assets específicos para o leitor
     */
    public function enqueue_reader_assets() {
        // Verificar se estamos em uma página com o shortcode do leitor
        global $post;
        
        if (is_singular() && $post && (
            has_shortcode($post->post_content, 'manga_reader') ||
            get_page_template_slug($post->ID) === 'manga-reader-template.php'
        )) {
            // Registrar e enfileirar estilos
            wp_enqueue_style('manga-reader-styles', MANGA_ADMIN_PANEL_URL . 'assets/css/manga-reader-styles.css', array(), MANGA_ADMIN_PANEL_VERSION);
            
            // Registrar e enfileirar scripts
            wp_enqueue_script('manga-reader-scripts', MANGA_ADMIN_PANEL_URL . 'assets/js/manga-reader-scripts.js', array('jquery'), MANGA_ADMIN_PANEL_VERSION, true);
            
            // Localizar script
            wp_localize_script('manga-reader-scripts', 'mangaReaderVars', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('manga_reader_nonce'),
                'i18n' => array(
                    'page' => __('Página', 'manga-admin-panel'),
                    'loading' => __('Carregando...', 'manga-admin-panel'),
                    'error_loading' => __('Erro ao carregar imagem', 'manga-admin-panel'),
                    'prev_chapter' => __('Capítulo Anterior', 'manga-admin-panel'),
                    'next_chapter' => __('Próximo Capítulo', 'manga-admin-panel'),
                    'progress_saved' => __('Progresso salvo', 'manga-admin-panel'),
                    'error_saving' => __('Erro ao salvar progresso', 'manga-admin-panel'),
                )
            ));
        }
    }
    
    /**
     * Salvar preferências do leitor via AJAX
     */
    public function save_reader_preferences() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_reader_nonce')) {
            wp_send_json_error(array('message' => __('Verificação de segurança falhou', 'manga-admin-panel')));
            exit;
        }
        
        // Obter preferências
        $view_mode = isset($_POST['view_mode']) ? sanitize_text_field($_POST['view_mode']) : 'pagination';
        $brightness = isset($_POST['brightness']) ? intval($_POST['brightness']) : 100;
        
        // Salvar em cookies (duração de 1 ano)
        $expiry = time() + 31536000;
        $path = '/';
        $domain = '';
        $secure = is_ssl();
        $httponly = true;
        
        // Definir cookies
        setcookie('manga_reader_view_mode', $view_mode, $expiry, $path, $domain, $secure, $httponly);
        setcookie('manga_reader_brightness', $brightness, $expiry, $path, $domain, $secure, $httponly);
        
        // Se usuário está logado, salvar também como meta de usuário
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            update_user_meta($user_id, 'manga_reader_view_mode', $view_mode);
            update_user_meta($user_id, 'manga_reader_brightness', $brightness);
        }
        
        wp_send_json_success(array(
            'message' => __('Preferências salvas com sucesso', 'manga-admin-panel'),
            'view_mode' => $view_mode,
            'brightness' => $brightness
        ));
        exit;
    }
    
    /**
     * Salvar progresso de leitura via AJAX
     */
    public function save_reading_progress() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_reader_nonce')) {
            wp_send_json_error(array('message' => __('Verificação de segurança falhou', 'manga-admin-panel')));
            exit;
        }
        
        // Verificar se usuário está logado
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Você precisa estar logado para salvar seu progresso', 'manga-admin-panel')));
            exit;
        }
        
        // Obter dados
        $manga_id = isset($_POST['manga_id']) ? intval($_POST['manga_id']) : 0;
        $chapter_id = isset($_POST['chapter_id']) ? intval($_POST['chapter_id']) : 0;
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $percent_read = isset($_POST['percent_read']) ? floatval($_POST['percent_read']) : 0;
        
        // Validar dados
        if (!$manga_id || !$chapter_id) {
            wp_send_json_error(array('message' => __('ID de mangá ou capítulo inválido', 'manga-admin-panel')));
            exit;
        }
        
        // Função para salvar progresso
        // Em um ambiente WordPress real, isso seria implementado para salvar os dados
        // Vamos simular o funcionamento
        $result = $this->update_reading_progress($manga_id, $chapter_id, $page, $percent_read);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Progresso salvo com sucesso', 'manga-admin-panel')));
        } else {
            wp_send_json_error(array('message' => __('Erro ao salvar progresso', 'manga-admin-panel')));
        }
        
        exit;
    }
    
    /**
     * Atualizar progresso de leitura (implementação simulada)
     */
    private function update_reading_progress($manga_id, $chapter_id, $page, $percent_read) {
        // Em uma implementação real, essa função salvaria o progresso no banco de dados
        $user_id = get_current_user_id();
        
        // Obter progresso atual
        $user_reading = get_user_meta($user_id, 'manga_reading_progress', true);
        
        if (!is_array($user_reading)) {
            $user_reading = array();
        }
        
        // Atualizar progresso
        $user_reading[$manga_id] = array(
            'last_chapter' => $chapter_id,
            'last_page' => $page,
            'percent_read' => $percent_read,
            'last_read' => current_time('mysql')
        );
        
        // Salvar meta de usuário
        update_user_meta($user_id, 'manga_reading_progress', $user_reading);
        
        // Também atualizar meta de visualizações para o capítulo
        $this->increment_chapter_views($chapter_id);
        
        return true;
    }
    
    /**
     * Incrementar contador de visualizações de capítulo
     */
    private function increment_chapter_views($chapter_id) {
        // Obter visualizações atuais
        $views = get_post_meta($chapter_id, 'chapter_views', true);
        
        if (!$views) {
            $views = 0;
        }
        
        // Incrementar e salvar
        $views++;
        update_post_meta($chapter_id, 'chapter_views', $views);
    }
}

// Inicializar a classe
new Manga_Reader_Shortcode();