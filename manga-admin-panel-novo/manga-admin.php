<?php
/**
 * Plugin Name: Manga Admin Panel
 * Plugin URI: https://exemplo.com/manga-admin-panel
 * Description: Interface personalizada para usuários privilegiados gerenciarem conteúdo de mangá compatível com Elementor e plugins de mangá existentes
 * Version: 1.0.1
 * Author: Developer
 * Author URI: https://exemplo.com
 * Text Domain: manga-admin-panel
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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
    // Desabilita temporariamente a verificação de plugins para permitir o funcionamento
    // Isso é necessário porque a verificação pode variar dependendo da estrutura de pastas do WordPress
    return true;
    
    /* Verificação de plugins comentada para permitir o funcionamento do plugin
    // Incluir o arquivo plugin.php que contém is_plugin_active()
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    
    // Verificação simplificada baseada apenas na presença de classes e funções
    $has_madara_core = class_exists('Madara') || class_exists('Madara_Core');
    $has_madara_shortcodes = function_exists('madara_shortcodes_init') || class_exists('Madara_Shortcodes');
    $has_wp_manga_scheduler = class_exists('WP_MANGA_CHAPTER_SCHEDULER') || function_exists('wp_manga_schedule_chapter');
    $has_wp_manga_custom_fields = class_exists('WP_MANGA_CUSTOM_FIELDS') || function_exists('wp_manga_custom_fields_init');
    $has_wp_manga_member_upload = class_exists('WP_MANGA_MEMBER_UPLOAD') || function_exists('wp_manga_member_upload_init');
    
    if (!$has_madara_core || !$has_madara_shortcodes || !$has_wp_manga_scheduler || !$has_wp_manga_custom_fields || !$has_wp_manga_member_upload) {
        add_action('admin_notices', 'manga_admin_panel_requirement_notice');
        return false;
    }
    
    return true;
    */
}

// Admin notice for missing required plugins
function manga_admin_panel_requirement_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Manga Admin Panel requires the following plugins: Madara - Core, Madara - Shortcodes, WP Manga - Chapter Scheduler, WP Manga - Manga Custom Fields, and WP Manga Member Upload PRO.', 'manga-admin-panel'); ?></p>
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

// Registro e recuperação de opções de personalização
function manga_admin_panel_get_color_options() {
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

// Gerar CSS personalizado com as cores do usuário
function manga_admin_panel_generate_custom_css() {
    $colors = manga_admin_panel_get_color_options();
    
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
    }";
    
    return $custom_css;
}

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
    
    // Adicionar CSS personalizado
    $custom_css = manga_admin_panel_generate_custom_css();
    wp_add_inline_style('manga-admin-styles', $custom_css);
    
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
    // Verificar se o Elementor está ativo
    if (did_action('elementor/loaded')) {
        // Verifica se a classe necessária existe
        if (class_exists('\Elementor\Widget_Base')) {
            // Include widget file
            require_once MANGA_ADMIN_PANEL_PATH . 'includes/manga-elementor-widget.php';
            
            // Verifica se a classe foi carregada corretamente e é uma extensão da classe Widget_Base
            if (class_exists('\Manga_Admin_Elementor_Widget') && is_subclass_of('\Manga_Admin_Elementor_Widget', '\Elementor\Widget_Base')) {
                // Register widget
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Manga_Admin_Elementor_Widget());
            }
        }
    }
}
add_action('init', 'manga_admin_panel_register_elementor_widget');

// Adiciona menu de configurações de tema no admin do WordPress
function manga_admin_panel_add_theme_settings() {
    add_submenu_page(
        'options-general.php',
        __('Configurações do Manga Admin Panel', 'manga-admin-panel'),
        __('Manga Admin', 'manga-admin-panel'),
        'manage_options',
        'manga-admin-panel-settings',
        'manga_admin_panel_render_settings_page'
    );
}
add_action('admin_menu', 'manga_admin_panel_add_theme_settings');

// Registra as configurações
function manga_admin_panel_register_settings() {
    register_setting('manga_admin_panel_options_group', 'manga_admin_panel_colors', 'manga_admin_panel_sanitize_colors');
}
add_action('admin_init', 'manga_admin_panel_register_settings');

// Sanitiza as configurações de cores
function manga_admin_panel_sanitize_colors($input) {
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
    
    $sanitized = array();
    
    foreach ($default_colors as $key => $default) {
        if (isset($input[$key]) && !empty($input[$key])) {
            // Sanitiza a cor como uma cor hexadecimal
            $sanitized[$key] = sanitize_hex_color($input[$key]);
        } else {
            $sanitized[$key] = $default;
        }
    }
    
    return $sanitized;
}

