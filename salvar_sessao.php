<?php
// salvar_sessao.php
require_once 'config.php';

// Recebe os dados via POST do JavaScript (Fetch API)
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['uid']) && isset($data['email'])) {
    $_SESSION['uid'] = $data['uid'];
    $_SESSION['email'] = $data['email'];
    $_SESSION['logado'] = true;
    
    echo json_encode(['status' => 'sucesso']);
} else {
    echo json_encode(['status' => 'erro']);
}
?>