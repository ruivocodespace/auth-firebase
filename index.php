<?php 
require_once 'config.php'; 

// Se já estiver logado no PHP, pula direto para o dashboard
if(isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: dashboard.php"); 
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Seguro - Sistema</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

    <div class="auth-container">
        <h2>Acessar Sistema</h2>
        
        <input type="email" id="email" placeholder="E-mail">
        <input type="password" id="senha" placeholder="Senha">
        
        <button class="btn-primary" id="btnLogin">Entrar</button>
        <button class="btn-google" id="btnGoogle">Entrar com Google</button>
        
        <button class="btn-link" id="btnReset" style="width: auto; padding: 5px;">Esqueci minha senha</button>
        
        <p id="msg" class="msg"></p>
        
        <br><br>
        <a href="cadastro.php" class="btn-link">Não tem uma conta? Cadastre-se</a>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
        // NOVO: Adicionado o sendPasswordResetEmail na importação
        import { getAuth, signInWithEmailAndPassword, signInWithPopup, GoogleAuthProvider, signOut, sendPasswordResetEmail } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
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

        async function criarSessaoPHP(user) {
            await fetch('salvar_sessao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ uid: user.uid, email: user.email })
            });
            window.location.href = "dashboard.php";
        }

        // --- LOGIN COM EMAIL E SENHA ---
        document.getElementById('btnLogin').onclick = async () => {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            
            if(!email || !senha) {
                msg.style.color = "red";
                return msg.innerText = "Preencha e-mail e senha!";
            }

            msg.innerText = "Aguarde..."; msg.style.color = "blue";

            try {
                const cred = await signInWithEmailAndPassword(auth, email, senha);
                
                if (!cred.user.emailVerified) {
                    await signOut(auth);
                    msg.style.color = "red";
                    return msg.innerText = "Acesso Negado! Verifique seu e-mail primeiro.";
                }

                await criarSessaoPHP(cred.user);
            } catch (error) {
                msg.style.color = "red"; 
                msg.innerText = "Usuário ou senha inválidos.";
            }
        };

        // --- LOGIN COM GOOGLE ---
        document.getElementById('btnGoogle').onclick = async () => {
            try {
                const result = await signInWithPopup(auth, provider);
                const user = result.user;
                
                await setDoc(doc(db, "usuarios", user.uid), {
                    nome: user.displayName, 
                    email: user.email, 
                    provedor: "google", 
                    ultimoAcesso: serverTimestamp()
                }, { merge: true });

                await criarSessaoPHP(user);
            } catch (error) {
                msg.style.color = "red"; 
                msg.innerText = "Erro ao fazer login com Google.";
            }
        };

        // --- NOVO: RECUPERAR SENHA ---
        document.getElementById('btnReset').onclick = async () => {
            const email = document.getElementById('email').value;
            
            if(!email) {
                msg.style.color = "orange";
                return msg.innerText = "Digite seu e-mail no campo acima primeiro!";
            }

            msg.innerText = "Enviando e-mail..."; msg.style.color = "blue";

            try {
                await sendPasswordResetEmail(auth, email);
                msg.style.color = "green";
                msg.innerText = "E-mail de redefinição enviado! Verifique sua caixa de entrada.";
            } catch (error) {
                msg.style.color = "red";
                // Tratamento amigável para erro comum
                if(error.code === 'auth/user-not-found') {
                    msg.innerText = "Este e-mail não está cadastrado.";
                } else {
                    msg.innerText = "Erro: " + error.message;
                }
            }
        };
    </script>
</body>
</html>