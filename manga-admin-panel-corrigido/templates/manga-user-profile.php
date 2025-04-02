<?php
/**
 * Template para perfil do usuário com interface moderna
 * Mostra estatísticas de leitura e biblioteca de mangás
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

// Verificar se o usuário está logado
if (!is_user_logged_in()) {
    ?>
    <div class="manga-profile-login-required">
        <div class="manga-card">
            <div class="manga-alert manga-alert-info">
                <i class="fas fa-info-circle"></i> <?php echo esc_html__('É necessário estar logado para acessar seu perfil e biblioteca de mangás.', 'manga-admin-panel'); ?>
            </div>
            
            <div class="manga-login-form-container">
                <?php echo manga_admin_login_form(__('Faça login para ver sua biblioteca e gerenciar seus mangás favoritos.', 'manga-admin-panel')); ?>
            </div>
        </div>
    </div>
    <?php
    return;
}

// Obter usuário atual ou usuário especificado
$current_user_id = get_current_user_id();
$profile_user_id = isset($user_id) && $user_id > 0 ? intval($user_id) : $current_user_id;
$is_current_user = $profile_user_id === $current_user_id;

// Obter informações do usuário
$user_data = get_userdata($profile_user_id);
if (!$user_data) {
    echo '<div class="manga-alert manga-alert-danger">' . esc_html__('Usuário não encontrado.', 'manga-admin-panel') . '</div>';
    return;
}

$user_display_name = $user_data->display_name;
$user_avatar_url = get_avatar_url($profile_user_id, array('size' => 150));

// Status de leitura disponíveis
$reading_statuses = array(
    'reading' => __('Lendo', 'manga-admin-panel'),
    'completed' => __('Concluído', 'manga-admin-panel'),
    'on_hold' => __('Em espera', 'manga-admin-panel'),
    'dropped' => __('Abandonado', 'manga-admin-panel'),
    'plan_to_read' => __('Planejo ler', 'manga-admin-panel'),
);

// Função para obter a lista de mangá do usuário (simulada)
function get_user_manga_list($user_id) {
    // Em uma implementação real, isso buscaria os dados do banco de dados
    // Para desenvolvimento, usamos dados de exemplo
    
    $mangas = array(
        array(
            'id' => 1,
            'title' => 'One Piece',
            'thumbnail' => 'https://via.placeholder.com/300x400.png?text=One+Piece',
            'status' => 'reading',
            'progress' => 1043,
            'total_chapters' => 1056,
            'rating' => 5,
            'last_read' => '2023-12-15',
        ),
        array(
            'id' => 2,
            'title' => 'Berserk',
            'thumbnail' => 'https://via.placeholder.com/300x400.png?text=Berserk',
            'status' => 'on_hold',
            'progress' => 250,
            'total_chapters' => 365,
            'rating' => 4.5,
            'last_read' => '2023-10-30',
        ),
        array(
            'id' => 3,
            'title' => 'Naruto',
            'thumbnail' => 'https://via.placeholder.com/300x400.png?text=Naruto',
            'status' => 'completed',
            'progress' => 700,
            'total_chapters' => 700,
            'rating' => 4,
            'last_read' => '2022-05-20',
        ),
        array(
            'id' => 4,
            'title' => 'Vagabond',
            'thumbnail' => 'https://via.placeholder.com/300x400.png?text=Vagabond',
            'status' => 'dropped',
            'progress' => 60,
            'total_chapters' => 327,
            'rating' => 3,
            'last_read' => '2021-08-10',
        ),
        array(
            'id' => 5,
            'title' => 'Chainsaw Man',
            'thumbnail' => 'https://via.placeholder.com/300x400.png?text=Chainsaw+Man',
            'status' => 'plan_to_read',
            'progress' => 0,
            'total_chapters' => 120,
            'rating' => 0,
            'last_read' => null,
        ),
    );
    
    return $mangas;
}

// Obter lista de mangás
$user_manga_list = get_user_manga_list($profile_user_id);

// Calcular estatísticas de leitura
function calculate_reading_stats($manga_list) {
    $stats = array(
        'total' => count($manga_list),
        'reading' => 0,
        'completed' => 0,
        'on_hold' => 0,
        'dropped' => 0,
        'plan_to_read' => 0,
        'total_chapters_read' => 0,
        'avg_rating' => 0,
    );
    
    $total_rating = 0;
    $rated_count = 0;
    
    foreach ($manga_list as $manga) {
        // Contagem por status
        $stats[$manga['status']]++;
        
        // Total de capítulos lidos
        $stats['total_chapters_read'] += $manga['progress'];
        
        // Soma de avaliações
        if ($manga['rating'] > 0) {
            $total_rating += $manga['rating'];
            $rated_count++;
        }
    }
    
    // Calcular média de avaliação
    $stats['avg_rating'] = $rated_count > 0 ? round($total_rating / $rated_count, 1) : 0;
    
    return $stats;
}

$reading_stats = calculate_reading_stats($user_manga_list);

// Verificar opções do shortcode
$show_stats = !isset($show_stats) || $show_stats;
$show_lists = !isset($show_lists) || $show_lists;
$show_avatar = !isset($show_avatar) || $show_avatar;
?>

<div class="manga-profile-container">
    <div class="manga-profile-header">
        <?php if ($show_avatar) : ?>
        <div class="manga-profile-avatar">
            <img src="<?php echo esc_url($user_avatar_url); ?>" alt="<?php echo esc_attr($user_display_name); ?>">
        </div>
        <?php endif; ?>
        
        <div class="manga-profile-info">
            <h1 class="manga-profile-name"><?php echo esc_html($user_display_name); ?></h1>
            
            <?php if ($show_stats) : ?>
            <div class="manga-profile-stats">
                <div class="manga-profile-stat-item">
                    <div class="manga-profile-stat-number"><?php echo esc_html($reading_stats['total']); ?></div>
                    <div class="manga-profile-stat-label"><?php echo esc_html__('Total de Mangás', 'manga-admin-panel'); ?></div>
                </div>
                
                <div class="manga-profile-stat-item">
                    <div class="manga-profile-stat-number"><?php echo esc_html($reading_stats['completed']); ?></div>
                    <div class="manga-profile-stat-label"><?php echo esc_html__('Concluídos', 'manga-admin-panel'); ?></div>
                </div>
                
                <div class="manga-profile-stat-item">
                    <div class="manga-profile-stat-number"><?php echo esc_html($reading_stats['total_chapters_read']); ?></div>
                    <div class="manga-profile-stat-label"><?php echo esc_html__('Capítulos Lidos', 'manga-admin-panel'); ?></div>
                </div>
                
                <div class="manga-profile-stat-item">
                    <div class="manga-profile-stat-number"><?php echo esc_html($reading_stats['avg_rating']); ?><i class="fas fa-star"></i></div>
                    <div class="manga-profile-stat-label"><?php echo esc_html__('Nota Média', 'manga-admin-panel'); ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($is_current_user) : ?>
        <div class="manga-profile-actions">
            <button id="edit-profile-button" class="manga-btn manga-btn-secondary">
                <i class="fas fa-user-edit"></i> <?php echo esc_html__('Editar Perfil', 'manga-admin-panel'); ?>
            </button>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if ($show_lists) : ?>
    <div class="manga-profile-tabs">
        <div class="manga-profile-tab active" data-target="all"><?php echo esc_html__('Todos', 'manga-admin-panel'); ?> (<?php echo $reading_stats['total']; ?>)</div>
        <div class="manga-profile-tab" data-target="reading"><?php echo esc_html__('Lendo', 'manga-admin-panel'); ?> (<?php echo $reading_stats['reading']; ?>)</div>
        <div class="manga-profile-tab" data-target="completed"><?php echo esc_html__('Concluído', 'manga-admin-panel'); ?> (<?php echo $reading_stats['completed']; ?>)</div>
        <div class="manga-profile-tab" data-target="on_hold"><?php echo esc_html__('Em espera', 'manga-admin-panel'); ?> (<?php echo $reading_stats['on_hold']; ?>)</div>
        <div class="manga-profile-tab" data-target="dropped"><?php echo esc_html__('Abandonado', 'manga-admin-panel'); ?> (<?php echo $reading_stats['dropped']; ?>)</div>
        <div class="manga-profile-tab" data-target="plan_to_read"><?php echo esc_html__('Planejo ler', 'manga-admin-panel'); ?> (<?php echo $reading_stats['plan_to_read']; ?>)</div>
    </div>
    
    <div class="manga-profile-search">
        <input type="text" id="manga-profile-search-input" class="manga-profile-search-input" placeholder="<?php echo esc_attr__('Buscar em sua biblioteca...', 'manga-admin-panel'); ?>">
        <div class="manga-profile-sort">
            <label for="manga-profile-sort"><?php echo esc_html__('Ordenar por:', 'manga-admin-panel'); ?></label>
            <select id="manga-profile-sort" class="manga-profile-sort-select">
                <option value="title_asc"><?php echo esc_html__('Título (A-Z)', 'manga-admin-panel'); ?></option>
                <option value="title_desc"><?php echo esc_html__('Título (Z-A)', 'manga-admin-panel'); ?></option>
                <option value="progress"><?php echo esc_html__('Progresso', 'manga-admin-panel'); ?></option>
                <option value="rating"><?php echo esc_html__('Avaliação', 'manga-admin-panel'); ?></option>
                <option value="last_read"><?php echo esc_html__('Última leitura', 'manga-admin-panel'); ?></option>
            </select>
        </div>
    </div>
    
    <div class="manga-profile-list-container">
        <?php if (empty($user_manga_list)) : ?>
            <div class="manga-empty-state">
                <div class="manga-empty-icon"><i class="fas fa-books"></i></div>
                <div class="manga-empty-text"><?php echo esc_html__('Biblioteca vazia. Comece adicionando mangás à sua lista!', 'manga-admin-panel'); ?></div>
                <a href="<?php echo esc_url(home_url()); ?>" class="manga-btn manga-btn-primary"><?php echo esc_html__('Explorar Mangás', 'manga-admin-panel'); ?></a>
            </div>
        <?php else : ?>
            <div class="manga-profile-list">
                <?php foreach ($user_manga_list as $manga) : 
                    // Status color classes
                    $status_class = 'status-' . $manga['status'];
                    
                    // Progress percentage
                    $progress_percent = $manga['total_chapters'] > 0 ? round(($manga['progress'] / $manga['total_chapters']) * 100) : 0;
                ?>
                    <div class="manga-profile-item <?php echo esc_attr($status_class); ?>" data-status="<?php echo esc_attr($manga['status']); ?>" data-title="<?php echo esc_attr(strtolower($manga['title'])); ?>" data-rating="<?php echo esc_attr($manga['rating']); ?>" data-progress="<?php echo esc_attr($progress_percent); ?>">
                        <div class="manga-profile-item-cover">
                            <img src="<?php echo esc_url($manga['thumbnail']); ?>" alt="<?php echo esc_attr($manga['title']); ?>">
                            <div class="manga-profile-item-status"><?php echo esc_html($reading_statuses[$manga['status']]); ?></div>
                        </div>
                        
                        <div class="manga-profile-item-details">
                            <h3 class="manga-profile-item-title"><?php echo esc_html($manga['title']); ?></h3>
                            
                            <div class="manga-profile-item-progress">
                                <div class="manga-profile-progress-text">
                                    <?php 
                                    if ($manga['status'] === 'plan_to_read') {
                                        echo esc_html__('Ainda não iniciado', 'manga-admin-panel');
                                    } else {
                                        echo sprintf(esc_html__('%1$d/%2$d capítulos (%3$d%%)', 'manga-admin-panel'), 
                                            $manga['progress'], 
                                            $manga['total_chapters'],
                                            $progress_percent
                                        );
                                    }
                                    ?>
                                </div>
                                <div class="manga-profile-progress-bar">
                                    <div class="manga-profile-progress-fill <?php echo esc_attr($status_class); ?>" style="width: <?php echo esc_attr($progress_percent); ?>%"></div>
                                </div>
                            </div>
                            
                            <?php if ($manga['rating'] > 0) : ?>
                                <div class="manga-profile-item-rating">
                                    <?php
                                    // Exibir estrelas
                                    $rating = $manga['rating'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= floor($rating)) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($i - 0.5 <= $rating) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                    <span class="manga-profile-rating-value"><?php echo esc_html($manga['rating']); ?>/5</span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($manga['last_read']) : ?>
                                <div class="manga-profile-last-read">
                                    <i class="far fa-clock"></i> 
                                    <?php 
                                    $last_read_date = new DateTime($manga['last_read']);
                                    echo sprintf(
                                        esc_html__('Última leitura: %s', 'manga-admin-panel'),
                                        $last_read_date->format('d/m/Y')
                                    ); 
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($is_current_user) : ?>
                        <div class="manga-profile-item-actions">
                            <!-- Status changer dropdown -->
                            <div class="manga-profile-status-change">
                                <select class="manga-profile-status-select" data-manga-id="<?php echo esc_attr($manga['id']); ?>">
                                    <?php foreach ($reading_statuses as $status_key => $status_label) : ?>
                                        <option value="<?php echo esc_attr($status_key); ?>" <?php selected($manga['status'], $status_key); ?>>
                                            <?php echo esc_html($status_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Update progress -->
                            <div class="manga-profile-progress-update">
                                <button class="manga-btn manga-btn-sm manga-btn-primary manga-update-progress-btn" data-manga-id="<?php echo esc_attr($manga['id']); ?>" data-progress="<?php echo esc_attr($manga['progress']); ?>" data-total="<?php echo esc_attr($manga['total_chapters']); ?>">
                                    <i class="fas fa-bookmark"></i> <?php echo esc_html__('Atualizar', 'manga-admin-panel'); ?>
                                </button>
                            </div>
                            
                            <!-- Continue reading -->
                            <?php if ($manga['status'] !== 'plan_to_read' && $manga['progress'] < $manga['total_chapters']) : ?>
                                <div class="manga-profile-continue">
                                    <a href="#" class="manga-btn manga-btn-sm manga-btn-accent">
                                        <i class="fas fa-book-reader"></i> <?php echo esc_html__('Continuar', 'manga-admin-panel'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Remove from list -->
                            <div class="manga-profile-remove">
                                <button class="manga-btn manga-btn-sm manga-btn-danger manga-remove-btn" data-manga-id="<?php echo esc_attr($manga['id']); ?>">
                                    <i class="fas fa-trash"></i> <?php echo esc_html__('Remover', 'manga-admin-panel'); ?>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($is_current_user) : ?>
<!-- Modal para edição de perfil -->
<div class="manga-modal" id="profile-edit-modal">
    <div class="manga-modal-content">
        <div class="manga-modal-header">
            <h3><?php echo esc_html__('Editar Perfil', 'manga-admin-panel'); ?></h3>
            <button class="manga-modal-close">&times;</button>
        </div>
        <div class="manga-modal-body">
            <form id="manga-profile-edit-form">
                <div class="manga-form-group">
                    <label for="profile-display-name"><?php echo esc_html__('Nome de Exibição:', 'manga-admin-panel'); ?></label>
                    <input type="text" id="profile-display-name" class="manga-form-control" value="<?php echo esc_attr($user_display_name); ?>">
                </div>
                
                <div class="manga-form-group">
                    <label for="profile-bio"><?php echo esc_html__('Bio:', 'manga-admin-panel'); ?></label>
                    <textarea id="profile-bio" class="manga-form-control" rows="3"><?php echo esc_textarea(get_user_meta($profile_user_id, 'description', true)); ?></textarea>
                </div>
                
                <div class="manga-form-group">
                    <label><?php echo esc_html__('Avatar:', 'manga-admin-panel'); ?></label>
                    <div class="manga-avatar-selector">
                        <div class="manga-current-avatar">
                            <img src="<?php echo esc_url($user_avatar_url); ?>" alt="<?php echo esc_attr($user_display_name); ?>">
                        </div>
                        <p class="manga-avatar-info"><?php echo esc_html__('O avatar é gerenciado através do Gravatar, vinculado ao seu e-mail.', 'manga-admin-panel'); ?></p>
                        <a href="https://gravatar.com/" target="_blank" class="manga-btn manga-btn-sm manga-btn-secondary">
                            <i class="fas fa-external-link-alt"></i> <?php echo esc_html__('Alterar no Gravatar', 'manga-admin-panel'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="manga-form-group">
                    <label><?php echo esc_html__('Configurações de Privacidade:', 'manga-admin-panel'); ?></label>
                    <div class="manga-checkbox-group">
                        <label>
                            <input type="checkbox" id="profile-public-library" <?php checked(get_user_meta($profile_user_id, 'public_manga_library', true), 'yes'); ?>>
                            <?php echo esc_html__('Tornar minha biblioteca de mangás pública', 'manga-admin-panel'); ?>
                        </label>
                    </div>
                    <div class="manga-checkbox-group">
                        <label>
                            <input type="checkbox" id="profile-show-reading-status" <?php checked(get_user_meta($profile_user_id, 'show_reading_status', true), 'yes'); ?>>
                            <?php echo esc_html__('Mostrar meu status de leitura para outros usuários', 'manga-admin-panel'); ?>
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <div class="manga-modal-footer">
            <button class="manga-btn manga-btn-secondary manga-modal-cancel"><?php echo esc_html__('Cancelar', 'manga-admin-panel'); ?></button>
            <button class="manga-btn manga-btn-primary" id="profile-save-changes"><?php echo esc_html__('Salvar Alterações', 'manga-admin-panel'); ?></button>
        </div>
    </div>
</div>

<!-- Modal para atualização de progresso -->
<div class="manga-modal" id="manga-progress-modal">
    <div class="manga-modal-content">
        <div class="manga-modal-header">
            <h3><?php echo esc_html__('Atualizar Progresso', 'manga-admin-panel'); ?></h3>
            <button class="manga-modal-close">&times;</button>
        </div>
        <div class="manga-modal-body">
            <div class="manga-form-group">
                <label for="manga-progress-input"><?php echo esc_html__('Capítulo atual:', 'manga-admin-panel'); ?></label>
                <input type="number" id="manga-progress-input" class="manga-form-control" min="0" value="0">
                <div class="manga-progress-total"><?php echo esc_html__('de', 'manga-admin-panel'); ?> <span id="manga-progress-total">0</span> <?php echo esc_html__('capítulos', 'manga-admin-panel'); ?></div>
            </div>
            
            <div class="manga-form-group">
                <label for="manga-rating-input"><?php echo esc_html__('Avaliação:', 'manga-admin-panel'); ?></label>
                <div class="manga-rating-stars">
                    <i class="far fa-star" data-rating="1"></i>
                    <i class="far fa-star" data-rating="2"></i>
                    <i class="far fa-star" data-rating="3"></i>
                    <i class="far fa-star" data-rating="4"></i>
                    <i class="far fa-star" data-rating="5"></i>
                </div>
                <input type="hidden" id="manga-rating-input" value="0">
            </div>
        </div>
        <div class="manga-modal-footer">
            <button class="manga-btn manga-btn-secondary manga-modal-cancel"><?php echo esc_html__('Cancelar', 'manga-admin-panel'); ?></button>
            <button class="manga-btn manga-btn-primary" id="manga-progress-save"><?php echo esc_html__('Salvar', 'manga-admin-panel'); ?></button>
        </div>
    </div>
</div>

<!-- Modal para confirmar remoção -->
<div class="manga-modal" id="manga-remove-modal">
    <div class="manga-modal-content">
        <div class="manga-modal-header">
            <h3><?php echo esc_html__('Remover Mangá', 'manga-admin-panel'); ?></h3>
            <button class="manga-modal-close">&times;</button>
        </div>
        <div class="manga-modal-body">
            <p><?php echo esc_html__('Tem certeza que deseja remover este mangá da sua lista? Esta ação não pode ser desfeita.', 'manga-admin-panel'); ?></p>
        </div>
        <div class="manga-modal-footer">
            <button class="manga-btn manga-btn-secondary manga-modal-cancel"><?php echo esc_html__('Cancelar', 'manga-admin-panel'); ?></button>
            <button class="manga-btn manga-btn-danger" id="manga-remove-confirm"><?php echo esc_html__('Remover', 'manga-admin-panel'); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Variáveis
    let currentTab = 'all';
    let currentSort = 'title_asc';
    let selectedMangaId = 0;
    let currentRating = 0;
    
    // Inicialização
    initializeRatingStars();
    
    // Editar perfil
    $('#edit-profile-button').on('click', function() {
        $('#profile-edit-modal').addClass('active');
    });
    
    // Salvar alterações de perfil
    $('#profile-save-changes').on('click', function() {
        const displayName = $('#profile-display-name').val();
        const bio = $('#profile-bio').val();
        const publicLibrary = $('#profile-public-library').is(':checked') ? 'yes' : 'no';
        const showReadingStatus = $('#profile-show-reading-status').is(':checked') ? 'yes' : 'no';
        
        // Simular salvamento
        setTimeout(function() {
            // Atualizar nome de exibição na interface
            $('.manga-profile-name').text(displayName);
            
            // Fechar modal
            $('#profile-edit-modal').removeClass('active');
            
            // Exibir notificação
            showNotification('<?php echo esc_js(__('Perfil atualizado com sucesso!', 'manga-admin-panel')); ?>', 'success');
        }, 500);
    });
    
    // Filtrar por abas
    $('.manga-profile-tab').on('click', function() {
        currentTab = $(this).data('target');
        
        // Atualizar abas ativas
        $('.manga-profile-tab').removeClass('active');
        $(this).addClass('active');
        
        // Filtrar os itens
        filterItems();
    });
    
    // Busca
    $('#manga-profile-search-input').on('input', function() {
        filterItems();
    });
    
    // Ordenação
    $('#manga-profile-sort').on('change', function() {
        currentSort = $(this).val();
        sortItems();
    });
    
    // Alteração de status
    $('.manga-profile-status-select').on('change', function() {
        const mangaId = $(this).data('manga-id');
        const newStatus = $(this).val();
        const itemElement = $(this).closest('.manga-profile-item');
        
        // Remover classes de status anteriores
        itemElement.removeClass(function(index, className) {
            return (className.match(/(^|\s)status-\S+/g) || []).join(' ');
        });
        
        // Adicionar nova classe de status
        itemElement.addClass('status-' + newStatus);
        
        // Atualizar atributo de status
        itemElement.attr('data-status', newStatus);
        
        // Atualizar texto do status
        const statusLabels = {
            'reading': '<?php echo esc_js(__('Lendo', 'manga-admin-panel')); ?>',
            'completed': '<?php echo esc_js(__('Concluído', 'manga-admin-panel')); ?>',
            'on_hold': '<?php echo esc_js(__('Em espera', 'manga-admin-panel')); ?>',
            'dropped': '<?php echo esc_js(__('Abandonado', 'manga-admin-panel')); ?>',
            'plan_to_read': '<?php echo esc_js(__('Planejo ler', 'manga-admin-panel')); ?>'
        };
        
        itemElement.find('.manga-profile-item-status').text(statusLabels[newStatus]);
        
        // Simular AJAX para salvar status
        setTimeout(function() {
            showNotification('<?php echo esc_js(__('Status atualizado com sucesso!', 'manga-admin-panel')); ?>', 'success');
            
            // Atualizar contagens
            updateCounts();
            
            // Filtrar novamente para mostrar/esconder o item se necessário
            filterItems();
        }, 300);
    });
    
    // Botão de atualizar progresso
    $('.manga-update-progress-btn').on('click', function() {
        selectedMangaId = $(this).data('manga-id');
        const progress = $(this).data('progress');
        const total = $(this).data('total');
        
        // Preencher o modal
        $('#manga-progress-input').val(progress).attr('max', total);
        $('#manga-progress-total').text(total);
        
        // Reset rating stars
        resetRatingStars();
        
        // Obter rating atual
        const mangaItem = $(this).closest('.manga-profile-item');
        const currentRating = parseFloat(mangaItem.attr('data-rating')) || 0;
        $('#manga-rating-input').val(currentRating);
        
        // Atualizar estrelas de avaliação
        updateRatingStars(currentRating);
        
        // Mostrar o modal
        $('#manga-progress-modal').addClass('active');
    });
    
    // Botão de remover
    $('.manga-remove-btn').on('click', function() {
        selectedMangaId = $(this).data('manga-id');
        
        // Mostrar modal de confirmação
        $('#manga-remove-modal').addClass('active');
    });
    
    // Fechar modais
    $('.manga-modal-close, .manga-modal-cancel').on('click', function() {
        $('.manga-modal').removeClass('active');
    });
    
    // Salvar progresso
    $('#manga-progress-save').on('click', function() {
        const newProgress = parseInt($('#manga-progress-input').val());
        const newRating = parseInt($('#manga-rating-input').val());
        
        // Simular AJAX para salvar progresso
        setTimeout(function() {
            const itemElement = $(`.manga-update-progress-btn[data-manga-id="${selectedMangaId}"]`).closest('.manga-profile-item');
            const totalChapters = parseInt($(`.manga-update-progress-btn[data-manga-id="${selectedMangaId}"]`).data('total'));
            
            // Atualizar botão com novo progresso
            $(`.manga-update-progress-btn[data-manga-id="${selectedMangaId}"]`).data('progress', newProgress);
            
            // Atualizar barra de progresso
            const progressPercent = Math.round((newProgress / totalChapters) * 100);
            itemElement.find('.manga-profile-progress-fill').css('width', progressPercent + '%');
            
            // Atualizar texto de progresso
            itemElement.find('.manga-profile-progress-text').text(
                `${newProgress}/${totalChapters} capítulos (${progressPercent}%)`
            );
            
            // Atualizar atributo para ordenação
            itemElement.attr('data-progress', progressPercent);
            
            // Atualizar classificação se foi alterada
            if (newRating > 0) {
                // Atualizar atributo para ordenação
                itemElement.attr('data-rating', newRating);
                
                // Atualizar exibição de estrelas
                let starsHTML = '';
                for (let i = 1; i <= 5; i++) {
                    if (i <= Math.floor(newRating)) {
                        starsHTML += '<i class="fas fa-star"></i>';
                    } else if (i - 0.5 <= newRating) {
                        starsHTML += '<i class="fas fa-star-half-alt"></i>';
                    } else {
                        starsHTML += '<i class="far fa-star"></i>';
                    }
                }
                
                starsHTML += `<span class="manga-profile-rating-value">${newRating}/5</span>`;
                
                // Atualizar ou criar elemento de classificação
                if (itemElement.find('.manga-profile-item-rating').length) {
                    itemElement.find('.manga-profile-item-rating').html(starsHTML);
                } else {
                    $('<div class="manga-profile-item-rating">' + starsHTML + '</div>').insertAfter(
                        itemElement.find('.manga-profile-item-progress')
                    );
                }
            }
            
            // Atualizar última leitura
            const today = new Date();
            const formattedDate = `${today.getDate().toString().padStart(2, '0')}/${(today.getMonth() + 1).toString().padStart(2, '0')}/${today.getFullYear()}`;
            
            if (itemElement.find('.manga-profile-last-read').length) {
                itemElement.find('.manga-profile-last-read').html(
                    `<i class="far fa-clock"></i> <?php echo esc_js(__('Última leitura:', 'manga-admin-panel')); ?> ${formattedDate}`
                );
            } else {
                $('<div class="manga-profile-last-read"><i class="far fa-clock"></i> <?php echo esc_js(__('Última leitura:', 'manga-admin-panel')); ?> ' + formattedDate + '</div>').appendTo(
                    itemElement.find('.manga-profile-item-details')
                );
            }
            
            // Se progresso = total, perguntar se deseja marcar como concluído
            if (newProgress >= totalChapters && itemElement.data('status') !== 'completed') {
                if (confirm('<?php echo esc_js(__('Você chegou ao último capítulo! Deseja marcar este mangá como concluído?', 'manga-admin-panel')); ?>')) {
                    // Mudar para status concluído
                    itemElement.find('.manga-profile-status-select').val('completed').trigger('change');
                }
            }
            
            // Fechar o modal
            $('#manga-progress-modal').removeClass('active');
            
            // Mostrar notificação
            showNotification('<?php echo esc_js(__('Progresso atualizado com sucesso!', 'manga-admin-panel')); ?>', 'success');
            
            // Reordenar se necessário
            if (currentSort === 'progress' || currentSort === 'rating') {
                sortItems();
            }
        }, 500);
    });
    
    // Confirmar remoção
    $('#manga-remove-confirm').on('click', function() {
        // Simular AJAX para remover mangá
        setTimeout(function() {
            $(`.manga-remove-btn[data-manga-id="${selectedMangaId}"]`).closest('.manga-profile-item').fadeOut(300, function() {
                $(this).remove();
                
                // Atualizar contagens
                updateCounts();
                
                // Verificar se a lista está vazia
                if ($('.manga-profile-item:visible').length === 0) {
                    $('.manga-profile-list').html(
                        `<div class="manga-empty-state">
                            <div class="manga-empty-icon"><i class="fas fa-books"></i></div>
                            <div class="manga-empty-text"><?php echo esc_js(__('Sua biblioteca está vazia. Comece adicionando mangás à sua lista!', 'manga-admin-panel')); ?></div>
                            <a href="<?php echo esc_url(home_url()); ?>" class="manga-btn manga-btn-primary"><?php echo esc_js(__('Explorar Mangás', 'manga-admin-panel')); ?></a>
                        </div>`
                    );
                }
            });
            
            // Fechar o modal
            $('#manga-remove-modal').removeClass('active');
            
            // Mostrar notificação
            showNotification('<?php echo esc_js(__('Mangá removido da sua lista com sucesso!', 'manga-admin-panel')); ?>', 'success');
        }, 500);
    });
    
    // Sistema de avaliação com estrelas
    function initializeRatingStars() {
        $('.manga-rating-stars i').on('click', function() {
            const rating = $(this).data('rating');
            $('#manga-rating-input').val(rating);
            updateRatingStars(rating);
        });
        
        // Efeito hover
        $('.manga-rating-stars i').on('mouseenter', function() {
            const rating = $(this).data('rating');
            
            $('.manga-rating-stars i').each(function() {
                if ($(this).data('rating') <= rating) {
                    $(this).removeClass('far').addClass('fas');
                } else {
                    $(this).removeClass('fas').addClass('far');
                }
            });
        });
        
        $('.manga-rating-stars').on('mouseleave', function() {
            const currentRating = $('#manga-rating-input').val();
            updateRatingStars(currentRating);
        });
    }
    
    function updateRatingStars(rating) {
        $('.manga-rating-stars i').removeClass('fas').addClass('far');
        $('.manga-rating-stars i').each(function() {
            if ($(this).data('rating') <= rating) {
                $(this).removeClass('far').addClass('fas');
            }
        });
    }
    
    function resetRatingStars() {
        $('.manga-rating-stars i').removeClass('fas').addClass('far');
        $('#manga-rating-input').val(0);
    }
    
    // Funções de filtro e ordenação
    function filterItems() {
        const searchText = $('#manga-profile-search-input').val().toLowerCase();
        
        $('.manga-profile-item').each(function() {
            const title = $(this).data('title');
            const status = $(this).data('status');
            
            // Verificar status
            const statusMatch = currentTab === 'all' || status === currentTab;
            
            // Verificar texto de busca
            const searchMatch = title.includes(searchText);
            
            // Mostrar/Esconder baseado nos filtros
            if (statusMatch && searchMatch) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Ordenar itens visíveis
        sortItems();
    }
    
    function sortItems() {
        const items = $('.manga-profile-item:visible').get();
        
        items.sort(function(a, b) {
            switch (currentSort) {
                case 'title_asc':
                    return $(a).data('title').localeCompare($(b).data('title'));
                case 'title_desc':
                    return $(b).data('title').localeCompare($(a).data('title'));
                case 'progress':
                    return $(b).data('progress') - $(a).data('progress');
                case 'rating':
                    return $(b).data('rating') - $(a).data('rating');
                case 'last_read':
                    // Implementação simplificada, sem considerar datas reais
                    return 0;
                default:
                    return 0;
            }
        });
        
        // Anexar itens ordenados de volta à lista
        $.each(items, function(i, item) {
            $('.manga-profile-list').append(item);
        });
    }
    
    function updateCounts() {
        const stats = {
            all: 0,
            reading: 0,
            completed: 0,
            on_hold: 0,
            dropped: 0,
            plan_to_read: 0
        };
        
        // Contar itens por status
        $('.manga-profile-item').each(function() {
            const status = $(this).data('status');
            stats.all++;
            stats[status]++;
        });
        
        // Atualizar texto das abas
        $('.manga-profile-tab').each(function() {
            const tabTarget = $(this).data('target');
            $(this).text(`${$(this).text().split('(')[0]} (${stats[tabTarget]})`);
        });
    }
    
    function showNotification(message, type = 'info') {
        // Remover notificações existentes
        $('.manga-notification').remove();
        
        // Criar elemento de notificação
        const notification = $('<div class="manga-notification ' + type + '">' + message + '</div>');
        
        // Adicionar ao corpo
        $('body').append(notification);
        
        // Animar entrada
        setTimeout(function() {
            notification.addClass('active');
        }, 10);
        
        // Animar saída e remover
        setTimeout(function() {
            notification.removeClass('active');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 4000);
    }
});
</script>
<?php endif; ?>