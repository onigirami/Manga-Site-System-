<?php
/**
 * Plugin Name: Manga Admin Panel
 * Plugin URI: https://exemplo.com/manga-admin-panel
 * Description: Interface personalizada para usuários privilegiados gerenciarem conteúdo de mangá compatível com Elementor e plugins de mangá existentes
 * Version: 1.2.0
 * Author: Developer
 * Author URI: https://exemplo.com
 * Text Domain: manga-admin-panel
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('MANGA_ADMIN_PANEL_VERSION', '1.2.0');
define('MANGA_ADMIN_PANEL_FILE', __FILE__);
define('MANGA_ADMIN_PANEL_PATH', plugin_dir_path(__FILE__));
define('MANGA_ADMIN_PANEL_URL', plugin_dir_url(__FILE__));

/**
 * Inicialização do plugin e carregamento de arquivos
 */
class Manga_Admin_Panel {
    
    /**
     * Instância única (singleton)
     */
    private static $instance = null;
    
    /**
     * Obtém uma instância única da classe
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Construtor
     */
    private function __construct() {
        // Carregar traduções
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Registrar scripts e estilos
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('admin_enqueue_scripts', array($this, 'register_admin_assets'));
        
        // Carregar classes e funcionalidades
        $this->load_files();
        
        // Adicionar modelos de página
        add_filter('theme_page_templates', array($this, 'add_page_templates'));
        add_filter('template_include', array($this, 'load_template'));
        
        // Configurar hooks de ativação/desativação
        register_activation_hook(MANGA_ADMIN_PANEL_FILE, array($this, 'activate'));
        register_deactivation_hook(MANGA_ADMIN_PANEL_FILE, array($this, 'deactivate'));
    }
    
    /**
     * Carregar traduções
     */
    public function load_textdomain() {
        load_plugin_textdomain('manga-admin-panel', false, dirname(plugin_basename(MANGA_ADMIN_PANEL_FILE)) . '/languages');
    }
    
