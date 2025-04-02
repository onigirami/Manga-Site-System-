<?php
/**
 * Carregador de Shortcodes
 * Responsável por incluir todos os arquivos de shortcodes
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

// Carregar todos os shortcodes
require_once MANGA_ADMIN_PANEL_PATH . 'includes/shortcodes/manga-upload-shortcode.php';
require_once MANGA_ADMIN_PANEL_PATH . 'includes/shortcodes/manga-display-shortcode.php';
require_once MANGA_ADMIN_PANEL_PATH . 'includes/shortcodes/manga-reader-shortcode.php';

// Função para registrar todos os assets CSS e JS necessários
function manga_admin_enqueue_shortcode_assets() {
    // Font Awesome para ícones
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4');
    
    // Slick Carousel para o modo carrossel do manga_display
    wp_enqueue_style('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', array(), '1.8.1');
    wp_enqueue_style('slick-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css', array(), '1.8.1');
    wp_enqueue_script('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
}
add_action('wp_enqueue_scripts', 'manga_admin_enqueue_shortcode_assets');

// Shortcode para exibir o conteúdo baseado no estado do usuário (logado/não logado)
function manga_user_content_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'state' => 'logged_in', // logged_in, logged_out, can_manage
    ), $atts, 'manga_user');
    
    if ($atts['state'] == 'logged_in' && is_user_logged_in()) {
        return do_shortcode($content);
    } elseif ($atts['state'] == 'logged_out' && !is_user_logged_in()) {
        return do_shortcode($content);
    } elseif ($atts['state'] == 'can_manage' && function_exists('manga_admin_panel_has_access') && manga_admin_panel_has_access()) {
        return do_shortcode($content);
    }
    
    return '';
}
add_shortcode('manga_user', 'manga_user_content_shortcode');