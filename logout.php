<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Saindo...</title>
</head>
<body>
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
        import { getAuth, signOut } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

        const app = initializeApp({
            apiKey: "<?php echo FIREBASE_API_KEY; ?>",
            authDomain: "<?php echo FIREBASE_AUTH_DOMAIN; ?>",
            projectId: "<?php echo FIREBASE_PROJECT_ID; ?>"
        });
        const auth = getAuth(app);

        // Desloga do Firebase e pede pro PHP limpar a sessão via GET
        signOut(auth).then(() => {
            window.location.href = "?acao=limpar_php";
        });
    </script>

    <?php
    // Quando o JS redirecionar de volta pra cá com "?acao=limpar_php", o PHP destrói a sessão
    if(isset($_GET['acao']) && $_GET['acao'] == 'limpar_php') {
        session_destroy();
        header("Location: index.php");
        exit;
    }
    ?>
</body>
</html>