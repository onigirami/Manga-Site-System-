<?php
/**
 * Manga Reader Shortcode
 * 
 * Shortcode para exibir um leitor de mangá simples no frontend
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode para o leitor de mangá
 */
function manga_reader_shortcode($atts) {
    $atts = shortcode_atts(array(
        'manga_id' => 0,
        'chapter_id' => 0,
        'show_navigation' => 'yes',
        'show_comments' => 'yes',
        'reading_direction' => 'default', // default, ltr, rtl
    ), $atts, 'manga_reader');
    
    // Verificar se manga_id é válido
    $manga_id = intval($atts['manga_id']);
    if (empty($manga_id)) {
        return '<div class="manga-alert manga-alert-danger">' . __('ID do mangá não fornecido.', 'manga-admin-panel') . '</div>';
    }
    
    // Verificar se o mangá existe
    $manga = get_post($manga_id);
    if (!$manga || $manga->post_type !== 'wp-manga') {
        return '<div class="manga-alert manga-alert-danger">' . __('Mangá não encontrado.', 'manga-admin-panel') . '</div>';
    }
    
    // Obter capítulos do mangá
    $chapters = array();
    if (function_exists('madara_get_manga_chapters')) {
        $chapters = madara_get_manga_chapters($manga_id);
    }
    
    if (empty($chapters)) {
        return '<div class="manga-alert manga-alert-warning">' . __('Este mangá não possui capítulos.', 'manga-admin-panel') . '</div>';
    }
    
    // Capítulo atual
    $current_chapter = null;
    $chapter_id = intval($atts['chapter_id']);
    
    // Se capítulo não for especificado, usar o primeiro capítulo
    if (empty($chapter_id)) {
        $current_chapter = reset($chapters);
        $chapter_id = $current_chapter['chapter_id'];
    } else {
        // Encontrar o capítulo pelo ID
        foreach ($chapters as $chapter) {
            if ($chapter['chapter_id'] == $chapter_id) {
                $current_chapter = $chapter;
                break;
            }
        }
        
        // Se não encontrar, usar o primeiro capítulo
        if (!$current_chapter) {
            $current_chapter = reset($chapters);
            $chapter_id = $current_chapter['chapter_id'];
        }
    }
    
    // Obter imagens do capítulo
    $chapter_images = array();
    if (function_exists('wp_manga_get_chapter_images')) {
        $chapter_images = wp_manga_get_chapter_images($chapter_id);
    } else {
        // Fallback simples se a função não estiver disponível
        $chapter_post = get_post($chapter_id);
        if ($chapter_post) {
            $attachment_ids = get_posts(array(
                'post_type' => 'attachment',
                'posts_per_page' => -1,
                'post_parent' => $chapter_id,
                'orderby' => 'menu_order',
                'order' => 'ASC',
            ));
            
            foreach ($attachment_ids as $attachment) {
                $chapter_images[] = array(
                    'id' => $attachment->ID,
                    'url' => wp_get_attachment_url($attachment->ID),
                );
            }
        }
    }
    
    // Determinar próximo e capítulo anterior
    $next_chapter = null;
    $prev_chapter = null;
    
    // Organizar capítulos por ordem numérica
    usort($chapters, function($a, $b) {
        return version_compare($a['chapter_slug'], $b['chapter_slug']);
    });
    
    for ($i = 0; $i < count($chapters); $i++) {
        if ($chapters[$i]['chapter_id'] == $chapter_id) {
            if ($i > 0) {
                $prev_chapter = $chapters[$i - 1];
            }
            if ($i < count($chapters) - 1) {
                $next_chapter = $chapters[$i + 1];
            }
            break;
        }
    }
    
    // Direção de leitura
    $reading_direction = $atts['reading_direction'];
    if ($reading_direction === 'default') {
        // Obter direção padrão das configurações
        $reading_direction = get_option('wp_manga_reading_direction', 'ltr');
    }
    
    $direction_class = $reading_direction === 'rtl' ? 'manga-reader-rtl' : 'manga-reader-ltr';
    
    // Iniciar buffer de saída
    ob_start();
    
    // Interface do leitor
    ?>
    <div class="manga-reader <?php echo esc_attr($direction_class); ?>">
        <!-- Cabeçalho do leitor -->
        <div class="manga-reader-header">
            <h2><?php echo esc_html($manga->post_title); ?> - <?php echo esc_html($current_chapter['chapter_name']); ?></h2>
            
            <?php if ($atts['show_navigation'] === 'yes'): ?>
            <div class="manga-reader-navigation">
                <div class="manga-chapter-selector">
                    <select id="chapter-select" class="manga-form-control">
                        <?php foreach ($chapters as $chapter): ?>
                            <option value="<?php echo esc_attr($chapter['chapter_id']); ?>" <?php selected($chapter['chapter_id'], $chapter_id); ?>>
                                <?php echo esc_html($chapter['chapter_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="manga-navigation-buttons">
                    <?php if ($prev_chapter): ?>
                        <a href="<?php echo esc_url(add_query_arg('chapter_id', $prev_chapter['chapter_id'], remove_query_arg('chapter_id'))); ?>" class="manga-btn manga-btn-secondary">
                            <i class="fas fa-arrow-left"></i> <?php _e('Capítulo Anterior', 'manga-admin-panel'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo esc_url(get_permalink($manga_id)); ?>" class="manga-btn manga-btn-secondary">
                        <i class="fas fa-home"></i> <?php _e('Página do Mangá', 'manga-admin-panel'); ?>
                    </a>
                    
                    <?php if ($next_chapter): ?>
                        <a href="<?php echo esc_url(add_query_arg('chapter_id', $next_chapter['chapter_id'], remove_query_arg('chapter_id'))); ?>" class="manga-btn manga-btn-primary">
                            <?php _e('Próximo Capítulo', 'manga-admin-panel'); ?> <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Controles de leitura -->
            <div class="manga-reader-controls">
                <div class="manga-reading-mode">
                    <label><?php _e('Modo de Leitura:', 'manga-admin-panel'); ?></label>
                    <select id="reading-mode" class="manga-form-control">
                        <option value="paged"><?php _e('Paginado', 'manga-admin-panel'); ?></option>
                        <option value="long-strip"><?php _e('Rolagem Vertical', 'manga-admin-panel'); ?></option>
                        <option value="webtoon"><?php _e('Webtoon', 'manga-admin-panel'); ?></option>
                    </select>
                </div>
                
                <div class="manga-reading-direction">
                    <label><?php _e('Direção:', 'manga-admin-panel'); ?></label>
                    <select id="reading-direction" class="manga-form-control">
                        <option value="ltr" <?php selected($reading_direction, 'ltr'); ?>><?php _e('Esquerda para Direita', 'manga-admin-panel'); ?></option>
                        <option value="rtl" <?php selected($reading_direction, 'rtl'); ?>><?php _e('Direita para Esquerda', 'manga-admin-panel'); ?></option>
                    </select>
                </div>
                
                <div class="manga-quality">
                    <label><?php _e('Qualidade:', 'manga-admin-panel'); ?></label>
                    <select id="image-quality" class="manga-form-control">
                        <option value="high"><?php _e('Alta', 'manga-admin-panel'); ?></option>
                        <option value="medium" selected><?php _e('Média', 'manga-admin-panel'); ?></option>
                        <option value="low"><?php _e('Baixa', 'manga-admin-panel'); ?></option>
                    </select>
                </div>
                
                <button id="toggle-fullscreen" class="manga-btn manga-btn-secondary">
                    <i class="fas fa-expand"></i> <?php _e('Tela Cheia', 'manga-admin-panel'); ?>
                </button>
            </div>
        </div>
        
        <!-- Aviso do capítulo (se houver) -->
        <?php
        $chapter_warning = get_post_meta($chapter_id, '_wp_manga_chapter_warning', true);
        if (!empty($chapter_warning)):
        ?>
        <div class="manga-chapter-warning">
            <?php echo wpautop(esc_html($chapter_warning)); ?>
        </div>
        <?php endif; ?>
        
        <!-- Conteúdo do leitor -->
        <div id="manga-reader-content" class="manga-reader-content manga-mode-paged">
            <?php if (!empty($chapter_images)): ?>
                <?php foreach ($chapter_images as $index => $image): ?>
                    <div class="manga-page" data-page="<?php echo esc_attr($index + 1); ?>">
                        <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo sprintf(__('Página %d', 'manga-admin-panel'), $index + 1); ?>" class="manga-page-image">
                        <div class="manga-page-number"><?php echo sprintf(__('Página %d de %d', 'manga-admin-panel'), $index + 1, count($chapter_images)); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="manga-empty-state">
                    <div class="manga-empty-icon"><i class="fas fa-image"></i></div>
                    <p class="manga-empty-text"><?php _e('Nenhuma imagem encontrada para este capítulo.', 'manga-admin-panel'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Navegação inferior -->
        <?php if ($atts['show_navigation'] === 'yes'): ?>
        <div class="manga-reader-footer">
            <div class="manga-navigation-buttons">
                <?php if ($prev_chapter): ?>
                    <a href="<?php echo esc_url(add_query_arg('chapter_id', $prev_chapter['chapter_id'], remove_query_arg('chapter_id'))); ?>" class="manga-btn manga-btn-secondary">
                        <i class="fas fa-arrow-left"></i> <?php _e('Capítulo Anterior', 'manga-admin-panel'); ?>
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(get_permalink($manga_id)); ?>" class="manga-btn manga-btn-secondary">
                    <i class="fas fa-home"></i> <?php _e('Página do Mangá', 'manga-admin-panel'); ?>
                </a>
                
                <?php if ($next_chapter): ?>
                    <a href="<?php echo esc_url(add_query_arg('chapter_id', $next_chapter['chapter_id'], remove_query_arg('chapter_id'))); ?>" class="manga-btn manga-btn-primary">
                        <?php _e('Próximo Capítulo', 'manga-admin-panel'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Comentários -->
        <?php if ($atts['show_comments'] === 'yes' && comments_open($chapter_id)): ?>
        <div class="manga-reader-comments">
            <h3><?php _e('Comentários', 'manga-admin-panel'); ?></h3>
            <?php comments_template(); ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var currentPage = 1;
        var totalPages = $('.manga-page').length;
        var readingMode = 'paged';
        var readingDirection = '<?php echo esc_js($reading_direction); ?>';
        
        // Inicializar o leitor
        function initReader() {
            if (readingMode === 'paged') {
                $('#manga-reader-content').addClass('manga-mode-paged').removeClass('manga-mode-longstrip manga-mode-webtoon');
                showPage(currentPage);
            } else if (readingMode === 'long-strip') {
                $('#manga-reader-content').addClass('manga-mode-longstrip').removeClass('manga-mode-paged manga-mode-webtoon');
                $('.manga-page').show();
            } else if (readingMode === 'webtoon') {
                $('#manga-reader-content').addClass('manga-mode-webtoon').removeClass('manga-mode-paged manga-mode-longstrip');
                $('.manga-page').show();
            }
            
            // Aplicar direção de leitura
            $('.manga-reader').removeClass('manga-reader-ltr manga-reader-rtl').addClass('manga-reader-' + readingDirection);
        }
        
        // Mostrar página específica no modo paginado
        function showPage(pageNum) {
            $('.manga-page').hide();
            $('.manga-page[data-page="' + pageNum + '"]').show();
            currentPage = pageNum;
        }
        
        // Alternar para a próxima página
        function nextPage() {
            if (currentPage < totalPages) {
                showPage(currentPage + 1);
            } else if ($('#next-chapter-link').length) {
                // Ir para o próximo capítulo
                window.location.href = $('#next-chapter-link').attr('href');
            }
        }
        
        // Alternar para a página anterior
        function prevPage() {
            if (currentPage > 1) {
                showPage(currentPage - 1);
            } else if ($('#prev-chapter-link').length) {
                // Ir para o capítulo anterior
                window.location.href = $('#prev-chapter-link').attr('href');
            }
        }
        
        // Eventos de teclado para navegação
        $(document).keydown(function(e) {
            if (readingMode === 'paged') {
                if (readingDirection === 'ltr') {
                    // Esquerda para direita (padrão ocidental)
                    if (e.keyCode === 37) { // Seta esquerda
                        prevPage();
                    } else if (e.keyCode === 39) { // Seta direita
                        nextPage();
                    }
                } else {
                    // Direita para esquerda (padrão manga)
                    if (e.keyCode === 37) { // Seta esquerda
                        nextPage();
                    } else if (e.keyCode === 39) { // Seta direita
                        prevPage();
                    }
                }
            }
        });
        
        // Clicar na imagem para navegar
        $('#manga-reader-content').on('click', '.manga-page', function(e) {
            if (readingMode !== 'paged') return;
            
            var pageWidth = $(this).width();
            var clickX = e.pageX - $(this).offset().left;
            
            if (readingDirection === 'ltr') {
                // Esquerda para direita (padrão ocidental)
                if (clickX < pageWidth / 2) {
                    prevPage();
                } else {
                    nextPage();
                }
            } else {
                // Direita para esquerda (padrão manga)
                if (clickX < pageWidth / 2) {
                    nextPage();
                } else {
                    prevPage();
                }
            }
        });
        
        // Alternar modo de leitura
        $('#reading-mode').on('change', function() {
            readingMode = $(this).val();
            initReader();
        });
        
        // Alternar direção de leitura
        $('#reading-direction').on('change', function() {
            readingDirection = $(this).val();
            initReader();
        });
        
        // Alternar qualidade das imagens
        $('#image-quality').on('change', function() {
            var quality = $(this).val();
            
            if (quality === 'low') {
                $('.manga-page-image').addClass('manga-quality-low').removeClass('manga-quality-medium manga-quality-high');
            } else if (quality === 'medium') {
                $('.manga-page-image').addClass('manga-quality-medium').removeClass('manga-quality-low manga-quality-high');
            } else {
                $('.manga-page-image').addClass('manga-quality-high').removeClass('manga-quality-low manga-quality-medium');
            }
        });
        
        // Alternar modo tela cheia
        $('#toggle-fullscreen').on('click', function() {
            var readerElem = document.querySelector('.manga-reader');
            
            if (!document.fullscreenElement) {
                if (readerElem.requestFullscreen) {
                    readerElem.requestFullscreen();
                } else if (readerElem.mozRequestFullScreen) {
                    readerElem.mozRequestFullScreen();
                } else if (readerElem.webkitRequestFullscreen) {
                    readerElem.webkitRequestFullscreen();
                } else if (readerElem.msRequestFullscreen) {
                    readerElem.msRequestFullscreen();
                }
                
                $(this).html('<i class="fas fa-compress"></i> <?php _e('Sair da Tela Cheia', 'manga-admin-panel'); ?>');
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                
                $(this).html('<i class="fas fa-expand"></i> <?php _e('Tela Cheia', 'manga-admin-panel'); ?>');
            }
        });
        
        // Navegar entre capítulos com o select
        $('#chapter-select').on('change', function() {
            var chapterId = $(this).val();
            window.location.href = window.location.href.split('?')[0] + '?manga_id=<?php echo esc_js($manga_id); ?>&chapter_id=' + chapterId;
        });
        
        // Inicializar o leitor
        initReader();
    });
    </script>
    
    <style>
    .manga-reader {
        max-width: 100%;
        margin: 0 auto;
        padding: 20px;
        background-color: #f7f7f7;
    }
    
    .manga-reader-header {
        margin-bottom: 20px;
    }
    
    .manga-reader-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 20px 0;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .manga-navigation-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .manga-reader-controls {
        display: flex;
        align-items: center;
        margin: 20px 0;
        background-color: #fff;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .manga-chapter-warning {
        background-color: #fff3cd;
        color: #856404;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        border: 1px solid #ffeeba;
    }
    
    .manga-reader-content {
        background-color: #333;
        position: relative;
        margin-bottom: 20px;
        min-height: 500px;
        display: flex;
        flex-direction: column;
        align-items: center;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .manga-mode-paged {
        justify-content: center;
    }
    
    .manga-mode-longstrip, .manga-mode-webtoon {
        align-items: center;
    }
    
    .manga-page {
        text-align: center;
        display: none;
        position: relative;
        max-width: 100%;
    }
    
    .manga-mode-longstrip .manga-page, .manga-mode-webtoon .manga-page {
        display: block;
        margin-bottom: 20px;
    }
    
    .manga-mode-webtoon .manga-page-number {
        display: none;
    }
    
    .manga-page-image {
        max-width: 100%;
        height: auto;
        cursor: pointer;
    }
    
    .manga-page-number {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background-color: rgba(0,0,0,0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 12px;
    }
    
    .manga-reader-footer {
        display: flex;
        justify-content: center;
        margin: 20px 0;
    }
    
    .manga-reader-comments {
        margin-top: 30px;
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    /* Qualidade das imagens */
    .manga-quality-low {
        filter: blur(0.5px);
        image-rendering: optimizeSpeed;
    }
    
    .manga-quality-medium {
        image-rendering: auto;
    }
    
    .manga-quality-high {
        image-rendering: high-quality;
    }
    
    /* Direção de leitura RTL */
    .manga-reader-rtl .manga-navigation-buttons {
        flex-direction: row-reverse;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .manga-reader-controls, .manga-reader-navigation, .manga-navigation-buttons {
            flex-direction: column;
            width: 100%;
        }
        
        .manga-reader-controls > div, .manga-chapter-selector, .manga-navigation-buttons .manga-btn {
            width: 100%;
            margin-bottom: 10px;
        }
    }
    </style>
    <?php
    
    return ob_get_clean();
}
add_shortcode('manga_reader', 'manga_reader_shortcode');
