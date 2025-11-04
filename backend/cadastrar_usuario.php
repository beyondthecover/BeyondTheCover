<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_completo  = $_POST['nome_completo'];
    $email = $_POST['email'];
    $usuario = $_POST['usuario'];

    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Data e hora atual do cadastro
    $data_cadastro = date('Y-m-d H:i:s');

    try {
        $sql = "INSERT INTO usuarios (usuario, nome_completo, email, senha, data_cadastro)
                VALUES (:usuario, :nome_completo, :email, :senha, :data_cadastro)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':nome_completo', $nome_completo);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);
        $stmt->bindParam(':data_cadastro', $data_cadastro);
        $stmt->execute();

        // Depois de cadastrar, redireciona para a página de login
        header("Location: ../tela-login&cadastro-final/tela-login/telalogin.html");
        exit();

    } catch (PDOException $e) {
        echo "Erro ao cadastrar: " . $e->getMessage();
    }
}
?>
