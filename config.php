<?php
// config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Suas credenciais do Firebase
define('FIREBASE_API_KEY', "AIzaSyDKVi7gs6AE3NcU7kZhDNtwMlcoBOqWvqs");
define('FIREBASE_AUTH_DOMAIN', "authfirebase-a1da0.firebaseapp.com");
define('FIREBASE_PROJECT_ID', "authfirebase-a1da0");
define('FIREBASE_STORAGE_BUCKET', "authfirebase-a1da0.firebasestorage.app");
define('FIREBASE_SENDER_ID', "389722877524");
define('FIREBASE_APP_ID', "1:389722877524:web:b3759bfb63693a0bfa07c1");

// Função para proteger páginas fechadas
function verificarLogado() {
    if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
        header("Location: index.php");
        exit;
    }
}
?>