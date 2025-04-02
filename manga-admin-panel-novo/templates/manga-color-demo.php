<?php
/**
 * Template para demonstração das cores personalizadas
 * Este arquivo pode ser incluído em qualquer página com shortcode [manga_color_demo]
 */

// Verifica se o usuário tem permissão
if (!manga_admin_panel_has_access()) {
    echo '<div class="manga-alert manga-alert-danger">' . 
         __('Você precisa estar logado com privilégios adequados para acessar este recurso.', 'manga-admin-panel') . 
         '</div>';
    return;
}

// Obtém as cores atuais
$colors = manga_admin_panel_get_color_options();
?>

<div class="manga-admin-container">
    <div class="manga-admin-header">
        <h1 class="manga-admin-title"><?php _e('Demonstração de Cores', 'manga-admin-panel'); ?></h1>
        <div class="manga-admin-actions">
            <a href="<?php echo esc_url(add_query_arg('page', 'manga-admin-panel-settings', admin_url('options-general.php'))); ?>" class="manga-btn manga-btn-primary">
                <?php _e('Editar Cores', 'manga-admin-panel'); ?>
            </a>
        </div>
    </div>

    <div class="manga-admin-tabs">
        <div class="manga-admin-tab active"><?php _e('Elementos', 'manga-admin-panel'); ?></div>
        <div class="manga-admin-tab"><?php _e('Cards', 'manga-admin-panel'); ?></div>
        <div class="manga-admin-tab"><?php _e('Tabelas', 'manga-admin-panel'); ?></div>
        <div class="manga-admin-tab"><?php _e('Formulários', 'manga-admin-panel'); ?></div>
    </div>

    <div class="manga-admin-content">
        <div class="manga-admin-tab-pane active">
            <h2><?php _e('Elementos de Interface', 'manga-admin-panel'); ?></h2>
            <p><?php _e('Esta página demonstra como as cores personalizadas são aplicadas aos diferentes elementos da interface.', 'manga-admin-panel'); ?></p>
            
            <h3><?php _e('Botões', 'manga-admin-panel'); ?></h3>
            <div class="manga-button-demo" style="display: flex; gap: 10px; margin: 20px 0;">
                <button class="manga-btn manga-btn-primary"><?php _e('Botão Primário', 'manga-admin-panel'); ?></button>
                <button class="manga-btn manga-btn-secondary"><?php _e('Botão Secundário', 'manga-admin-panel'); ?></button>
                <button class="manga-btn manga-btn-success"><?php _e('Botão Sucesso', 'manga-admin-panel'); ?></button>
                <button class="manga-btn manga-btn-danger"><?php _e('Botão Perigo', 'manga-admin-panel'); ?></button>
            </div>
            
            <h3><?php _e('Alertas', 'manga-admin-panel'); ?></h3>
            <div class="manga-alert manga-alert-success">
                <?php _e('Esta é uma mensagem de sucesso.', 'manga-admin-panel'); ?>
            </div>
            <div class="manga-alert manga-alert-info">
                <?php _e('Esta é uma mensagem informativa.', 'manga-admin-panel'); ?>
            </div>
            <div class="manga-alert manga-alert-warning">
                <?php _e('Esta é uma mensagem de aviso.', 'manga-admin-panel'); ?>
            </div>
            <div class="manga-alert manga-alert-danger">
                <?php _e('Esta é uma mensagem de erro.', 'manga-admin-panel'); ?>
            </div>
            
            <h3><?php _e('Estados', 'manga-admin-panel'); ?></h3>
            <div style="display: flex; gap: 10px; margin: 20px 0;">
                <span class="chapter-status published"><?php _e('Publicado', 'manga-admin-panel'); ?></span>
                <span class="chapter-status scheduled"><?php _e('Agendado', 'manga-admin-panel'); ?></span>
                <span class="chapter-status draft"><?php _e('Rascunho', 'manga-admin-panel'); ?></span>
            </div>
        </div>
        
        <div class="manga-admin-tab-pane">
            <h2><?php _e('Cards', 'manga-admin-panel'); ?></h2>
            
            <div class="manga-grid">
                <?php for ($i = 1; $i <= 6; $i++) : ?>
                <div class="manga-card">
                    <div class="manga-card-thumbnail">
                        <div class="manga-card-status"><?php _e('Publicado', 'manga-admin-panel'); ?></div>
                        <img src="https://via.placeholder.com/300x400" alt="Manga Thumbnail">
                    </div>
                    <div class="manga-card-content">
                        <div class="manga-card-header">
                            <h3 class="manga-card-title"><?php _e('Título do Mangá', 'manga-admin-panel'); ?> <?php echo $i; ?></h3>
                            <div class="manga-card-actions">
                                <button class="manga-btn manga-btn-icon manga-btn-primary"><i class="fas fa-edit"></i></button>
                                <button class="manga-btn manga-btn-icon manga-btn-danger"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="manga-card-meta">
                            <span><i class="fas fa-book"></i> 24 <?php _e('capítulos', 'manga-admin-panel'); ?></span>
                            <span><i class="fas fa-eye"></i> 1.5K <?php _e('views', 'manga-admin-panel'); ?></span>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="manga-admin-tab-pane">
            <h2><?php _e('Tabelas', 'manga-admin-panel'); ?></h2>
            
            <table class="manga-table">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Título', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Autor', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Capítulos', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Status', 'manga-admin-panel'); ?></th>
                        <th><?php _e('Ações', 'manga-admin-panel'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php _e('Título do Mangá', 'manga-admin-panel'); ?> <?php echo $i; ?></td>
                        <td>Autor <?php echo $i; ?></td>
                        <td><?php echo rand(10, 50); ?></td>
                        <td>
                            <?php if ($i % 3 == 0) : ?>
                                <span class="chapter-status draft"><?php _e('Rascunho', 'manga-admin-panel'); ?></span>
                            <?php elseif ($i % 3 == 1) : ?>
                                <span class="chapter-status published"><?php _e('Publicado', 'manga-admin-panel'); ?></span>
                            <?php else : ?>
                                <span class="chapter-status scheduled"><?php _e('Agendado', 'manga-admin-panel'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="manga-card-actions">
                                <button class="manga-btn manga-btn-icon manga-btn-sm manga-btn-primary"><i class="fas fa-edit"></i></button>
                                <button class="manga-btn manga-btn-icon manga-btn-sm manga-btn-danger"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
        
        <div class="manga-admin-tab-pane">
            <h2><?php _e('Formulários', 'manga-admin-panel'); ?></h2>
            
            <form class="manga-form">
                <div class="manga-form-group">
                    <label class="manga-form-label"><?php _e('Título do Mangá', 'manga-admin-panel'); ?></label>
                    <input type="text" class="manga-form-control" placeholder="<?php _e('Digite o título do mangá', 'manga-admin-panel'); ?>">
                </div>
                
                <div class="manga-form-group">
                    <label class="manga-form-label"><?php _e('Descrição', 'manga-admin-panel'); ?></label>
                    <textarea class="manga-form-control" rows="4" placeholder="<?php _e('Digite a descrição do mangá', 'manga-admin-panel'); ?>"></textarea>
                </div>
                
                <div class="manga-form-group">
                    <label class="manga-form-label"><?php _e('Categoria', 'manga-admin-panel'); ?></label>
                    <select class="manga-form-control">
                        <option value=""><?php _e('Selecione uma categoria', 'manga-admin-panel'); ?></option>
                        <option value="acao"><?php _e('Ação', 'manga-admin-panel'); ?></option>
                        <option value="aventura"><?php _e('Aventura', 'manga-admin-panel'); ?></option>
                        <option value="comedia"><?php _e('Comédia', 'manga-admin-panel'); ?></option>
                        <option value="drama"><?php _e('Drama', 'manga-admin-panel'); ?></option>
                        <option value="ficcao"><?php _e('Ficção Científica', 'manga-admin-panel'); ?></option>
                    </select>
                </div>
                
                <div class="manga-form-group">
                    <label class="manga-form-label"><?php _e('Status', 'manga-admin-panel'); ?></label>
                    <div style="display: flex; gap: 15px;">
                        <label>
                            <input type="radio" name="status" value="published" checked>
                            <?php _e('Publicado', 'manga-admin-panel'); ?>
                        </label>
                        <label>
                            <input type="radio" name="status" value="draft">
                            <?php _e('Rascunho', 'manga-admin-panel'); ?>
                        </label>
                        <label>
                            <input type="radio" name="status" value="scheduled">
                            <?php _e('Agendado', 'manga-admin-panel'); ?>
                        </label>
                    </div>
                </div>
                
                <div class="manga-form-group">
                    <label class="manga-form-label"><?php _e('Tags', 'manga-admin-panel'); ?></label>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <label>
                            <input type="checkbox" name="tags[]" value="popular">
                            <?php _e('Popular', 'manga-admin-panel'); ?>
                        </label>
                        <label>
                            <input type="checkbox" name="tags[]" value="trending">
                            <?php _e('Em Alta', 'manga-admin-panel'); ?>
                        </label>
                        <label>
                            <input type="checkbox" name="tags[]" value="new">
                            <?php _e('Novo', 'manga-admin-panel'); ?>
                        </label>
                        <label>
                            <input type="checkbox" name="tags[]" value="completed">
                            <?php _e('Completo', 'manga-admin-panel'); ?>
                        </label>
                    </div>
                </div>
                
                <div class="manga-form-group">
                    <label class="manga-form-label"><?php _e('Capa do Mangá', 'manga-admin-panel'); ?></label>
                    <div class="manga-file-upload">
                        <div class="manga-file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <p><?php _e('Arraste e solte imagens aqui ou clique para selecionar arquivos', 'manga-admin-panel'); ?></p>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="manga-btn manga-btn-primary"><?php _e('Salvar Mangá', 'manga-admin-panel'); ?></button>
                    <button type="button" class="manga-btn manga-btn-secondary"><?php _e('Cancelar', 'manga-admin-panel'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tabs
    $('.manga-admin-tab').on('click', function() {
        const index = $(this).index();
        
        $('.manga-admin-tab').removeClass('active');
        $(this).addClass('active');
        
        $('.manga-admin-tab-pane').removeClass('active');
        $('.manga-admin-tab-pane').eq(index).addClass('active');
    });
});
</script>