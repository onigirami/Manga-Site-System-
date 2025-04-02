<?php
/**
 * Manga Admin Panel Shortcodes Loader
 * 
 * Carrega todos os shortcodes disponíveis
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Carregar todos os arquivos de shortcodes
$shortcodes_dir = MANGA_ADMIN_PANEL_PATH . 'includes/shortcodes/';
$shortcode_files = glob($shortcodes_dir . '*.php');

if (!empty($shortcode_files)) {
    foreach ($shortcode_files as $shortcode_file) {
        require_once $shortcode_file;
    }
}

/**
 * Adicionar suporte a shortcodes em widgets de texto
 */
add_filter('widget_text', 'do_shortcode');

/**
 * Registrar estilos específicos para shortcodes
 */
function manga_admin_shortcodes_styles() {
    wp_register_style(
        'manga-admin-shortcodes',
        MANGA_ADMIN_PANEL_URL . 'assets/css/manga-admin-shortcodes.css',
        array(),
        MANGA_ADMIN_PANEL_VERSION
    );
    
    // Adicionar estilos apenas quando necessário
    global $post;
    if (is_a($post, 'WP_Post') && (
        has_shortcode($post->post_content, 'manga_admin_panel') ||
        has_shortcode($post->post_content, 'manga_dashboard') ||
        has_shortcode($post->post_content, 'manga_chapter_manager') ||
        has_shortcode($post->post_content, 'manga_creator') ||
        has_shortcode($post->post_content, 'manga_upload') ||
        has_shortcode($post->post_content, 'manga_reader') ||
        has_shortcode($post->post_content, 'manga_user_profile')
    )) {
        wp_enqueue_style('manga-admin-shortcodes');
    }
}
add_action('wp_enqueue_scripts', 'manga_admin_shortcodes_styles');

/**
 * Adicionar botões de shortcode ao editor clássico
 */
function manga_admin_add_shortcode_buttons() {
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
        return;
    }
    
    if (get_user_option('rich_editing') !== 'true') {
        return;
    }
    
    add_filter('mce_external_plugins', 'manga_admin_add_shortcode_tinymce_plugin');
    add_filter('mce_buttons', 'manga_admin_register_shortcode_button');
}
add_action('admin_init', 'manga_admin_add_shortcode_buttons');

/**
 * Registrar novo botão no editor
 */
function manga_admin_register_shortcode_button($buttons) {
    array_push($buttons, 'manga_admin_shortcodes');
    return $buttons;
}

/**
 * Adicionar plugin TinyMCE para shortcodes
 */
function manga_admin_add_shortcode_tinymce_plugin($plugin_array) {
    $plugin_array['manga_admin_shortcodes'] = MANGA_ADMIN_PANEL_URL . 'assets/js/shortcode-button.js';
    return $plugin_array;
}

/**
 * Enviar lista de shortcodes para o editor
 */
function manga_admin_shortcodes_js() {
    ?>
    <script type="text/javascript">
    var mangaAdminShortcodes = [
        {
            text: '<?php _e("Painel de Administração Completo", "manga-admin-panel"); ?>',
            value: '[manga_admin_panel]'
        },
        {
            text: '<?php _e("Dashboard de Mangás", "manga-admin-panel"); ?>',
            value: '[manga_dashboard]'
        },
        {
            text: '<?php _e("Gerenciador de Capítulos", "manga-admin-panel"); ?>',
            value: '[manga_chapter_manager manga_id="ID_DO_MANGA"]'
        },
        {
            text: '<?php _e("Criar/Editar Mangá", "manga-admin-panel"); ?>',
            value: '[manga_creator]'
        },
        {
            text: '<?php _e("Upload de Capítulos", "manga-admin-panel"); ?>',
            value: '[manga_upload manga_id="ID_DO_MANGA" show_title="yes" max_files="50" allow_scheduling="yes"]'
        },
        {
            text: '<?php _e("Leitor de Mangá", "manga-admin-panel"); ?>',
            value: '[manga_reader manga_id="ID_DO_MANGA" chapter_id="ID_DO_CAPITULO" show_navigation="yes" show_comments="yes" reading_direction="default"]'
        },
        {
            text: '<?php _e("Perfil do Usuário", "manga-admin-panel"); ?>',
            value: '[manga_user_profile show_avatar="yes" show_stats="yes" show_recent="yes" recent_count="5" show_edit_profile="yes"]'
        },
        {
            text: '<?php _e("Conteúdo Condicional", "manga-admin-panel"); ?>',
            value: '[manga_user state="logged_in"]Conteúdo visível apenas para usuários logados[/manga_user]'
        }
    ];
    </script>
    <?php
}
add_action('admin_head', 'manga_admin_shortcodes_js');
