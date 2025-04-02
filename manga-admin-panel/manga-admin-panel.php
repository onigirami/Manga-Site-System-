<?php
/**
 * Plugin Name: Manga Admin Panel
 * Description: Interface personalizada para usuários privilegiados gerenciarem conteúdo de mangá
 * Version: 1.0
 * Author: Developer
 * Text Domain: manga-admin-panel
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MANGA_ADMIN_PANEL_VERSION', '1.0');
define('MANGA_ADMIN_PANEL_PATH', plugin_dir_path(__FILE__));
define('MANGA_ADMIN_PANEL_URL', plugin_dir_url(__FILE__));

// Check if required plugins are active
function manga_admin_panel_check_requirements() {
    // Incluir o arquivo plugin.php que contém is_plugin_active()
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    
    if (!is_plugin_active('madara-core/madara-core.php') || 
        !is_plugin_active('wp-manga-chapter-scheduler/wp-manga-chapter-scheduler.php') || 
        !is_plugin_active('wp-manga-custom-fields/wp-manga-custom-fields.php') || 
        !is_plugin_active('wp-manga-member-upload-pro/wp-manga-member-upload-pro.php')) {
        
        add_action('admin_notices', 'manga_admin_panel_requirement_notice');
        return false;
    }
    return true;
}

// Admin notice for missing required plugins
function manga_admin_panel_requirement_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Manga Admin Panel requires the following plugins: Madara Core, WP Manga Chapter Scheduler, WP Manga Custom Fields, and WP Manga Member Upload PRO.', 'manga-admin-panel'); ?></p>
    </div>
    <?php
}

// Load plugin files
function manga_admin_panel_load_files() {
    if (!manga_admin_panel_check_requirements()) {
        return;
    }
    
    // Include function files
    require_once MANGA_ADMIN_PANEL_PATH . 'includes/manga-admin-functions.php';
    require_once MANGA_ADMIN_PANEL_PATH . 'includes/manga-elementor-widget.php';
    require_once MANGA_ADMIN_PANEL_PATH . 'includes/manga-ajax-handlers.php';
    
    // Carregar shortcodes
    require_once MANGA_ADMIN_PANEL_PATH . 'includes/shortcodes-loader.php';
}
add_action('plugins_loaded', 'manga_admin_panel_load_files');

// Register styles and scripts

// Register styles and scripts
function manga_admin_panel_enqueue_assets() {
    // Carrega em qualquer página do frontend onde o usuário esteja logado
    // e também no template específico do painel de admin
    if (!is_user_logged_in() && !is_page_template('manga-admin-dashboard')) {
        return;
    }
    
    // CSS
    wp_enqueue_style('manga-admin-styles', MANGA_ADMIN_PANEL_URL . 'assets/css/manga-admin-styles.css', array(), MANGA_ADMIN_PANEL_VERSION);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4');
    
    // JavaScript
    wp_enqueue_script('manga-admin-scripts', MANGA_ADMIN_PANEL_URL . 'assets/js/manga-admin-scripts.js', array('jquery'), MANGA_ADMIN_PANEL_VERSION, true);
    
    // Notificações com Toastr
    wp_enqueue_style('toastr', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css', array(), MANGA_ADMIN_PANEL_VERSION);
    wp_enqueue_script('toastr', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js', array('jquery'), MANGA_ADMIN_PANEL_VERSION, true);
    
    // Adiciona dropzone para uploads mais intuitivos
    wp_enqueue_style('dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css', array(), '5.9.3');
    wp_enqueue_script('dropzone', 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js', array('jquery'), '5.9.3', true);
    
    // Carrega datepicker para agendamento
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css', array(), '1.12.1');
    
    // Localize script for AJAX
    wp_localize_script('manga-admin-scripts', 'mangaAdminVars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('manga_admin_nonce'),
        'user_logged_in' => is_user_logged_in() ? 'yes' : 'no',
        'user_id' => get_current_user_id(),
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
}























add_action('wp_enqueue_scripts', 'manga_admin_panel_enqueue_assets');

// Add custom page template

// Add custom page template and shortcodes para uso em qualquer página
function manga_admin_panel_add_template($templates) {
    $templates['manga-admin-dashboard'] = 'Manga Admin Dashboard';
    return $templates;
}
add_filter('theme_page_templates', 'manga_admin_panel_add_template');

// Shortcode para inserir o painel completo em qualquer página
function manga_admin_panel_shortcode($atts) {
    // Verifica se o usuário está logado e tem permissões
    if (!manga_admin_panel_has_access()) {
        return '<div class="manga-alert manga-alert-danger">' . 
               __('Você precisa estar logado com privilégios adequados para acessar este painel.', 'manga-admin-panel') . 
               '</div>';
    }
    
    // Carrega o painel completo
    ob_start();
    include_once MANGA_ADMIN_PANEL_PATH . 'templates/manga-admin-dashboard.php';
    return ob_get_clean();
}
add_shortcode('manga_admin_panel', 'manga_admin_panel_shortcode');

// Shortcode para inserir apenas o dashboard em qualquer página
function manga_admin_dashboard_shortcode($atts) {
    // Verifica se o usuário está logado e tem permissões
    if (!manga_admin_panel_has_access()) {
        return '<div class="manga-alert manga-alert-danger">' . 
               __('Você precisa estar logado com privilégios adequados para acessar este painel.', 'manga-admin-panel') . 
               '</div>';
    }
    
    // Carrega apenas o dashboard
    ob_start();
    include_once MANGA_ADMIN_PANEL_PATH . 'templates/manga-dashboard.php';
    return ob_get_clean();
}
add_shortcode('manga_dashboard', 'manga_admin_dashboard_shortcode');

// Shortcode para inserir apenas o gerenciador de capítulos
function manga_chapter_manager_shortcode($atts) {
    $atts = shortcode_atts(array(
        'manga_id' => 0,
    ), $atts, 'manga_chapter_manager');
    
    // Verifica se o usuário está logado e tem permissões
    if (!manga_admin_panel_has_access()) {
        return '<div class="manga-alert manga-alert-danger">' . 
               __('Você precisa estar logado com privilégios adequados para acessar este painel.', 'manga-admin-panel') . 
               '</div>';
    }
    
    // Verifica se o manga_id é válido
    if (empty($atts['manga_id'])) {
        // Interface para selecionar um mangá primeiro
        ob_start();
        ?>
        <div class="manga-chapter-manager-selector">
            <h2><?php _e('Gerenciador de Capítulos', 'manga-admin-panel'); ?></h2>
            <p><?php _e('Selecione um mangá para gerenciar seus capítulos:', 'manga-admin-panel'); ?></p>
            <select id="manga-select-for-chapters" class="manga-form-control">
                <option value=""><?php _e('Selecione um mangá', 'manga-admin-panel'); ?></option>
                <?php
                $manga_list = manga_admin_get_manga_list();
                foreach ($manga_list as $manga) {
                    echo '<option value="' . esc_attr($manga->ID) . '">' . esc_html($manga->post_title) . '</option>';
                }
                ?>
            </select>
            <button id="go-to-chapter-manager" class="manga-btn manga-btn-primary"><?php _e('Gerenciar Capítulos', 'manga-admin-panel'); ?></button>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#go-to-chapter-manager').on('click', function() {
                    const mangaId = $('#manga-select-for-chapters').val();
                    if (mangaId) {
                        window.location.href = window.location.href + (window.location.href.indexOf('?') !== -1 ? '&' : '?') + 'manga_id=' + mangaId;
                    }
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }
    
    // Carrega o gerenciador de capítulos com o manga_id selecionado
    ob_start();
    $_GET['id'] = intval($atts['manga_id']); // Simula o parâmetro GET para o template
    include_once MANGA_ADMIN_PANEL_PATH . 'templates/manga-chapter-manager.php';
    return ob_get_clean();
}
add_shortcode('manga_chapter_manager', 'manga_chapter_manager_shortcode');

// Adiciona mais shortcodes para outras funcionalidades específicas
function manga_creator_shortcode($atts) {
    // Verifica se o usuário está logado e tem permissões
    if (!manga_admin_panel_has_access()) {
        return '<div class="manga-alert manga-alert-danger">' . 
               __('Você precisa estar logado com privilégios adequados para acessar este recurso.', 'manga-admin-panel') . 
               '</div>';
    }
    
    // Carrega o formulário de criação/edição
    ob_start();
    include_once MANGA_ADMIN_PANEL_PATH . 'templates/manga-create-edit.php';
    return ob_get_clean();
}
add_shortcode('manga_creator', 'manga_creator_shortcode');

// Load template
function manga_admin_panel_load_template($template) {
    $page_template = get_post_meta(get_the_ID(), '_wp_page_template', true);
    
    if ('manga-admin-dashboard' === $page_template) {
        $template = MANGA_ADMIN_PANEL_PATH . 'templates/manga-admin-dashboard.php';
    }
    
    return $template;
}
add_filter('page_template', 'manga_admin_panel_load_template');

// Add access control for the admin panel
function manga_admin_panel_access_control() {
    if (is_page_template('manga-admin-dashboard')) {
        if (!manga_admin_panel_has_access()) {
            wp_redirect(home_url());
            exit;
        }
    }
}
add_action('template_redirect', 'manga_admin_panel_access_control');




























// Check if user has sufficient privileges

// Check if user has sufficient privileges
function manga_admin_panel_has_access() {
    // Verificar se o usuário está logado
    if (!is_user_logged_in()) {
        return false;
    }
    
    // Permitir qualquer usuário com papéis específicos
    // Adicionando "contributor" e "subscriber" com meta personalizado para permitir mais usuários logados
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

// Função para adicionar interface de login inline quando usuário não está logado
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
            <a href="<?php echo esc_url(wp_registration_url()); ?>"><?php _e('Registrar', 'manga-admin-panel'); ?></a> | 
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php _e('Esqueceu a senha?', 'manga-admin-panel'); ?></a>
        </p>
    </div>
    <?php
    
    return ob_get_clean();
}

// Filtro para mostrar conteúdo condicionalmente baseado no estado de login
function manga_user_content_filter($atts, $content = null) {
    $atts = shortcode_atts(array(
        'state' => 'logged_in', // logged_in, logged_out, can_manage
    ), $atts, 'manga_user');
    
    if ($atts['state'] == 'logged_in' && is_user_logged_in()) {
        return do_shortcode($content);
    } elseif ($atts['state'] == 'logged_out' && !is_user_logged_in()) {
        return do_shortcode($content);
    } elseif ($atts['state'] == 'can_manage' && manga_admin_panel_has_access()) {
        return do_shortcode($content);
    }
    
    return '';
}
add_shortcode('manga_user', 'manga_user_content_filter');

// Register custom manga editor role on activation
function manga_admin_panel_activate() {
    // Add custom role for manga editors
    add_role(
        'manga_editor',
        __('Manga Editor', 'manga-admin-panel'),



















        array(
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => true,
            'upload_files' => true,
            'publish_posts' => true,
            'edit_published_posts' => true,
            'delete_published_posts' => true,
            'edit_manga' => true,
            'publish_manga' => true,
            'delete_manga' => true
        )
    );
    
    // Create page with the custom template if it doesn't exist
    $existing_page = get_page_by_path('manga-admin');
    
    if (!$existing_page) {
        $page_id = wp_insert_post(array(
            'post_title' => __('Manga Admin', 'manga-admin-panel'),
            'post_name' => 'manga-admin',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '',
        ));
        
        if ($page_id && !is_wp_error($page_id)) {
            update_post_meta($page_id, '_wp_page_template', 'manga-admin-dashboard');
        }
    }
}
register_activation_hook(__FILE__, 'manga_admin_panel_activate');

// Register Elementor widget
function manga_admin_panel_register_elementor_widget() {
    if (did_action('elementor/loaded')) {
        // Include widget file
        require_once MANGA_ADMIN_PANEL_PATH . 'includes/manga-elementor-widget.php';
        
        // Register widget
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Manga_Admin_Elementor_Widget());
    }
}
add_action('init', 'manga_admin_panel_register_elementor_widget');
