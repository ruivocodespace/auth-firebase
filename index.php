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
    
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

    <div class="auth-container">
        <h2>Acessar Sistema</h2>
        
        <input type="email" id="email" placeholder="E-mail">
        <input type="password" id="senha" placeholder="Senha">
        
        <div class="g-recaptcha" data-sitekey="6LdCjYcsAAAAACm6fIpG6xO8qQpd9P3HdIzPYthp" style="margin-bottom: 15px; display: flex; justify-content: center;"></div>
        
        <button class="btn-primary" id="btnLogin">Entrar</button>
        <button class="btn-google" id="btnGoogle">Entrar com Google</button>
        
        <button class="btn-link" id="btnReset" style="width: auto; padding: 5px;">Esqueci minha senha</button>
        
        <p id="msg" class="msg"></p>
        
        <br><br>
        <a href="cadastro.php" class="btn-link">Não tem uma conta? Cadastre-se</a>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
        import { getAuth, signInWithEmailAndPassword, signInWithPopup, GoogleAuthProvider, signOut, sendPasswordResetEmail } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";
        import { getFirestore, doc, setDoc, getDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js";

        // Inicializa o Firebase com as chaves do seu config.php
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

        // --- LOGIN COM EMAIL, SENHA E CAPTCHA ---
        document.getElementById('btnLogin').onclick = async () => {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            
            if(!email || !senha) {
                msg.style.color = "red";
                return msg.innerText = "Preencha e-mail e senha!";
            }

            // 1. Verifica se o usuário clicou no Captcha
            const recaptchaResponse = grecaptcha.getResponse();
            if(recaptchaResponse.length === 0) {
                msg.style.color = "orange";
                return msg.innerText = "Por favor, marque a caixa 'Não sou um robô'.";
            }

            msg.innerText = "Validando segurança..."; 
            msg.style.color = "blue";

            try {
                // 2. Valida o Captcha no seu servidor PHP (`verificar_captcha.php`)
                const respostaPHP = await fetch('verificar_captcha.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: recaptchaResponse })
                });
                
                const resultadoCaptcha = await respostaPHP.json();

                if (!resultadoCaptcha.sucesso) {
                    msg.style.color = "red";
                    grecaptcha.reset(); 
                    return msg.innerText = "Falha na verificação de segurança.";
                }

                // 3. Se o Captcha passou, tenta logar no Firebase
                msg.innerText = "Autenticando...";
                const cred = await signInWithEmailAndPassword(auth, email, senha);
                const user = cred.user;
                
                // 4. Trava de Segurança: Verifica se o e-mail foi validado
                if (!user.emailVerified) {
                    await signOut(auth);
                    grecaptcha.reset();
                    msg.style.color = "red";
                    return msg.innerText = "Acesso Negado! Verifique seu e-mail primeiro.";
                }

                // 5. NOVA LÓGICA: Salvar no Firestore só no primeiro login
                msg.innerText = "Preparando seu painel...";
                const userRef = doc(db, "usuarios", user.uid);
                const docSnap = await getDoc(userRef);

                // Se não existir documento, é o primeiro acesso pós-verificação
                if (!docSnap.exists()) {
                    await setDoc(userRef, {
                        nome: user.displayName || "Usuário", // Pega do perfil (salvo no cadastro)
                        email: user.email,
                        provedor: "senha",
                        criadoEm: serverTimestamp()
                    });
                }

                // 6. Cria a Sessão no PHP e entra
                await criarSessaoPHP(user);

            } catch (error) {
                console.error(error);
                msg.style.color = "red"; 
                grecaptcha.reset(); // Reseta a caixinha se errar a senha
                
                if (error.code === 'auth/invalid-credential') {
                    msg.innerText = "Usuário ou senha inválidos.";
                } else {
                    msg.innerText = "Erro ao fazer login: " + error.message;
                }
            }
        };

        // --- LOGIN COM GOOGLE ---
        document.getElementById('btnGoogle').onclick = async () => {
            try {
                const result = await signInWithPopup(auth, provider);
                const user = result.user;
                
                // Para o Google, sempre fazemos um merge para atualizar o último acesso ou salvar se for novo
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

        // --- RECUPERAR SENHA ---
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