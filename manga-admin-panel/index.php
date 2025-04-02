<?php
/**
 * Manga Admin Panel Demo
 * 
 * This is a demo for the Manga Admin Panel plugin
 * In a real WordPress environment, this would be loaded within WordPress
 */

// Show a simple interface explaining the plugin since we are not in WordPress
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manga Admin Panel</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f7f7f7;
        }
        header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        h1 {
            color: #ff6b6b;
            font-size: 36px;
            margin-bottom: 10px;
        }
        h2 {
            color: #2d3436;
            font-size: 24px;
            margin-top: 30px;
            border-bottom: 2px solid #ff6b6b;
            padding-bottom: 10px;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .feature-card {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .feature-card h3 {
            color: #ff6b6b;
            margin-top: 0;
            font-size: 18px;
        }
        .screenshot {
            margin-top: 40px;
            text-align: center;
        }
        .screenshot img {
            max-width: 100%;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 30px 0;
            border: 1px solid #ffeeba;
        }
    </style>
</head>
<body>
    <header>
        <h1>Manga Admin Panel</h1>
        <p>Interface personalizada para usuários privilegiados gerenciarem conteúdo de mangá</p>
    </header>

    <div class="container">
        <div class="warning">
            <strong>Nota:</strong> Este é um ambiente de demonstração. O Manga Admin Panel é um plugin para WordPress que precisa ser executado dentro de um ambiente WordPress completo com os plugins necessários.
        </div>

        <h2>Sobre o Plugin</h2>
        <p>O Manga Admin Panel oferece uma interface personalizada e intuitiva para gerenciar conteúdo de mangá no WordPress. Criado especificamente para funcionar com o tema Madata e os plugins WP Manga Member Upload PRO, WP Manga Chapter Scheduler e WP Manga Custom Fields.</p>

        <h2>Recursos Principais</h2>
        <div class="features">
            <div class="feature-card">
                <h3>Gerenciamento de Mangás</h3>
                <p>Interface intuitiva para criar, editar e excluir mangás, incluindo gerenciamento de gêneros, tags, autores e artistas.</p>
            </div>
            <div class="feature-card">
                <h3>Gerenciamento de Capítulos</h3>
                <p>Upload e gerenciamento de capítulos com suporte para imagens, organização automática e edição de metadados.</p>
            </div>
            <div class="feature-card">
                <h3>Agendamento de Lançamentos</h3>
                <p>Agende capítulos para serem publicados automaticamente em datas específicas, com notificações para assinantes.</p>
            </div>
            <div class="feature-card">
                <h3>Campos Personalizados</h3>
                <p>Adicione metadados personalizados aos seus mangás com suporte para vários tipos de campo, como texto, número, seleção e mais.</p>
            </div>
            <div class="feature-card">
                <h3>Gerenciador de Arquivos</h3>
                <p>Gerencie facilmente arquivos de capítulos e imagens com ferramentas de otimização automática.</p>
            </div>
            <div class="feature-card">
                <h3>Integração com Elementor</h3>
                <p>Widget personalizado para Elementor, permitindo integração completa com páginas e temas.</p>
            </div>
        </div>

        <h2>Como Usar</h2>
        <p>O Manga Admin Panel é projetado para ser usado dentro do WordPress e requer os seguintes plugins:</p>
        <ul>
            <li>Madara Core</li>
            <li>WP Manga Member Upload PRO</li>
            <li>WP Manga Chapter Scheduler</li>
            <li>WP Manga Custom Fields</li>
        </ul>
        <p>Uma vez instalado e ativado, o plugin adiciona um novo modelo de página "Manga Admin Dashboard" que pode ser usado para criar uma página com o painel administrativo.</p>

        <div class="screenshot">
            <h2>Prévia da Interface</h2>
            <p>Abaixo está uma prévia da interface do Manga Admin Panel em um ambiente WordPress:</p>
            <img src="assets/images/dashboard-preview.png" alt="Prévia do Manga Admin Panel">
        </div>
    </div>
</body>
</html>

    <script>
        // This is just a demo script to show how it would work in a real WordPress environment
        document.addEventListener('DOMContentLoaded', function() {
            // Add second screenshot
            const screenshotDiv = document.querySelector('.screenshot');
            const additionalImg = document.createElement('img');
            additionalImg.src = 'assets/images/chapter-manager-preview.png';
            additionalImg.alt = 'Prévia do Gerenciador de Capítulos';
            additionalImg.style.marginTop = '20px';
            
            const captionP = document.createElement('p');
            captionP.textContent = 'Gerenciador de capítulos com upload de imagens e organização automática';
            
            screenshotDiv.appendChild(document.createElement('br'));
            screenshotDiv.appendChild(captionP);
            screenshotDiv.appendChild(additionalImg);
        });
    </script>
