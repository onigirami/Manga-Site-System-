<?php
/**
 * Manga Display Shortcode
 * Implementa funcionalidades para exibir mangás em diferentes formatos
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe para o Shortcode de Exibição de Mangás
 */
class Manga_Display_Shortcode {
    
    /**
     * Construtor
     */
    public function __construct() {
        // Adicionar scripts e estilos específicos para exibição
        add_action('wp_enqueue_scripts', array($this, 'enqueue_display_assets'));
        
        // Adicionar handlers AJAX para filtros
        add_action('wp_ajax_manga_filter_display', array($this, 'filter_display_ajax'));
        add_action('wp_ajax_nopriv_manga_filter_display', array($this, 'filter_display_ajax'));
    }
    
    /**
     * Registrar e carregar assets específicos para exibição
     */
    public function enqueue_display_assets() {
        global $post;
        
        if (is_singular() && $post && has_shortcode($post->post_content, 'manga_display')) {
            // Registrar e enfileirar estilos
            wp_enqueue_style('manga-display-styles', MANGA_ADMIN_PANEL_URL . 'assets/css/manga-display-styles.css', array(), MANGA_ADMIN_PANEL_VERSION);
            
            // Registrar e enfileirar scripts
            wp_enqueue_script('manga-display-scripts', MANGA_ADMIN_PANEL_URL . 'assets/js/manga-display-scripts.js', array('jquery'), MANGA_ADMIN_PANEL_VERSION, true);
            
            // Se houver layout carousel, carregar Slick Carousel
            if (strpos($post->post_content, 'layout="carousel"') !== false) {
                wp_enqueue_style('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', array(), '1.8.1');
                wp_enqueue_style('slick-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css', array(), '1.8.1');
                wp_enqueue_script('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '1.8.1', true);
            }
            
            // Localizar script
            wp_localize_script('manga-display-scripts', 'mangaDisplayVars', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('manga_display_nonce'),
                'i18n' => array(
                    'loading' => __('Carregando...', 'manga-admin-panel'),
                    'no_results' => __('Nenhum mangá encontrado', 'manga-admin-panel'),
                    'error_loading' => __('Erro ao carregar mangás', 'manga-admin-panel'),
                    'filter' => __('Filtrar', 'manga-admin-panel'),
                    'clear_filters' => __('Limpar filtros', 'manga-admin-panel'),
                )
            ));
        }
    }
    
    /**
     * Filtrar exibição via AJAX
     */
    public function filter_display_ajax() {
        // Verificar nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_display_nonce')) {
            wp_send_json_error(array('message' => __('Verificação de segurança falhou', 'manga-admin-panel')));
            exit;
        }
        
        // Obter parâmetros de filtro
        $genres = isset($_POST['genres']) ? sanitize_text_field($_POST['genres']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';
        $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC';
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 12;
        
        // Obter mangás filtrados
        $mangas = $this->get_filtered_mangas($genres, $status, $search, $orderby, $order, $limit);
        
        // Obter HTML para cada mangá
        $html = '';
        $layout = isset($_POST['layout']) ? sanitize_text_field($_POST['layout']) : 'grid';
        $columns = isset($_POST['columns']) ? intval($_POST['columns']) : 4;
        $card_style = isset($_POST['card_style']) ? sanitize_text_field($_POST['card_style']) : 'default';
        
        foreach ($mangas as $manga) {
            $html .= $this->get_manga_card_html($manga, $layout, $card_style);
        }
        
        if (empty($html)) {
            $html = '<div class="manga-empty-state">';
            $html .= '<div class="manga-empty-icon"><i class="fas fa-search"></i></div>';
            $html .= '<div class="manga-empty-text">' . __('Nenhum mangá encontrado com os filtros atuais', 'manga-admin-panel') . '</div>';
            $html .= '</div>';
        }
        
        wp_send_json_success(array(
            'html' => $html,
            'count' => count($mangas)
        ));
        
        exit;
    }
    
