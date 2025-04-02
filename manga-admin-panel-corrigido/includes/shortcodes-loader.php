<?php
/**
 * Carregador de Shortcodes
 * Responsável por incluir todos os arquivos de shortcodes e registrar funcionalidades
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Função para registrar todos os shortcodes de uma vez
 */
class Manga_Admin_Shortcodes {
    
    /**
     * Construtor
     */
    public function __construct() {
        // Registrar shortcodes principais
        add_shortcode('manga_admin_panel', array($this, 'admin_panel_shortcode'));
        add_shortcode('manga_upload', array($this, 'upload_shortcode'));
        add_shortcode('manga_user_profile', array($this, 'user_profile_shortcode'));
        add_shortcode('manga_reader', array($this, 'reader_shortcode'));
        add_shortcode('manga_display', array($this, 'display_shortcode'));
        add_shortcode('manga_user', array($this, 'user_content_shortcode'));
        add_shortcode('manga_admin_settings', array($this, 'admin_settings_shortcode'));
        
        // Carregar arquivos de shortcodes adicionais
        $this->load_shortcode_files();
    }
    
    /**
     * Carregar arquivos de shortcodes individuais
     */
    private function load_shortcode_files() {
        $shortcode_files = array(
            'manga-upload-shortcode.php',
            'manga-reader-shortcode.php',
            'manga-display-shortcode.php'
        );
        
        foreach ($shortcode_files as $file) {
            $file_path = MANGA_ADMIN_PANEL_PATH . 'includes/shortcodes/' . $file;
            
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                // Registrar falha de carregamento para debug
                error_log('Manga Admin Panel: Arquivo de shortcode não encontrado - ' . $file_path);
            }
        }
    }
    
    /**
     * Shortcode para o painel de administração
     */
    public function admin_panel_shortcode($atts) {
        // Verificar se o usuário tem permissão para acessar o painel
        if (!manga_admin_panel_has_access()) {
            ob_start();
            ?>
            <div class="manga-alert manga-alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <?php echo esc_html__('Você não tem permissão para acessar o painel administrativo de mangás.', 'manga-admin-panel'); ?>
            </div>
            
            <?php echo manga_admin_login_form(__('Faça login com uma conta que tenha permissões administrativas para acessar o painel.', 'manga-admin-panel')); ?>
            <?php
            return ob_get_clean();
        }
        
        // Extrair e definir valores padrão para atributos
        $atts = shortcode_atts(array(
            'dashboard_type' => 'full', // full, simple
            'hide_menu' => 'no',      // yes, no
            'show_stats' => 'yes',    // yes, no
        ), $atts, 'manga_admin_panel');
        
        // Iniciar buffer de saída
        ob_start();
        
        // Incluir o template
        $template_path = MANGA_ADMIN_PANEL_PATH . 'templates/manga-admin-dashboard.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<div class="manga-alert manga-alert-danger">';
            echo esc_html__('Template não encontrado: ', 'manga-admin-panel') . 'manga-admin-dashboard.php';
            echo '</div>';
        }
        
        // Retornar o conteúdo do buffer
        return ob_get_clean();
    }
    
    /**
     * Shortcode para upload de mangá
     */
    public function upload_shortcode($atts) {
        // Verificar se o usuário tem permissão para fazer upload
        if (!manga_admin_panel_has_access()) {
            ob_start();
            ?>
            <div class="manga-alert manga-alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <?php echo esc_html__('Você não tem permissão para fazer upload de mangás.', 'manga-admin-panel'); ?>
            </div>
            
            <?php echo manga_admin_login_form(__('Faça login com uma conta que tenha permissões para upload de mangás.', 'manga-admin-panel')); ?>
            <?php
            return ob_get_clean();
        }
        
        // Extrair e definir valores padrão para atributos
        $atts = shortcode_atts(array(
            'manga_id' => 0,              // 0 = novo mangá, ID = editar existente
            'redirect_url' => '',         // URL para redirecionar após envio
            'show_taxonomies' => 'yes',   // yes, no
            'show_featured_image' => 'yes', // yes, no
        ), $atts, 'manga_upload');
        
        // Iniciar buffer de saída
        ob_start();
        
        // Incluir o template
        $template_path = MANGA_ADMIN_PANEL_PATH . 'templates/manga-create-edit.php';
        
        if (file_exists($template_path)) {
            // Definir variáveis para o template
            $manga_id = intval($atts['manga_id']);
            $redirect_url = esc_url($atts['redirect_url']);
            $show_taxonomies = $atts['show_taxonomies'] === 'yes';
            $show_featured_image = $atts['show_featured_image'] === 'yes';
            
            include $template_path;
        } else {
            echo '<div class="manga-alert manga-alert-danger">';
            echo esc_html__('Template não encontrado: ', 'manga-admin-panel') . 'manga-create-edit.php';
            echo '</div>';
        }
        
        // Retornar o conteúdo do buffer
        return ob_get_clean();
    }
    
    /**
     * Shortcode para perfil do usuário
     */
    public function user_profile_shortcode($atts) {
        // Extrair e definir valores padrão para atributos
        $atts = shortcode_atts(array(
            'user_id' => 0,  // 0 = usuário atual ou logado
            'show_stats' => 'yes',
            'show_lists' => 'yes',
            'show_avatar' => 'yes',
        ), $atts, 'manga_user_profile');
        
        // Iniciar buffer de saída
        ob_start();
        
        // Incluir o template
        $template_path = MANGA_ADMIN_PANEL_PATH . 'templates/manga-user-profile.php';
        
        if (file_exists($template_path)) {
            // Definir variáveis para o template
            $user_id = intval($atts['user_id']);
            $show_stats = $atts['show_stats'] === 'yes';
            $show_lists = $atts['show_lists'] === 'yes';
            $show_avatar = $atts['show_avatar'] === 'yes';
            
            include $template_path;
        } else {
            echo '<div class="manga-alert manga-alert-danger">';
            echo esc_html__('Template não encontrado: ', 'manga-admin-panel') . 'manga-user-profile.php';
            echo '</div>';
        }
        
        // Retornar o conteúdo do buffer
        return ob_get_clean();
    }
    
    /**
     * Shortcode para o leitor de mangá
     */
    public function reader_shortcode($atts) {
        // Extrair e definir valores padrão para atributos
        $atts = shortcode_atts(array(
            'manga_id' => 0,          // ID do mangá
            'chapter_id' => 0,        // ID do capítulo
            'default_mode' => 'pagination', // pagination ou webtoon
            'show_chapter_list' => 'yes',   // yes ou no
            'show_comments' => 'no',        // yes ou no
            'show_header' => 'yes',         // yes ou no
        ), $atts, 'manga_reader');
        
        // Iniciar buffer de saída
        ob_start();
        
        // Verificar se manga_id é fornecido no shortcode ou na URL
        $manga_id = !empty($atts['manga_id']) ? intval($atts['manga_id']) : (isset($_GET['manga_id']) ? intval($_GET['manga_id']) : 0);
        $chapter_id = !empty($atts['chapter_id']) ? intval($atts['chapter_id']) : (isset($_GET['chapter_id']) ? intval($_GET['chapter_id']) : 0);
        
        // Definir outras variáveis para o template
        $default_mode = $atts['default_mode'];
        $show_chapter_list = $atts['show_chapter_list'] === 'yes';
        $show_comments = $atts['show_comments'] === 'yes';
        $show_header = $atts['show_header'] === 'yes';
        
        // Verificar se temos manga_id
        if (!$manga_id) {
            echo '<div class="manga-alert manga-alert-danger">';
            echo esc_html__('ID do mangá não fornecido. Use o atributo manga_id no shortcode ou o parâmetro manga_id na URL.', 'manga-admin-panel');
            echo '</div>';
        } else {
            // Verificar qual template incluir
            if ($chapter_id) {
                // Leitor de capítulo
                $template_path = MANGA_ADMIN_PANEL_PATH . 'templates/manga-modern-reader.php';
            } else {
                // Lista de capítulos
                $template_path = MANGA_ADMIN_PANEL_PATH . 'templates/manga-chapter-list.php';
            }
            
            if (file_exists($template_path)) {
                include $template_path;
            } else {
                echo '<div class="manga-alert manga-alert-danger">';
                echo esc_html__('Template não encontrado: ', 'manga-admin-panel') . basename($template_path);
                echo '</div>';
            }
        }
        
        // Retornar o conteúdo do buffer
        return ob_get_clean();
    }
    
    /**
     * Shortcode para exibir mangás
     */
    public function display_shortcode($atts) {
        // Extrair e definir valores padrão para atributos
        $atts = shortcode_atts(array(
            'limit' => 12,            // Número de mangás a exibir
            'orderby' => 'date',      // date, title, views, popularity, random
            'order' => 'DESC',        // ASC, DESC
            'layout' => 'grid',       // grid, list, carousel
            'columns' => 4,           // 1-6
            'genre' => '',            // Gêneros (slugs separados por vírgula)
            'status' => '',           // Status (slugs separados por vírgula)
            'author' => '',           // Autor (slugs separados por vírgula)
            'show_rating' => 'yes',   // yes, no
            'show_views' => 'yes',    // yes, no
            'show_chapters' => 'yes', // yes, no
            'card_style' => 'default', // default, compact, expanded
        ), $atts, 'manga_display');
        
        // Iniciar buffer de saída
        ob_start();
        
        // Incluir o template
        $template_path = MANGA_ADMIN_PANEL_PATH . 'templates/manga-display.php';
        
        if (file_exists($template_path)) {
            // Converter atributos para variáveis
            $limit = intval($atts['limit']);
            $orderby = $atts['orderby'];
            $order = $atts['order'];
            $layout = $atts['layout'];
            $columns = intval($atts['columns']);
            $genre = $atts['genre'];
            $status = $atts['status'];
            $author = $atts['author'];
            $show_rating = $atts['show_rating'] === 'yes';
            $show_views = $atts['show_views'] === 'yes';
            $show_chapters = $atts['show_chapters'] === 'yes';
            $card_style = $atts['card_style'];
            
            include $template_path;
        } else {
            echo '<div class="manga-alert manga-alert-danger">';
            echo esc_html__('Template não encontrado: ', 'manga-admin-panel') . 'manga-display.php';
            echo '</div>';
        }
        
        // Retornar o conteúdo do buffer
        return ob_get_clean();
    }
    
    /**
     * Shortcode para exibir conteúdo baseado no estado do usuário
     */
    public function user_content_shortcode($atts, $content = null) {
        $atts = shortcode_atts(array(
            'state' => 'logged_in', // logged_in, logged_out, can_manage
        ), $atts, 'manga_user');
        
        if ($atts['state'] === 'logged_in' && is_user_logged_in()) {
            return do_shortcode($content);
        } elseif ($atts['state'] === 'logged_out' && !is_user_logged_in()) {
            return do_shortcode($content);
        } elseif ($atts['state'] === 'can_manage' && manga_admin_panel_has_access()) {
            return do_shortcode($content);
        }
        
        return '';
    }
    
    /**
     * Shortcode para o painel de configurações de cores
     */
    public function admin_settings_shortcode($atts) {
        // Apenas administradores podem acessar configurações
        if (!current_user_can('manage_options')) {
            return '<div class="manga-alert manga-alert-warning">' . 
                   esc_html__('Você não tem permissão para acessar as configurações do plugin.', 'manga-admin-panel') . 
                   '</div>';
        }
        
        // Iniciar buffer de saída
        ob_start();
        
        // Incluir o template de demonstração de cores
        $template_path = MANGA_ADMIN_PANEL_PATH . 'templates/manga-color-demo.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<div class="manga-alert manga-alert-danger">';
            echo esc_html__('Template não encontrado: ', 'manga-admin-panel') . 'manga-color-demo.php';
            echo '</div>';
        }
        
        // Retornar o conteúdo do buffer
        return ob_get_clean();
    }
}

// Inicializar os shortcodes
$manga_admin_shortcodes = new Manga_Admin_Shortcodes();