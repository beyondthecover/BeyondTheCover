<?php
session_start();
require_once 'db.php'; // conexão PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    // Verificação básica
    if ($email === '' || $senha === '') {
        header("Location: ../tela-login&cadastro-final/tela-login/telalogin.html");
        exit;
    }

    // Buscar usuário pelo e-mail
    $sql = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
    $sql->bindParam(':email', $email, PDO::PARAM_STR);
    $sql->execute();

    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    // Usuário encontrado e senha confere?
    if ($usuario && password_verify($senha, $usuario['senha'])) {

        // Criar sessão
        $_SESSION['id_usuario'] = $usuario['id']; 
        $_SESSION['usuario'] = $usuario['usuario'];
        $_SESSION['nome_completo'] = $usuario['nome_completo'];
        $_SESSION['email'] = $usuario['email'];

        // Redireciona para a home
        header("Location: ../index.php");
        exit;

    } else {
        // erro de login
        header("Location: telalogin.php?erro=1");
        exit;
    }
}
?>
