<?php
/**
 * Manga User Profile Shortcode
 * 
 * Shortcode para exibir o perfil do usuário logado com seus mangás e opções de edição
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode para exibir o perfil do usuário
 */
function manga_user_profile_shortcode($atts) {
    $atts = shortcode_atts(array(
        'show_avatar' => 'yes',
        'show_stats' => 'yes',
        'show_recent' => 'yes',
        'recent_count' => 5,
        'show_edit_profile' => 'yes',
    ), $atts, 'manga_user_profile');
    
    // Verifica se o usuário está logado
    if (!is_user_logged_in()) {
        return manga_admin_login_form(__('Você precisa estar logado para visualizar seu perfil.', 'manga-admin-panel'));
    }
    
    // Obter usuário atual
    $user = wp_get_current_user();
    $user_id = $user->ID;
    
    // Iniciar buffer de saída
    ob_start();
    
    // Cabeçalho do perfil
    echo '<div class="manga-user-profile">';
    
    // Informações do usuário
    echo '<div class="manga-profile-header">';
    
    // Avatar do usuário
    if ($atts['show_avatar'] === 'yes') {
        echo '<div class="manga-profile-avatar">';
        echo get_avatar($user_id, 96);
        echo '</div>';
    }
    
    echo '<div class="manga-profile-info">';
    echo '<h2>' . esc_html($user->display_name) . '</h2>';
    echo '<p class="manga-profile-role">';
    
    // Mostrar papel principal do usuário
    $user_roles = $user->roles;
    if (!empty($user_roles)) {
        $role_display = array(
            'administrator' => __('Administrador', 'manga-admin-panel'),
            'editor' => __('Editor', 'manga-admin-panel'),
            'manga_editor' => __('Editor de Mangá', 'manga-admin-panel'),
            'author' => __('Autor', 'manga-admin-panel'),
            'contributor' => __('Colaborador', 'manga-admin-panel'),
            'subscriber' => __('Assinante', 'manga-admin-panel'),
        );
        
        $main_role = reset($user_roles);
        echo isset($role_display[$main_role]) ? esc_html($role_display[$main_role]) : esc_html(ucfirst($main_role));
    }
    
    echo '</p>';
    
    // Data de registro
    echo '<p class="manga-profile-registered">';
    printf(__('Membro desde %s', 'manga-admin-panel'), date_i18n(get_option('date_format'), strtotime($user->user_registered)));
    echo '</p>';
    
    echo '</div>'; // .manga-profile-info
    
    // Ações do perfil
    echo '<div class="manga-profile-actions">';
    
    // Link para editar perfil
    if ($atts['show_edit_profile'] === 'yes') {
        echo '<a href="' . esc_url(get_edit_profile_url()) . '" class="manga-btn manga-btn-secondary">';
        echo '<i class="fas fa-user-edit"></i> ' . __('Editar Perfil', 'manga-admin-panel');
        echo '</a>';
    }
    
    // Link para criar novo mangá (se tiver permissão)
    if (manga_admin_panel_has_access()) {
        echo '<a href="' . esc_url(add_query_arg('view', 'create', remove_query_arg('id'))) . '" class="manga-btn manga-btn-primary">';
        echo '<i class="fas fa-plus"></i> ' . __('Novo Mangá', 'manga-admin-panel');
        echo '</a>';
    }
    
    echo '</div>'; // .manga-profile-actions
    
    echo '</div>'; // .manga-profile-header
    
    // Estatísticas do usuário
    if ($atts['show_stats'] === 'yes' && manga_admin_panel_has_access()) {
        $user_stats = manga_admin_get_user_stats($user_id);
        
        echo '<div class="manga-profile-stats">';
        echo '<h3>' . __('Suas Estatísticas', 'manga-admin-panel') . '</h3>';
        
        echo '<div class="manga-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">';
        
        echo '<div class="manga-stat-card">';
        echo '<h4>' . __('Total de Mangás', 'manga-admin-panel') . '</h4>';
        echo '<div class="manga-stat-value">' . esc_html($user_stats['total_manga']) . '</div>';
        echo '</div>';
        
        echo '<div class="manga-stat-card">';
        echo '<h4>' . __('Total de Capítulos', 'manga-admin-panel') . '</h4>';
        echo '<div class="manga-stat-value">' . esc_html($user_stats['total_chapters']) . '</div>';
        echo '</div>';
        
        echo '<div class="manga-stat-card">';
        echo '<h4>' . __('Publicados', 'manga-admin-panel') . '</h4>';
        echo '<div class="manga-stat-value">' . esc_html($user_stats['published_manga']) . '</div>';
        echo '</div>';
        
        echo '<div class="manga-stat-card">';
        echo '<h4>' . __('Rascunhos', 'manga-admin-panel') . '</h4>';
        echo '<div class="manga-stat-value">' . esc_html($user_stats['draft_manga']) . '</div>';
        echo '</div>';
        
        echo '</div>'; // .manga-stats-grid
        echo '</div>'; // .manga-profile-stats
    }
    
    // Mangás recentes do usuário
    if ($atts['show_recent'] === 'yes' && manga_admin_panel_has_access()) {
        // Obter mangás do usuário
        $args = array(
            'post_type'      => 'wp-manga',
            'post_status'    => array('publish', 'draft', 'future'),
            'posts_per_page' => intval($atts['recent_count']),
            'orderby'        => 'modified',
            'order'          => 'DESC',
            'author'         => $user_id,
        );
        
        $manga_query = new WP_Query($args);
        
        echo '<div class="manga-profile-recent">';
        echo '<h3>' . __('Seus Mangás Recentes', 'manga-admin-panel') . '</h3>';
        
        if ($manga_query->have_posts()) {
            echo '<div class="manga-table-container">';
            echo '<table class="manga-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>' . __('Mangá', 'manga-admin-panel') . '</th>';
            echo '<th>' . __('Último Capítulo', 'manga-admin-panel') . '</th>';
            echo '<th>' . __('Atualizado', 'manga-admin-panel') . '</th>';
            echo '<th>' . __('Status', 'manga-admin-panel') . '</th>';
            echo '<th>' . __('Ações', 'manga-admin-panel') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            while ($manga_query->have_posts()) {
                $manga_query->the_post();
                $manga_id = get_the_ID();
                
                // Obter último capítulo
                $latest_chapter = array(
                    'name' => __('Sem capítulos', 'manga-admin-panel'),
                    'date' => ''
                );
                
                if (function_exists('madara_get_manga_chapters')) {
                    $chapters = madara_get_manga_chapters($manga_id, 1); // Apenas o último capítulo
                    
                    if (!empty($chapters)) {
                        $chapter = reset($chapters); // Primeiro elemento
                        $latest_chapter = array(
                            'name' => $chapter['chapter_name'],
                            'date' => date_i18n(get_option('date_format'), strtotime($chapter['date']))
                        );
                    }
                }
                
                // Obter texto do status
                $status_text = '';
                $status_class = '';
                
                switch (get_post_status()) {
                    case 'publish':
                        $status_text = __('Publicado', 'manga-admin-panel');
                        $status_class = 'published';
                        break;
                    case 'draft':
                        $status_text = __('Rascunho', 'manga-admin-panel');
                        $status_class = 'draft';
                        break;
                    case 'future':
                        $status_text = __('Agendado', 'manga-admin-panel');
                        $status_class = 'scheduled';
                        break;
                    default:
                        $status_text = ucfirst(get_post_status());
                        break;
                }
                
                echo '<tr>';
                echo '<td><strong>' . esc_html(get_the_title()) . '</strong></td>';
                echo '<td>' . esc_html($latest_chapter['name']) . '</td>';
                echo '<td>' . esc_html(get_the_modified_date()) . '</td>';
                echo '<td><span class="chapter-status ' . esc_attr($status_class) . '">' . esc_html($status_text) . '</span></td>';
                echo '<td>';
                echo '<a href="' . esc_url(add_query_arg(array('view' => 'edit', 'id' => $manga_id))) . '" class="manga-btn manga-btn-secondary manga-btn-sm">' . __('Editar', 'manga-admin-panel') . '</a>';
                echo '<a href="' . esc_url(add_query_arg(array('view' => 'chapters', 'id' => $manga_id))) . '" class="manga-btn manga-btn-primary manga-btn-sm">' . __('Capítulos', 'manga-admin-panel') . '</a>';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>'; // .manga-table-container
            
            // Botão para ver todos os mangás
            echo '<p class="manga-view-all">';
            echo '<a href="' . esc_url(add_query_arg('view', 'dashboard')) . '" class="manga-btn manga-btn-secondary">';
            echo __('Ver Todos os Mangás', 'manga-admin-panel');
            echo '</a>';
            echo '</p>';
            
        } else {
            echo '<div class="manga-empty-state">';
            echo '<div class="manga-empty-icon"><i class="fas fa-book"></i></div>';
            echo '<p class="manga-empty-text">' . __('Você ainda não tem mangás.', 'manga-admin-panel') . '</p>';
            echo '<a href="' . esc_url(add_query_arg('view', 'create')) . '" class="manga-btn manga-btn-primary">';
            echo __('Criar Primeiro Mangá', 'manga-admin-panel');
            echo '</a>';
            echo '</div>';
        }
        
        wp_reset_postdata();
        
        echo '</div>'; // .manga-profile-recent
    }
    
    // Assinaturas do usuário (se implementado)
    echo '<div class="manga-profile-subscriptions">';
    echo '<h3>' . __('Suas Assinaturas', 'manga-admin-panel') . '</h3>';
    
    $subscriptions = get_user_meta($user_id, 'manga_subscriptions', true);
    
    if (!empty($subscriptions) && is_array($subscriptions)) {
        echo '<div class="manga-table-container">';
        echo '<table class="manga-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('Mangá', 'manga-admin-panel') . '</th>';
        echo '<th>' . __('Tipo de Notificação', 'manga-admin-panel') . '</th>';
        echo '<th>' . __('Ações', 'manga-admin-panel') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($subscriptions as $manga_id => $type) {
            $manga_title = get_the_title($manga_id);
            
            $notification_types = array(
                'email' => __('E-mail', 'manga-admin-panel'),
                'browser' => __('Navegador', 'manga-admin-panel'),
                'both' => __('E-mail e Navegador', 'manga-admin-panel'),
            );
            
            $notification_type = isset($notification_types[$type]) ? $notification_types[$type] : __('E-mail', 'manga-admin-panel');
            
            echo '<tr>';
            echo '<td><strong>' . esc_html($manga_title) . '</strong></td>';
            echo '<td>' . esc_html($notification_type) . '</td>';
            echo '<td>';
            echo '<button class="manga-btn manga-btn-danger manga-btn-sm unsubscribe-manga" data-manga-id="' . esc_attr($manga_id) . '">' . __('Cancelar', 'manga-admin-panel') . '</button>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; // .manga-table-container
    } else {
        echo '<div class="manga-empty-state">';
        echo '<div class="manga-empty-icon"><i class="fas fa-bell-slash"></i></div>';
        echo '<p class="manga-empty-text">' . __('Você não tem assinaturas de mangás ainda.', 'manga-admin-panel') . '</p>';
        echo '<p>' . __('Assine mangás para receber notificações quando novos capítulos forem publicados.', 'manga-admin-panel') . '</p>';
        echo '</div>';
    }
    
    echo '</div>'; // .manga-profile-subscriptions
    
    // JavaScript para interação
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Cancelar assinatura
        $(".unsubscribe-manga").on("click", function() {
            var mangaId = $(this).data("manga-id");
            var button = $(this);
            
            if (confirm("<?php _e('Tem certeza que deseja cancelar esta assinatura?', 'manga-admin-panel'); ?>")) {
                $.ajax({
                    url: mangaAdminVars.ajaxurl,
                    type: "POST",
                    data: {
                        action: "manga_admin_unsubscribe",
                        manga_id: mangaId,
                        nonce: mangaAdminVars.nonce
                    },
                    beforeSend: function() {
                        button.prop("disabled", true).text("<?php _e('Aguarde...', 'manga-admin-panel'); ?>");
                    },
                    success: function(response) {
                        if (response.success) {
                            button.closest("tr").fadeOut(300, function() {
                                $(this).remove();
                                
                                // Verificar se não há mais assinaturas
                                if ($(".manga-profile-subscriptions tbody tr").length === 0) {
                                    $(".manga-profile-subscriptions table").replaceWith(
                                        '<div class="manga-empty-state">' +
                                        '<div class="manga-empty-icon"><i class="fas fa-bell-slash"></i></div>' +
                                        '<p class="manga-empty-text"><?php _e('Você não tem assinaturas de mangás ainda.', 'manga-admin-panel'); ?></p>' +
                                        '<p><?php _e('Assine mangás para receber notificações quando novos capítulos forem publicados.', 'manga-admin-panel'); ?></p>' +
                                        '</div>'
                                    );
                                }
                            });
                            
                            toastr.success(response.data.message);
                        } else {
                            button.prop("disabled", false).text("<?php _e('Cancelar', 'manga-admin-panel'); ?>");
                            toastr.error(response.data.message);
                        }
                    },
                    error: function() {
                        button.prop("disabled", false).text("<?php _e('Cancelar', 'manga-admin-panel'); ?>");
                        toastr.error("<?php _e('Erro ao processar a solicitação.', 'manga-admin-panel'); ?>");
                    }
                });
            }
        });
    });
    </script>
    <?php
    
    echo '</div>'; // .manga-user-profile
    
    return ob_get_clean();
}
add_shortcode('manga_user_profile', 'manga_user_profile_shortcode');


