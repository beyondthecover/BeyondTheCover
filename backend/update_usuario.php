<?php
session_start();
require_once 'db.php';

// Verifica se está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../tela-login&cadastro-final/tela-login/telalogin.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = $_POST['id'];
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    // Verificar email duplicado
    $sql = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
    $sql->bindParam(':email', $email);
    $sql->bindParam(':id', $id);
    $sql->execute();

    if ($sql->rowCount() > 0) {
        // Email já usado por outro usuário
        header("Location: ../perfil.php?erro=email");
        exit();
    }

    // Se a senha veio vazia, não altera
    if (empty($senha)) {
        $query = "UPDATE usuarios 
                  SET nome_completo = :nome, email = :email
                  WHERE id = :id";
        $stmt = $pdo->prepare($query);

    } else {
        // Atualiza também a senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $query = "UPDATE usuarios 
                  SET nome_completo = :nome, email = :email, senha = :senha
                  WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':senha', $senhaHash);
    }

    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {

        // Atualizar sessão
        $_SESSION['nome_completo'] = $nome;
        $_SESSION['email'] = $email;

        header("Location: ../perfil.php?sucesso=1");
        exit();
    } else {
        echo "Erro ao atualizar usuário.";
    }
}
?>