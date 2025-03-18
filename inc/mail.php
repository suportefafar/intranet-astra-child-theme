<?php
// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$html_mail_body_template = '
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Atualizações na Intranet - Faculdade de Farmácia da UFMG</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    .email-container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .header {
      background-color: #004a8d; /* UFMG brand color */
      color: #ffffff;
      text-align: center;
      padding: 20px;
    }
    .header h1 {
      margin: 0;
      font-size: 24px;
    }
    .content {
      padding: 20px;
      color: #333333;
    }
    .icon {
      text-align: center;
      margin-bottom: 20px;
    }
    .icon img {
      width: 64px;
      height: 64px;
    }
    .message {
      font-size: 16px;
      line-height: 1.6;
      margin-bottom: 20px;
    }
    .footer {
      background-color: #f1f1f1;
      text-align: center;
      padding: 10px;
      font-size: 14px;
      color: #666666;
    }
    .footer a {
      color: #004a8d;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <!-- Header -->
    <div class="header">
      <h1>Faculdade de Farmácia da UFMG</h1>
    </div>

    <!-- Content -->
    <div class="content">
      <!-- Icon Placeholder -->
      <div class="icon">
        <img src="ICON_PLACEHOLDER" alt="Ícone de Notificação">
      </div>

      <!-- Message Placeholder -->
      <div class="message">
        <p>Prezados(as) Alunos(as), Docentes e Colaboradores,</p>
        <p>MESSAGE_PLACEHOLDER</p>
        <p>Atenciosamente,<br>Equipe de Suporte da Faculdade de Farmácia da UFMG</p>
      </div>
    </div>

    <!-- Footer -->
    <div class="footer">
      <p>Este é um e-mail automático. Por favor, não responda diretamente a esta mensagem.</p>
      <p>Dúvidas? Entre em contato: <a href="mailto:suporte@farmacia.ufmg.br">suporte@farmacia.ufmg.br</a></p>
    </div>
  </div>
</body>
</html>
';