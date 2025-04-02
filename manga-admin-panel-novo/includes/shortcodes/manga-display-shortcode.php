<?php
/**
 * Shortcode para exibir mangás em diversos formatos
 */

// Registrar o shortcode [manga_display]
function manga_display_shortcode($atts) {
    // Extrair e definir valores padrão para atributos
    $atts = shortcode_atts(array(
        'limit' => 12,
        'orderby' => 'date',  // Opções: date, title, views, popularity, random
        'order' => 'DESC',    // ASC ou DESC
        'layout' => 'grid',   // Opções: grid, list, carousel
        'columns' => 4,       // Para layout grid
        'genre' => '',        // Gênero específico
        'status' => '',       // Status do mangá (completed, ongoing, etc.)
        'author' => '',       // Autor do mangá
        'exclude' => '',      // IDs de mangás a excluir, separados por vírgula
        'include' => '',      // IDs de mangás a incluir, separados por vírgula
        'show_rating' => 'yes', // yes ou no
        'show_views' => 'yes',  // yes ou no
        'show_author' => 'yes', // yes ou no
        'show_status' => 'yes', // yes ou no
        'show_chapters' => 'yes', // yes ou no
        'card_style' => 'default', // default, compact, expanded
    ), $atts, 'manga_display');
    
    // Iniciar buffer de saída
    ob_start();
    
    // Consultar mangás
    $query_args = array(
        'post_type' => 'wp-manga',
        'posts_per_page' => intval($atts['limit']),
        'post_status' => 'publish',
    );
    
    // Definir ordenação
    switch ($atts['orderby']) {
        case 'title':
            $query_args['orderby'] = 'title';
            break;
        case 'views':
            $query_args['meta_key'] = '_wp_manga_views';
            $query_args['orderby'] = 'meta_value_num';
            break;
        case 'popularity':
            $query_args['meta_key'] = '_wp_manga_popularity';
            $query_args['orderby'] = 'meta_value_num';
            break;
        case 'random':
            $query_args['orderby'] = 'rand';
            break;
        default: // date
            $query_args['orderby'] = 'date';
            break;
    }
    
    $query_args['order'] = strtoupper($atts['order']) === 'ASC' ? 'ASC' : 'DESC';
    
    // Filtrar por gênero
    if (!empty($atts['genre'])) {
        $query_args['tax_query'][] = array(
            'taxonomy' => 'wp-manga-genre',
            'field' => 'slug',
            'terms' => explode(',', $atts['genre']),
        );
    }
    
    // Filtrar por status
    if (!empty($atts['status'])) {
        $query_args['tax_query'][] = array(
            'taxonomy' => 'wp-manga-status',
            'field' => 'slug',
            'terms' => explode(',', $atts['status']),
        );
    }
    
    // Filtrar por autor
    if (!empty($atts['author'])) {
        $query_args['tax_query'][] = array(
            'taxonomy' => 'wp-manga-author',
            'field' => 'slug',
            'terms' => explode(',', $atts['author']),
        );
    }
    
    // Incluir mangás específicos
    if (!empty($atts['include'])) {
        $query_args['post__in'] = explode(',', $atts['include']);
    }
    
    // Excluir mangás específicos
    if (!empty($atts['exclude'])) {
        $query_args['post__not_in'] = explode(',', $atts['exclude']);
    }
    
    // Executar a consulta
    $manga_query = new WP_Query($query_args);
    
    // Verificar se há mangás
    if ($manga_query->have_posts()) {
        // Adicionar classe baseada no layout
        $container_class = 'manga-display-container';
        $layout_class = '';
        
        switch ($atts['layout']) {
            case 'list':
                $layout_class = 'manga-display-list';
                break;
            case 'carousel':
                $layout_class = 'manga-display-carousel';
                break;
            default: // grid
                $layout_class = 'manga-display-grid';
                $layout_class .= ' manga-col-' . intval($atts['columns']);
                break;
        }
        
        // Adicionar classe baseada no estilo do card
        $card_class = 'manga-card';
        if ($atts['card_style'] !== 'default') {
            $card_class .= ' manga-card-' . esc_attr($atts['card_style']);
        }
        
        // Container principal
        echo '<div class="' . esc_attr($container_class . ' ' . $layout_class) . '">';
        
        // Loop pelos mangás
        while ($manga_query->have_posts()) {
            $manga_query->the_post();
            $manga_id = get_the_ID();
            
            // Obter dados do mangá
            $thumbnail = get_the_post_thumbnail_url($manga_id, 'medium');
            $title = get_the_title();
            $permalink = get_permalink();
            
            // Obter meta dados
            $views = get_post_meta($manga_id, '_wp_manga_views', true);
            $rating = get_post_meta($manga_id, '_wp_manga_rating', true);
            
            // Obter termos de taxonomia
            $genres = get_the_terms($manga_id, 'wp-manga-genre');
            $status = get_the_terms($manga_id, 'wp-manga-status');
            $authors = get_the_terms($manga_id, 'wp-manga-author');
            
            // Obter contagem de capítulos
            $chapters_count = 0;
            if (function_exists('get_manga_chapters')) {
                $chapters = get_manga_chapters($manga_id);
                if ($chapters) {
                    $chapters_count = count($chapters);
                }
            }
            
            // Começar card de mangá
            echo '<div class="' . esc_attr($card_class) . '">';
            
            // Thumbnail com link
            echo '<div class="manga-card-thumbnail">';
            
            // Status badge (se ativo)
            if ($atts['show_status'] === 'yes' && $status && !is_wp_error($status)) {
                $status_term = reset($status); // pega o primeiro termo apenas
                echo '<div class="manga-card-status">' . esc_html($status_term->name) . '</div>';
            }
            
            echo '<a href="' . esc_url($permalink) . '">';
            if ($thumbnail) {
                echo '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($title) . '">';
            } else {
                echo '<div class="manga-no-thumbnail"><i class="fas fa-book"></i></div>';
            }
            echo '</a>';
            echo '</div>';
            
            // Card content
            echo '<div class="manga-card-content">';
            
            // Cabeçalho do card
            echo '<div class="manga-card-header">';
            echo '<h3 class="manga-card-title"><a href="' . esc_url($permalink) . '">' . esc_html($title) . '</a></h3>';
            echo '</div>';
            
            // Meta informações
            echo '<div class="manga-card-meta">';
            
            // Views (se ativo)
            if ($atts['show_views'] === 'yes') {
                $formatted_views = number_format(intval($views));
                echo '<span><i class="fas fa-eye"></i> ' . esc_html($formatted_views) . '</span>';
            }
            
            // Capítulos (se ativo)
            if ($atts['show_chapters'] === 'yes') {
                echo '<span><i class="fas fa-book-open"></i> ' . esc_html($chapters_count) . '</span>';
            }
            
            // Rating (se ativo)
            if ($atts['show_rating'] === 'yes' && !empty($rating)) {
                // Formatar como estrelas
                $rating_value = floatval($rating);
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $rating_value) {
                        $stars .= '<i class="fas fa-star"></i>';
                    } elseif ($i - 0.5 <= $rating_value) {
                        $stars .= '<i class="fas fa-star-half-alt"></i>';
                    } else {
                        $stars .= '<i class="far fa-star"></i>';
                    }
                }
                echo '<span class="manga-rating">' . $stars . '</span>';
            }
            
            echo '</div>';
            
            // Author (se ativo e no estilo expandido)
            if ($atts['show_author'] === 'yes' && $authors && !is_wp_error($authors) && $atts['card_style'] === 'expanded') {
                $author_names = array();
                foreach ($authors as $author) {
                    $author_names[] = $author->name;
                }
                echo '<div class="manga-card-author"><i class="fas fa-user"></i> ' . esc_html(implode(', ', $author_names)) . '</div>';
            }
            
            // Gêneros (apenas no estilo expandido)
            if ($genres && !is_wp_error($genres) && $atts['card_style'] === 'expanded') {
                echo '<div class="manga-card-genres">';
                foreach ($genres as $genre) {
                    echo '<a href="' . esc_url(get_term_link($genre)) . '" class="manga-genre-tag">' . esc_html($genre->name) . '</a>';
                }
                echo '</div>';
            }
            
            echo '</div>'; // Fim do manga-card-content
            echo '</div>'; // Fim do manga-card
        }
        
        echo '</div>'; // Fim do container
        
        // Caso seja carousel, adicionar JS para slick carousel
        if ($atts['layout'] === 'carousel') {
            echo '<script>
                jQuery(document).ready(function($) {
                    $(".manga-display-carousel").slick({
                        dots: true,
                        infinite: true,
                        speed: 300,
                        slidesToShow: ' . intval(min(4, $atts['columns'])) . ',
                        slidesToScroll: 1,
                        responsive: [
                            {
                                breakpoint: 1024,
                                settings: {
                                    slidesToShow: 3,
                                    slidesToScroll: 1,
                                }
                            },
                            {
                                breakpoint: 768,
                                settings: {
                                    slidesToShow: 2,
                                    slidesToScroll: 1
                                }
                            },
                            {
                                breakpoint: 480,
                                settings: {
                                    slidesToShow: 1,
                                    slidesToScroll: 1
                                }
                            }
                        ]
                    });
                });
            </script>';
        }
        
        // Resetar a consulta do WP
        wp_reset_postdata();
    } else {
        // Sem mangás encontrados
        echo '<div class="manga-empty-state">';
        echo '<div class="manga-empty-icon"><i class="fas fa-book"></i></div>';
        echo '<div class="manga-empty-text">' . esc_html__('Nenhum mangá encontrado com os critérios especificados.', 'manga-admin-panel') . '</div>';
        echo '</div>';
    }
    
    // Retornar buffer de saída
    return ob_get_clean();
}
add_shortcode('manga_display', 'manga_display_shortcode');

