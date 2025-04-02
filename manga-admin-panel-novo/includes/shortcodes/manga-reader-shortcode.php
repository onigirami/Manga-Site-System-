<?php
/**
 * Shortcode para o leitor de mangá moderno
 * Suporta ajuste de brilho e opções de visualização (página ou lista corrida)
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

// Registrar o shortcode [manga_reader]
function manga_reader_shortcode($atts) {
    // Extrair e definir valores padrão para atributos
    $atts = shortcode_atts(array(
        'manga_id' => 0,          // ID do mangá
        'chapter_id' => 0,        // ID do capítulo
        'default_mode' => 'pagination', // pagination ou webtoon
        'show_chapter_list' => 'yes',   // yes ou no
        'show_comments' => 'no',        // yes ou no
        'show_header' => 'yes',         // yes ou no
    ), $atts, 'manga_reader');
    
    // Verificar se temos IDs válidos
    if (empty($atts['manga_id'])) {
        // Verificar se temos parâmetros na URL
        $manga_id = isset($_GET['manga_id']) ? intval($_GET['manga_id']) : 0;
        $chapter_id = isset($_GET['chapter_id']) ? intval($_GET['chapter_id']) : 0;
        
        if (!$manga_id) {
            return '<div class="manga-alert manga-alert-danger">' . 
                   esc_html__('ID do mangá não fornecido. Use o atributo manga_id no shortcode ou o parâmetro manga_id na URL.', 'manga-admin-panel') . 
                   '</div>';
        }
    } else {
        $manga_id = intval($atts['manga_id']);
        $chapter_id = intval($atts['chapter_id']);
        
        // Se não temos chapter_id no shortcode, verificar a URL
        if (!$chapter_id) {
            $chapter_id = isset($_GET['chapter_id']) ? intval($_GET['chapter_id']) : 0;
        }
    }
    
    // Iniciar buffer de saída
    ob_start();
    
    // Verificar se estamos no modo de leitura de capítulo
    if ($chapter_id) {
        // Incluir o template do leitor
        include_once MANGA_ADMIN_PANEL_PATH . 'templates/manga-modern-reader.php';
    } else {
        // Mostrar a lista de capítulos
        include_once MANGA_ADMIN_PANEL_PATH . 'templates/manga-chapter-list.php';
    }
    
    // Retornar o conteúdo do buffer
    return ob_get_clean();
}
add_shortcode('manga_reader', 'manga_reader_shortcode');

// Registrar o shortcode [manga_user_profile]
function manga_user_profile_shortcode($atts) {
    // Extrair e definir valores padrão para atributos
    $atts = shortcode_atts(array(
        'user_id' => 0,  // 0 = usuário atual ou logado
        'show_stats' => 'yes',
        'show_lists' => 'yes',
        'show_avatar' => 'yes',
    ), $atts, 'manga_user_profile');
    
    // Iniciar buffer de saída
    ob_start();
    
    // Incluir o template de perfil do usuário
    include_once MANGA_ADMIN_PANEL_PATH . 'templates/manga-user-profile.php';
    
    // Retornar o conteúdo do buffer
    return ob_get_clean();
}
add_shortcode('manga_user_profile', 'manga_user_profile_shortcode');