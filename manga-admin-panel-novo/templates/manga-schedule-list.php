<?php
/**
 * Template para exibir lista de capítulos agendados
 */

// Verificação de segurança
if (!defined('ABSPATH')) {
    exit;
}

// Verificar se o plugin WP Manga Chapter Scheduler está ativo
$scheduler_active = class_exists('WP_MANGA_CHAPTER_SCHEDULER') || function_exists('wp_manga_schedule_chapter');

// Obter ID do mangá
$manga_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$manga_id) {
    echo '<div class="manga-alert manga-alert-danger">' . esc_html__('ID do mangá não fornecido.', 'manga-admin-panel') . '</div>';
    return;
}

// Obter título do mangá
$manga_title = get_the_title($manga_id);

// Obter capítulos agendados (se o plugin estiver ativo)
$scheduled_chapters = array();

if ($scheduler_active && function_exists('get_scheduled_chapters')) {
    $scheduled_chapters = get_scheduled_chapters($manga_id);
} else {
    // Simulação para desenvolvimento - remover em produção
    // Isso é apenas um exemplo para desenvolvimento
    $scheduled_chapters = array(
        array(
            'id' => 1,
            'chapter_id' => 123,
            'chapter_name' => 'Capítulo 10',
            'scheduled_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'status' => 'scheduled'
        ),
        array(
            'id' => 2,
            'chapter_id' => 124,
            'chapter_name' => 'Capítulo 11',
            'scheduled_date' => date('Y-m-d H:i:s', strtotime('+5 days')),
            'status' => 'scheduled'
        )
    );
}
?>

