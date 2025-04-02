<?php
/**
 * Template para exibir a lista de capítulos de um mangá
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

// Obter o ID do mangá
if (!isset($manga_id) || !$manga_id) {
    echo '<div class="manga-alert manga-alert-danger">' . 
         esc_html__('ID do mangá não fornecido.', 'manga-admin-panel') . 
         '</div>';
    return;
}

// Obter informações do mangá
$manga_title = get_the_title($manga_id);
$manga_permalink = get_permalink($manga_id);
$manga_thumbnail = get_the_post_thumbnail_url($manga_id, 'medium');
$manga_excerpt = get_the_excerpt($manga_id);

// Obter lista de capítulos
$chapters = array();

// Função simulada para obter capítulos - remover em produção
// Esta é apenas uma simulação para desenvolvimento
function get_manga_chapters_list($manga_id) {
    // Em um ambiente real, isso buscaria os capítulos do banco de dados
    $chapters = array();
    
    // Gerar capítulos de exemplo
    for ($i = 1; $i <= 30; $i++) {
        $date = date('Y-m-d H:i:s', strtotime('-' . (30 - $i) . ' days'));
        $is_premium = $i > 25 ? true : false;
        $is_scheduled = $i > 27 ? true : false;
        
        $chapters[] = array(
            'id' => $i,
            'number' => $i,
            'title' => 'Capítulo ' . $i,
            'date' => $date,
            'views' => rand(100, 5000),
            'is_premium' => $is_premium,
            'is_scheduled' => $is_scheduled,
            'scheduled_date' => $is_scheduled ? date('Y-m-d H:i:s', strtotime('+' . (30 - $i) . ' days')) : '',
        );
    }
    
    return $chapters;
}

$chapters = get_manga_chapters_list($manga_id);

// Ordenação dos capítulos (mais recentes primeiro por padrão)
$chapters = array_reverse($chapters);

// Verificar se o usuário tem acesso a capítulos premium
function user_has_premium_access() {
    // Implementar lógica real
    return current_user_can('administrator') || current_user_can('editor');
}

$has_premium_access = user_has_premium_access();

// Determinar a URL de base para os links dos capítulos
$chapter_base_url = add_query_arg('manga_id', $manga_id, remove_query_arg('chapter_id'));
?>

<div class="manga-chapters-container">
    <div class="manga-chapters-header">
        <div class="manga-chapters-info">
            <?php if ($manga_thumbnail) : ?>
                <div class="manga-chapters-thumbnail">
                    <img src="<?php echo esc_url($manga_thumbnail); ?>" alt="<?php echo esc_attr($manga_title); ?>">
                </div>
            <?php endif; ?>
            
            <div class="manga-chapters-details">
                <h1 class="manga-chapters-title"><?php echo esc_html($manga_title); ?></h1>
                
                <?php if ($manga_excerpt) : ?>
                    <div class="manga-chapters-excerpt">
                        <?php echo wp_kses_post($manga_excerpt); ?>
                    </div>
                <?php endif; ?>
                
                <div class="manga-chapters-meta">
                    <div class="manga-chapters-count">
                        <i class="fas fa-book-open"></i> <?php echo sprintf(esc_html(_n('%s capítulo', '%s capítulos', count($chapters), 'manga-admin-panel')), count($chapters)); ?>
                    </div>
                    
                    <a href="<?php echo esc_url($manga_permalink); ?>" class="manga-chapters-info-link">
                        <i class="fas fa-info-circle"></i> <?php echo esc_html__('Informações do Mangá', 'manga-admin-panel'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="manga-chapters-controls">
        <div class="manga-chapters-search">
            <input type="text" id="manga-chapters-search" placeholder="<?php echo esc_attr__('Buscar capítulos...', 'manga-admin-panel'); ?>" class="manga-chapters-search-input">
        </div>
        
        <div class="manga-chapters-sort">
            <label for="manga-chapters-sort-select"><?php echo esc_html__('Ordenar por:', 'manga-admin-panel'); ?></label>
            <select id="manga-chapters-sort-select" class="manga-chapters-sort-select">
                <option value="newest"><?php echo esc_html__('Mais recentes primeiro', 'manga-admin-panel'); ?></option>
                <option value="oldest"><?php echo esc_html__('Mais antigos primeiro', 'manga-admin-panel'); ?></option>
                <option value="number_asc"><?php echo esc_html__('Número (crescente)', 'manga-admin-panel'); ?></option>
                <option value="number_desc"><?php echo esc_html__('Número (decrescente)', 'manga-admin-panel'); ?></option>
                <option value="views"><?php echo esc_html__('Mais vistos', 'manga-admin-panel'); ?></option>
            </select>
        </div>
    </div>
    
    <?php if (empty($chapters)) : ?>
        <div class="manga-empty-state">
            <div class="manga-empty-icon"><i class="fas fa-book"></i></div>
            <div class="manga-empty-text"><?php echo esc_html__('Nenhum capítulo disponível para este mangá.', 'manga-admin-panel'); ?></div>
        </div>
    <?php else : ?>
        <div class="manga-chapters-list">
            <?php foreach ($chapters as $chapter) :
                $chapter_url = add_query_arg('chapter_id', $chapter['id'], $chapter_base_url);
                $chapter_date = new DateTime($chapter['date']);
                $now = new DateTime();
                $interval = $now->diff($chapter_date);
                
                // Formatação de tempo relativo
                if ($interval->days == 0) {
                    if ($interval->h == 0) {
                        $time_ago = sprintf(esc_html__('há %d minutos', 'manga-admin-panel'), $interval->i);
                    } else {
                        $time_ago = sprintf(esc_html__('há %d horas', 'manga-admin-panel'), $interval->h);
                    }
                } elseif ($interval->days == 1) {
                    $time_ago = esc_html__('ontem', 'manga-admin-panel');
                } elseif ($interval->days < 7) {
                    $time_ago = sprintf(esc_html__('há %d dias', 'manga-admin-panel'), $interval->days);
                } else {
                    $time_ago = $chapter_date->format('d/m/Y');
                }
                
                // Verificar se é premium e se o usuário tem acesso
                $is_locked = $chapter['is_premium'] && !$has_premium_access;
                
                // Verificar se é agendado
                $is_scheduled = $chapter['is_scheduled'];
                
                // Classes para o capítulo
                $chapter_classes = array('manga-chapter-item');
                if ($is_locked) $chapter_classes[] = 'premium-locked';
                if ($is_scheduled) $chapter_classes[] = 'scheduled';
                
                // Se este é o primeiro capítulo da lista, adicionar classe
                if ($chapter === reset($chapters)) {
                    $chapter_classes[] = 'first-chapter';
                }
                
                // Se este é o último capítulo da lista, adicionar classe
                if ($chapter === end($chapters)) {
                    $chapter_classes[] = 'latest-chapter';
                }
            ?>
                <div class="<?php echo esc_attr(implode(' ', $chapter_classes)); ?>" data-number="<?php echo esc_attr($chapter['number']); ?>" data-views="<?php echo esc_attr($chapter['views']); ?>" data-date="<?php echo esc_attr($chapter['date']); ?>">
                    <div class="manga-chapter-info">
                        <div class="manga-chapter-name">
                            <span class="manga-chapter-number"><?php echo esc_html($chapter['number']); ?></span>
                            <h3 class="manga-chapter-title">
                                <?php if ($is_locked) : ?>
                                    <i class="fas fa-crown premium-icon" title="<?php echo esc_attr__('Capítulo Premium', 'manga-admin-panel'); ?>"></i>
                                <?php endif; ?>
                                
                                <?php if ($is_scheduled) : ?>
                                    <i class="fas fa-lock schedule-icon" title="<?php echo esc_attr__('Capítulo Agendado', 'manga-admin-panel'); ?>"></i>
                                    <?php 
                                    // Mostrar data de agendamento
                                    $scheduled_date = new DateTime($chapter['scheduled_date']);
                                    echo esc_html($chapter['title']) . ' - ' . sprintf(esc_html__('Disponível em %s', 'manga-admin-panel'), $scheduled_date->format('d/m/Y'));
                                    ?>
                                <?php else : ?>
                                    <a href="<?php echo esc_url($chapter_url); ?>" class="manga-chapter-link">
                                        <?php echo esc_html($chapter['title']); ?>
                                    </a>
                                <?php endif; ?>
                            </h3>
                        </div>
                        
                        <div class="manga-chapter-meta">
                            <span class="manga-chapter-date">
                                <i class="far fa-clock"></i> <?php echo esc_html($time_ago); ?>
                            </span>
                            
                            <span class="manga-chapter-views">
                                <i class="far fa-eye"></i> <?php echo number_format($chapter['views']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="manga-chapter-actions">
                        <?php if (!$is_scheduled) : ?>
                            <?php if ($is_locked) : ?>
                                <a href="#unlock-premium" class="manga-btn manga-btn-sm manga-btn-premium unlock-premium-btn">
                                    <i class="fas fa-crown"></i> <?php echo esc_html__('Desbloquear', 'manga-admin-panel'); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url($chapter_url); ?>" class="manga-btn manga-btn-sm manga-btn-primary manga-read-btn">
                                    <i class="fas fa-book-reader"></i> <?php echo esc_html__('Ler', 'manga-admin-panel'); ?>
                                </a>
                            <?php endif; ?>
                        <?php else : ?>
                            <span class="manga-scheduled-badge">
                                <i class="fas fa-clock"></i> <?php echo esc_html__('Agendado', 'manga-admin-panel'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Estilos para a lista de capítulos */
    .manga-chapters-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    
    /* Cabeçalho com informações do mangá */
    .manga-chapters-header {
        margin-bottom: 30px;
    }
    
    .manga-chapters-info {
        display: flex;
        gap: 20px;
    }
    
    .manga-chapters-thumbnail {
        width: 150px;
        height: 225px;
        border-radius: 5px;
        overflow: hidden;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .manga-chapters-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .manga-chapters-details {
        flex: 1;
    }
    
    .manga-chapters-title {
        font-size: 24px;
        margin: 0 0 15px 0;
        color: var(--manga-text-color, #333);
    }
    
    .manga-chapters-excerpt {
        font-size: 15px;
        color: var(--manga-light-text, #718093);
        margin-bottom: 20px;
        line-height: 1.5;
    }
    
    .manga-chapters-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 14px;
        color: var(--manga-light-text, #718093);
    }
    
    .manga-chapters-count {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .manga-chapters-info-link {
        display: flex;
        align-items: center;
        gap: 5px;
        color: var(--manga-accent-color, #4b7bec);
        text-decoration: none;
    }
    
    .manga-chapters-info-link:hover {
        text-decoration: underline;
    }
    
    /* Controles de busca e ordenação */
    .manga-chapters-controls {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        padding: 15px;
        background-color: var(--manga-card-color, #fff);
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .manga-chapters-search {
        flex: 1;
        max-width: 300px;
    }
    
    .manga-chapters-search-input {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .manga-chapters-sort {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .manga-chapters-sort label {
        font-size: 14px;
        color: var(--manga-light-text, #718093);
    }
    
    .manga-chapters-sort-select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    /* Lista de capítulos */
    .manga-chapters-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .manga-chapter-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background-color: var(--manga-card-color, #fff);
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .manga-chapter-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }
    
    .manga-chapter-item.latest-chapter {
        border-left: 3px solid var(--manga-primary-color, #ff6b6b);
    }
    
    .manga-chapter-item.premium-locked {
        background-color: rgba(255, 223, 107, 0.1);
        border-left: 3px solid #f7b731;
    }
    
    .manga-chapter-item.scheduled {
        background-color: rgba(75, 123, 236, 0.05);
        border-left: 3px solid var(--manga-accent-color, #4b7bec);
    }
    
    .manga-chapter-info {
        flex: 1;
    }
    
    .manga-chapter-name {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 5px;
    }
    
    .manga-chapter-number {
        background-color: var(--manga-primary-color, #ff6b6b);
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
    }
    
    .manga-chapter-title {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--manga-text-color, #333);
    }
    
    .manga-chapter-link {
        color: var(--manga-text-color, #333);
        text-decoration: none;
    }
    
    .manga-chapter-link:hover {
        color: var(--manga-primary-color, #ff6b6b);
    }
    
    .manga-chapter-meta {
        display: flex;
        gap: 15px;
        font-size: 13px;
        color: var(--manga-light-text, #718093);
        margin-left: 38px;
    }
    
    .premium-icon, .schedule-icon {
        margin-right: 5px;
        font-size: 14px;
    }
    
    .premium-icon {
        color: #f7b731;
    }
    
    .schedule-icon {
        color: var(--manga-accent-color, #4b7bec);
    }
    
    .manga-chapter-actions {
        flex-shrink: 0;
    }
    
    .manga-btn-premium {
        background-color: #f7b731;
        color: white;
    }
    
    .manga-btn-premium:hover {
        background-color: #e1a425;
    }
    
    .manga-scheduled-badge {
        display: inline-block;
        padding: 8px 12px;
        background-color: #f0f0f0;
        border-radius: 4px;
        font-size: 12px;
        color: var(--manga-accent-color, #4b7bec);
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .manga-chapters-info {
            flex-direction: column;
        }
        
        .manga-chapters-thumbnail {
            width: 120px;
            height: 180px;
            margin: 0 auto 20px auto;
        }
        
        .manga-chapters-details {
            text-align: center;
        }
        
        .manga-chapters-meta {
            justify-content: center;
        }
        
        .manga-chapters-controls {
            flex-direction: column;
            gap: 15px;
        }
        
        .manga-chapters-search {
            max-width: 100%;
        }
        
        .manga-chapter-item {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .manga-chapter-actions {
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Variáveis
        let currentSort = 'newest';
        
        // Busca
        $('#manga-chapters-search').on('input', function() {
            const searchText = $(this).val().toLowerCase();
            
            $('.manga-chapter-item').each(function() {
                const chapterTitle = $(this).find('.manga-chapter-title').text().toLowerCase();
                const chapterNumber = $(this).data('number').toString();
                
                if (chapterTitle.includes(searchText) || chapterNumber.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // Ordenação
        $('#manga-chapters-sort-select').on('change', function() {
            currentSort = $(this).val();
            sortChapters();
        });
        
        // Função para ordenar capítulos
        function sortChapters() {
            const chapters = $('.manga-chapter-item').get();
            
            chapters.sort(function(a, b) {
                switch (currentSort) {
                    case 'newest':
                        return new Date($(b).data('date')) - new Date($(a).data('date'));
                    case 'oldest':
                        return new Date($(a).data('date')) - new Date($(b).data('date'));
                    case 'number_asc':
                        return $(a).data('number') - $(b).data('number');
                    case 'number_desc':
                        return $(b).data('number') - $(a).data('number');
                    case 'views':
                        return $(b).data('views') - $(a).data('views');
                    default:
                        return 0;
                }
            });
            
            // Reordenar no DOM
            $.each(chapters, function(i, chapter) {
                $('.manga-chapters-list').append(chapter);
            });
        }
        
        // Modal para desbloquear premium (simulação)
        $('.unlock-premium-btn').on('click', function(e) {
            e.preventDefault();
            
            // Em um ambiente real, isto mostraria um modal ou redirecionaria para página de assinatura
            alert('<?php echo esc_js(__('Recurso Premium: Para acessar este capítulo, é necessário ser um assinante premium.', 'manga-admin-panel')); ?>');
        });
    });
</script>