=== Manga Admin Panel ===
Contributors: developer
Tags: manga, admin, upload, chapters, comics, elementor
Requires at least: 5.6
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Uma interface WordPress personalizada para usuários privilegiados gerenciarem conteúdo de mangá compatível com Elementor e plugins de mangá existentes.

== Description ==

O Manga Admin Panel é um plugin WordPress que oferece uma interface intuitiva e completa para gerenciamento de mangás diretamente pelo frontend do site, eliminando a necessidade de acessar o painel administrativo do WordPress.

Este plugin é totalmente compatível com o tema Madata e os seguintes plugins:
- WP Manga Member Upload PRO
- WP Manga Chapter Scheduler
- WP Manga Custom Fields

**Características Principais**

* **Dashboard Completo**: Visão geral de todos os seus mangás, estatísticas e ferramentas de gerenciamento.
* **Criação e Edição de Mangás**: Interface amigável para criar e editar detalhes dos mangás.
* **Gerenciamento de Capítulos**: Adicionar, editar, excluir e programar capítulos facilmente.
* **Upload Simplificado**: Upload de capítulos por imagens individuais ou arquivo ZIP.
* **Controle de Acesso**: Permissões sofisticadas para determinar quem pode gerenciar mangás.
* **Design Responsivo**: Interface adaptável a diferentes tamanhos de tela.
* **Integração com Elementor**: Widgets específicos para o Elementor.
* **Shortcodes Versáteis**: Vários shortcodes para adicionar funcionalidades em qualquer página.

**Shortcodes Disponíveis**

* `[manga_admin_panel]`: Painel de administração completo
* `[manga_dashboard]`: Dashboard de mangás
* `[manga_chapter_manager]`: Gerenciador de capítulos
* `[manga_creator]`: Criação/edição de mangás
* `[manga_upload]`: Upload de capítulos
* `[manga_reader]`: Leitor de mangá simples
* `[manga_user_profile]`: Perfil do usuário
* `[manga_user]`: Conteúdo condicional baseado no login

== Installation ==

1. Faça upload do arquivo zip através do painel administrativo do WordPress (Plugins > Adicionar novo > Enviar plugin)
2. Ou extraia o zip e faça upload via FTP para a pasta `/wp-content/plugins/`
3. Ative o plugin através do menu 'Plugins' no WordPress
4. Verifique a página "Manga Admin" criada automaticamente

== Frequently Asked Questions ==

= Quais plugins são necessários para o Manga Admin Panel funcionar? =

O Manga Admin Panel requer os seguintes plugins:
- Madara Core
- WP Manga Chapter Scheduler
- WP Manga Custom Fields
- WP Manga Member Upload PRO

= Como posso dar permissão para usuários gerenciarem mangás? =

Existem várias maneiras:
1. Atribuir os papéis Administrator, Editor, Author ou Manga Editor
2. Adicionar o meta 'can_manage_manga' com valor 'yes' ao usuário
3. Fazer o usuário ser autor do mangá
4. Dar ao usuário a capacidade 'manage_manga'

= É possível usar os shortcodes em qualquer página? =

Sim, todos os shortcodes podem ser usados em qualquer página ou post do site.

== Screenshots ==

1. Dashboard principal do Manga Admin Panel
2. Interface de gerenciamento de capítulos
3. Formulário de criação/edição de mangás
4. Perfil do usuário

== Changelog ==

= 1.0.1 =
* Corrigido o problema com a função is_plugin_active()
* Melhorado o cabeçalho do plugin para integração com o WordPress
* Pequenas correções de bugs

= 1.0 =
* Versão inicial do plugin

== Upgrade Notice ==

= 1.0.1 =
Atualização importante para corrigir erros de ativação do plugin. Atualização recomendada para todos os usuários.