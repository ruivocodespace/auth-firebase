<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - Sistema</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Criar Conta</h2>
        <input type="text" id="nome" placeholder="Nome Completo">
        <input type="email" id="email" placeholder="E-mail">
        <input type="password" id="senha" placeholder="Senha (mín. 6 chars)">
        
        <button class="btn-primary" id="btnCadastrar">Cadastrar</button>
        <p id="msg" class="msg"></p>
        
        <a href="index.php" class="btn-link">Já tenho uma conta (Fazer Login)</a>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
        import { getAuth, createUserWithEmailAndPassword, sendEmailVerification, signOut } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
        import { getFirestore, doc, setDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js";

        const app = initializeApp({
            apiKey: "<?php echo FIREBASE_API_KEY; ?>",
            authDomain: "<?php echo FIREBASE_AUTH_DOMAIN; ?>",
            projectId: "<?php echo FIREBASE_PROJECT_ID; ?>"
        });
        const auth = getAuth(app);
        const db = getFirestore(app);

        document.getElementById('btnCadastrar').onclick = async () => {
            const nome = document.getElementById('nome').value;
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const msg = document.getElementById('msg');

            if(!nome || !email || !senha) return msg.innerText = "Preencha todos os campos!";
            msg.style.color = "blue"; msg.innerText = "Processando...";

            try {
                // 1. Cria usuário
                const userCredential = await createUserWithEmailAndPassword(auth, email, senha);
                const user = userCredential.user;

                // 2. Envia e-mail de verificação
                await sendEmailVerification(user);

                // 3. Salva no banco (Firestore)
                await setDoc(doc(db, "usuarios", user.uid), {
                    nome: nome, email: email, provedor: "senha", criadoEm: serverTimestamp()
                });

                // 4. Desloga para impedir acesso sem verificar e-mail
                await signOut(auth);

                msg.style.color = "green";
                msg.innerHTML = "Sucesso! <strong>Verifique seu e-mail</strong> antes de logar.";
                setTimeout(() => window.location.href = "index.php", 3000);

            } catch (error) {
                msg.style.color = "red";
                msg.innerText = "Erro: " + error.message;
            }
        };
    </script>
</body>
</html>