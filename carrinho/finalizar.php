<?php
session_start();
require_once '../backend/db.php';
$fotoUsuario = '../uploads/default.jpg';

if (isset($_SESSION['id_usuario'])) {
  $sqlFoto = $pdo->prepare("SELECT foto FROM usuarios WHERE id = :id LIMIT 1");
  $sqlFoto->bindParam(':id', $_SESSION['id_usuario'], PDO::PARAM_INT);
  $sqlFoto->execute();
  $dadosFoto = $sqlFoto->fetch(PDO::FETCH_ASSOC);

  if ($dadosFoto && !empty($dadosFoto['foto'])) {
    $fotoUsuario = '../uploads/' . $dadosFoto['foto'];
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Finalizar Compra - G•W</title>
  <link rel="stylesheet" href="carrinho.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  
  <style>
    .shipping-form input,
    .shipping-form select {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.3s;
    }

    .shipping-form input:focus,
    .shipping-form select:focus {
      outline: none;
      border-color: #667eea;
    }

    .shipping-form label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: #333;
    }

    .payment-info {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
    }

    .payment-info h4 {
      margin-bottom: 15px;
      color: #667eea;
    }

    .order-summary {
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #e0e0e0;
    }

    .order-item:last-child {
      border-bottom: none;
      font-weight: bold;
      font-size: 1.1rem;
      color: #667eea;
      margin-top: 10px;
      padding-top: 15px;
      border-top: 2px solid #667eea;
    }

    .card-form input {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 14px;
    }

    .card-form label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
      color: #333;
    }

    #finalize-btn {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.2s;
    }

    #finalize-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .pix-info {
      text-align: center;
      padding: 20px;
    }

    .pix-qrcode {
      width: 200px;
      height: 200px;
      margin: 20px auto;
      background: white;
      border: 2px solid #ddd;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      color: #666;
    }

    .pix-code {
      background: #f0f0f0;
      padding: 10px;
      border-radius: 8px;
      font-family: monospace;
      word-break: break-all;
      margin: 15px 0;
      font-size: 12px;
    }

    .boleto-info {
      text-align: center;
      padding: 20px;
    }

    .boleto-barcode {
      background: white;
      padding: 20px;
      border: 2px solid #ddd;
      border-radius: 8px;
      margin: 20px 0;
      font-family: monospace;
      font-size: 12px;
    }

    .btn-copy {
      background: #6c757d;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      margin-top: 10px;
    }

    .btn-copy:hover {
      background: #5a6268;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg px-4">
    <div class="container-fluid justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-4 me-5">
        <a class="nav-link text-decoration-none" href="#">Maquiagem</a>
        <a class="nav-link text-decoration-none" href="../index.php">Home</a>
      </div>
      
      <a class="navbar-brand position-absolute start-50 translate-middle-x" href="../index.php">
        G <span>✦</span> W
      </a>
      
      <div class="d-flex align-items-center gap-3 ms-5">
        <form class="d-flex" role="search">
          <input class="form-control form-control-sm px-3" type="search" placeholder="Buscar..." aria-label="Buscar">
        </form>
        
        <a href="carrinho.php" class="text-dark" title="Carrinho">
          <i class="bi bi-bag-heart-fill fs-5"></i>
        </a>
        <a href="../perfil.php" class="text-dark d-flex align-items-center" title="Perfil">
          <img src="<?php echo $fotoUsuario; ?>" alt="Perfil"
            style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
        </a>
      </div>
    </div>
  </nav>

  <main>
    <div class="cart-body">
      <!-- Lado esquerdo: dados de envio -->
      <div class="products" id="shipping-panel">
        <h3>Informações de Envio</h3>
        <form id="shipping-form" class="shipping-form">
          <label>Nome de quem recebe:</label>
          <input type="text" id="ship-name" required>

          <label>Telefone:</label>
          <input type="tel" id="ship-phone" required placeholder="(00) 00000-0000">

          <label>CEP:</label>
          <input type="text" id="ship-cep" required placeholder="00000-000" maxlength="9">

          <label>Endereço (rua):</label>
          <input type="text" id="ship-street" required>

          <label>Número:</label>
          <input type="text" id="ship-number" required>

          <label>Complemento:</label>
          <input type="text" id="ship-complement" placeholder="Opcional">

          <label>Bairro:</label>
          <input type="text" id="ship-neighborhood" required>

          <label>Cidade:</label>
          <input type="text" id="ship-city" required>

          <label>Estado:</label>
          <select id="ship-state" required>
            <option value="">Selecione</option>
            <option value="AC">Acre</option>
            <option value="AL">Alagoas</option>
            <option value="AP">Amapá</option>
            <option value="AM">Amazonas</option>
            <option value="BA">Bahia</option>
            <option value="CE">Ceará</option>
            <option value="DF">Distrito Federal</option>
            <option value="ES">Espírito Santo</option>
            <option value="GO">Goiás</option>
            <option value="MA">Maranhão</option>
            <option value="MT">Mato Grosso</option>
            <option value="MS">Mato Grosso do Sul</option>
            <option value="MG">Minas Gerais</option>
            <option value="PA">Pará</option>
            <option value="PB">Paraíba</option>
            <option value="PR">Paraná</option>
            <option value="PE">Pernambuco</option>
            <option value="PI">Piauí</option>
            <option value="RJ">Rio de Janeiro</option>
            <option value="RN">Rio Grande do Norte</option>
            <option value="RS">Rio Grande do Sul</option>
            <option value="RO">Rondônia</option>
            <option value="RR">Roraima</option>
            <option value="SC">Santa Catarina</option>
            <option value="SP">São Paulo</option>
            <option value="SE">Sergipe</option>
            <option value="TO">Tocantins</option>
          </select>

          <button type="submit" id="finalize-btn">Finalizar Compra</button>
        </form>
      </div>

      <!-- Lado direito: resumo e pagamento -->
      <div class="resume" id="payment-section">
        <!-- Será preenchido por finalizar.js -->
      </div>
    </div>
  </main>

  <script src="finalizar.js"></script>
</body>
</html>