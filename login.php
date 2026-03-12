<?php 
require_once 'config.php'; 
// Se já estiver logado, joga pro painel
if(isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: dashboard.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Acessar Sistema</h2>
        <input type="email" id="email" placeholder="E-mail">
        <input type="password" id="senha" placeholder="Senha">
        
        <button class="btn-primary" id="btnLogin">Entrar</button>
        <button class="btn-google" id="btnGoogle">Entrar com Google</button>
        
        <p id="msg" class="msg"></p>
        
        <a href="cadastro.php" class="btn-link">Não tem conta? Cadastre-se</a><br>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
        import { getAuth, signInWithEmailAndPassword, signInWithPopup, GoogleAuthProvider, signOut } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
        import { getFirestore, doc, setDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js";

        const app = initializeApp({
            apiKey: "<?php echo FIREBASE_API_KEY; ?>",
            authDomain: "<?php echo FIREBASE_AUTH_DOMAIN; ?>",
            projectId: "<?php echo FIREBASE_PROJECT_ID; ?>"
        });
        const auth = getAuth(app);
        const db = getFirestore(app);
        const provider = new GoogleAuthProvider();
        const msg = document.getElementById('msg');

        // Função para avisar o PHP que o login deu certo
        async function criarSessaoPHP(user) {
            await fetch('salvar_sessao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ uid: user.uid, email: user.email })
            });
            window.location.href = "dashboard.php";
        }

        // LOGIN COM EMAIL E SENHA
        document.getElementById('btnLogin').onclick = async () => {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            msg.innerText = "Aguarde..."; msg.style.color = "blue";

            try {
                const cred = await signInWithEmailAndPassword(auth, email, senha);
                
                // TRAVA DE VERIFICAÇÃO DE E-MAIL
                if (!cred.user.emailVerified) {
                    await signOut(auth);
                    msg.style.color = "red";
                    return msg.innerText = "Você precisa verificar seu e-mail primeiro!";
                }

                await criarSessaoPHP(cred.user);
            } catch (error) {
                msg.style.color = "red"; msg.innerText = "Usuário ou senha inválidos.";
            }
        };

        // LOGIN COM GOOGLE
        document.getElementById('btnGoogle').onclick = async () => {
            try {
                const result = await signInWithPopup(auth, provider);
                const user = result.user;
                
                // Salva ou atualiza no Firestore
                await setDoc(doc(db, "usuarios", user.uid), {
                    nome: user.displayName, email: user.email, provedor: "google", ultimoAcesso: serverTimestamp()
                }, { merge: true });

                await criarSessaoPHP(user);
            } catch (error) {
                msg.style.color = "red"; msg.innerText = "Erro ao fazer login com Google.";
            }
        };
    </script>
</body>
</html>