<?php
session_start();

// Limpa todas as variáveis da sessão
$_SESSION = array();

// Se existir cookie de sessão, remove também
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();

// Impede que o usuário volte com o botão do navegador
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Expires: 0");
header("Pragma: no-cache");

// Redireciona
header("Location: ../tela-login&cadastro-final/tela-login/telalogin.html");
exit();
?>