    /**
     * Obter mangás filtrados
     */
    private function get_filtered_mangas($genres, $status, $search, $orderby, $order, $limit) {
        // Em um ambiente real do WordPress, esta função consultaria o banco de dados
        // Aqui, vamos simular o retorno para desenvolvimento
        
        // Simular os dados de mangás
        $mangas = array(
            array(
                'id' => 1,
                'title' => 'One Piece',
                'thumbnail' => 'https://via.placeholder.com/350x500?text=One+Piece',
                'rating' => 4.9,
                'views' => 12500,
                'chapters' => 1056,
                'genres' => array('Ação', 'Aventura', 'Comédia', 'Fantasia'),
                'status' => 'Em Andamento',
            ),
            array(
                'id' => 2,
                'title' => 'Naruto',
                'thumbnail' => 'https://via.placeholder.com/350x500?text=Naruto',
                'rating' => 4.8,
                'views' => 10800,
                'chapters' => 700,
                'genres' => array('Ação', 'Aventura', 'Fantasia', 'Artes Marciais'),
                'status' => 'Completo',
            ),
            array(
                'id' => 3,
                'title' => 'Berserk',
                'thumbnail' => 'https://via.placeholder.com/350x500?text=Berserk',
                'rating' => 4.9,
                'views' => 8500,
                'chapters' => 363,
                'genres' => array('Ação', 'Aventura', 'Drama', 'Fantasia', 'Horror'),
                'status' => 'Em Andamento',
            ),
            array(
                'id' => 4,
                'title' => 'Attack on Titan',
                'thumbnail' => 'https://via.placeholder.com/350x500?text=Attack+on+Titan',
                'rating' => 4.8,
                'views' => 9200,
                'chapters' => 139,
                'genres' => array('Ação', 'Drama', 'Fantasia', 'Horror'),
                'status' => 'Completo',
            ),
            array(
                'id' => 5,
                'title' => 'Dragon Ball',
                'thumbnail' => 'https://via.placeholder.com/350x500?text=Dragon+Ball',
                'rating' => 4.7,
                'views' => 11000,
                'chapters' => 519,
                'genres' => array('Ação', 'Aventura', 'Comédia', 'Artes Marciais'),
                'status' => 'Completo',
            ),
            array(
                'id' => 6,
                'title' => 'My Hero Academia',
                'thumbnail' => 'https://via.placeholder.com/350x500?text=My+Hero+Academia',
                'rating' => 4.7,
                'views' => 8900,
                'chapters' => 362,
                'genres' => array('Ação', 'Comédia', 'Escolar', 'Super Poderes'),
                'status' => 'Em Andamento',
            ),
        );
        
        // Filtrar por gêneros
        if (!empty($genres)) {
            $genre_array = explode(',', $genres);
            $mangas = array_filter($mangas, function($manga) use ($genre_array) {
                foreach ($genre_array as $genre) {
                    if (!in_array(trim($genre), $manga['genres'])) {
                        return false;
                    }
                }
                return true;
            });
        }
        
        // Filtrar por status
        if (!empty($status)) {
            $mangas = array_filter($mangas, function($manga) use ($status) {
                return $manga['status'] === $status;
            });
        }
        
        // Filtrar por busca
        if (!empty($search)) {
            $mangas = array_filter($mangas, function($manga) use ($search) {
                return stripos($manga['title'], $search) !== false;
            });
        }
        
        // Ordenar
        usort($mangas, function($a, $b) use ($orderby, $order) {
            $comparison = 0;
            
            switch ($orderby) {
                case 'title':
                    $comparison = strcmp($a['title'], $b['title']);
                    break;
                case 'rating':
                    $comparison = $a['rating'] <=> $b['rating'];
                    break;
                case 'views':
                    $comparison = $a['views'] <=> $b['views'];
                    break;
                case 'chapters':
                    $comparison = $a['chapters'] <=> $b['chapters'];
                    break;
                case 'random':
                    $comparison = rand(-1, 1);
                    break;
                default:
                    $comparison = $a['id'] <=> $b['id']; // Default by ID
            }
            
            return $order === 'ASC' ? $comparison : -$comparison;
        });
        
        // Limitar resultados
        return array_slice($mangas, 0, $limit);
    }
    
    /**
     * Obter HTML do card de mangá
     */
    private function get_manga_card_html($manga, $layout, $card_style) {
        $html = '';
        
        switch ($layout) {
            case 'grid':
                $html = $this->get_grid_card_html($manga, $card_style);
                break;
                
            case 'list':
                $html = $this->get_list_card_html($manga, $card_style);
                break;
                
            case 'carousel':
                $html = $this->get_grid_card_html($manga, $card_style); // Mesmo HTML do grid, mas com classes diferentes
                break;
                
            default:
                $html = $this->get_grid_card_html($manga, $card_style);
        }
        
        return $html;
    }
    
