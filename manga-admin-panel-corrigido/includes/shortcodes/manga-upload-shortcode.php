<?php
/**
 * Manga Upload Shortcode
 * Implementa funcionalidades para criação e edição de mangás
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe para o Shortcode de Upload de Mangá
 */
class Manga_Upload_Shortcode {
    
    /**
     * Construtor
     */
    public function __construct() {
        // Enfileirar scripts e estilos
        add_action('wp_enqueue_scripts', array($this, 'enqueue_upload_assets'));
        
        // AJAX handlers para upload e edição
        add_action('wp_ajax_manga_create_manga', array($this, 'create_manga_ajax'));
        add_action('wp_ajax_manga_update_manga', array($this, 'update_manga_ajax'));
        add_action('wp_ajax_manga_upload_chapter', array($this, 'upload_chapter_ajax'));
        add_action('wp_ajax_manga_upload_cover', array($this, 'upload_cover_ajax'));
    }
    
    /**
     * Registrar e carregar assets específicos para upload
     */
    public function enqueue_upload_assets() {
        global $post;
        
        if (is_singular() && $post && has_shortcode($post->post_content, 'manga_upload')) {
            // Estilos
            wp_enqueue_style('manga-upload-styles', MANGA_ADMIN_PANEL_URL . 'assets/css/manga-upload-styles.css', array(), MANGA_ADMIN_PANEL_VERSION);
            
            // Scripts
            wp_enqueue_script('manga-upload-scripts', MANGA_ADMIN_PANEL_URL . 'assets/js/manga-upload-scripts.js', array('jquery'), MANGA_ADMIN_PANEL_VERSION, true);
            
            // Dropzone para upload
            wp_enqueue_style('dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css', array(), '5.9.3');
            wp_enqueue_script('dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js', array('jquery'), '5.9.3', true);
            
            // Selecione2 para melhor experiência de seleção múltipla
            wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13');
            wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);
            
            // Media uploader do WordPress
            wp_enqueue_media();
            
            // Localizar script
            wp_localize_script('manga-upload-scripts', 'mangaUploadVars', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('manga_upload_nonce'),
                'i18n' => array(
                    'uploading' => __('Enviando...', 'manga-admin-panel'),
                    'success_create' => __('Mangá criado com sucesso!', 'manga-admin-panel'),
                    'success_update' => __('Mangá atualizado com sucesso!', 'manga-admin-panel'),
                    'success_chapter' => __('Capítulo enviado com sucesso!', 'manga-admin-panel'),
                    'success_cover' => __('Capa enviada com sucesso!', 'manga-admin-panel'),
                    'error' => __('Ocorreu um erro. Por favor, tente novamente.', 'manga-admin-panel'),
                    'confirm_delete' => __('Tem certeza que deseja excluir? Esta ação não pode ser desfeita.', 'manga-admin-panel'),
                    'choose_image' => __('Escolher imagem', 'manga-admin-panel'),
                    'drop_files' => __('Arraste os arquivos aqui ou clique para selecionar', 'manga-admin-panel'),
                    'processing' => __('Processando...', 'manga-admin-panel'),
                )
            ));
        }
    }
    
    /**
     * Criar mangá via AJAX
     */
    public function create_manga_ajax() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_upload_nonce')) {
            wp_send_json_error(array('message' => __('Verificação de segurança falhou', 'manga-admin-panel')));
            exit;
        }
        
        // Verificar permissão
        if (!manga_admin_panel_has_access()) {
            wp_send_json_error(array('message' => __('Você não tem permissão para criar mangás', 'manga-admin-panel')));
            exit;
        }
        
        // Obter e validar dados
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $genres = isset($_POST['genres']) ? array_map('intval', $_POST['genres']) : array();
        $authors = isset($_POST['authors']) ? array_map('intval', $_POST['authors']) : array();
        
        if (empty($title)) {
            wp_send_json_error(array('message' => __('O título é obrigatório', 'manga-admin-panel')));
            exit;
        }
        
        // Em um ambiente real do WordPress, aqui teríamos a criação do post
        // Vamos simular o comportamento para o desenvolvimento
        
        // Simulação de criação de mangá
        $manga_id = $this->create_manga_post($title, $content, $status, $genres, $authors);
        
        if ($manga_id) {
            wp_send_json_success(array(
                'message' => __('Mangá criado com sucesso!', 'manga-admin-panel'),
                'manga_id' => $manga_id,
                'redirect' => add_query_arg(array('manga_id' => $manga_id, 'action' => 'edit'), get_permalink())
            ));
        } else {
            wp_send_json_error(array('message' => __('Erro ao criar mangá', 'manga-admin-panel')));
        }
        
        exit;
    }
    
    /**
     * Atualizar mangá via AJAX
     */
    public function update_manga_ajax() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_upload_nonce')) {
            wp_send_json_error(array('message' => __('Verificação de segurança falhou', 'manga-admin-panel')));
            exit;
        }
        
        // Verificar permissão
        if (!manga_admin_panel_has_access()) {
            wp_send_json_error(array('message' => __('Você não tem permissão para editar mangás', 'manga-admin-panel')));
            exit;
        }
        
        // Obter e validar dados
        $manga_id = isset($_POST['manga_id']) ? intval($_POST['manga_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $genres = isset($_POST['genres']) ? array_map('intval', $_POST['genres']) : array();
        $authors = isset($_POST['authors']) ? array_map('intval', $_POST['authors']) : array();
        
        if (!$manga_id || empty($title)) {
            wp_send_json_error(array('message' => __('ID de mangá inválido ou título vazio', 'manga-admin-panel')));
            exit;
        }
        
        // Verificar se o usuário pode editar este mangá
        $manga = get_post($manga_id);
        
        if (!$manga || ($manga->post_author != get_current_user_id() && !current_user_can('edit_others_posts'))) {
            wp_send_json_error(array('message' => __('Você não tem permissão para editar este mangá', 'manga-admin-panel')));
            exit;
        }
        
        // Atualizar mangá
        $updated = $this->update_manga_post($manga_id, $title, $content, $status, $genres, $authors);
        
        if ($updated) {
            wp_send_json_success(array(
                'message' => __('Mangá atualizado com sucesso!', 'manga-admin-panel'),
                'manga_id' => $manga_id
            ));
        } else {
            wp_send_json_error(array('message' => __('Erro ao atualizar mangá', 'manga-admin-panel')));
        }
        
        exit;
    }
    
    /**
     * Upload de capítulo via AJAX
     */
    public function upload_chapter_ajax() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_upload_nonce')) {
            wp_send_json_error(array('message' => __('Verificação de segurança falhou', 'manga-admin-panel')));
            exit;
        }
        
        // Verificar permissão
        if (!manga_admin_panel_has_access()) {
            wp_send_json_error(array('message' => __('Você não tem permissão para fazer upload de capítulos', 'manga-admin-panel')));
            exit;
        }
        
        // Obter e validar dados
        $manga_id = isset($_POST['manga_id']) ? intval($_POST['manga_id']) : 0;
        $chapter_name = isset($_POST['chapter_name']) ? sanitize_text_field($_POST['chapter_name']) : '';
        $chapter_number = isset($_POST['chapter_number']) ? floatval($_POST['chapter_number']) : 0;
        $is_premium = isset($_POST['is_premium']) && $_POST['is_premium'] === 'yes';
        $is_scheduled = isset($_POST['is_scheduled']) && $_POST['is_scheduled'] === 'yes';
        $scheduled_date = isset($_POST['scheduled_date']) ? sanitize_text_field($_POST['scheduled_date']) : '';
        
        if (!$manga_id || empty($chapter_name) || $chapter_number <= 0) {
            wp_send_json_error(array('message' => __('Dados de capítulo inválidos', 'manga-admin-panel')));
            exit;
        }
        
        // Verificar arquivo de upload
        if (!isset($_FILES['chapter_files']) || empty($_FILES['chapter_files']['name'])) {
            wp_send_json_error(array('message' => __('Nenhum arquivo enviado', 'manga-admin-panel')));
            exit;
        }
        
        // Em um ambiente real, aqui processaríamos o upload dos arquivos
        // Vamos simular o comportamento
        
        // Simulação de upload de capítulo
        $chapter_id = $this->create_chapter($manga_id, $chapter_name, $chapter_number, $is_premium, $is_scheduled, $scheduled_date);
        
        if ($chapter_id) {
            wp_send_json_success(array(
                'message' => __('Capítulo enviado com sucesso!', 'manga-admin-panel'),
                'chapter_id' => $chapter_id,
                'manga_id' => $manga_id
            ));
        } else {
            wp_send_json_error(array('message' => __('Erro ao enviar capítulo', 'manga-admin-panel')));
        }
        
        exit;
    }
    
    /**
     * Upload de capa via AJAX
     */
    public function upload_cover_ajax() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_upload_nonce')) {
            wp_send_json_error(array('message' => __('Verificação de segurança falhou', 'manga-admin-panel')));
            exit;
        }
        
        // Verificar permissão
        if (!manga_admin_panel_has_access()) {
            wp_send_json_error(array('message' => __('Você não tem permissão para fazer upload de capas', 'manga-admin-panel')));
            exit;
        }
        
        // Obter e validar dados
        $manga_id = isset($_POST['manga_id']) ? intval($_POST['manga_id']) : 0;
        
        if (!$manga_id) {
            wp_send_json_error(array('message' => __('ID de mangá inválido', 'manga-admin-panel')));
            exit;
        }
        
        // Verificar arquivo de upload
        if (!isset($_FILES['cover_file']) || empty($_FILES['cover_file']['name'])) {
            wp_send_json_error(array('message' => __('Nenhum arquivo enviado', 'manga-admin-panel')));
            exit;
        }
        
        // Em um ambiente real, aqui processaríamos o upload da capa
        // Vamos simular o comportamento
        
        // Simulação de upload de capa
        $attachment_id = $this->upload_cover_image($manga_id);
        
        if ($attachment_id) {
            wp_send_json_success(array(
                'message' => __('Capa enviada com sucesso!', 'manga-admin-panel'),
                'attachment_id' => $attachment_id,
                'manga_id' => $manga_id,
                'cover_url' => wp_get_attachment_url($attachment_id)
            ));
        } else {
            wp_send_json_error(array('message' => __('Erro ao enviar capa', 'manga-admin-panel')));
        }
        
        exit;
    }
    
    /**
     * Criar post de mangá (simulação)
     */
    private function create_manga_post($title, $content, $status, $genres, $authors) {
        // Em um ambiente real do WordPress, aqui usaríamos wp_insert_post
        // Para desenvolvimento, vamos retornar um ID simulado
        return 123; // ID simulado
    }
    
    /**
     * Atualizar post de mangá (simulação)
     */
    private function update_manga_post($manga_id, $title, $content, $status, $genres, $authors) {
        // Em um ambiente real do WordPress, aqui usaríamos wp_update_post
        // Para desenvolvimento, vamos sempre retornar sucesso
        return true;
    }
    
    /**
     * Criar capítulo (simulação)
     */
    private function create_chapter($manga_id, $chapter_name, $chapter_number, $is_premium, $is_scheduled, $scheduled_date) {
        // Em um ambiente real do WordPress, aqui criaríamos um post relacionado ao mangá
        // Para desenvolvimento, vamos retornar um ID simulado
        return 456; // ID simulado
    }
    
    /**
     * Upload de imagem de capa (simulação)
     */
    private function upload_cover_image($manga_id) {
        // Em um ambiente real, aqui usaríamos media_handle_upload
        // Para desenvolvimento, vamos retornar um ID simulado
        return 789; // ID simulado
    }
}

// Inicializar a classe
new Manga_Upload_Shortcode();