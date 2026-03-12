<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifique seu E-mail - Sistema</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .icon-mail {
            font-size: 60px;
            margin-bottom: 10px;
        }
        .aviso-texto {
            color: #555;
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 25px;
        }
        .email-destaque {
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="icon-mail">✉️</div>
        <h2>Quase lá!</h2>
        
        <p class="aviso-texto">
            Enviamos um link de confirmação para o e-mail:<br>
            <span class="email-destaque">
                <?php 
                    // Pega o e-mail da URL por segurança básica (se não tiver, mostra texto genérico)
                    echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : 'seu e-mail'; 
                ?>
            </span>
        </p>
        
        <p class="aviso-texto" style="font-size: 13px; color: #e74c3c;">
            <em>Dica: Verifique também a sua caixa de Spam ou Lixo Eletrônico.</em>
        </p>
        
        <button class="btn-primary" onclick="window.location.href='index.php'">Já confirmei, ir para o Login</button>
    </div>

</body>
</html>