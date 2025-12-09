<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_completo = trim($_POST['nome_completo']);
    $email = trim($_POST['email']);
    $usuario = trim($_POST['usuario']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Data e hora atual do cadastro
    $data_cadastro = date('Y-m-d H:i:s');

    // Foto padrão
    $foto = '../uploads/default.jpg';

    try {
        // Verificar se email já existe
        $checkEmail = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $checkEmail->bindParam(':email', $email);
        $checkEmail->execute();

        if ($checkEmail->rowCount() > 0) {
            header("Location: ../tela-login&cadastro-final/tela de cadastro/telacadastro.html?erro=email");
            exit();
        }

        // Verificar se usuário já existe
        $checkUsuario = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :usuario");
        $checkUsuario->bindParam(':usuario', $usuario);
        $checkUsuario->execute();

        if ($checkUsuario->rowCount() > 0) {
            header("Location: ../tela-login&cadastro-final/tela de cadastro/telacadastro.html?erro=usuario");
            exit();
        }

        // Inserir novo usuário
        $sql = "INSERT INTO usuarios (usuario, nome_completo, email, foto, senha,  data_cadastro)
                VALUES (:usuario, :nome_completo, :email, :foto, :senha, :data_cadastro)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':nome_completo', $nome_completo);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':foto', $foto);
        $stmt->bindParam(':senha', $senha);
        $stmt->bindParam(':data_cadastro', $data_cadastro);
        $stmt->execute();

        // Redireciona para login com sucesso
        header("Location: ../tela-login&cadastro-final/tela-login/telalogin.html?sucesso=1");
        exit();

    } catch (PDOException $e) {
        // Log do erro (em produção, use um sistema de log adequado)
        error_log("Erro ao cadastrar: " . $e->getMessage());
        header("Location: ../tela-login&cadastro-final/tela de cadastro/telacadastro.html?erro=1");
        exit();
    }
}
?>