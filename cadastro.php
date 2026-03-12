<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - Sistema</title>
    <link rel="stylesheet" href="style.css">
    
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="auth-container">
        <h2>Criar Conta</h2>
        <input type="text" id="nome" placeholder="Nome Completo">
        <input type="email" id="email" placeholder="E-mail">
        <input type="password" id="senha" placeholder="Senha (mín. 6 chars)">
        
        <div class="g-recaptcha" data-sitekey="6LdCjYcsAAAAACm6fIpG6xO8qQpd9P3HdIzPYthp" style="margin-bottom: 15px; display: flex; justify-content: center;"></div>
        
        <button class="btn-primary" id="btnCadastrar">Cadastrar</button>
        <p id="msg" class="msg"></p>
        
        <a href="index.php" class="btn-link">Já tenho uma conta (Fazer Login)</a>
    </div>

    <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
    // NOVO: Adicionamos o updateProfile aqui
    import { getAuth, createUserWithEmailAndPassword, sendEmailVerification, signOut, updateProfile } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

    const app = initializeApp({
        apiKey: "<?php echo FIREBASE_API_KEY; ?>",
        authDomain: "<?php echo FIREBASE_AUTH_DOMAIN; ?>",
        projectId: "<?php echo FIREBASE_PROJECT_ID; ?>"
    });
    const auth = getAuth(app);

    document.getElementById('btnCadastrar').onclick = async () => {
        const nome = document.getElementById('nome').value;
        const email = document.getElementById('email').value;
        const senha = document.getElementById('senha').value;
        const msg = document.getElementById('msg');

        if(!nome || !email || !senha) return msg.innerText = "Preencha todos os campos!";
        
        const recaptchaResponse = grecaptcha.getResponse();
        if(recaptchaResponse.length === 0) {
            msg.style.color = "orange";
            return msg.innerText = "Por favor, marque a caixa 'Não sou um robô'.";
        }

        msg.style.color = "green";
            msg.innerText = "Sucesso! Redirecionando...";
            // Redireciona para a tela de verificação passando o e-mail na URL
            window.location.href = "verificacao.php?email=" + encodeURIComponent(email);

        try {
            const respostaPHP = await fetch('verificar_captcha.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: recaptchaResponse })
            });
            const resultadoCaptcha = await respostaPHP.json();

            if (!resultadoCaptcha.sucesso) {
                msg.style.color = "red"; grecaptcha.reset();
                return msg.innerText = "Falha na verificação de segurança.";
            }

            msg.innerText = "Criando conta...";
            
            // 1. Cria o usuário no Auth
            const userCredential = await createUserWithEmailAndPassword(auth, email, senha);
            const user = userCredential.user;

            // 2. Salva o Nome no perfil interno do Auth (NÃO NO BANCO AINDA)
            await updateProfile(user, { displayName: nome });

            // 3. Envia e-mail de verificação e desloga
            await sendEmailVerification(user);
            await signOut(auth);

            msg.style.color = "green";
            msg.innerHTML = "Sucesso! <strong>Verifique seu e-mail</strong> antes de logar.";
            setTimeout(() => window.location.href = "index.php", 3000);

        } catch (error) {
            msg.style.color = "red"; grecaptcha.reset();
            if(error.code === 'auth/email-already-in-use') msg.innerText = "E-mail já cadastrado!";
            else if(error.code === 'auth/weak-password') msg.innerText = "Senha muito fraca!";
            else msg.innerText = "Erro: " + error.message;
        }
    };
    </script>
</body>
</html>