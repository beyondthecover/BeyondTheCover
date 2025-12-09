<?php
session_start();
require_once 'db.php';

// Verifica se está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../tela-login&cadastro-final/tela-login/telalogin.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['foto'])) {
    
    $id_usuario = $_SESSION['id_usuario'];
    $foto = $_FILES['foto'];
    
    // Verificar se houve erro no upload
    if ($foto['error'] !== UPLOAD_ERR_OK) {
        header("Location: ../perfil.php?erro=foto");
        exit();
    }

    // Validar tipo de arquivo
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    if (!in_array($foto['type'], $tiposPermitidos)) {
        header("Location: ../perfil.php?erro=foto");
        exit();
    }

    // Validar tamanho (máx 5MB)
    if ($foto['size'] > 5 * 1024 * 1024) {
        header("Location: ../perfil.php?erro=foto");
        exit();
    }

    // Gerar nome único para o arquivo
    $extensao = pathinfo($foto['name'], PATHINFO_EXTENSION);
    $nomeArquivo = 'user_' . $id_usuario . '_' . time() . '.' . $extensao;
    
    // Diretório de upload
    $diretorio = '../uploads/';
    
    // Criar diretório se não existir
    if (!file_exists($diretorio)) {
        mkdir($diretorio, 0777, true);
    }

    $caminhoCompleto = $diretorio . $nomeArquivo;

    // Buscar foto antiga para deletar
    $sql = $pdo->prepare("SELECT foto FROM usuarios WHERE id = :id");
    $sql->bindParam(':id', $id_usuario);
    $sql->execute();
    $fotoAntiga = $sql->fetchColumn();

    // Mover arquivo para o diretório
    if (move_uploaded_file($foto['tmp_name'], $caminhoCompleto)) {
        
        // Atualizar no banco de dados
        $sqlUpdate = $pdo->prepare("UPDATE usuarios SET foto = :foto WHERE id = :id");
        $sqlUpdate->bindParam(':foto', $nomeArquivo);
        $sqlUpdate->bindParam(':id', $id_usuario);
        
        if ($sqlUpdate->execute()) {
            // Deletar foto antiga se existir e não for a padrão
            if ($fotoAntiga && $fotoAntiga != 'default.png' && file_exists($diretorio . $fotoAntiga)) {
                unlink($diretorio . $fotoAntiga);
            }
            
            header("Location: ../perfil.php?sucesso=1");
            exit();
        }
    }
    
    header("Location: ../perfil.php?erro=foto");
    exit();
}

header("Location: ../perfil.php");
exit();
?>