<?php
/**
 * Manga Upload Shortcode
 * 
 * Shortcode para permitir que usuários façam upload de capítulos diretamente do frontend
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode para o formulário de upload de capítulos
 */
function manga_upload_shortcode($atts) {
    $atts = shortcode_atts(array(
        'manga_id' => 0,
        'show_title' => 'yes',
        'max_files' => 50,
        'allow_scheduling' => 'yes',
    ), $atts, 'manga_upload');
    
    // Verifica se o usuário está logado e tem permissões
    if (!manga_admin_panel_has_access()) {
        return manga_admin_login_form(__('Você precisa estar logado para fazer upload de capítulos.', 'manga-admin-panel'));
    }
    
    // Iniciar buffer de saída
    ob_start();
    
    // Título do formulário
    if ($atts['show_title'] === 'yes') {
        echo '<h2>' . __('Upload de Capítulo', 'manga-admin-panel') . '</h2>';
    }
    
    // Se não tiver manga_id, mostra seletor de mangá
    if (empty($atts['manga_id'])) {
        ?>
        <div class="manga-form-group">
            <label for="manga_id_upload" class="manga-form-label"><?php _e('Selecione o Mangá', 'manga-admin-panel'); ?> *</label>
            <select id="manga_id_upload" name="manga_id" class="manga-form-control" required>
                <option value=""><?php _e('Selecione um mangá', 'manga-admin-panel'); ?></option>
                <?php
                $manga_list = manga_admin_get_manga_list();
                foreach ($manga_list as $manga) {
                    echo '<option value="' . esc_attr($manga->ID) . '">' . esc_html($manga->post_title) . '</option>';
                }
                ?>
            </select>
        </div>
        <?php
    } else {
        echo '<input type="hidden" id="manga_id_upload" name="manga_id" value="' . esc_attr($atts['manga_id']) . '">';
        
        // Mostrar informações do mangá
        $manga = get_post(intval($atts['manga_id']));
        if ($manga) {
            echo '<div class="manga-info-bar">';
            echo '<h3>' . esc_html($manga->post_title) . '</h3>';
            echo '</div>';
        }
    }
    ?>
    
    <form id="manga-upload-form" class="manga-upload-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('manga_admin_upload_chapter', 'upload_nonce'); ?>
        
        <div class="manga-form-row" style="display: flex; gap: 20px;">
            <div class="manga-form-group" style="flex: 1;">
                <label for="chapter_number" class="manga-form-label"><?php _e('Número do Capítulo', 'manga-admin-panel'); ?> *</label>
                <input type="text" id="chapter_number" name="chapter_number" class="manga-form-control" required>
            </div>
            
            <div class="manga-form-group" style="flex: 2;">
                <label for="chapter_title" class="manga-form-label"><?php _e('Título do Capítulo (Opcional)', 'manga-admin-panel'); ?></label>
                <input type="text" id="chapter_title" name="chapter_title" class="manga-form-control">
            </div>
        </div>
        
        <?php if ($atts['allow_scheduling'] === 'yes'): ?>
        <div class="manga-form-group">
            <label for="upload_schedule" class="manga-form-label"><?php _e('Programar Publicação?', 'manga-admin-panel'); ?></label>
            <div class="manga-checkbox-item">
                <label>
                    <input type="checkbox" id="enable_schedule" name="enable_schedule" value="1">
                    <?php _e('Sim, programar para publicação futura', 'manga-admin-panel'); ?>
                </label>
            </div>
            
            <div id="schedule-options" style="margin-top: 10px; display: none;">
                <div class="manga-form-row" style="display: flex; gap: 20px;">
                    <div class="manga-form-group" style="flex: 1;">
                        <label for="schedule_date" class="manga-form-label"><?php _e('Data', 'manga-admin-panel'); ?></label>
                        <input type="date" id="schedule_date" name="schedule_date" class="manga-form-control">
                    </div>
                    
                    <div class="manga-form-group" style="flex: 1;">
                        <label for="schedule_time" class="manga-form-label"><?php _e('Hora', 'manga-admin-panel'); ?></label>
                        <input type="time" id="schedule_time" name="schedule_time" class="manga-form-control">
                    </div>
                </div>
                
                <div class="manga-checkbox-item">
                    <label>
                        <input type="checkbox" id="notify_subscribers" name="notify_subscribers" value="1" checked>
                        <?php _e('Notificar assinantes quando publicado', 'manga-admin-panel'); ?>
                    </label>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="manga-form-group">
            <label class="manga-form-label"><?php _e('Upload de Imagens do Capítulo', 'manga-admin-panel'); ?> *</label>
            <div class="manga-tabs-secondary" style="margin-bottom: 15px;">
                <div class="manga-tab-secondary active" data-upload-tab="images"><?php _e('Imagens', 'manga-admin-panel'); ?></div>
                <div class="manga-tab-secondary" data-upload-tab="zip"><?php _e('Arquivo ZIP', 'manga-admin-panel'); ?></div>
            </div>
            
            <div class="manga-tab-upload-content active" id="upload-tab-images">
                <div id="dropzone-upload" class="dropzone manga-dropzone">
                    <div class="dz-message">
                        <div class="manga-file-upload-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <h3><?php _e('Arraste imagens aqui', 'manga-admin-panel'); ?></h3>
                        <p><?php _e('Ou clique para selecionar arquivos', 'manga-admin-panel'); ?></p>
                        <p><small><?php _e('As imagens serão ordenadas alfabeticamente pelo nome do arquivo. Use numeração nos nomes para sequência correta.', 'manga-admin-panel'); ?></small></p>
                    </div>
                </div>
            </div>
            
            <div class="manga-tab-upload-content" id="upload-tab-zip" style="display: none;">
                <div id="dropzone-zip" class="dropzone manga-dropzone">
                    <div class="dz-message">
                        <div class="manga-file-upload-icon">
                            <i class="fas fa-file-archive"></i>
                        </div>
                        <h3><?php _e('Arraste um arquivo ZIP aqui', 'manga-admin-panel'); ?></h3>
                        <p><?php _e('Ou clique para selecionar o arquivo', 'manga-admin-panel'); ?></p>
                        <p><small><?php _e('O arquivo ZIP deve conter todas as imagens do capítulo na ordem correta.', 'manga-admin-panel'); ?></small></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="manga-form-group">
            <label class="manga-form-label"><?php _e('Opções Adicionais', 'manga-admin-panel'); ?></label>
            
            <div class="manga-checkbox-item">
                <label>
                    <input type="checkbox" name="optimize_images" value="1" checked>
                    <?php _e('Otimizar imagens automaticamente', 'manga-admin-panel'); ?>
                </label>
                <small><?php _e('Redimensiona e comprime imagens para melhor performance.', 'manga-admin-panel'); ?></small>
            </div>
            
            <div class="manga-checkbox-item">
                <label>
                    <input type="checkbox" name="publish_immediately" value="1" checked>
                    <?php _e('Publicar imediatamente', 'manga-admin-panel'); ?>
                </label>
                <small><?php _e('Se desmarcado, o capítulo será salvo como rascunho.', 'manga-admin-panel'); ?></small>
            </div>
        </div>
        
        <div class="manga-form-group">
            <label for="chapter_warning" class="manga-form-label"><?php _e('Aviso/Nota do Capítulo (Opcional)', 'manga-admin-panel'); ?></label>
            <textarea id="chapter_warning" name="chapter_warning" class="manga-form-control" rows="2"></textarea>
            <small><?php _e('Aviso ou nota opcional que aparece antes do conteúdo do capítulo', 'manga-admin-panel'); ?></small>
        </div>
        
        <div class="manga-form-actions" style="margin-top: 20px;">
            <button type="submit" id="submit-upload" class="manga-btn manga-btn-primary" disabled><?php _e('Salvar Capítulo', 'manga-admin-panel'); ?></button>
            <div id="upload-progress-container" style="display: none; margin-top: 15px;">
                <div class="manga-progress-bar" style="height: 20px; background-color: #f1f2f6; border-radius: 10px; overflow: hidden;">
                    <div id="upload-progress" style="height: 100%; width: 0%; background-color: #ff6b6b; transition: width 0.3s ease;"></div>
                </div>
                <div id="upload-progress-text" style="text-align: center; margin-top: 5px;">0%</div>
            </div>
        </div>
    </form>
    
    <script>
    jQuery(document).ready(function($) {
        // Inicializar Dropzone para imagens
        var myDropzone = new Dropzone("#dropzone-upload", {
            url: mangaAdminVars.ajaxurl,
            paramName: "chapter_files",
            maxFiles: <?php echo intval($atts['max_files']); ?>,
            maxFilesize: 10, // MB
            acceptedFiles: "image/*",
            autoProcessQueue: false,
            uploadMultiple: true,
            parallelUploads: 10,
            addRemoveLinks: true,
            dictRemoveFile: "<?php _e('Remover', 'manga-admin-panel'); ?>",
            dictCancelUpload: "<?php _e('Cancelar', 'manga-admin-panel'); ?>",
            dictDefaultMessage: "<?php _e('Arraste imagens aqui para fazer upload', 'manga-admin-panel'); ?>",
            init: function() {
                var dropzone = this;
                
                // Habilitar botão quando houver arquivos
                this.on("addedfile", function() {
                    $("#submit-upload").prop("disabled", false);
                });
                
                // Desabilitar botão quando não houver arquivos
                this.on("removedfile", function() {
                    if (dropzone.files.length === 0) {
                        $("#submit-upload").prop("disabled", true);
                    }
                });
                
                // Configurar envio do formulário
                $("#manga-upload-form").on("submit", function(e) {
                    e.preventDefault();
                    
                    // Verificar manga_id
                    var mangaId = $("#manga_id_upload").val();
                    if (!mangaId) {
                        toastr.error("<?php _e('Selecione um mangá primeiro.', 'manga-admin-panel'); ?>");
                        return false;
                    }
                    
                    // Verificar número do capítulo
                    var chapterNumber = $("#chapter_number").val();
                    if (!chapterNumber) {
                        toastr.error("<?php _e('Número do capítulo é obrigatório.', 'manga-admin-panel'); ?>");
                        return false;
                    }
                    
                    // Adicionar dados do formulário ao Dropzone
                    var formData = $(this).serializeArray();
                    
                    $.each(formData, function(i, field) {
                        dropzone.options.params[field.name] = field.value;
                    });
                    
                    // Adicionar ação e nonce
                    dropzone.options.params.action = "manga_admin_upload_chapter";
                    dropzone.options.params.nonce = mangaAdminVars.nonce;
                    
                    // Mostrar progresso
                    $("#upload-progress-container").show();
                    
                    // Processar fila
                    dropzone.processQueue();
                });
                
                // Atualizar progresso
                this.on("totaluploadprogress", function(progress) {
                    $("#upload-progress").width(progress + "%");
                    $("#upload-progress-text").text(Math.round(progress) + "%");
                });
                
                // Resposta de sucesso
                this.on("success", function(file, response) {
                    if (response.success) {
                        toastr.success(response.data.message);
                        setTimeout(function() {
                            // Redirecionar para o gerenciador de capítulos com o manga_id
                            window.location.href = window.location.href.split('?')[0] + '?view=chapters&id=' + $("#manga_id_upload").val();
                        }, 2000);
                    } else {
                        toastr.error(response.data.message);
                    }
                });
                
                // Resposta de erro
                this.on("error", function(file, errorMessage) {
                    toastr.error(errorMessage);
                });
            }
        });
        
        // Inicializar Dropzone para ZIP
        var zipDropzone = new Dropzone("#dropzone-zip", {
            url: mangaAdminVars.ajaxurl,
            paramName: "zip_file",
            maxFiles: 1,
            maxFilesize: 50, // MB
            acceptedFiles: ".zip",
            autoProcessQueue: false,
            addRemoveLinks: true,
            dictRemoveFile: "<?php _e('Remover', 'manga-admin-panel'); ?>",
            dictCancelUpload: "<?php _e('Cancelar', 'manga-admin-panel'); ?>",
            dictDefaultMessage: "<?php _e('Arraste um arquivo ZIP aqui para fazer upload', 'manga-admin-panel'); ?>",
            init: function() {
                var zipDz = this;
                
                // Habilitar botão quando houver arquivo
                this.on("addedfile", function() {
                    $("#submit-upload").prop("disabled", false);
                });
                
                // Desabilitar botão quando não houver arquivo
                this.on("removedfile", function() {
                    if (zipDz.files.length === 0) {
                        $("#submit-upload").prop("disabled", true);
                    }
                });
            }
        });
        
        // Alternar entre abas de upload
        $(".manga-tab-secondary").on("click", function() {
            var tab = $(this).data("upload-tab");
            
            $(".manga-tab-secondary").removeClass("active");
            $(this).addClass("active");
            
            $(".manga-tab-upload-content").hide();
            $("#upload-tab-" + tab).show();
        });
        
        // Mostrar/esconder opções de agendamento
        $("#enable_schedule").on("change", function() {
            if ($(this).is(":checked")) {
                $("#schedule-options").show();
                $("input[name='publish_immediately']").prop("checked", false).prop("disabled", true);
            } else {
                $("#schedule-options").hide();
                $("input[name='publish_immediately']").prop("disabled", false);
            }
        });
    });
    </script>
    <?php
    
    return ob_get_clean();
}
add_shortcode('manga_upload', 'manga_upload_shortcode');