    /**
     * Obter HTML de card em grid
     */
    private function get_grid_card_html($manga, $card_style) {
        $permalink = get_permalink($manga['id']);
        
        $html = '<div class="manga-card manga-grid-card manga-card-' . esc_attr($card_style) . '">';
        
        // Imagem e overlay
        $html .= '<div class="manga-card-cover">';
        $html .= '<a href="' . esc_url($permalink) . '">';
        $html .= '<img src="' . esc_url($manga['thumbnail']) . '" alt="' . esc_attr($manga['title']) . '">';
        $html .= '</a>';
        
        // Status e rating
        $html .= '<div class="manga-card-status">' . esc_html($manga['status']) . '</div>';
        $html .= '<div class="manga-card-rating">';
        $html .= '<i class="fas fa-star"></i> ' . esc_html(number_format($manga['rating'], 1));
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Detalhes
        $html .= '<div class="manga-card-details">';
        $html .= '<h3 class="manga-card-title"><a href="' . esc_url($permalink) . '">' . esc_html($manga['title']) . '</a></h3>';
        
        // Informações adicionais
        $html .= '<div class="manga-card-meta">';
        
        // Capítulos
        $html .= '<div class="manga-card-chapters">';
        $html .= '<i class="fas fa-book-open"></i> ' . esc_html($manga['chapters']) . ' ' . __('capítulos', 'manga-admin-panel');
        $html .= '</div>';
        
        // Visualizações
        $html .= '<div class="manga-card-views">';
        $html .= '<i class="fas fa-eye"></i> ' . esc_html(number_format($manga['views']));
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Gêneros
        if (!empty($manga['genres'])) {
            $html .= '<div class="manga-card-genres">';
            foreach ($manga['genres'] as $genre) {
                $html .= '<span class="manga-card-genre">' . esc_html($genre) . '</span>';
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Obter HTML de card em lista
     */
    private function get_list_card_html($manga, $card_style) {
        $permalink = get_permalink($manga['id']);
        
        $html = '<div class="manga-card manga-list-card manga-card-' . esc_attr($card_style) . '">';
        
        // Imagem
        $html .= '<div class="manga-list-card-cover">';
        $html .= '<a href="' . esc_url($permalink) . '">';
        $html .= '<img src="' . esc_url($manga['thumbnail']) . '" alt="' . esc_attr($manga['title']) . '">';
        $html .= '</a>';
        $html .= '<div class="manga-card-status">' . esc_html($manga['status']) . '</div>';
        $html .= '</div>';
        
        // Detalhes
        $html .= '<div class="manga-list-card-details">';
        $html .= '<h3 class="manga-card-title"><a href="' . esc_url($permalink) . '">' . esc_html($manga['title']) . '</a></h3>';
        
        // Rating
        $html .= '<div class="manga-card-rating">';
        $html .= '<i class="fas fa-star"></i> ' . esc_html(number_format($manga['rating'], 1));
        $html .= '</div>';
        
        // Informações adicionais
        $html .= '<div class="manga-card-meta">';
        
        // Capítulos
        $html .= '<div class="manga-card-chapters">';
        $html .= '<i class="fas fa-book-open"></i> ' . esc_html($manga['chapters']) . ' ' . __('capítulos', 'manga-admin-panel');
        $html .= '</div>';
        
        // Visualizações
        $html .= '<div class="manga-card-views">';
        $html .= '<i class="fas fa-eye"></i> ' . esc_html(number_format($manga['views']));
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Gêneros
        if (!empty($manga['genres'])) {
            $html .= '<div class="manga-card-genres">';
            foreach ($manga['genres'] as $genre) {
                $html .= '<span class="manga-card-genre">' . esc_html($genre) . '</span>';
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        // Ações
        $html .= '<div class="manga-list-card-actions">';
        $html .= '<a href="' . esc_url($permalink) . '" class="manga-btn manga-btn-sm manga-btn-primary">';
        $html .= '<i class="fas fa-info-circle"></i> ' . __('Detalhes', 'manga-admin-panel');
        $html .= '</a>';
        
        $html .= '<a href="' . esc_url($permalink) . '?chapter_id=1" class="manga-btn manga-btn-sm manga-btn-accent">';
        $html .= '<i class="fas fa-book-reader"></i> ' . __('Ler', 'manga-admin-panel');
        $html .= '</a>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
}

// Inicializar a classe
new Manga_Display_Shortcode();