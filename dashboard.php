<?php
require_once 'config.php';
verificarLogado(); // Esta função lá do config.php expulsa quem não tá logado
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Um pequeno ajuste extra só para o painel */
        body { align-items: flex-start; padding-top: 80px; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #3498db; text-align: left; }
    </style>
</head>
<body>

    <div class="dash-container">
        <h1>Bem-vindo ao Sistema!</h1>
        <p>Área restrita e protegida por sessão PHP + Firebase.</p>
        
        <div class="info-box">
            <p><strong>Seu E-mail:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
            <p><strong>Seu ID no Banco:</strong> <?php echo htmlspecialchars($_SESSION['uid']); ?></p>
        </div>
        
        <button class="btn-google" style="background-color: #7f8c8d;" onclick="window.location.href='logout.php'">Sair da Conta</button>
    </div>

</body>
</html>