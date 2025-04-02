<?php
/**
 * Template para o leitor de mangá moderno
 * Inspirado no design do Taiyo.moe e SlimeRead.com
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

// Verificar se temos os parâmetros necessários
if (!isset($manga_id) || !$manga_id) {
    echo '<div class="manga-alert manga-alert-danger">';
    echo esc_html__('ID do mangá não fornecido.', 'manga-admin-panel');
    echo '</div>';
    return;
}

// Obter informações do mangá
$manga_title = get_the_title($manga_id);
$manga_permalink = get_permalink($manga_id);
$manga_thumbnail = get_the_post_thumbnail_url($manga_id, 'thumbnail');

// Se não temos thumbnail, usar um placeholder
if (!$manga_thumbnail) {
    $manga_thumbnail = 'https://via.placeholder.com/80x80?text=' . urlencode(substr($manga_title, 0, 1));
}

// Verificar se está no modo de leitura de capítulo
if (!isset($chapter_id) || !$chapter_id) {
    // Se não temos chapter_id, redirecionar para a lista de capítulos
    include MANGA_ADMIN_PANEL_PATH . 'templates/manga-chapter-list.php';
    return;
}

// Obter informações do capítulo (função simulada para desenvolvimento)
function get_chapter_data($manga_id, $chapter_id) {
    // Em uma implementação real, isso obteria os dados do capítulo do banco de dados
    
    // Gerar dados de exemplo para o capítulo
    $chapter_name = 'Capítulo ' . $chapter_id;
    $chapter_number = $chapter_id;
    
    // Gerar páginas de exemplo para o capítulo
    // Em um ambiente real, estas seriam as imagens reais do capítulo
    $pages = array();
    $total_pages = rand(8, 15);
    
    for ($i = 1; $i <= $total_pages; $i++) {
        $pages[] = array(
            'id' => $i,
            'url' => 'https://via.placeholder.com/800x1200.png?text=Página+' . $i,
            'alt' => sprintf(__('Página %d', 'manga-admin-panel'), $i)
        );
    }
    
    // Estrutura completa do capítulo
    return array(
        'id' => $chapter_id,
        'name' => $chapter_name,
        'number' => $chapter_number,
        'pages' => $pages,
        'prev_chapter' => ($chapter_id > 1) ? $chapter_id - 1 : null,
        'next_chapter' => $chapter_id + 1,
        'date' => date('Y-m-d H:i:s', time() - ($chapter_id * 86400)), // Data simulada
        'is_premium' => ($chapter_id % 5 === 0), // Alguns capítulos são premium (para simulação)
    );
}

// Obter dados do capítulo
$chapter_data = get_chapter_data($manga_id, $chapter_id);

// Verificar preferências do usuário
$user_view_mode = isset($_COOKIE['manga_reader_view_mode']) ? sanitize_text_field($_COOKIE['manga_reader_view_mode']) : 'pagination';
$user_brightness = isset($_COOKIE['manga_reader_brightness']) ? intval($_COOKIE['manga_reader_brightness']) : 100;

// Usar modo de visualização do shortcode, se fornecido
if (isset($default_mode) && in_array($default_mode, array('pagination', 'webtoon'))) {
    $user_view_mode = $default_mode;
}

// Obter lista de capítulos para seletor
function get_manga_chapters_for_select($manga_id, $current_chapter_id) {
    // Em uma implementação real, isso obteria a lista de capítulos do banco de dados
    
    // Gerar lista simulada de capítulos
    $chapters = array();
    $max_chapters = 20; // Número máximo de capítulos para simulação
    
    for ($i = 1; $i <= $max_chapters; $i++) {
        $chapters[] = array(
            'id' => $i,
            'name' => 'Capítulo ' . $i,
            'number' => $i,
            'is_premium' => ($i % 5 === 0),
            'is_current' => ($i == $current_chapter_id)
        );
    }
    
    // Ordenar por número de capítulo (decrescente)
    usort($chapters, function($a, $b) {
        return $b['number'] <=> $a['number'];
    });
    
    return $chapters;
}

// Obter lista de capítulos
$chapters_list = get_manga_chapters_for_select($manga_id, $chapter_id);

// Verificar acesso a capítulos premium
function user_has_premium_access() {
    // Em uma implementação real, isso verificaria se o usuário tem acesso premium
    // Para simulação, vamos considerar administradores e editores como usuários premium
    return current_user_can('administrator') || current_user_can('editor');
}

$has_premium_access = user_has_premium_access();

// Verificar se o capítulo é premium e se o usuário tem acesso
$is_premium_chapter = $chapter_data['is_premium'] ?? false;
$can_access_chapter = !$is_premium_chapter || $has_premium_access;

// Se o usuário não tem acesso, exibir mensagem premium
if (!$can_access_chapter) {
    ?>
    <div class="manga-reader-premium-required">
        <div class="manga-premium-icon">
            <i class="fas fa-crown"></i>
        </div>
        <h2><?php echo esc_html__('Capítulo Premium', 'manga-admin-panel'); ?></h2>
        <p><?php echo esc_html__('Este capítulo está disponível apenas para usuários premium. Torne-se um membro premium para acessar todos os capítulos exclusivos.', 'manga-admin-panel'); ?></p>
        <div class="manga-premium-actions">
            <a href="#" class="manga-btn-premium">
                <i class="fas fa-crown"></i> <?php echo esc_html__('Tornar-se Premium', 'manga-admin-panel'); ?>
            </a>
            <a href="<?php echo esc_url($manga_permalink); ?>" class="manga-btn manga-btn-secondary">
                <i class="fas fa-arrow-left"></i> <?php echo esc_html__('Voltar para a lista de capítulos', 'manga-admin-panel'); ?>
            </a>
        </div>
    </div>
    <?php
    return;
}

// Adicionar classe de leitor ao body
add_filter('body_class', function($classes) {
    $classes[] = 'manga-reading';
    $classes[] = 'manga-dark-theme';
    return $classes;
});

// Carregar estilos específicos para o leitor
wp_enqueue_style('manga-reader-styles', MANGA_ADMIN_PANEL_URL . 'assets/css/manga-reader-styles.css', array(), MANGA_ADMIN_PANEL_VERSION);
?>

<div class="manga-reader-container" id="manga-reader-main">
    <!-- Barra de Navegação Superior (Cabeçalho) -->
    <div class="manga-reader-header" id="manga-reader-header">
        <div class="manga-reader-top-bar">
            <div class="manga-reader-title-group">
                <div class="manga-reader-thumbnail">
                    <img src="<?php echo esc_url($manga_thumbnail); ?>" alt="<?php echo esc_attr($manga_title); ?>">
                </div>
                
                <div class="manga-reader-title">
                    <h1><?php echo esc_html($manga_title); ?></h1>
                    <h2><?php echo esc_html($chapter_data['name']); ?></h2>
                </div>
            </div>
            
            <div class="manga-reader-nav-top">
                <a href="<?php echo esc_url($manga_permalink); ?>" class="manga-reader-top-btn">
                    <i class="fas fa-list"></i> <?php echo esc_html__('Capítulos', 'manga-admin-panel'); ?>
                </a>
                
                <?php if (is_user_logged_in()) : ?>
                <button id="bookmark-chapter" class="manga-reader-top-btn">
                    <i class="far fa-bookmark"></i> <?php echo esc_html__('Adicionar aos Favoritos', 'manga-admin-panel'); ?>
                </button>
                <?php endif; ?>
                
                <button id="toggle-fullscreen" class="manga-reader-top-btn">
                    <i class="fas fa-expand"></i> <span id="fullscreen-text"><?php echo esc_html__('Tela Cheia', 'manga-admin-panel'); ?></span>
                </button>
                
                <?php if ($chapter_data['next_chapter']) : ?>
                <a href="<?php echo esc_url(add_query_arg(array('manga_id' => $manga_id, 'chapter_id' => $chapter_data['next_chapter']))); ?>" class="manga-reader-top-btn manga-btn-accent">
                    <?php echo esc_html__('Próximo Capítulo', 'manga-admin-panel'); ?> <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Controles do Leitor -->
        <div class="manga-reader-controls">
            <div class="manga-reader-setting-group">
                <label for="reader-view-mode"><?php echo esc_html__('Modo:', 'manga-admin-panel'); ?></label>
                <select id="reader-view-mode" class="manga-reader-view-mode">
                    <option value="pagination" <?php selected($user_view_mode, 'pagination'); ?>><?php echo esc_html__('Páginas', 'manga-admin-panel'); ?></option>
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
                            <option value="<?php echo esc_attr($chapter['id']); ?>" <?php selected($chapter['is_current'], true); ?>>
                                <?php echo esc_html($chapter['name']); ?>
                                <?php if ($chapter['is_premium']) : ?>
                                    👑
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if ($chapter_data['prev_chapter']) : ?>
                    <a href="<?php echo esc_url(add_query_arg(array('manga_id' => $manga_id, 'chapter_id' => $chapter_data['prev_chapter']))); ?>" class="manga-reader-nav-btn prev-chapter">
                        <i class="fas fa-chevron-left"></i> <?php echo esc_html__('Capítulo Anterior', 'manga-admin-panel'); ?>
                    </a>
                <?php endif; ?>
                
                <?php if ($chapter_data['next_chapter']) : ?>
                    <a href="<?php echo esc_url(add_query_arg(array('manga_id' => $manga_id, 'chapter_id' => $chapter_data['next_chapter']))); ?>" class="manga-reader-nav-btn next-chapter">
                        <?php echo esc_html__('Próximo Capítulo', 'manga-admin-panel'); ?> <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Botão para mostrar/esconder cabeçalho -->
    <button id="toggle-header" class="manga-reader-toggle-header">
        <i class="fas fa-chevron-down"></i>
    </button>
    
    <!-- Conteúdo Principal -->
    <div class="manga-reader-content">
        <!-- Modo de visualização paginada -->
        <div id="reader-pagination" class="manga-reader-pagination <?php echo $user_view_mode === 'pagination' ? 'active' : ''; ?>">
            <div class="manga-reader-pages" id="manga-pages-container">
                <div class="manga-reader-loading">
                    <i class="fas fa-spinner"></i>
                    <div><?php echo esc_html__('Carregando...', 'manga-admin-panel'); ?></div>
                </div>
                
                <?php foreach ($chapter_data['pages'] as $index => $page) : ?>
                    <div class="manga-reader-page <?php echo $index === 0 ? 'active' : ''; ?>" data-page="<?php echo esc_attr($page['id']); ?>">
                        <img src="<?php echo esc_url($page['url']); ?>" alt="<?php echo esc_attr($page['alt']); ?>" loading="lazy">
                        <div class="manga-reader-page-number"><?php echo esc_html($page['id']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Áreas de clique para navegação -->
            <div class="manga-reader-nav-overlay left" id="prev-page-click">
                <div class="nav-arrow">
                    <i class="fas fa-chevron-left"></i>
                </div>
            </div>
            
            <div class="manga-reader-nav-overlay right" id="next-page-click">
                <div class="nav-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            
            <!-- Controles de paginação -->
            <div class="manga-reader-pagination-controls">
                <button class="manga-reader-page-btn prev-page" id="prev-page-btn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="manga-reader-page-counter">
                    <span id="current-page">1</span> / <span id="total-pages"><?php echo count($chapter_data['pages']); ?></span>
                </div>
                
                <button class="manga-reader-page-btn next-page" id="next-page-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        
        <!-- Modo de visualização de lista corrida (webtoon) -->
        <div id="reader-webtoon" class="manga-reader-webtoon <?php echo $user_view_mode === 'webtoon' ? 'active' : ''; ?>">
            <div class="manga-reader-loading">
                <i class="fas fa-spinner"></i>
                <div><?php echo esc_html__('Carregando...', 'manga-admin-panel'); ?></div>
            </div>
            
            <?php foreach ($chapter_data['pages'] as $page) : ?>
                <div class="manga-reader-webtoon-image">
                    <img src="<?php echo esc_url($page['url']); ?>" alt="<?php echo esc_attr($page['alt']); ?>" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Barra de Progresso de Leitura -->
    <div class="manga-reading-progress">
        <div class="manga-reading-progress-fill" id="reading-progress-bar"></div>
    </div>
    
    <!-- Botão flutuante para voltar ao topo -->
    <button id="back-to-top" class="manga-reader-back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <!-- Botão de tela cheia para dispositivos móveis -->
    <button id="mobile-fullscreen" class="manga-fullscreen-toggle">
        <i class="fas fa-expand"></i>
    </button>
    
    <!-- Dica para navegação -->
    <div id="reader-hint" class="manga-reader-hint">
        <?php echo esc_html__('Use as setas', 'manga-admin-panel'); ?> <kbd>←</kbd> <kbd>→</kbd> <?php echo esc_html__('ou clique nas laterais para navegar', 'manga-admin-panel'); ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Variáveis
    let currentPage = 1;
    const totalPages = <?php echo count($chapter_data['pages']); ?>;
    let viewMode = '<?php echo esc_js($user_view_mode); ?>';
    let brightness = <?php echo esc_js($user_brightness); ?>;
    const mangaId = <?php echo esc_js($manga_id); ?>;
    const chapterId = <?php echo esc_js($chapter_id); ?>;
    let isFullscreen = false;
    let headerVisible = true;
    let immersiveMode = false;
    
    // Inicialização
    updatePageDisplay();
    applyBrightness();
    initializeView();
    showHint();
    
    // Remover loading quando as imagens estiverem carregadas
    $(".manga-reader-page img, .manga-reader-webtoon-image img").on('load', function() {
        $(".manga-reader-loading").fadeOut(300);
    });
    
    // Mostrar dica para navegação
    function showHint() {
        setTimeout(function() {
            $("#reader-hint").addClass('visible');
            
            setTimeout(function() {
                $("#reader-hint").removeClass('visible');
            }, 5000);
        }, 1000);
    }
    
    // Inicializar visualização baseado no modo
    function initializeView() {
        if (viewMode === 'pagination') {
            showActivePage();
        } else {
            // Mostrar botão de voltar ao topo e inicializar barra de progresso
            checkScrollPosition();
            updateReadingProgress();
        }
    }
    
    // Alternar entre modos de visualização
    $('#reader-view-mode').on('change', function() {
        viewMode = $(this).val();
        
        // Salvar preferência
        saveReaderPreferences();
        
        // Atualizar interface
        if (viewMode === 'pagination') {
            $('#reader-pagination').addClass('active');
            $('#reader-webtoon').removeClass('active');
            $('#back-to-top').removeClass('visible');
            showActivePage();
        } else {
            $('#reader-pagination').removeClass('active');
            $('#reader-webtoon').addClass('active');
            checkScrollPosition();
        }
    });
    
    // Ajustar brilho
    $('#reader-brightness').on('input', function() {
        brightness = $(this).val();
        $('.brightness-value').text(brightness + '%');
        applyBrightness();
        
        // Salvar preferência com delay para evitar múltiplas chamadas
        clearTimeout(window.brightnessTimeout);
        window.brightnessTimeout = setTimeout(function() {
            saveReaderPreferences();
        }, 300);
    });
    
    // Salvar preferências do leitor
    function saveReaderPreferences() {
        // Salvar cookies localmente
        document.cookie = 'manga_reader_view_mode=' + viewMode + '; path=/; max-age=31536000'; // 1 ano
        document.cookie = 'manga_reader_brightness=' + brightness + '; path=/; max-age=31536000'; // 1 ano
        
        // Em um ambiente real, também salvaria via AJAX para usuários logados
    }
    
    // Navegação de páginas no modo paginação
    $('.next-page, #next-page-btn, #next-page-click').on('click', function() {
        if (viewMode === 'pagination') {
            if (currentPage < totalPages) {
                currentPage++;
                updatePageDisplay();
                showActivePage();
                saveReadingProgress();
            } else {
                goToNextChapter();
            }
        }
    });
    
    $('.prev-page, #prev-page-btn, #prev-page-click').on('click', function() {
        if (viewMode === 'pagination') {
            if (currentPage > 1) {
                currentPage--;
                updatePageDisplay();
                showActivePage();
            }
        }
    });
    
    // Ir para o próximo capítulo
    function goToNextChapter() {
        <?php if ($chapter_data['next_chapter']) : ?>
        // Verificar se o usuário deseja ir para o próximo capítulo
        if (confirm('<?php echo esc_js(__('Ir para o próximo capítulo?', 'manga-admin-panel')); ?>')) {
            window.location.href = '<?php echo esc_js(add_query_arg(array('manga_id' => $manga_id, 'chapter_id' => $chapter_data['next_chapter']))); ?>';
        }
        <?php endif; ?>
    }
    
    // Navegação por teclado
    $(document).on('keydown', function(e) {
        if (viewMode === 'pagination') {
            if (e.keyCode === 39 || e.keyCode === 40 || e.keyCode === 32) {
                // Seta direita, seta baixo ou espaço
                $('#next-page-btn').click();
                e.preventDefault();
            } else if (e.keyCode === 37 || e.keyCode === 38) {
                // Seta esquerda ou seta cima
                $('#prev-page-btn').click();
                e.preventDefault();
            }
        }
    });
    
    // Alterar capítulo
    $('#chapter-select').on('change', function() {
        const chapterId = $(this).val();
        window.location.href = '<?php echo esc_js(add_query_arg(array('manga_id' => $manga_id, 'chapter_id' => ''))); ?>' + chapterId;
    });
    
    // Botão voltar ao topo
    $('#back-to-top').on('click', function() {
        $('html, body').animate({ scrollTop: 0 }, 300);
    });
    
    // Verificar posição de scroll para mostrar botão voltar ao topo
    $(window).on('scroll', function() {
        checkScrollPosition();
        updateReadingProgress();
    });
    
    // Atualizar barra de progresso de leitura
    function updateReadingProgress() {
        if (viewMode === 'webtoon') {
            const windowHeight = $(window).height();
            const documentHeight = $(document).height();
            const scrollTop = $(window).scrollTop();
            const scrollPercent = (scrollTop / (documentHeight - windowHeight)) * 100;
            
            $('#reading-progress-bar').css('width', scrollPercent + '%');
            
            // Salvar progresso de leitura a cada 10% de scroll
            if (Math.floor(scrollPercent) % 10 === 0) {
                saveReadingProgress(scrollPercent);
            }
        } else {
            // Em modo paginação, atualizar baseado na página atual
            const progressPercent = (currentPage / totalPages) * 100;
            $('#reading-progress-bar').css('width', progressPercent + '%');
        }
    }
    
    // Verificar posição de scroll
    function checkScrollPosition() {
        const scrollTop = $(window).scrollTop();
        
        // Mostrar/esconder botão voltar ao topo
        if (scrollTop > 300) {
            $('#back-to-top').addClass('visible');
        } else {
            $('#back-to-top').removeClass('visible');
        }
        
        // Auto-esconder cabeçalho ao rolar para baixo (só em modo webtoon)
        if (viewMode === 'webtoon' && !immersiveMode) {
            if (scrollTop > 200 && headerVisible) {
                hideHeader();
            } else if (scrollTop < 100 && !headerVisible) {
                showHeader();
            }
        }
        
        // Mostrar botão para alternar cabeçalho
        if (scrollTop > 150) {
            $('#toggle-header').addClass('visible');
        } else {
            $('#toggle-header').removeClass('visible');
        }
    }
    
    // Alternar cabeçalho
    $('#toggle-header').on('click', function() {
        if (headerVisible) {
            hideHeader();
        } else {
            showHeader();
        }
    });
    
    // Esconder cabeçalho
    function hideHeader() {
        $('#manga-reader-header').addClass('hidden');
        $('#toggle-header').html('<i class="fas fa-chevron-up"></i>');
        headerVisible = false;
    }
    
    // Mostrar cabeçalho
    function showHeader() {
        $('#manga-reader-header').removeClass('hidden');
        $('#toggle-header').html('<i class="fas fa-chevron-down"></i>');
        headerVisible = true;
    }
    
    // Modo tela cheia
    $('#toggle-fullscreen, #mobile-fullscreen').on('click', function() {
        toggleFullscreen();
    });
    
    // Alternar modo tela cheia
    function toggleFullscreen() {
        if (!isFullscreen) {
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            } else if (document.documentElement.mozRequestFullScreen) {
                document.documentElement.mozRequestFullScreen();
            } else if (document.documentElement.webkitRequestFullscreen) {
                document.documentElement.webkitRequestFullscreen();
            } else if (document.documentElement.msRequestFullscreen) {
                document.documentElement.msRequestFullscreen();
            }
            isFullscreen = true;
            $('#fullscreen-text').text('<?php echo esc_js(__('Sair da Tela Cheia', 'manga-admin-panel')); ?>');
            $('#toggle-fullscreen i, #mobile-fullscreen i').removeClass('fa-expand').addClass('fa-compress');
            $('#manga-reader-main').addClass('manga-reader-fullscreen');
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
            isFullscreen = false;
            $('#fullscreen-text').text('<?php echo esc_js(__('Tela Cheia', 'manga-admin-panel')); ?>');
            $('#toggle-fullscreen i, #mobile-fullscreen i').removeClass('fa-compress').addClass('fa-expand');
            $('#manga-reader-main').removeClass('manga-reader-fullscreen');
        }
    }
    
    // Detectar mudanças no estado de tela cheia
    $(document).on('fullscreenchange webkitfullscreenchange mozfullscreenchange MSFullscreenChange', function() {
        isFullscreen = !!document.fullscreenElement;
        
        if (!isFullscreen) {
            $('#fullscreen-text').text('<?php echo esc_js(__('Tela Cheia', 'manga-admin-panel')); ?>');
            $('#toggle-fullscreen i, #mobile-fullscreen i').removeClass('fa-compress').addClass('fa-expand');
            $('#manga-reader-main').removeClass('manga-reader-fullscreen');
        } else {
            $('#fullscreen-text').text('<?php echo esc_js(__('Sair da Tela Cheia', 'manga-admin-panel')); ?>');
            $('#toggle-fullscreen i, #mobile-fullscreen i').removeClass('fa-expand').addClass('fa-compress');
            $('#manga-reader-main').addClass('manga-reader-fullscreen');
        }
    });
    
    // Duplo clique para entrar/sair do modo tela cheia
    $('.manga-reader-page, .manga-reader-webtoon-image').on('dblclick', function(e) {
        // Prevenir que a imagem seja selecionada ao clicar
        e.preventDefault();
        toggleFullscreen();
    });
    
    // Adicionar aos favoritos
    $('#bookmark-chapter').on('click', function() {
        // Verificar se já está marcado
        const isFavorited = $(this).hasClass('active');
        
        if (!isFavorited) {
            // Adicionar aos favoritos
            $(this).addClass('active');
            $(this).html('<i class="fas fa-bookmark"></i> <?php echo esc_js(__('Favoritado', 'manga-admin-panel')); ?>');
            
            // Em um ambiente real, enviaria uma requisição AJAX
            console.log('Adicionado aos favoritos:', mangaId, chapterId);
        } else {
            // Remover dos favoritos
            $(this).removeClass('active');
            $(this).html('<i class="far fa-bookmark"></i> <?php echo esc_js(__('Adicionar aos Favoritos', 'manga-admin-panel')); ?>');
            
            // Em um ambiente real, enviaria uma requisição AJAX
            console.log('Removido dos favoritos:', mangaId, chapterId);
        }
    });
    
    // Salvar progresso de leitura
    function saveReadingProgress(percentRead = 0) {
        // Calcular porcentagem lida para o modo paginado
        if (viewMode === 'pagination') {
            percentRead = (currentPage / totalPages) * 100;
        }
        
        // Em um ambiente real, enviaria uma requisição AJAX para salvar o progresso
        console.log('Salvando progresso:', mangaId, chapterId, currentPage, percentRead);
    }
    
    // Funções auxiliares
    function updatePageDisplay() {
        $('#current-page').text(currentPage);
    }
    
    function showActivePage() {
        $('.manga-reader-page').removeClass('active');
        $(`.manga-reader-page[data-page="${currentPage}"]`).addClass('active');
        
        // Atualizar barra de progresso
        updateReadingProgress();
    }
    
    function applyBrightness() {
        $('.manga-reader-page img, .manga-reader-webtoon-image img').css('filter', `brightness(${brightness/100})`);
    }
    
    // Pré-carregar as próximas páginas para melhor performance
    function preloadNextPages() {
        if (viewMode === 'pagination') {
            // Pré-carregar as próximas 2 páginas e a anterior
            const pagesToPreload = [];
            
            // Página anterior
            if (currentPage > 1) {
                pagesToPreload.push(currentPage - 1);
            }
            
            // Próximas páginas
            for (let i = currentPage + 1; i <= Math.min(currentPage + 2, totalPages); i++) {
                pagesToPreload.push(i);
            }
            
            // Carregar imagens
            pagesToPreload.forEach(pageNum => {
                const pageEl = $(`.manga-reader-page[data-page="${pageNum}"] img`);
                if (pageEl.attr('loading') === 'lazy') {
                    pageEl.attr('loading', 'eager');
                }
            });
        }
    }
    
    // Chamar preload inicial
    preloadNextPages();
    
    // Preload após mudança de página
    $('.next-page, .prev-page').on('click', function() {
        preloadNextPages();
    });
});
</script>