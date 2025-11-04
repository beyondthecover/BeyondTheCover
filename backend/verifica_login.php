<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $sql = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
    $sql->bindParam(':email', $email, PDO::PARAM_STR);
    $sql->execute();

    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['id_usuario'] = $usuario['id']; 
        $_SESSION['email'] = $usuario['email'];
        header("Location: ../index.html");
        exit;
    } else {
        // Redireciona com parâmetro de erro
        header("Location: ../tela-login/telalogin.php?erro=1");
        exit;
    }
}
?>
