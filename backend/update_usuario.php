      <?php
session_start();
require_once 'db.php';

// só permite se usuário estiver logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../tela-login/telalogin.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_SESSION['id_usuario'];

    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $senha = $_POST['senha'] ?? '';

    try {
        // Se o campo senha estiver vazio, não atualiza a senha
        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios 
                    SET nome = :nome, email = :email, telefone = :telefone, senha = :senha 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':senha', $senha_hash);
        } else {
            $sql = "UPDATE usuarios 
                    SET nome = :nome, email = :email, telefone = :telefone 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
        }

        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);

        $stmt->execute();

      header("Location: /tela-login-cadastro/perfil.php?atualizado=1");
exit;
    } catch (PDOException $e) {
        echo "Erro ao atualizar perfil: " . $e->getMessage();
    }
}
?>

    