    /**
     * Registrar scripts e estilos para o frontend
     */
    public function register_assets() {
        // Estilos principais
        wp_register_style('manga-admin-styles', MANGA_ADMIN_PANEL_URL . 'assets/css/manga-admin-styles.css', array(), MANGA_ADMIN_PANEL_VERSION);
        wp_register_style('manga-admin-shortcodes', MANGA_ADMIN_PANEL_URL . 'assets/css/manga-admin-shortcodes.css', array(), MANGA_ADMIN_PANEL_VERSION);
        
        // Font Awesome
        wp_register_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4');
        
        // Scripts principais
        wp_register_script('manga-admin-scripts', MANGA_ADMIN_PANEL_URL . 'assets/js/manga-admin-scripts.js', array('jquery'), MANGA_ADMIN_PANEL_VERSION, true);
        
        // Toastr para notificações
        wp_register_style('toastr', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css', array(), '2.1.4');
        wp_register_script('toastr', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js', array('jquery'), '2.1.4', true);
        
        // Dropzone para uploads
        wp_register_style('dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css', array(), '5.9.3');
        wp_register_script('dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js', array('jquery'), '5.9.3', true);
        
        // Slick Carousel
        wp_register_style('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', array(), '1.8.1');
        wp_register_style('slick-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css', array(), '1.8.1');
        wp_register_script('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
        
        // jQuery UI para datepicker e outros componentes
        wp_register_style('jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css', array(), '1.12.1');
        
        // Carregar estilos e scripts em todas as páginas onde usamos shortcodes
        if (is_singular() || is_archive()) {
            global $post;
            
            $load_assets = false;
            
            // Verificar conteúdo da página para shortcodes
            if (is_singular() && $post && has_shortcode($post->post_content, 'manga_admin_panel') ||
                has_shortcode($post->post_content, 'manga_upload') ||
                has_shortcode($post->post_content, 'manga_user_profile') ||
                has_shortcode($post->post_content, 'manga_reader') ||
                has_shortcode($post->post_content, 'manga_display')) {
                $load_assets = true;
            }
            
            // Verificar se estamos usando um modelo de página específico
            if (is_page() && (
                get_page_template_slug($post->ID) === 'manga-admin-dashboard.php' ||
                get_page_template_slug($post->ID) === 'manga-reader-template.php' ||
                get_page_template_slug($post->ID) === 'manga-profile-template.php'
            )) {
                $load_assets = true;
            }
            
            if ($load_assets) {
                // Carregando todos os estilos necessários
                wp_enqueue_style('manga-admin-styles');
                wp_enqueue_style('manga-admin-shortcodes');
                wp_enqueue_style('font-awesome');
                wp_enqueue_style('toastr');
                
                // Carregando scripts necessários
                wp_enqueue_script('manga-admin-scripts');
                wp_enqueue_script('toastr');
                
                // Localizando script com variáveis
                wp_localize_script('manga-admin-scripts', 'mangaAdminVars', array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('manga_admin_nonce'),
                    'user_logged_in' => is_user_logged_in() ? 'yes' : 'no',
                    'user_id' => get_current_user_id(),
                    'home_url' => home_url(),
                    'current_page_id' => get_the_ID(),
                    'i18n' => array(
                        'confirm_delete' => __('Tem certeza que deseja excluir?', 'manga-admin-panel'),
                        'saving' => __('Salvando...', 'manga-admin-panel'),
                        'success' => __('Operação concluída com sucesso!', 'manga-admin-panel'),
                        'error' => __('Ocorreu um erro. Por favor, tente novamente.', 'manga-admin-panel'),
                        'login_required' => __('Você precisa estar logado para acessar esta função.', 'manga-admin-panel'),
                        'upload_complete' => __('Upload concluído com sucesso!', 'manga-admin-panel'),
                        'upload_error' => __('Erro ao fazer upload. Verifique o tipo de arquivo e tente novamente.', 'manga-admin-panel')
                    )
                ));
                
                // Adicionar CSS personalizado
                $custom_css = $this->generate_custom_css();
                wp_add_inline_style('manga-admin-styles', $custom_css);
            }
        }
    }
    
    /**
     * Registrar scripts e estilos para o admin
     */
    public function register_admin_assets($hook) {
        // Registrar assets específicos para o admin
        wp_enqueue_style('manga-admin-admin', MANGA_ADMIN_PANEL_URL . 'assets/css/manga-admin-admin.css', array(), MANGA_ADMIN_PANEL_VERSION);
        wp_enqueue_script('manga-admin-admin', MANGA_ADMIN_PANEL_URL . 'assets/js/manga-admin-admin.js', array('jquery'), MANGA_ADMIN_PANEL_VERSION, true);
    }
    
    /**
     * Carregar arquivos e classes
     */
    private function load_files() {
        // Lista de arquivos a serem carregados
        $files = array(
            'includes/manga-admin-functions.php',
            'includes/manga-ajax-handlers.php',
            'includes/shortcodes-loader.php',
        );
        
        // Tentar carregar cada arquivo
        foreach ($files as $file) {
            $file_path = MANGA_ADMIN_PANEL_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                // Registrar erro em log, mas continuar funcionando
                error_log(sprintf('Manga Admin Panel: Arquivo %s não encontrado.', $file_path));
            }
        }
        
        // Carregar integração com Elementor se estiver ativo
        if (did_action('elementor/loaded')) {
            // Arquivo principal de integração Elementor
            $elementor_file = MANGA_ADMIN_PANEL_PATH . 'includes/manga-elementor-widget.php';
            if (file_exists($elementor_file)) {
                require_once $elementor_file;
            }
        }
    }
    
    /**
     * Gerar CSS personalizado com base nas cores configuradas
     */
    public function generate_custom_css() {
        $colors = $this->get_color_options();
        
        $custom_css = "
        :root {
            --manga-primary-color: {$colors['primary_color']};
            --manga-secondary-color: {$colors['secondary_color']};
            --manga-accent-color: {$colors['accent_color']};
            --manga-success-color: {$colors['success_color']};
            --manga-danger-color: {$colors['danger_color']};
            --manga-background-color: {$colors['background_color']};
            --manga-card-color: {$colors['card_color']};
            --manga-text-color: {$colors['text_color']};
            --manga-light-text: {$colors['light_text']};
        }
        
        /* Estilos fundamentais para formulários */
        .manga-form-control {
            color: {$colors['text_color']} !important;
        }
        
        .manga-form-control:focus {
            color: {$colors['text_color']} !important;
            border-color: {$colors['primary_color']} !important;
        }
        
        /* Correção de cores em elementos específicos */
        .manga-btn-primary { 
            background-color: {$colors['primary_color']} !important;
            color: white !important;
        }
        
        .manga-btn-secondary { 
            background-color: {$colors['secondary_color']} !important;
            color: white !important;
        }
        
        .manga-btn-accent { 
            background-color: {$colors['accent_color']} !important;
            color: white !important;
        }
        
        .manga-btn-success { 
            background-color: {$colors['success_color']} !important;
            color: white !important;
        }
        
        .manga-btn-danger { 
            background-color: {$colors['danger_color']} !important;
            color: white !important;
        }
        ";
        
        return $custom_css;
    }
    
    /**
     * Obter opções de cores
     */
    public function get_color_options() {
        $default_colors = array(
            'primary_color' => '#ff6b6b',
            'secondary_color' => '#576574',
            'accent_color' => '#4b7bec',
            'success_color' => '#1dd1a1',
            'danger_color' => '#ff7675',
            'background_color' => '#f7f7f7',
            'card_color' => '#ffffff',
            'text_color' => '#333333',
            'light_text' => '#718093'
        );
        
        $saved_colors = get_option('manga_admin_panel_colors', array());
        
        return wp_parse_args($saved_colors, $default_colors);
    }
    
    /**
     * Adicionar modelos de página personalizados
     */
    public function add_page_templates($templates) {
        $templates['manga-admin-dashboard.php'] = 'Manga Admin Dashboard';
        $templates['manga-reader-template.php'] = 'Manga Reader Template';
        $templates['manga-profile-template.php'] = 'Manga Profile Template';
        return $templates;
    }
    
    /**
     * Carregar o modelo de página correto
     */
    public function load_template($template) {
        $post = get_post();
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ('manga-admin-dashboard.php' === $page_template) {
            $new_template = MANGA_ADMIN_PANEL_PATH . 'templates/manga-admin-dashboard.php';
            if (file_exists($new_template)) {
                return $new_template;
            }
        }
        
        if ('manga-reader-template.php' === $page_template) {
            $new_template = MANGA_ADMIN_PANEL_PATH . 'templates/manga-reader-template.php';
            if (file_exists($new_template)) {
                return $new_template;
            }
        }
        
        if ('manga-profile-template.php' === $page_template) {
            $new_template = MANGA_ADMIN_PANEL_PATH . 'templates/manga-profile-template.php';
            if (file_exists($new_template)) {
                return $new_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Ação de ativação do plugin
     */
    public function activate() {
        // Criar páginas padrão se não existirem
        $this->maybe_create_pages();
        
        // Registrar opções padrão
        $this->register_default_options();
        
        // Limpar regras de rewrite
        flush_rewrite_rules();
    }
    
    /**
     * Criar páginas padrão para o plugin
     */
    private function maybe_create_pages() {
        $pages = array(
            'manga-admin-dashboard' => array(
                'title' => __('Painel de Administração de Mangá', 'manga-admin-panel'),
                'content' => '[manga_admin_panel]',
                'template' => 'manga-admin-dashboard.php'
            ),
            'manga-profile' => array(
                'title' => __('Meu Perfil de Mangá', 'manga-admin-panel'),
                'content' => '[manga_user_profile]',
                'template' => 'manga-profile-template.php'
            ),
            'manga-reader' => array(
                'title' => __('Leitor de Mangá', 'manga-admin-panel'),
                'content' => '[manga_reader]',
                'template' => 'manga-reader-template.php'
            ),
            'manga-upload' => array(
                'title' => __('Upload de Mangá', 'manga-admin-panel'),
                'content' => '[manga_upload]',
                'template' => ''
            ),
            'manga-display' => array(
                'title' => __('Biblioteca de Mangás', 'manga-admin-panel'),
                'content' => '[manga_display]',
                'template' => ''
            )
        );
        
        foreach ($pages as $slug => $page_data) {
            // Verificar se a página já existe
            $page_exists = get_page_by_path($slug);
            
            if (!$page_exists) {
                // Criar nova página
                $page_id = wp_insert_post(array(
                    'post_title' => $page_data['title'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $slug
                ));
                
                // Definir template se especificado
                if (!empty($page_data['template'])) {
                    update_post_meta($page_id, '_wp_page_template', $page_data['template']);
                }
            }
        }
    }
    
    /**
     * Registrar opções padrão
     */
    private function register_default_options() {
        // Cores padrão já registradas em get_color_options
        $default_colors = $this->get_color_options();
        
        // Adicionar opção apenas se não existir
        if (!get_option('manga_admin_panel_colors')) {
            add_option('manga_admin_panel_colors', $default_colors);
        }
        
        // Outras opções do plugin
        $default_options = array(
            'show_admin_bar' => 'yes',
            'default_reader_mode' => 'pagination',
            'default_display_mode' => 'grid',
            'default_chapters_order' => 'desc',
            'enable_comments' => 'yes',
            'enable_ratings' => 'yes'
        );
        
        // Adicionar opções apenas se não existirem
        if (!get_option('manga_admin_panel_options')) {
            add_option('manga_admin_panel_options', $default_options);
        }
    }
    
    /**
     * Ação de desativação do plugin
     */
    public function deactivate() {
        // Limpar possíveis crons
        wp_clear_scheduled_hook('manga_admin_panel_daily_cleanup');
        
        // Limpar regras de rewrite
        flush_rewrite_rules();
    }
}

// Inicializar o plugin
function manga_admin_panel() {
    return Manga_Admin_Panel::get_instance();
}

// Iniciar o plugin
manga_admin_panel();

/**
 * Função auxiliar para verificar acesso
 * Usado nos shortcodes e templates
 */
function manga_admin_panel_has_access() {
    // Verificar se o usuário está logado
    if (!is_user_logged_in()) {
        return false;
    }
    
    // Permitir qualquer usuário com papéis específicos
    $allowed_roles = array('administrator', 'editor', 'manga_editor', 'author');
    $user = wp_get_current_user();
    
    // Verificar papéis permitidos
    foreach ($allowed_roles as $role) {
        if (in_array($role, (array) $user->roles)) {
            return true;
        }
    }
    
    // Verificar se tem meta personalizado que permite acesso
    if (get_user_meta(get_current_user_id(), 'can_manage_manga', true) == 'yes') {
        return true;
    }
    
    // Verificar se é autor do mangá específico (para uso nos shortcodes)
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $manga_id = intval($_GET['id']);
        $manga = get_post($manga_id);
        
        if ($manga && $manga->post_author == get_current_user_id()) {
            return true;
        }
    }
    
    // Verificar se possui a capacidade personalizada
    if (current_user_can('manage_manga')) {
        return true;
    }
    
    return false;
}

/**
 * Função para exibir formulário de login inline
 * Usado nos shortcodes quando o usuário não está logado
 */
function manga_admin_login_form($message = '') {
    ob_start();
    
    if (!empty($message)) {
        echo '<div class="manga-alert manga-alert-info">' . esc_html($message) . '</div>';
    }
    
    ?>
    <div class="manga-login-form">
        <h3><?php _e('Login', 'manga-admin-panel'); ?></h3>
        <?php
        // Mostrar formulário de login do WordPress
        $args = array(
            'echo'           => true,
            'remember'       => true,
            'redirect'       => (!empty($_SERVER['REQUEST_URI']) ? esc_url($_SERVER['REQUEST_URI']) : ''),
            'form_id'        => 'manga-loginform',
            'id_username'    => 'manga-user-login',
            'id_password'    => 'manga-user-pass',
            'id_remember'    => 'manga-rememberme',
            'id_submit'      => 'manga-submit',
            'label_username' => __('Nome de usuário ou e-mail', 'manga-admin-panel'),
            'label_password' => __('Senha', 'manga-admin-panel'),
            'label_remember' => __('Lembrar-me', 'manga-admin-panel'),
            'label_log_in'   => __('Entrar', 'manga-admin-panel'),
            'value_username' => '',
            'value_remember' => false
        );
        
        wp_login_form($args);
        ?>
        <p class="manga-login-register">
            <?php if (get_option('users_can_register')): ?>
                <a href="<?php echo esc_url(wp_registration_url()); ?>"><?php _e('Registrar', 'manga-admin-panel'); ?></a> | 
            <?php endif; ?>
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php _e('Esqueci minha senha', 'manga-admin-panel'); ?></a>
        </p>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Shortcode para exibir formulário de diagnóstico
 * Útil para depurar problemas
 */
function manga_admin_debug_shortcode() {
    if (!current_user_can('manage_options')) {
        return '<div class="manga-alert manga-alert-warning">' . 
               __('Apenas administradores podem acessar informações de depuração.', 'manga-admin-panel') .
               '</div>';
    }
    
    ob_start();
    ?>
    <div class="manga-admin-debug">
        <h3><?php _e('Informações de Depuração do Manga Admin Panel', 'manga-admin-panel'); ?></h3>
        
        <p><strong><?php _e('Versão do Plugin:', 'manga-admin-panel'); ?></strong> <?php echo MANGA_ADMIN_PANEL_VERSION; ?></p>
        
        <p><strong><?php _e('Caminho do Plugin:', 'manga-admin-panel'); ?></strong> <?php echo MANGA_ADMIN_PANEL_PATH; ?></p>
        
        <p><strong><?php _e('URL do Plugin:', 'manga-admin-panel'); ?></strong> <?php echo MANGA_ADMIN_PANEL_URL; ?></p>
        
        <p><strong><?php _e('Shortcodes Registrados:', 'manga-admin-panel'); ?></strong></p>
        <ul>
        <?php 
        global $shortcode_tags;
        foreach ($shortcode_tags as $tag => $function) {
            if (strpos($tag, 'manga_') !== false) {
                echo '<li>' . esc_html($tag) . '</li>';
            }
        }
        ?>
        </ul>
        
        <p><strong><?php _e('Modelos de Página Registrados:', 'manga-admin-panel'); ?></strong></p>
        <ul>
            <li>manga-admin-dashboard.php</li>
            <li>manga-reader-template.php</li>
            <li>manga-profile-template.php</li>
        </ul>
        
        <p><strong><?php _e('Elementor Integrado:', 'manga-admin-panel'); ?></strong> 
            <?php echo did_action('elementor/loaded') ? __('Sim', 'manga-admin-panel') : __('Não', 'manga-admin-panel'); ?>
        </p>
        
        <p><strong><?php _e('Usuário atual pode gerenciar mangás:', 'manga-admin-panel'); ?></strong> 
            <?php echo manga_admin_panel_has_access() ? __('Sim', 'manga-admin-panel') : __('Não', 'manga-admin-panel'); ?>
        </p>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('manga_admin_debug', 'manga_admin_debug_shortcode');