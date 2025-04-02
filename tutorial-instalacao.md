# Tutorial de Instalação do Manga Admin Panel

## Requisitos
- WordPress 5.6 ou superior
- PHP 7.2 ou superior
- Os seguintes plugins instalados e ativos:
  - Madara Core
  - WP Manga Chapter Scheduler
  - WP Manga Custom Fields
  - WP Manga Member Upload PRO

## Passos para Instalação

### Método 1: Upload via Painel Administrativo do WordPress

1. Faça login no seu painel de administração do WordPress.
2. Navegue até "Plugins" > "Adicionar novo".
3. Clique no botão "Enviar Plugin" localizado no topo da página.
4. Clique em "Escolher arquivo" e selecione o arquivo `manga-admin-panel.zip` que você baixou.
5. Clique em "Instalar agora".
6. Após a instalação, clique em "Ativar plugin".

### Método 2: Upload via FTP

1. Descompacte o arquivo `manga-admin-panel.zip` em seu computador.
2. Usando um cliente FTP, conecte-se ao seu servidor web.
3. Navegue até o diretório `/wp-content/plugins/` em seu site WordPress.
4. Faça upload da pasta `manga-admin-panel` para este diretório.
5. Volte ao painel de administração do WordPress.
6. Navegue até "Plugins" e ative "Manga Admin Panel".

## Configuração Inicial

1. Após a ativação, o plugin criará automaticamente uma página chamada "Manga Admin" com o painel de administração.
2. Também criará um novo papel de usuário chamado "Manga Editor" que pode ser atribuído a usuários específicos.

## Como Usar os Shortcodes

Os seguintes shortcodes estão disponíveis para uso em qualquer página ou post:

### Painel Completo
```
[manga_admin_panel]
```
Insere o painel de administração completo.

### Dashboard de Mangás
```
[manga_dashboard]
```
Insere apenas a lista de mangás e estatísticas rápidas.

### Gerenciador de Capítulos
```
[manga_chapter_manager manga_id="ID_DO_MANGA"]
```
Insere o gerenciador de capítulos para um mangá específico. Se manga_id for omitido, mostra um seletor de mangá.

### Criação/Edição de Mangá
```
[manga_creator]
```
Insere o formulário para criar ou editar mangás.

### Upload de Capítulos
```
[manga_upload manga_id="ID_DO_MANGA" show_title="yes" max_files="50" allow_scheduling="yes"]
```
Insere um formulário para upload de capítulos.

### Leitor de Mangá
```
[manga_reader manga_id="ID_DO_MANGA" chapter_id="ID_DO_CAPITULO" show_navigation="yes" show_comments="yes" reading_direction="default"]
```
Insere um leitor de mangá simples.

### Perfil do Usuário
```
[manga_user_profile show_avatar="yes" show_stats="yes" show_recent="yes" recent_count="5" show_edit_profile="yes"]
```
Exibe o perfil do usuário logado com seus mangás e estatísticas.

### Conteúdo Condicional
```
[manga_user state="logged_in"]
   Conteúdo visível apenas para usuários logados
[/manga_user]
```
Exibe conteúdo condicionalmente com base no estado de login do usuário. Opções de state: logged_in, logged_out, can_manage.

## Integração com Elementor

Para usar o widget do Manga Admin Panel no Elementor:

1. Certifique-se de que o Elementor está instalado e ativado.
2. Edite uma página com o Elementor.
3. Na barra lateral de widgets, procure por "Manga Admin Panel" na seção de widgets.
4. Arraste o widget para a sua página e configure suas opções.

## Solução de Problemas

Se você encontrar qualquer problema ao usar o plugin:

1. Verifique se todos os plugins necessários estão instalados e ativos.
2. Certifique-se de que seu site WordPress e PHP estão nas versões mínimas requeridas.
3. Desative temporariamente outros plugins para verificar conflitos.
4. Se o problema persistir, entre em contato com o suporte.

## Atribuindo Permissões a Usuários

Existem várias maneiras de conceder acesso às funcionalidades do painel:

1. **Papel de Usuário**: Os papéis Administrator, Editor, Author e Manga Editor têm acesso automático.
2. **Metadado de Usuário**: Adicione o meta `can_manage_manga` com valor `yes` a qualquer usuário.
3. **Autor do Mangá**: O autor original de um mangá tem permissão para editá-lo.
4. **Capacidade Personalizada**: Usuários com a capacidade `manage_manga` têm acesso completo.

Para adicionar o metadado a um usuário através de código:
```php
update_user_meta(USER_ID, 'can_manage_manga', 'yes');
```

## Customização do Plugin

O plugin foi projetado para ser altamente personalizável. Você pode:

- Modificar os arquivos CSS para corresponder ao seu tema
- Adicionar novas funcionalidades através de hooks do WordPress
- Personalizar os templates na pasta `templates/`
- Estender o widget do Elementor para adicionar mais opções

## Atualizações Futuras

Para receber notificações sobre atualizações e novas funcionalidades:

1. Mantenha seu endereço de e-mail atualizado nas configurações do WordPress.
2. Verifique regularmente o painel de plugins para atualizações disponíveis.

---

Por favor, note que este plugin requer os plugins WP Manga para funcionar corretamente. Certifique-se de que todos os plugins necessários estão instalados e ativos antes de usar o Manga Admin Panel.