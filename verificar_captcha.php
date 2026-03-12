<?php
// verificar_captcha.php

// Sua Chave Secreta do Google reCAPTCHA (COLE AQUI)
$secretKey = "6LdCjYcsAAAAADwV8pq7Mdo9IsrtKC0tUTXuyD8J"; 

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['token'])) {
    $token = $data['token'];
    
    // Faz a requisição para os servidores do Google
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $response = file_get_contents($url . '?secret=' . $secretKey . '&response=' . $token);
    $responseData = json_decode($response);

    // Retorna o resultado para o nosso JavaScript
    if ($responseData->success) {
        echo json_encode(['sucesso' => true]);
    } else {
        echo json_encode(['sucesso' => false]);
    }
} else {
    echo json_encode(['sucesso' => false]);
}
?>