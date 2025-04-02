<?php
/**
 * Template para o dashboard administrativo
 * Fornece interface completa para gerenciamento de mangás
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

// Verificar se o usuário tem permissão para acessar o painel
if (!manga_admin_panel_has_access()) {
    ?>
    <div class="manga-alert manga-alert-danger">
        <i class="fas fa-exclamation-triangle"></i> <?php echo esc_html__('Você não tem permissão para acessar este painel.', 'manga-admin-panel'); ?>
    </div>
    
    <?php 
    echo manga_admin_login_form(__('Faça login com uma conta que tenha permissões administrativas para acessar o painel.', 'manga-admin-panel'));
    return;
}

// Obter seção atual (padrão: dashboard)
$current_section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'dashboard';

// Obter estatísticas para o dashboard
function get_manga_admin_stats() {
    // Em ambiente real, esta função consultaria o banco de dados
    // Para desenvolvimento, usamos dados de exemplo
    return array(
        'total_manga' => 125,
        'total_chapters' => 3421,
        'recent_uploads' => 17,
        'pending_chapters' => 5,
        'total_views' => 182654,
        'total_users' => 4250,
    );
}

$manga_stats = get_manga_admin_stats();

// Obter mangás recentes
function get_recent_manga($limit = 5) {
    // Em ambiente real, esta função consultaria o banco de dados
    // Para desenvolvimento, usamos dados de exemplo
    $mangas = array(
        array(
            'id' => 1,
            'title' => 'One Piece',
            'thumbnail' => 'https://via.placeholder.com/200x300.png?text=One+Piece',
            'chapters' => 1050,
            'views' => 12500,
            'date' => '2023-12-15',
        ),
        array(
            'id' => 2,
            'title' => 'Berserk',
            'thumbnail' => 'https://via.placeholder.com/200x300.png?text=Berserk',
            'chapters' => 365,
            'views' => 8400,
            'date' => '2023-12-10',
        ),
        array(
            'id' => 3,
            'title' => 'Naruto',
            'thumbnail' => 'https://via.placeholder.com/200x300.png?text=Naruto',
            'chapters' => 700,
            'views' => 10800,
            'date' => '2023-12-05',
        ),
        array(
            'id' => 4,
            'title' => 'Attack on Titan',
            'thumbnail' => 'https://via.placeholder.com/200x300.png?text=Attack+on+Titan',
            'chapters' => 139,
            'views' => 9200,
            'date' => '2023-12-01',
        ),
        array(
            'id' => 5,
            'title' => 'Dragon Ball',
            'thumbnail' => 'https://via.placeholder.com/200x300.png?text=Dragon+Ball',
            'chapters' => 519,
            'views' => 11000,
            'date' => '2023-11-25',
        ),
    );
    
    return $mangas;
}

// Obter capítulos agendados
function get_scheduled_chapters($limit = 5) {
    // Em ambiente real, esta função consultaria o banco de dados
    // Para desenvolvimento, usamos dados de exemplo
    $scheduled_chapters = array(
        array(
            'id' => 101,
            'manga_id' => 1,
            'manga_title' => 'One Piece',
            'chapter_number' => 1051,
            'scheduled_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
        ),
        array(
            'id' => 102,
            'manga_id' => 2,
            'manga_title' => 'Berserk',
            'chapter_number' => 366,
            'scheduled_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
        ),
        array(
            'id' => 103,
            'manga_id' => 3,
            'manga_title' => 'Naruto',
            'chapter_number' => 701,
            'scheduled_date' => date('Y-m-d H:i:s', strtotime('+3 days')),
        ),
        array(
            'id' => 104,
            'manga_id' => 4,
            'manga_title' => 'Attack on Titan',
            'chapter_number' => 140,
            'scheduled_date' => date('Y-m-d H:i:s', strtotime('+4 days')),
        ),
        array(
            'id' => 105,
            'manga_id' => 5,
            'manga_title' => 'Dragon Ball',
            'chapter_number' => 520,
            'scheduled_date' => date('Y-m-d H:i:s', strtotime('+5 days')),
        ),
    );
    
    return $scheduled_chapters;
}

// Formatar número grande com sufixo K/M
function format_number_with_suffix($number) {
    if ($number >= 1000000) {
        return number_format($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return number_format($number / 1000, 1) . 'K';
    } else {
        return number_format($number);
    }
}
?>

<div class="manga-admin-container">
    <!-- Cabeçalho do Painel -->
    <div class="manga-admin-header">
        <h1 class="manga-admin-title">
            <i class="fas fa-columns"></i> <?php echo esc_html__('Painel de Administração de Mangá', 'manga-admin-panel'); ?>
        </h1>
        
        <div class="manga-admin-actions">
            <a href="<?php echo esc_url(add_query_arg('section', 'create', remove_query_arg('id'))); ?>" class="manga-btn manga-btn-primary">
                <i class="fas fa-plus"></i> <?php echo esc_html__('Adicionar Novo Mangá', 'manga-admin-panel'); ?>
            </a>
            
            <a href="<?php echo esc_url(home_url()); ?>" class="manga-btn manga-btn-secondary">
                <i class="fas fa-home"></i> <?php echo esc_html__('Ir para o Site', 'manga-admin-panel'); ?>
            </a>
        </div>
    </div>
    
    <!-- Menu de Navegação -->
    <div class="manga-admin-nav">
        <a href="<?php echo esc_url(remove_query_arg('section')); ?>" class="manga-admin-nav-item <?php echo $current_section === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> <?php echo esc_html__('Dashboard', 'manga-admin-panel'); ?>
        </a>
        
        <a href="<?php echo esc_url(add_query_arg('section', 'mangas')); ?>" class="manga-admin-nav-item <?php echo $current_section === 'mangas' ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> <?php echo esc_html__('Gerenciar Mangás', 'manga-admin-panel'); ?>
        </a>
        
        <a href="<?php echo esc_url(add_query_arg('section', 'chapters')); ?>" class="manga-admin-nav-item <?php echo $current_section === 'chapters' ? 'active' : ''; ?>">
            <i class="fas fa-list-ol"></i> <?php echo esc_html__('Gerenciar Capítulos', 'manga-admin-panel'); ?>
        </a>
        
        <a href="<?php echo esc_url(add_query_arg('section', 'create')); ?>" class="manga-admin-nav-item <?php echo $current_section === 'create' ? 'active' : ''; ?>">
            <i class="fas fa-plus-circle"></i> <?php echo esc_html__('Adicionar Mangá', 'manga-admin-panel'); ?>
        </a>
        
        <a href="<?php echo esc_url(add_query_arg('section', 'schedule')); ?>" class="manga-admin-nav-item <?php echo $current_section === 'schedule' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> <?php echo esc_html__('Capítulos Agendados', 'manga-admin-panel'); ?>
        </a>
        
        <a href="<?php echo esc_url(add_query_arg('section', 'settings')); ?>" class="manga-admin-nav-item <?php echo $current_section === 'settings' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> <?php echo esc_html__('Configurações', 'manga-admin-panel'); ?>
        </a>
    </div>
    
    <!-- Conteúdo Principal -->
    <div class="manga-admin-content">
        <?php if ($current_section === 'dashboard') : ?>
            
            <!-- Dashboard -->
            <div class="manga-admin-dashboard">
                <!-- Cards de Estatísticas -->
                <div class="manga-admin-stats">
                    <div class="manga-admin-stat-card">
                        <div class="manga-admin-stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="manga-admin-stat-info">
                            <div class="manga-admin-stat-number"><?php echo esc_html(number_format($manga_stats['total_manga'])); ?></div>
                            <div class="manga-admin-stat-label"><?php echo esc_html__('Total de Mangás', 'manga-admin-panel'); ?></div>
                        </div>
                    </div>
                    
                    <div class="manga-admin-stat-card">
                        <div class="manga-admin-stat-icon">
                            <i class="fas fa-list-ol"></i>
                        </div>
                        <div class="manga-admin-stat-info">
                            <div class="manga-admin-stat-number"><?php echo esc_html(number_format($manga_stats['total_chapters'])); ?></div>
                            <div class="manga-admin-stat-label"><?php echo esc_html__('Total de Capítulos', 'manga-admin-panel'); ?></div>
                        </div>
                    </div>
                    
                    <div class="manga-admin-stat-card">
                        <div class="manga-admin-stat-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="manga-admin-stat-info">
                            <div class="manga-admin-stat-number"><?php echo esc_html(format_number_with_suffix($manga_stats['total_views'])); ?></div>
                            <div class="manga-admin-stat-label"><?php echo esc_html__('Visualizações Totais', 'manga-admin-panel'); ?></div>
                        </div>
                    </div>
                    
                    <div class="manga-admin-stat-card">
                        <div class="manga-admin-stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="manga-admin-stat-info">
                            <div class="manga-admin-stat-number"><?php echo esc_html(format_number_with_suffix($manga_stats['total_users'])); ?></div>
                            <div class="manga-admin-stat-label"><?php echo esc_html__('Usuários Registrados', 'manga-admin-panel'); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Seção de Atividade Recente -->
                <div class="manga-admin-row">
                    <!-- Mangás Recentes -->
                    <div class="manga-admin-column">
                        <div class="manga-admin-panel">
                            <div class="manga-admin-panel-header">
                                <h2 class="manga-admin-panel-title">
                                    <i class="fas fa-clock"></i> <?php echo esc_html__('Mangás Recentes', 'manga-admin-panel'); ?>
                                </h2>
                                <a href="<?php echo esc_url(add_query_arg('section', 'mangas')); ?>" class="manga-admin-panel-action">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                            
                            <div class="manga-admin-panel-content">
                                <table class="manga-admin-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo esc_html__('Mangá', 'manga-admin-panel'); ?></th>
                                            <th><?php echo esc_html__('Capítulos', 'manga-admin-panel'); ?></th>
                                            <th><?php echo esc_html__('Visualizações', 'manga-admin-panel'); ?></th>
                                            <th><?php echo esc_html__('Ações', 'manga-admin-panel'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (get_recent_manga() as $manga) : ?>
                                            <tr>
                                                <td>
                                                    <div class="manga-admin-item">
                                                        <div class="manga-admin-item-thumb">
                                                            <img src="<?php echo esc_url($manga['thumbnail']); ?>" alt="<?php echo esc_attr($manga['title']); ?>">
                                                        </div>
                                                        <div class="manga-admin-item-title">
                                                            <?php echo esc_html($manga['title']); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo esc_html(number_format($manga['chapters'])); ?></td>
                                                <td><?php echo esc_html(format_number_with_suffix($manga['views'])); ?></td>
                                                <td>
                                                    <div class="manga-admin-actions-compact">
                                                        <a href="<?php echo esc_url(add_query_arg(array('section' => 'edit', 'id' => $manga['id']))); ?>" class="manga-action-btn" title="<?php echo esc_attr__('Editar', 'manga-admin-panel'); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?php echo esc_url(add_query_arg(array('section' => 'chapters', 'manga_id' => $manga['id']))); ?>" class="manga-action-btn" title="<?php echo esc_attr__('Gerenciar Capítulos', 'manga-admin-panel'); ?>">
                                                            <i class="fas fa-list"></i>
                                                        </a>
                                                        <a href="#" class="manga-action-btn manga-action-btn-danger manga-delete-btn" data-id="<?php echo esc_attr($manga['id']); ?>" title="<?php echo esc_attr__('Excluir', 'manga-admin-panel'); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Capítulos Agendados -->
                    <div class="manga-admin-column">
                        <div class="manga-admin-panel">
                            <div class="manga-admin-panel-header">
                                <h2 class="manga-admin-panel-title">
                                    <i class="fas fa-calendar-alt"></i> <?php echo esc_html__('Capítulos Agendados', 'manga-admin-panel'); ?>
                                </h2>
                                <a href="<?php echo esc_url(add_query_arg('section', 'schedule')); ?>" class="manga-admin-panel-action">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                            
                            <div class="manga-admin-panel-content">
                                <table class="manga-admin-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo esc_html__('Mangá', 'manga-admin-panel'); ?></th>
                                            <th><?php echo esc_html__('Capítulo', 'manga-admin-panel'); ?></th>
                                            <th><?php echo esc_html__('Data Agendada', 'manga-admin-panel'); ?></th>
                                            <th><?php echo esc_html__('Ações', 'manga-admin-panel'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (get_scheduled_chapters() as $chapter) : 
                                            $scheduled_date = new DateTime($chapter['scheduled_date']);
                                            $now = new DateTime();
                                            $time_diff = $scheduled_date->diff($now);
                                            
                                            // Verificar se a data agendada já passou
                                            $is_past = $scheduled_date < $now;
                                            
                                            // Formatar tempo restante
                                            if ($is_past) {
                                                $time_remaining = __('Atrasado', 'manga-admin-panel');
                                            } elseif ($time_diff->days > 0) {
                                                $time_remaining = sprintf(_n('Em %d dia', 'Em %d dias', $time_diff->days, 'manga-admin-panel'), $time_diff->days);
                                            } elseif ($time_diff->h > 0) {
                                                $time_remaining = sprintf(_n('Em %d hora', 'Em %d horas', $time_diff->h, 'manga-admin-panel'), $time_diff->h);
                                            } else {
                                                $time_remaining = sprintf(_n('Em %d minuto', 'Em %d minutos', $time_diff->i, 'manga-admin-panel'), $time_diff->i);
                                            }
                                            
                                            // Classe baseada no status
                                            $status_class = $is_past ? 'manga-status-late' : 'manga-status-scheduled';
                                        ?>
                                            <tr>
                                                <td><?php echo esc_html($chapter['manga_title']); ?></td>
                                                <td><?php echo esc_html(sprintf(__('Capítulo %s', 'manga-admin-panel'), $chapter['chapter_number'])); ?></td>
                                                <td>
                                                    <span class="manga-schedule-status <?php echo esc_attr($status_class); ?>">
                                                        <?php echo esc_html($time_remaining); ?>
                                                    </span>
                                                    <div class="manga-schedule-date">
                                                        <?php echo esc_html($scheduled_date->format('d/m/Y H:i')); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="manga-admin-actions-compact">
                                                        <a href="<?php echo esc_url(add_query_arg(array('section' => 'edit-chapter', 'id' => $chapter['id']))); ?>" class="manga-action-btn" title="<?php echo esc_attr__('Editar', 'manga-admin-panel'); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="#" class="manga-action-btn manga-action-btn-accent manga-publish-now-btn" data-id="<?php echo esc_attr($chapter['id']); ?>" title="<?php echo esc_attr__('Publicar agora', 'manga-admin-panel'); ?>">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </a>
                                                        <a href="#" class="manga-action-btn manga-action-btn-danger manga-delete-chapter-btn" data-id="<?php echo esc_attr($chapter['id']); ?>" title="<?php echo esc_attr__('Excluir', 'manga-admin-panel'); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ações Rápidas -->
                <div class="manga-admin-quick-actions">
                    <h2 class="manga-admin-section-title"><?php echo esc_html__('Ações Rápidas', 'manga-admin-panel'); ?></h2>
                    
                    <div class="manga-admin-quick-action-grid">
                        <a href="<?php echo esc_url(add_query_arg('section', 'create')); ?>" class="manga-admin-quick-action-item">
                            <div class="manga-admin-quick-action-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="manga-admin-quick-action-label"><?php echo esc_html__('Novo Mangá', 'manga-admin-panel'); ?></div>
                        </a>
                        
                        <a href="<?php echo esc_url(add_query_arg('section', 'upload-chapter')); ?>" class="manga-admin-quick-action-item">
                            <div class="manga-admin-quick-action-icon">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div class="manga-admin-quick-action-label"><?php echo esc_html__('Enviar Capítulo', 'manga-admin-panel'); ?></div>
                        </a>
                        
                        <a href="<?php echo esc_url(add_query_arg('section', 'schedule')); ?>" class="manga-admin-quick-action-item">
                            <div class="manga-admin-quick-action-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div class="manga-admin-quick-action-label"><?php echo esc_html__('Agendar Capítulo', 'manga-admin-panel'); ?></div>
                        </a>
                        
                        <a href="<?php echo esc_url(add_query_arg('section', 'settings')); ?>" class="manga-admin-quick-action-item">
                            <div class="manga-admin-quick-action-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="manga-admin-quick-action-label"><?php echo esc_html__('Configurações', 'manga-admin-panel'); ?></div>
                        </a>
                    </div>
                </div>
            </div>
            
        <?php elseif ($current_section === 'create' || $current_section === 'edit') : ?>
            
            <!-- Criar/Editar Mangá -->
            <?php include MANGA_ADMIN_PANEL_PATH . 'templates/manga-create-edit.php'; ?>
            
        <?php elseif ($current_section === 'mangas') : ?>
            
            <!-- Gerenciar Mangás -->
            <div class="manga-admin-section-header">
                <h2 class="manga-admin-section-title">
                    <i class="fas fa-book"></i> <?php echo esc_html__('Gerenciar Mangás', 'manga-admin-panel'); ?>
                </h2>
                <a href="<?php echo esc_url(add_query_arg('section', 'create')); ?>" class="manga-btn manga-btn-primary">
                    <i class="fas fa-plus"></i> <?php echo esc_html__('Adicionar Novo', 'manga-admin-panel'); ?>
                </a>
            </div>
            
            <div class="manga-admin-filter-bar">
                <div class="manga-admin-search">
                    <input type="text" id="manga-search" placeholder="<?php echo esc_attr__('Buscar mangás...', 'manga-admin-panel'); ?>" class="manga-admin-search-input">
                    <button class="manga-admin-search-btn"><i class="fas fa-search"></i></button>
                </div>
                
                <div class="manga-admin-filters">
                    <select id="manga-filter-status" class="manga-admin-filter-select">
                        <option value=""><?php echo esc_html__('Todos os status', 'manga-admin-panel'); ?></option>
                        <option value="ongoing"><?php echo esc_html__('Em andamento', 'manga-admin-panel'); ?></option>
                        <option value="completed"><?php echo esc_html__('Concluído', 'manga-admin-panel'); ?></option>
                        <option value="hiatus"><?php echo esc_html__('Hiato', 'manga-admin-panel'); ?></option>
                        <option value="canceled"><?php echo esc_html__('Cancelado', 'manga-admin-panel'); ?></option>
                    </select>
                    
                    <select id="manga-filter-genre" class="manga-admin-filter-select">
                        <option value=""><?php echo esc_html__('Todos os gêneros', 'manga-admin-panel'); ?></option>
                        <option value="action"><?php echo esc_html__('Ação', 'manga-admin-panel'); ?></option>
                        <option value="adventure"><?php echo esc_html__('Aventura', 'manga-admin-panel'); ?></option>
                        <option value="comedy"><?php echo esc_html__('Comédia', 'manga-admin-panel'); ?></option>
                        <option value="drama"><?php echo esc_html__('Drama', 'manga-admin-panel'); ?></option>
                        <option value="fantasy"><?php echo esc_html__('Fantasia', 'manga-admin-panel'); ?></option>
                        <option value="horror"><?php echo esc_html__('Horror', 'manga-admin-panel'); ?></option>
                        <option value="romance"><?php echo esc_html__('Romance', 'manga-admin-panel'); ?></option>
                    </select>
                    
                    <select id="manga-sort-by" class="manga-admin-filter-select">
                        <option value="date-desc"><?php echo esc_html__('Mais recentes', 'manga-admin-panel'); ?></option>
                        <option value="date-asc"><?php echo esc_html__('Mais antigos', 'manga-admin-panel'); ?></option>
                        <option value="title-asc"><?php echo esc_html__('Título (A-Z)', 'manga-admin-panel'); ?></option>
                        <option value="title-desc"><?php echo esc_html__('Título (Z-A)', 'manga-admin-panel'); ?></option>
                        <option value="views-desc"><?php echo esc_html__('Mais vistos', 'manga-admin-panel'); ?></option>
                        <option value="chapters-desc"><?php echo esc_html__('Mais capítulos', 'manga-admin-panel'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="manga-admin-table-container">
                <table class="manga-admin-table manga-admin-mangas-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Mangá', 'manga-admin-panel'); ?></th>
                            <th><?php echo esc_html__('Status', 'manga-admin-panel'); ?></th>
                            <th><?php echo esc_html__('Capítulos', 'manga-admin-panel'); ?></th>
                            <th><?php echo esc_html__('Visualizações', 'manga-admin-panel'); ?></th>
                            <th><?php echo esc_html__('Última Atualização', 'manga-admin-panel'); ?></th>
                            <th><?php echo esc_html__('Ações', 'manga-admin-panel'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (get_recent_manga(15) as $index => $manga) : 
                            // Apenas para simular diferentes status
                            $statuses = array('ongoing', 'completed', 'hiatus', 'canceled');
                            $status = $statuses[$index % count($statuses)];
                            $status_labels = array(
                                'ongoing' => __('Em andamento', 'manga-admin-panel'),
                                'completed' => __('Concluído', 'manga-admin-panel'),
                                'hiatus' => __('Hiato', 'manga-admin-panel'),
                                'canceled' => __('Cancelado', 'manga-admin-panel'),
                            );
                            
                            // Classe CSS para o status
                            $status_class = 'manga-status-' . $status;
                            
                            // Data da última atualização
                            $update_date = new DateTime($manga['date']);
                        ?>
                            <tr>
                                <td>
                                    <div class="manga-admin-item">
                                        <div class="manga-admin-item-thumb">
                                            <img src="<?php echo esc_url($manga['thumbnail']); ?>" alt="<?php echo esc_attr($manga['title']); ?>">
                                        </div>
                                        <div class="manga-admin-item-title">
                                            <?php echo esc_html($manga['title']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="manga-status-badge <?php echo esc_attr($status_class); ?>">
                                        <?php echo esc_html($status_labels[$status]); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(number_format($manga['chapters'])); ?></td>
                                <td><?php echo esc_html(format_number_with_suffix($manga['views'])); ?></td>
                                <td><?php echo esc_html($update_date->format('d/m/Y')); ?></td>
                                <td>
                                    <div class="manga-admin-actions-compact">
                                        <a href="<?php echo esc_url(add_query_arg(array('section' => 'edit', 'id' => $manga['id']))); ?>" class="manga-action-btn" title="<?php echo esc_attr__('Editar', 'manga-admin-panel'); ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo esc_url(add_query_arg(array('section' => 'chapters', 'manga_id' => $manga['id']))); ?>" class="manga-action-btn" title="<?php echo esc_attr__('Gerenciar Capítulos', 'manga-admin-panel'); ?>">
                                            <i class="fas fa-list"></i>
                                        </a>
                                        <a href="#" class="manga-action-btn manga-action-btn-accent manga-view-btn" data-id="<?php echo esc_attr($manga['id']); ?>" title="<?php echo esc_attr__('Visualizar', 'manga-admin-panel'); ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" class="manga-action-btn manga-action-btn-danger manga-delete-btn" data-id="<?php echo esc_attr($manga['id']); ?>" title="<?php echo esc_attr__('Excluir', 'manga-admin-panel'); ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="manga-admin-pagination">
                    <a href="#" class="manga-admin-pagination-btn disabled"><i class="fas fa-chevron-left"></i></a>
                    <a href="#" class="manga-admin-pagination-btn active">1</a>
                    <a href="#" class="manga-admin-pagination-btn">2</a>
                    <a href="#" class="manga-admin-pagination-btn">3</a>
                    <span class="manga-admin-pagination-ellipsis">...</span>
                    <a href="#" class="manga-admin-pagination-btn">10</a>
                    <a href="#" class="manga-admin-pagination-btn"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
            
        <?php elseif ($current_section === 'chapters') : ?>
            
            <!-- Gerenciar Capítulos -->
            <?php include MANGA_ADMIN_PANEL_PATH . 'templates/manga-chapter-manager.php'; ?>
            
        <?php elseif ($current_section === 'schedule') : ?>
            
            <!-- Capítulos Agendados -->
            <?php include MANGA_ADMIN_PANEL_PATH . 'templates/manga-schedule-list.php'; ?>
            
        <?php elseif ($current_section === 'settings') : ?>
            
            <!-- Configurações -->
            <?php include MANGA_ADMIN_PANEL_PATH . 'templates/manga-color-demo.php'; ?>
            
        <?php else : ?>
            
            <!-- Seção Desconhecida -->
            <div class="manga-alert manga-alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <?php echo esc_html__('Seção desconhecida ou não implementada.', 'manga-admin-panel'); ?>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<style>
/* Estilos adicionais específicos para o dashboard administrativo */
.manga-admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background-color: var(--manga-background-color, #f7f7f7);
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.manga-admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.manga-admin-title {
    font-size: 24px;
    margin: 0;
    color: var(--manga-text-color, #333);
    display: flex;
    align-items: center;
    gap: 10px;
}

.manga-admin-actions {
    display: flex;
    gap: 10px;
}

.manga-admin-nav {
    display: flex;
    margin-bottom: 30px;
    background-color: var(--manga-card-color, #fff);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.manga-admin-nav-item {
    padding: 15px 20px;
    color: var(--manga-text-color, #333);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
}

.manga-admin-nav-item:hover {
    background-color: rgba(0, 0, 0, 0.03);
}

.manga-admin-nav-item.active {
    color: var(--manga-primary-color, #ff6b6b);
    border-bottom-color: var(--manga-primary-color, #ff6b6b);
}

.manga-admin-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.manga-admin-stat-card {
    background-color: var(--manga-card-color, #fff);
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.manga-admin-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.manga-admin-stat-icon {
    width: 50px;
    height: 50px;
    background-color: var(--manga-primary-color, #ff6b6b);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.manga-admin-stat-card:nth-child(2) .manga-admin-stat-icon {
    background-color: var(--manga-accent-color, #4b7bec);
}

.manga-admin-stat-card:nth-child(3) .manga-admin-stat-icon {
    background-color: var(--manga-success-color, #1dd1a1);
}

.manga-admin-stat-card:nth-child(4) .manga-admin-stat-icon {
    background-color: var(--manga-secondary-color, #576574);
}

.manga-admin-stat-info {
    flex: 1;
}

.manga-admin-stat-number {
    font-size: 24px;
    font-weight: 700;
    color: var(--manga-text-color, #333);
    margin-bottom: 3px;
}

.manga-admin-stat-label {
    font-size: 14px;
    color: var(--manga-light-text, #718093);
}

.manga-admin-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.manga-admin-panel {
    background-color: var(--manga-card-color, #fff);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.manga-admin-panel-header {
    padding: 15px 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.manga-admin-panel-title {
    margin: 0;
    font-size: 18px;
    color: var(--manga-text-color, #333);
    display: flex;
    align-items: center;
    gap: 10px;
}

.manga-admin-panel-action {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: var(--manga-primary-color, #ff6b6b);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: background-color 0.2s ease;
}

.manga-admin-panel-action:hover {
    background-color: #ee5253;
}

.manga-admin-panel-content {
    padding: 0;
}

.manga-admin-table {
    width: 100%;
    border-collapse: collapse;
}

.manga-admin-table th,
.manga-admin-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.manga-admin-table th {
    background-color: rgba(0, 0, 0, 0.02);
    font-weight: 600;
    color: var(--manga-light-text, #718093);
}

.manga-admin-table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.01);
}

.manga-admin-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.manga-admin-item-thumb {
    width: 40px;
    height: 50px;
    border-radius: 4px;
    overflow: hidden;
}

.manga-admin-item-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.manga-admin-item-title {
    font-weight: 600;
    color: var(--manga-text-color, #333);
}

.manga-admin-actions-compact {
    display: flex;
    gap: 8px;
}

.manga-action-btn {
    width: 30px;
    height: 30px;
    border-radius: 4px;
    background-color: #f1f2f6;
    color: var(--manga-text-color, #333);
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s ease;
}

.manga-action-btn:hover {
    background-color: #dfe4ea;
}

.manga-action-btn-accent {
    background-color: var(--manga-accent-color, #4b7bec);
    color: white;
}

.manga-action-btn-accent:hover {
    background-color: #3867d6;
}

.manga-action-btn-danger {
    background-color: var(--manga-danger-color, #ff7675);
    color: white;
}

.manga-action-btn-danger:hover {
    background-color: #ff6b6b;
}

.manga-schedule-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.manga-status-scheduled {
    background-color: rgba(75, 123, 236, 0.15);
    color: var(--manga-accent-color, #4b7bec);
}

.manga-status-late {
    background-color: rgba(255, 118, 117, 0.15);
    color: var(--manga-danger-color, #ff7675);
}

.manga-schedule-date {
    font-size: 12px;
    color: var(--manga-light-text, #718093);
    margin-top: 5px;
}

.manga-admin-quick-actions {
    margin-bottom: 30px;
}

.manga-admin-section-title {
    font-size: 20px;
    margin: 0 0 20px 0;
    color: var(--manga-text-color, #333);
}

.manga-admin-quick-action-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

.manga-admin-quick-action-item {
    background-color: var(--manga-card-color, #fff);
    border-radius: 8px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.manga-admin-quick-action-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.manga-admin-quick-action-icon {
    width: 60px;
    height: 60px;
    background-color: var(--manga-primary-color, #ff6b6b);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.manga-admin-quick-action-item:nth-child(2) .manga-admin-quick-action-icon {
    background-color: var(--manga-accent-color, #4b7bec);
}

.manga-admin-quick-action-item:nth-child(3) .manga-admin-quick-action-icon {
    background-color: var(--manga-success-color, #1dd1a1);
}

.manga-admin-quick-action-item:nth-child(4) .manga-admin-quick-action-icon {
    background-color: var(--manga-secondary-color, #576574);
}

.manga-admin-quick-action-label {
    font-weight: 600;
    color: var(--manga-text-color, #333);
    text-align: center;
}

.manga-admin-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.manga-admin-filter-bar {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    padding: 15px;
    background-color: var(--manga-card-color, #fff);
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.manga-admin-search {
    position: relative;
    flex: 1;
    max-width: 300px;
}

.manga-admin-search-input {
    width: 100%;
    padding: 10px 15px;
    padding-right: 40px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.manga-admin-search-btn {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--manga-light-text, #718093);
    cursor: pointer;
    padding: 5px;
}

.manga-admin-filters {
    display: flex;
    gap: 10px;
}

.manga-admin-filter-select {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    background-color: white;
    color: var(--manga-text-color, #333);
}

.manga-admin-table-container {
    background-color: var(--manga-card-color, #fff);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.manga-status-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.manga-status-ongoing {
    background-color: rgba(75, 123, 236, 0.15);
    color: var(--manga-accent-color, #4b7bec);
}

.manga-status-completed {
    background-color: rgba(29, 209, 161, 0.15);
    color: var(--manga-success-color, #1dd1a1);
}

.manga-status-hiatus {
    background-color: rgba(254, 202, 87, 0.15);
    color: #feca57;
}

.manga-status-canceled {
    background-color: rgba(255, 118, 117, 0.15);
    color: var(--manga-danger-color, #ff7675);
}

.manga-admin-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 15px;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.manga-admin-pagination-btn {
    width: 35px;
    height: 35px;
    border-radius: 4px;
    background-color: white;
    color: var(--manga-text-color, #333);
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    margin: 0 5px;
    transition: all 0.2s ease;
    border: 1px solid #ddd;
}

.manga-admin-pagination-btn:hover {
    background-color: #f5f5f5;
}

.manga-admin-pagination-btn.active {
    background-color: var(--manga-primary-color, #ff6b6b);
    color: white;
    border-color: var(--manga-primary-color, #ff6b6b);
}

.manga-admin-pagination-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.manga-admin-pagination-ellipsis {
    margin: 0 5px;
    color: var(--manga-light-text, #718093);
}

/* Responsividade */
@media (max-width: 992px) {
    .manga-admin-stats, 
    .manga-admin-quick-action-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .manga-admin-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .manga-admin-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .manga-admin-nav {
        flex-wrap: wrap;
    }
    
    .manga-admin-nav-item {
        flex: 1 0 33.333%;
        justify-content: center;
        padding: 10px;
    }
    
    .manga-admin-filter-bar {
        flex-direction: column;
        gap: 15px;
    }
    
    .manga-admin-search {
        max-width: 100%;
    }
    
    .manga-admin-filters {
        flex-wrap: wrap;
    }
    
    .manga-admin-filter-select {
        flex: 1;
    }
    
    .manga-admin-table th:nth-child(2),
    .manga-admin-table td:nth-child(2),
    .manga-admin-table th:nth-child(4),
    .manga-admin-table td:nth-child(4) {
        display: none;
    }
}

@media (max-width: 576px) {
    .manga-admin-stats,
    .manga-admin-quick-action-grid {
        grid-template-columns: 1fr;
    }
    
    .manga-admin-nav-item {
        flex: 1 0 50%;
    }
    
    .manga-admin-actions-compact {
        flex-wrap: wrap;
    }
    
    .manga-admin-pagination-btn:not(.active):not(:first-child):not(:last-child) {
        display: none;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Botão de exclusão de mangá
    $('.manga-delete-btn').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('<?php echo esc_js(__('Tem certeza que deseja excluir este mangá? Esta ação não pode ser desfeita.', 'manga-admin-panel')); ?>')) {
            // Simulação de exclusão
            alert('<?php echo esc_js(__('Mangá excluído com sucesso (simulação).', 'manga-admin-panel')); ?>');
        }
    });
    
    // Botão de exclusão de capítulo
    $('.manga-delete-chapter-btn').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('<?php echo esc_js(__('Tem certeza que deseja excluir este capítulo? Esta ação não pode ser desfeita.', 'manga-admin-panel')); ?>')) {
            // Simulação de exclusão
            alert('<?php echo esc_js(__('Capítulo excluído com sucesso (simulação).', 'manga-admin-panel')); ?>');
        }
    });
    
    // Botão de publicação imediata
    $('.manga-publish-now-btn').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('<?php echo esc_js(__('Deseja publicar este capítulo agora?', 'manga-admin-panel')); ?>')) {
            // Simulação de publicação
            alert('<?php echo esc_js(__('Capítulo publicado com sucesso (simulação).', 'manga-admin-panel')); ?>');
        }
    });
    
    // Filtro de busca de mangás
    $('#manga-search').on('input', function() {
        const searchText = $(this).val().toLowerCase();
        
        $('.manga-admin-mangas-table tbody tr').each(function() {
            const mangaTitle = $(this).find('.manga-admin-item-title').text().toLowerCase();
            
            if (mangaTitle.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Filtro por status
    $('#manga-filter-status').on('change', function() {
        const selectedStatus = $(this).val();
        
        if (!selectedStatus) {
            // Mostrar todos
            $('.manga-admin-mangas-table tbody tr').show();
            return;
        }
        
        $('.manga-admin-mangas-table tbody tr').each(function() {
            const statusBadge = $(this).find('.manga-status-badge');
            const rowStatus = statusBadge.hasClass('manga-status-' + selectedStatus);
            
            if (rowStatus) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script>