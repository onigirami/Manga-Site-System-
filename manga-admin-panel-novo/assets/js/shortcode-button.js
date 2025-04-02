(function() {
    tinymce.PluginManager.add('manga_admin_shortcodes', function(editor, url) {
        editor.addButton('manga_admin_shortcodes', {
            text: 'Manga Admin',
            icon: 'wp_code',
            tooltip: 'Inserir Shortcode do Manga Admin Panel',
            onclick: function() {
                editor.windowManager.open({
                    title: 'Inserir Shortcode do Manga Admin Panel',
                    body: [
                        {
                            type: 'listbox',
                            name: 'shortcode',
                            label: 'Escolha um Shortcode',
                            values: window.mangaAdminShortcodes,
                            value: window.mangaAdminShortcodes[0].value
                        }
                    ],
                    onsubmit: function(e) {
                        editor.insertContent(e.data.shortcode);
                    }
                });
            }
        });
    });
})();