/**
 * CSS adicional para o shortcode de exibição
 */
function manga_display_shortcode_styles() {
    ?>
    <style>
        /* Container de exibição */
        .manga-display-container {
            margin: 30px 0;
        }
        
        /* Layout Grid */
        .manga-display-grid {
            display: grid;
            gap: 20px;
        }
        
        .manga-col-1 { grid-template-columns: repeat(1, 1fr); }
        .manga-col-2 { grid-template-columns: repeat(2, 1fr); }
        .manga-col-3 { grid-template-columns: repeat(3, 1fr); }
        .manga-col-4 { grid-template-columns: repeat(4, 1fr); }
        .manga-col-5 { grid-template-columns: repeat(5, 1fr); }
        .manga-col-6 { grid-template-columns: repeat(6, 1fr); }
        
        /* Layout Lista */
        .manga-display-list .manga-card {
            display: flex;
            margin-bottom: 15px;
        }
        
        .manga-display-list .manga-card-thumbnail {
            flex: 0 0 120px;
            height: 180px;
            padding-top: 0;
        }
        
        .manga-display-list .manga-card-content {
            flex: 1;
            padding: 15px;
        }
        
        /* Estilos de Cards */
        .manga-card {
            background-color: var(--manga-card-color, #fff);
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .manga-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .manga-card-thumbnail {
            position: relative;
            width: 100%;
            padding-top: 142%; /* Aspect ratio para capas de mangá */
            overflow: hidden;
        }
        
        .manga-card-thumbnail img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .manga-no-thumbnail {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f0f0f0;
            color: #999;
            font-size: 48px;
        }
        
        .manga-card-status {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: var(--manga-primary-color, #ff6b6b);
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }
        
        .manga-card-content {
            padding: 15px;
        }
        
        .manga-card-header {
            margin-bottom: 10px;
        }
        
        .manga-card-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .manga-card-title a {
            color: var(--manga-text-color, #333);
            text-decoration: none;
        }
        
        .manga-card-title a:hover {
            color: var(--manga-primary-color, #ff6b6b);
        }
        
        .manga-card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: var(--manga-light-text, #718093);
        }
        
        .manga-card-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .manga-card-meta i {
            font-size: 14px;
        }
        
        .manga-card-author {
            margin-top: 10px;
            font-size: 13px;
            color: var(--manga-light-text, #718093);
        }
        
        .manga-card-genres {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }
        
        .manga-genre-tag {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 3px;
            background-color: #f0f0f0;
            color: var(--manga-secondary-color, #576574);
            text-decoration: none;
        }
        
        .manga-genre-tag:hover {
            background-color: var(--manga-primary-color, #ff6b6b);
            color: white;
        }
        
        .manga-rating {
            color: #f1c40f;
        }
        
        /* Card - Estilo Compacto */
        .manga-card-compact .manga-card-content {
            padding: 10px;
        }
        
        .manga-card-compact .manga-card-title {
            font-size: 14px;
        }
        
        .manga-card-compact .manga-card-meta {
            font-size: 12px;
        }
        
        /* Card - Estilo Expandido */
        .manga-card-expanded .manga-card-content {
            padding: 20px;
        }
        
        .manga-card-expanded .manga-card-title {
            font-size: 18px;
            white-space: normal;
            margin-bottom: 10px;
        }
        
        /* Estado vazio */
        .manga-empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #a5b1c2;
        }
        
        .manga-empty-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .manga-empty-text {
            font-size: 16px;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .manga-col-2, .manga-col-3, .manga-col-4, .manga-col-5, .manga-col-6 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .manga-display-grid {
                grid-template-columns: 1fr;
            }
            
            .manga-display-list .manga-card {
                flex-direction: column;
            }
            
            .manga-display-list .manga-card-thumbnail {
                width: 100%;
                flex: none;
                padding-top: 142%;
                height: auto;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'manga_display_shortcode_styles');