<div class="manga-admin-container manga-scheduler-container">
    <div class="manga-admin-header">
        <h1 class="manga-admin-title"><?php echo esc_html__('Agendador de Capítulos', 'manga-admin-panel'); ?></h1>
        <div class="manga-admin-actions">
            <a href="<?php echo esc_url(add_query_arg(array('action' => 'schedule_new', 'id' => $manga_id), remove_query_arg('chapter'))); ?>" class="manga-btn manga-btn-primary">
                <i class="fas fa-calendar-plus"></i> <?php echo esc_html__('Agendar Novo Capítulo', 'manga-admin-panel'); ?>
            </a>
            <a href="<?php echo esc_url(remove_query_arg(array('action', 'chapter'))); ?>" class="manga-btn manga-btn-secondary">
                <i class="fas fa-arrow-left"></i> <?php echo esc_html__('Voltar', 'manga-admin-panel'); ?>
            </a>
        </div>
    </div>

    <div class="manga-admin-content">
        <div class="manga-summary-card">
            <div class="manga-summary-header">
                <h2><?php echo esc_html($manga_title); ?></h2>
                <span class="manga-summary-meta"><?php echo esc_html__('Capítulos Agendados', 'manga-admin-panel'); ?></span>
            </div>

            <?php if (empty($scheduled_chapters)) : ?>
                <div class="manga-empty-state">
                    <div class="manga-empty-icon"><i class="fas fa-calendar-times"></i></div>
                    <div class="manga-empty-text"><?php echo esc_html__('Nenhum capítulo agendado para este mangá.', 'manga-admin-panel'); ?></div>
                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'schedule_new', 'id' => $manga_id), remove_query_arg('chapter'))); ?>" class="manga-btn manga-btn-primary">
                        <?php echo esc_html__('Agendar Capítulo', 'manga-admin-panel'); ?>
                    </a>
                </div>
            <?php else : ?>
                <div class="scheduled-chapters-list">
                    <div class="manga-table-responsive">
                        <table class="manga-table manga-scheduler-table">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html__('Capítulo', 'manga-admin-panel'); ?></th>
                                    <th><?php echo esc_html__('Data Programada', 'manga-admin-panel'); ?></th>
                                    <th><?php echo esc_html__('Status', 'manga-admin-panel'); ?></th>
                                    <th><?php echo esc_html__('Ações', 'manga-admin-panel'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scheduled_chapters as $chapter) : 
                                    $scheduled_date = new DateTime($chapter['scheduled_date']);
                                    $now = new DateTime();
                                    $is_past = $scheduled_date < $now;
                                    $is_today = $scheduled_date->format('Y-m-d') === $now->format('Y-m-d');
                                    $time_left = '';
                                    
                                    if (!$is_past) {
                                        $interval = $now->diff($scheduled_date);
                                        if ($interval->days > 0) {
                                            $time_left = sprintf(
                                                _n('Falta %d dia', 'Faltam %d dias', $interval->days, 'manga-admin-panel'),
                                                $interval->days
                                            );
                                        } else {
                                            $hours = $interval->h;
                                            $minutes = $interval->i;
                                            if ($hours > 0) {
                                                $time_left = sprintf(
                                                    _n('Falta %d hora', 'Faltam %d horas', $hours, 'manga-admin-panel'),
                                                    $hours
                                                );
                                            } else {
                                                $time_left = sprintf(
                                                    _n('Falta %d minuto', 'Faltam %d minutos', $minutes, 'manga-admin-panel'),
                                                    $minutes
                                                );
                                            }
                                        }
                                    }
                                    
                                    $status_class = '';
                                    $status_text = '';
                                    
                                    if ($is_past) {
                                        $status_class = 'published';
                                        $status_text = __('Publicado', 'manga-admin-panel');
                                    } elseif ($is_today) {
                                        $status_class = 'today';
                                        $status_text = __('Hoje', 'manga-admin-panel');
                                    } else {
                                        $status_class = 'scheduled';
                                        $status_text = __('Agendado', 'manga-admin-panel');
                                    }
                                ?>
                                <tr class="<?php echo esc_attr($status_class); ?>">
                                    <td>
                                        <div class="chapter-name">
                                            <?php if (!$is_past) : ?>
                                                <i class="fas fa-lock schedule-lock-icon" title="<?php echo esc_attr__('Capítulo agendado - Ainda não publicado', 'manga-admin-panel'); ?>"></i>
                                            <?php endif; ?>
                                            <?php echo esc_html($chapter['chapter_name']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="scheduled-date">
                                            <?php echo esc_html($scheduled_date->format('d/m/Y H:i')); ?>
                                            <?php if (!empty($time_left)) : ?>
                                                <span class="time-left">(<?php echo esc_html($time_left); ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="chapter-status <?php echo esc_attr($status_class); ?>">
                                            <?php echo esc_html($status_text); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="chapter-actions">
                                            <?php if (!$is_past) : ?>
                                                <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit_schedule', 'chapter' => $chapter['id'], 'id' => $manga_id))); ?>" class="manga-btn manga-btn-sm manga-btn-secondary manga-tooltip" data-tooltip="<?php echo esc_attr__('Editar agendamento', 'manga-admin-panel'); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo esc_url(add_query_arg(array('action' => 'publish_now', 'chapter' => $chapter['id'], 'id' => $manga_id))); ?>" class="manga-btn manga-btn-sm manga-btn-success manga-tooltip" data-tooltip="<?php echo esc_attr__('Publicar agora', 'manga-admin-panel'); ?>">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="<?php echo esc_url(add_query_arg(array('action' => 'delete_schedule', 'chapter' => $chapter['id'], 'id' => $manga_id))); ?>" class="manga-btn manga-btn-sm manga-btn-danger manga-tooltip" data-tooltip="<?php echo esc_attr__('Cancelar agendamento', 'manga-admin-panel'); ?>" onclick="return confirm('<?php echo esc_js(__('Tem certeza que deseja cancelar este agendamento?', 'manga-admin-panel')); ?>')">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php else : ?>
                                                <span class="manga-published-notice"><?php echo esc_html__('Já publicado', 'manga-admin-panel'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .manga-summary-card {
        background-color: var(--manga-card-color, #fff);
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .manga-summary-header {
        margin-bottom: 20px;
        border-bottom: 1px solid #eaeaea;
        padding-bottom: 15px;
    }
    
    .manga-summary-header h2 {
        margin: 0;
        font-size: 20px;
        color: var(--manga-text-color, #333);
    }
    
    .manga-summary-meta {
        font-size: 14px;
        color: var(--manga-light-text, #718093);
    }
    
    .manga-table-responsive {
        overflow-x: auto;
    }
    
    .manga-scheduler-table {
        min-width: 600px;
    }
    
    .manga-scheduler-table tr.published {
        background-color: rgba(29, 209, 161, 0.1);
    }
    
    .manga-scheduler-table tr.today {
        background-color: rgba(255, 166, 0, 0.1);
    }
    
    .chapter-name {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .scheduled-date {
        white-space: nowrap;
    }
    
    .time-left {
        font-size: 12px;
        color: var(--manga-light-text, #718093);
    }
    
    .schedule-lock-icon {
        color: var(--manga-accent-color, #4b7bec);
    }
    
    .chapter-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }
    
    .chapter-status.published {
        background-color: var(--manga-success-color, #1dd1a1);
    }
    
    .chapter-status.today {
        background-color: #ff9f43;
    }
    
    .chapter-status.scheduled {
        background-color: var(--manga-accent-color, #4b7bec);
    }
    
    .manga-published-notice {
        font-size: 12px;
        color: var(--manga-success-color, #1dd1a1);
    }
</style>