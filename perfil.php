<?php
session_start();
require_once './backend/db.php';

$fotoUsuario = './uploads/default.jpg';


if (isset($_SESSION['id_usuario'])) {
  $sqlFoto = $pdo->prepare("SELECT foto FROM usuarios WHERE id = :id LIMIT 1");
  $sqlFoto->bindParam(':id', $_SESSION['id_usuario'], PDO::PARAM_INT);
  $sqlFoto->execute();
  $dadosFoto = $sqlFoto->fetch(PDO::FETCH_ASSOC);

  if ($dadosFoto && !empty($dadosFoto['foto'])) {
    $fotoUsuario = './uploads/' . $dadosFoto['foto'];
  }
}

// Usuário precisa estar logado
if (!isset($_SESSION['id_usuario'])) {
  header("Location: ./tela-login&cadastro-final/tela-login/telalogin.html");
  exit();
}

$id = $_SESSION['id_usuario'];

// Buscar dados do usuário
$sql = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
$sql->bindParam(':id', $id, PDO::PARAM_INT);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  echo "Erro: usuário não encontrado.";
  exit();
}

// Definir foto padrão se não existir
$fotoUsuario = !empty($usuario['foto']) ? './uploads/' . $usuario['foto'] : './img/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meu Perfil - G•W</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="perfil.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg px-4 bg-white shadow-sm">
    <div class="container-fluid justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-4 me-5">
        <a class="nav-link text-decoration-none" href="index.php">Home</a>
        <a class="nav-link text-decoration-none" href="#">Skin Care</a>
      </div>
      <a class="navbar-brand position-absolute start-50 translate-middle-x" href="index.php">G ✦ W</a>
      <div class="d-flex align-items-center gap-3 ms-5">
        <a href="#" class="text-dark"><i class="bi bi-bag-heart-fill fs-5"></i></a>
        <a href="perfil.php" class="text-dark d-flex align-items-center" title="Perfil">
          <img src="<?php echo $fotoUsuario; ?>" alt="Perfil"
            style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
        </a>
      </div>
    </div>
  </nav>

  <!-- Alertas -->
  <?php if (isset($_GET['sucesso'])): ?>
    <div class="container mt-3">
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>Sucesso!</strong> Perfil atualizado com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['erro'])): ?>
    <div class="container mt-3">
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Erro!</strong>
        <?php
        if ($_GET['erro'] == 'email')
          echo 'Este e-mail já está em uso.';
        elseif ($_GET['erro'] == 'foto')
          echo 'Erro ao fazer upload da foto.';
        else
          echo 'Ocorreu um erro ao atualizar o perfil.';
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
  <?php endif; ?>

  <!-- Perfil -->
  <section class="perfil-container">
    <div class="perfil-card">
      <div class="perfil-foto">
        <img src="<?php echo $fotoUsuario; ?>" alt="Foto de perfil" id="fotoPerfil">
        <form action="./backend/upload_foto.php" method="POST" enctype="multipart/form-data" id="formFoto">
          <label for="uploadFoto" class="btn-editar"><i class="bi bi-camera-fill"></i></label>
          <input type="file" id="uploadFoto" name="foto" accept="image/*" hidden onchange="previewAndSubmit(this)">
        </form>
      </div>

      <!-- VISUALIZAÇÃO -->
      <div id="viewMode" class="perfil-info">
        <h2><?php echo htmlspecialchars($usuario['nome_completo']); ?></h2>
        <p><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($usuario['email']); ?></p>

        <?php if (!empty($usuario['usuario'])): ?>
          <p><i class="bi bi-person"></i> @<?php echo htmlspecialchars($usuario['usuario']); ?></p>
        <?php endif; ?>

        <?php if (!empty($usuario['data_cadastro'])): ?>
          <p><i class="bi bi-calendar-check"></i> Membro desde
            <?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?></p>
        <?php endif; ?>

        <button class="btn-salvar w-100" onclick="toggleEdit()">Editar Perfil</button>
      </div>

      <!-- FORMULÁRIO DE EDIÇÃO -->
      <form id="editMode" action="./backend/update_usuario.php" method="POST" class="perfil-info" style="display:none;">
        <h2>Editar Perfil</h2>

        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">

        <div class="mb-3 text-start">
          <label for="nome" class="form-label">Nome</label>
          <input type="text" class="form-control" id="nome" name="nome"
            value="<?php echo htmlspecialchars($usuario['nome_completo']); ?>" required>
        </div>

        <div class="mb-3 text-start">
          <label for="email" class="form-label">E-mail</label>
          <input type="email" class="form-control" id="email" name="email"
            value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>

        <div class="mb-3 text-start">
          <label for="senha" class="form-label">Senha (deixe vazio para não alterar)</label>
          <input type="password" class="form-control" id="senha" name="senha" placeholder="********">
        </div>

        <button type="submit" class="btn-salvar w-100">Salvar Alterações</button>
        <button type="button" class="btn-cancelar w-100" onclick="toggleView()">Cancelar</button>
      </form>
    </div>
  </section>

  <footer class="footer-bottom bg-white py-3 mt-4 text-center">
    <p class="mb-0">&copy; 2025 G•W Beauty. Todos os direitos reservados.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- JS alternar -->
  <script>
    function toggleEdit() {
      document.getElementById("viewMode").style.display = "none";
      document.getElementById("editMode").style.display = "block";
    }

    function toggleView() {
      document.getElementById("editMode").style.display = "none";
      document.getElementById("viewMode").style.display = "block";
    }

    // Preview e submit automático da foto
    function previewAndSubmit(input) {
      if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validar tamanho (5MB)
        if (file.size > 5 * 1024 * 1024) {
          alert('A imagem deve ter no máximo 5MB!');
          return;
        }

        // Validar tipo
        const tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        if (!tiposPermitidos.includes(file.type)) {
          alert('Formato inválido! Use JPG, PNG, GIF ou WEBP.');
          return;
        }

        // Preview
        const reader = new FileReader();
        reader.onload = function (e) {
          document.getElementById('fotoPerfil').src = e.target.result;
        }
        reader.readAsDataURL(file);

        // Submit automático
        document.getElementById('formFoto').submit();
      }
    }
  </script>

</body>

</html>