/**
 * AJAX para cancelar assinatura
 */
function manga_admin_ajax_unsubscribe() {
    // Verificar nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'manga_admin_nonce')) {
        wp_send_json_error(['message' => __('Verificação de segurança falhou.', 'manga-admin-panel')]);
    }
    
    // Verificar se o usuário está logado
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('Você precisa estar logado para cancelar assinaturas.', 'manga-admin-panel')]);
    }
    
    // Verificar se o manga_id foi fornecido
    if (!isset($_POST['manga_id']) || empty($_POST['manga_id'])) {
        wp_send_json_error(['message' => __('ID do mangá não fornecido.', 'manga-admin-panel')]);
    }
    
    $manga_id = intval($_POST['manga_id']);
    $user_id = get_current_user_id();
    
    // Obter assinaturas do usuário
    $subscriptions = get_user_meta($user_id, 'manga_subscriptions', true);
    
    if (!is_array($subscriptions)) {
        $subscriptions = array();
    }
    
    // Remover assinatura
    if (isset($subscriptions[$manga_id])) {
        unset($subscriptions[$manga_id]);
        update_user_meta($user_id, 'manga_subscriptions', $subscriptions);
        wp_send_json_success(['message' => __('Assinatura cancelada com sucesso.', 'manga-admin-panel')]);
    } else {
        wp_send_json_error(['message' => __('Assinatura não encontrada.', 'manga-admin-panel')]);
    }
}
add_action('wp_ajax_manga_admin_unsubscribe', 'manga_admin_ajax_unsubscribe');