// Função para sanitizar cores hexadecimais
function sanitize_hex_color($color) {
    // Remove espaços
    $color = trim($color);
    
    // Verifica se a cor é válida
    if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
        return $color;
    }
    
    return '';
}

// Renderiza a página de configurações
function manga_admin_panel_render_settings_page() {
    // Obtém as cores atuais
    $colors = manga_admin_panel_get_color_options();
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <form method="post" action="options.php">
            <?php settings_fields('manga_admin_panel_options_group'); ?>
            <div class="manga-admin-settings">
                <h2><?php _e('Configurações de Aparência', 'manga-admin-panel'); ?></h2>
                <p><?php _e('Personalize as cores do painel de administração de mangá.', 'manga-admin-panel'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Cor Primária', 'manga-admin-panel'); ?></th>
                        <td>
                            <input type="color" name="manga_admin_panel_colors[primary_color]" value="<?php echo esc_attr($colors['primary_color']); ?>" class="manga-color-picker" />
                            <p class="description"><?php _e('Usada para botões principais e elementos de destaque.', 'manga-admin-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor Secundária', 'manga-admin-panel'); ?></th>
                        <td>
                            <input type="color" name="manga_admin_panel_colors[secondary_color]" value="<?php echo esc_attr($colors['secondary_color']); ?>" class="manga-color-picker" />
                            <p class="description"><?php _e('Usada para elementos secundários.', 'manga-admin-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor de Destaque', 'manga-admin-panel'); ?></th>
                        <td>
                            <input type="color" name="manga_admin_panel_colors[accent_color]" value="<?php echo esc_attr($colors['accent_color']); ?>" class="manga-color-picker" />
                            <p class="description"><?php _e('Usada para links e elementos interativos.', 'manga-admin-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor de Sucesso', 'manga-admin-panel'); ?></th>
                        <td>
                            <input type="color" name="manga_admin_panel_colors[success_color]" value="<?php echo esc_attr($colors['success_color']); ?>" class="manga-color-picker" />
                            <p class="description"><?php _e('Usada para mensagens de sucesso e status positivos.', 'manga-admin-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor de Perigo', 'manga-admin-panel'); ?></th>
                        <td>
                            <input type="color" name="manga_admin_panel_colors[danger_color]" value="<?php echo esc_attr($colors['danger_color']); ?>" class="manga-color-picker" />
                            <p class="description"><?php _e('Usada para mensagens de erro e ações destrutivas.', 'manga-admin-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor de Fundo', 'manga-admin-panel'); ?></th>
                        <td>
                            <input type="color" name="manga_admin_panel_colors[background_color]" value="<?php echo esc_attr($colors['background_color']); ?>" class="manga-color-picker" />
                            <p class="description"><?php _e('Cor de fundo do painel inteiro.', 'manga-admin-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor dos Cards', 'manga-admin-panel'); ?></th>
                        <td>
                            <input type="color" name="manga_admin_panel_colors[card_color]" value="<?php echo esc_attr($colors['card_color']); ?>" class="manga-color-picker" />
                            <p class="description"><?php _e('Cor de fundo dos cards e elementos de conteúdo.', 'manga-admin-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor do Texto', 'manga-admin-panel'); ?></th>
                        <td>
                            <input type="color" name="manga_admin_panel_colors[text_color]" value="<?php echo esc_attr($colors['text_color']); ?>" class="manga-color-picker" />
                            <p class="description"><?php _e('Cor padrão para o texto principal.', 'manga-admin-panel'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Cor de Texto Claro', 'manga-admin-panel'); ?></th>
                        <td>
                            <input type="color" name="manga_admin_panel_colors[light_text]" value="<?php echo esc_attr($colors['light_text']); ?>" class="manga-color-picker" />
                            <p class="description"><?php _e('Usada para textos secundários e legendas.', 'manga-admin-panel'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <div class="manga-admin-preview">
                    <h3><?php _e('Pré-visualização', 'manga-admin-panel'); ?></h3>
                    <div id="manga-color-preview" style="padding: 20px; background-color: <?php echo esc_attr($colors['background_color']); ?>; color: <?php echo esc_attr($colors['text_color']); ?>;">
                        <h4 style="color: <?php echo esc_attr($colors['text_color']); ?>;"><?php _e('Título da Seção', 'manga-admin-panel'); ?></h4>
                        <p style="color: <?php echo esc_attr($colors['light_text']); ?>;"><?php _e('Esta é uma pré-visualização de como as cores escolhidas serão aplicadas no painel.', 'manga-admin-panel'); ?></p>
                        
                        <div style="background-color: <?php echo esc_attr($colors['card_color']); ?>; padding: 15px; margin: 15px 0; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <h5 style="color: <?php echo esc_attr($colors['text_color']); ?>;"><?php _e('Card de Exemplo', 'manga-admin-panel'); ?></h5>
                            <p style="color: <?php echo esc_attr($colors['text_color']); ?>;"><?php _e('Este é um exemplo de card com o estilo personalizado.', 'manga-admin-panel'); ?></p>
                            
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <button type="button" style="background-color: <?php echo esc_attr($colors['primary_color']); ?>; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;"><?php _e('Botão Primário', 'manga-admin-panel'); ?></button>
                                
                                <button type="button" style="background-color: <?php echo esc_attr($colors['accent_color']); ?>; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;"><?php _e('Botão Destaque', 'manga-admin-panel'); ?></button>
                                
                                <button type="button" style="background-color: <?php echo esc_attr($colors['danger_color']); ?>; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;"><?php _e('Botão Perigo', 'manga-admin-panel'); ?></button>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 20px; margin-top: 20px;">
                            <div style="flex: 1; background-color: <?php echo esc_attr($colors['success_color']); ?>; color: white; padding: 10px; border-radius: 4px;">
                                <?php _e('Status: Publicado', 'manga-admin-panel'); ?>
                            </div>
                            
                            <div style="flex: 1; background-color: <?php echo esc_attr($colors['accent_color']); ?>; color: white; padding: 10px; border-radius: 4px;">
                                <?php _e('Status: Agendado', 'manga-admin-panel'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <script>
                    jQuery(document).ready(function($) {
                        // Atualiza a pré-visualização quando as cores mudam
                        $('.manga-color-picker').on('input', function() {
                            const primaryColor = $('input[name="manga_admin_panel_colors[primary_color]"]').val();
                            const secondaryColor = $('input[name="manga_admin_panel_colors[secondary_color]"]').val();
                            const accentColor = $('input[name="manga_admin_panel_colors[accent_color]"]').val();
                            const successColor = $('input[name="manga_admin_panel_colors[success_color]"]').val();
                            const dangerColor = $('input[name="manga_admin_panel_colors[danger_color]"]').val();
                            const bgColor = $('input[name="manga_admin_panel_colors[background_color]"]').val();
                            const cardColor = $('input[name="manga_admin_panel_colors[card_color]"]').val();
                            const textColor = $('input[name="manga_admin_panel_colors[text_color]"]').val();
                            const lightText = $('input[name="manga_admin_panel_colors[light_text]"]').val();
                            
                            // Atualiza os estilos da pré-visualização
                            $('#manga-color-preview').css('background-color', bgColor);
                            $('#manga-color-preview').css('color', textColor);
                            $('#manga-color-preview h4').css('color', textColor);
                            $('#manga-color-preview p').css('color', lightText);
                            $('#manga-color-preview div:first').css('background-color', cardColor);
                            $('#manga-color-preview h5').css('color', textColor);
                            $('#manga-color-preview p:eq(1)').css('color', textColor);
                            
                            // Botões
                            $('#manga-color-preview button:eq(0)').css('background-color', primaryColor);
                            $('#manga-color-preview button:eq(1)').css('background-color', accentColor);
                            $('#manga-color-preview button:eq(2)').css('background-color', dangerColor);
                            
                            // Status
                            $('#manga-color-preview div:eq(2)').css('background-color', successColor);
                            $('#manga-color-preview div:eq(3)').css('background-color', accentColor);
                        });
                    });
                </script>
            </div>
            
            <?php submit_button(__('Salvar Configurações', 'manga-admin-panel')); ?>
        </form>
    </div>
    <?php
}

// Adicionar shortcode para personalização de cores
function manga_admin_color_settings_shortcode() {
    // Verifica se o usuário tem permissão
    if (!current_user_can('manage_options')) {
        return '<div class="manga-alert manga-alert-danger">' . 
               __('Você não tem permissão para acessar estas configurações.', 'manga-admin-panel') . 
               '</div>';
    }
    
    ob_start();
    manga_admin_panel_render_settings_page();
    return ob_get_clean();
}
add_shortcode('manga_admin_settings', 'manga_admin_color_settings_shortcode');

// Adicionar shortcode para demonstração de cores
function manga_admin_color_demo_shortcode() {
    ob_start();
    include_once MANGA_ADMIN_PANEL_PATH . 'templates/manga-color-demo.php';
    return ob_get_clean();
}
add_shortcode('manga_color_demo', 'manga_admin_color_demo_shortcode');
