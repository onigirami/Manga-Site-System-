<?php
/**
 * Template para o leitor de mangá moderno
 * Suporta ajuste de brilho e opções de visualização (página ou lista corrida)
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

// Obter os parâmetros necessários
$manga_id = isset($_GET['manga_id']) ? intval($_GET['manga_id']) : 0;
$chapter_id = isset($_GET['chapter_id']) ? intval($_GET['chapter_id']) : 0;

if (!$manga_id || !$chapter_id) {
    echo '<div class="manga-alert manga-alert-danger">' . esc_html__('Parâmetros de mangá ou capítulo inválidos.', 'manga-admin-panel') . '</div>';
    return;
}

// Obter informações do mangá
$manga_title = get_the_title($manga_id);

// Obter informações do capítulo
$chapter_data = array(); // Em um ambiente real, isso seria carregado do banco de dados

// Função simulada para obter dados do capítulo - remover em produção
// Esta é apenas uma simulação para desenvolvimento
function get_chapter_data($manga_id, $chapter_id) {
    // Em um ambiente real, isso buscaria os dados do capítulo do banco de dados
    return array(
        'id' => $chapter_id,
        'name' => 'Capítulo ' . rand(1, 30),
        'images' => array(
            array(
                'url' => 'https://via.placeholder.com/800x1200.png?text=Página+1',
                'page' => 1
            ),
            array(
                'url' => 'https://via.placeholder.com/800x1200.png?text=Página+2',
                'page' => 2
            ),
            array(
                'url' => 'https://via.placeholder.com/800x1200.png?text=Página+3',
                'page' => 3
            ),
            array(
                'url' => 'https://via.placeholder.com/800x1200.png?text=Página+4',
                'page' => 4
            ),
            array(
                'url' => 'https://via.placeholder.com/800x1200.png?text=Página+5',
                'page' => 5
            ),
        )
    );
}

// Obter dados do capítulo
$chapter_data = get_chapter_data($manga_id, $chapter_id);

// Obter capítulos anterior e próximo
function get_next_prev_chapters($manga_id, $chapter_id) {
    // Em um ambiente real, isso buscaria os capítulos anterior e próximo
    return array(
        'prev' => $chapter_id > 1 ? $chapter_id - 1 : null,
        'next' => $chapter_id < 30 ? $chapter_id + 1 : null,
    );
}

$adjacent_chapters = get_next_prev_chapters($manga_id, $chapter_id);

// Obter lista de capítulos
function get_manga_chapters_list($manga_id) {
    // Em um ambiente real, isso buscaria a lista de capítulos
    $chapters = array();
    for ($i = 1; $i <= 5; $i++) {
        $chapters[] = array(
            'id' => $i,
            'name' => 'Capítulo ' . $i,
        );
    }
    return $chapters;
}

$chapters_list = get_manga_chapters_list($manga_id);

// Verificar se o usuário já definiu preferências
$user_view_mode = isset($_COOKIE['manga_reader_view_mode']) ? sanitize_text_field($_COOKIE['manga_reader_view_mode']) : 'pagination';
$user_brightness = isset($_COOKIE['manga_reader_brightness']) ? intval($_COOKIE['manga_reader_brightness']) : 100;
?>

<div class="manga-reader-container">
    <div class="manga-reader-header">
        <div class="manga-reader-title">
            <h1><?php echo esc_html($manga_title); ?></h1>
            <h2><?php echo esc_html($chapter_data['name']); ?></h2>
        </div>
        
        <div class="manga-reader-controls">
            <div class="manga-reader-setting-group">
                <label for="reader-view-mode"><?php echo esc_html__('Modo de Visualização:', 'manga-admin-panel'); ?></label>
                <select id="reader-view-mode" class="manga-reader-view-mode">
                    <option value="pagination" <?php selected($user_view_mode, 'pagination'); ?>><?php echo esc_html__('Paginado', 'manga-admin-panel'); ?></option>
                    <option value="webtoon" <?php selected($user_view_mode, 'webtoon'); ?>><?php echo esc_html__('Lista Corrida', 'manga-admin-panel'); ?></option>
                </select>
            </div>
            
            <div class="manga-reader-setting-group">
                <label for="reader-brightness"><?php echo esc_html__('Brilho:', 'manga-admin-panel'); ?></label>
                <input type="range" id="reader-brightness" class="manga-reader-brightness" min="50" max="150" value="<?php echo esc_attr($user_brightness); ?>">
                <span class="brightness-value"><?php echo esc_html($user_brightness); ?>%</span>
            </div>
            
            <div class="manga-reader-navigation">
                <div class="manga-chapter-select-wrapper">
                    <select id="chapter-select" class="manga-chapter-select">
                        <?php foreach ($chapters_list as $chapter) : ?>
                            <option value="<?php echo esc_attr($chapter['id']); ?>" <?php selected($chapter['id'], $chapter_id); ?>>
                                <?php echo esc_html($chapter['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="manga-reader-nav-buttons">
                    <?php if ($adjacent_chapters['prev']) : ?>
                        <a href="<?php echo esc_url(add_query_arg(array('manga_id' => $manga_id, 'chapter_id' => $adjacent_chapters['prev']))); ?>" class="manga-reader-nav-btn prev-chapter">
                            <i class="fas fa-chevron-left"></i> <?php echo esc_html__('Capítulo Anterior', 'manga-admin-panel'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo esc_url(get_permalink($manga_id)); ?>" class="manga-reader-nav-btn manga-reader-info">
                        <i class="fas fa-info-circle"></i>
                    </a>
                    
                    <?php if ($adjacent_chapters['next']) : ?>
                        <a href="<?php echo esc_url(add_query_arg(array('manga_id' => $manga_id, 'chapter_id' => $adjacent_chapters['next']))); ?>" class="manga-reader-nav-btn next-chapter">
                            <?php echo esc_html__('Próximo Capítulo', 'manga-admin-panel'); ?> <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="manga-reader-content">
        <!-- Modo de visualização paginada -->
        <div id="reader-pagination" class="manga-reader-pagination <?php echo $user_view_mode === 'pagination' ? 'active' : ''; ?>">
            <div class="manga-reader-pages">
                <?php foreach ($chapter_data['images'] as $image) : ?>
                    <div class="manga-reader-page" data-page="<?php echo esc_attr($image['page']); ?>">
                        <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr(sprintf(__('Página %d', 'manga-admin-panel'), $image['page'])); ?>">
                        <div class="manga-reader-page-number"><?php echo esc_html($image['page']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="manga-reader-pagination-controls">
                <button class="manga-reader-page-btn prev-page">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="manga-reader-page-counter">
                    <span id="current-page">1</span> / <span id="total-pages"><?php echo count($chapter_data['images']); ?></span>
                </div>
                
                <button class="manga-reader-page-btn next-page">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        
        <!-- Modo de visualização de lista corrida (webtoon) -->
        <div id="reader-webtoon" class="manga-reader-webtoon <?php echo $user_view_mode === 'webtoon' ? 'active' : ''; ?>">
            <?php foreach ($chapter_data['images'] as $image) : ?>
                <div class="manga-reader-webtoon-image">
                    <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr(sprintf(__('Página %d', 'manga-admin-panel'), $image['page'])); ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Botão flutuante para voltar ao topo -->
    <button class="manga-reader-back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>
</div>

<style>
    /* Reset */
    .manga-reader-container * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body.manga-reading {
        overflow-x: hidden;
        background-color: #0D1117;
        transition: background-color 0.3s ease;
    }
    
    .manga-reader-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        background-color: var(--manga-background-color, #f7f7f7);
        overflow: hidden;
        position: relative;
        min-height: 100vh;
    }
    
    /* Header */
    .manga-reader-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .manga-reader-title {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .manga-reader-title h1 {
        font-size: 24px;
        margin-bottom: 5px;
        color: var(--manga-text-color, #333);
    }
    
    .manga-reader-title h2 {
        font-size: 18px;
        color: var(--manga-light-text, #718093);
        font-weight: normal;
    }
    
    /* Controles */
    .manga-reader-controls {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }
    
    .manga-reader-setting-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .manga-reader-setting-group label {
        font-size: 14px;
        color: var(--manga-text-color, #333);
    }
    
    .manga-reader-view-mode, 
    .manga-chapter-select {
        padding: 8px 12px;
        border-radius: 4px;
        border: 1px solid #ddd;
        background-color: white;
        font-size: 14px;
        color: var(--manga-text-color, #333);
        cursor: pointer;
    }
    
    .manga-reader-brightness {
        width: 100px;
        cursor: pointer;
    }
    
    .brightness-value {
        font-size: 14px;
        color: var(--manga-light-text, #718093);
        min-width: 40px;
        text-align: right;
    }
    
    .manga-reader-navigation {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
        width: 100%;
        margin-top: 10px;
    }
    
    .manga-chapter-select-wrapper {
        width: 100%;
        max-width: 300px;
    }
    
    .manga-chapter-select {
        width: 100%;
    }
    
    .manga-reader-nav-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
        width: 100%;
    }
    
    .manga-reader-nav-btn {
        padding: 8px 15px;
        background-color: var(--manga-primary-color, #ff6b6b);
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
        transition: background-color 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .manga-reader-nav-btn:hover {
        background-color: #ee5253;
    }
    
    .manga-reader-info {
        padding: 8px;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Conteúdo */
    .manga-reader-content {
        position: relative;
    }
    
    /* Modo de visualização paginado */
    .manga-reader-pagination {
        display: none;
        flex-direction: column;
        align-items: center;
    }
    
    .manga-reader-pagination.active {
        display: flex;
    }
    
    .manga-reader-pages {
        width: 100%;
        display: flex;
        overflow: hidden;
        position: relative;
        max-width: 800px;
        margin: 0 auto;
        border-radius: 5px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .manga-reader-page {
        flex: 0 0 100%;
        max-width: 100%;
        position: relative;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .manga-reader-page.active {
        transform: translateX(0);
    }
    
    .manga-reader-page img {
        width: 100%;
        height: auto;
        display: block;
    }
    
    .manga-reader-page-number {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background-color: rgba(0,0,0,0.7);
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .manga-reader-pagination-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
    }
    
    .manga-reader-page-btn {
        background-color: var(--manga-primary-color, #ff6b6b);
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: background-color 0.2s;
    }
    
    .manga-reader-page-btn:hover {
        background-color: #ee5253;
    }
    
    .manga-reader-page-counter {
        font-size: 16px;
        color: var(--manga-text-color, #333);
    }
    
    /* Modo de visualização Webtoon (lista corrida) */
    .manga-reader-webtoon {
        display: none;
        flex-direction: column;
        gap: 5px;
    }
    
    .manga-reader-webtoon.active {
        display: flex;
    }
    
    .manga-reader-webtoon-image {
        max-width: 800px;
        margin: 0 auto;
        width: 100%;
    }
    
    .manga-reader-webtoon-image img {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 5px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    /* Botão de voltar ao topo */
    .manga-reader-back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: var(--manga-primary-color, #ff6b6b);
        color: white;
        border: none;
        font-size: 20px;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 100;
        opacity: 0.8;
        transition: opacity 0.2s, transform 0.2s;
    }
    
    .manga-reader-back-to-top:hover {
        opacity: 1;
        transform: translateY(-3px);
    }
    
    .manga-reader-back-to-top.visible {
        display: flex;
    }
    
    /* Responsivo */
    @media (max-width: 768px) {
        .manga-reader-controls {
            flex-direction: column;
        }
        
        .manga-reader-setting-group {
            width: 100%;
            justify-content: space-between;
        }
        
        .manga-reader-brightness {
            flex: 1;
        }
        
        .manga-reader-nav-buttons {
            flex-wrap: wrap;
        }
        
        .manga-reader-nav-btn {
            flex: 1;
            text-align: center;
            justify-content: center;
        }
        
        .manga-reader-info {
            flex: 0 0 auto;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Adicionar classe ao body
        $('body').addClass('manga-reading');
        
        // Variáveis
        let currentPage = 1;
        const totalPages = <?php echo count($chapter_data['images']); ?>;
        let viewMode = '<?php echo esc_js($user_view_mode); ?>';
        let brightness = <?php echo esc_js($user_brightness); ?>;
        
        // Inicialização
        updatePageDisplay();
        applyBrightness();
        showActivePage();
        
        // Alternar entre modos de visualização
        $('#reader-view-mode').on('change', function() {
            viewMode = $(this).val();
            
            // Salvar preferência em cookie
            document.cookie = 'manga_reader_view_mode=' + viewMode + '; path=/; max-age=31536000'; // 1 ano
            
            if (viewMode === 'pagination') {
                $('#reader-pagination').addClass('active');
                $('#reader-webtoon').removeClass('active');
                showActivePage();
            } else { // webtoon
                $('#reader-pagination').removeClass('active');
                $('#reader-webtoon').addClass('active');
                
                // Mostrar botão voltar ao topo quando necessário
                checkScrollPosition();
            }
        });
        
        // Ajustar brilho
        $('#reader-brightness').on('input', function() {
            brightness = $(this).val();
            $('.brightness-value').text(brightness + '%');
            applyBrightness();
            
            // Salvar preferência em cookie
            document.cookie = 'manga_reader_brightness=' + brightness + '; path=/; max-age=31536000'; // 1 ano
        });
        
        // Navegação de páginas
        $('.next-page').on('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                updatePageDisplay();
                showActivePage();
            }
        });
        
        $('.prev-page').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                updatePageDisplay();
                showActivePage();
            }
        });
        
        // Navegação por teclado
        $(document).on('keydown', function(e) {
            if (viewMode === 'pagination') {
                if (e.keyCode === 39) { // Seta direita
                    $('.next-page').click();
                } else if (e.keyCode === 37) { // Seta esquerda
                    $('.prev-page').click();
                }
            }
        });
        
        // Alterar capítulo
        $('#chapter-select').on('change', function() {
            const chapterId = $(this).val();
            window.location.href = '<?php echo esc_js(add_query_arg(array('manga_id' => $manga_id, 'chapter_id' => ''))); ?>' + chapterId;
        });
        
        // Botão voltar ao topo
        $('.manga-reader-back-to-top').on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
        
        // Verificar posição de scroll para mostrar botão voltar ao topo
        $(window).on('scroll', function() {
            checkScrollPosition();
        });
        
        // Funções
        function updatePageDisplay() {
            $('#current-page').text(currentPage);
        }
        
        function showActivePage() {
            $('.manga-reader-page').removeClass('active');
            $(`.manga-reader-page[data-page="${currentPage}"]`).addClass('active');
        }
        
        function applyBrightness() {
            // Aplicar filtro de brilho
            $('.manga-reader-pages img, .manga-reader-webtoon-image img').css('filter', `brightness(${brightness/100})`);
        }
        
        function checkScrollPosition() {
            if ($(window).scrollTop() > 300) {
                $('.manga-reader-back-to-top').addClass('visible');
            } else {
                $('.manga-reader-back-to-top').removeClass('visible');
            }
        }
    });
</script>