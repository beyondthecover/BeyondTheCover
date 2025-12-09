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
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carrinho - G•W</title>
  <link rel="stylesheet" href="carrinho.css" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* Estilos para os itens do carrinho */
    .cart-item {
      display: flex;
      align-items: center;
      gap: 20px;
      padding: 20px;
      background: white;
      border-radius: 12px;
      margin-bottom: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }

    .cart-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .item-image {
      flex-shrink: 0;
    }

    .item-image img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
    }

    .item-info {
      flex: 1;
      min-width: 0;
    }

    .item-info h4 {
      margin: 0 0 8px 0;
      font-size: 1rem;
      font-weight: 600;
      color: #333;
    }

    .item-price {
      margin: 0;
      color: #666;
      font-size: 0.9rem;
    }

    .item-quantity {
      display: flex;
      align-items: center;
      gap: 10px;
      background: #f5f5f5;
      padding: 5px 10px;
      border-radius: 8px;
    }

    .item-quantity button {
      background: white;
      border: 1px solid #ddd;
      width: 30px;
      height: 30px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .item-quantity button:hover {
      background: #333;
      color: white;
      border-color: #333;
    }

    .item-quantity span {
      min-width: 30px;
      text-align: center;
      font-weight: 600;
    }

    .item-total {
      font-size: 1.1rem;
      font-weight: 700;
      color: #333;
      min-width: 100px;
      text-align: right;
    }

    .item-remove {
      background: #ff4444;
      color: white;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .item-remove:hover {
      background: #cc0000;
      transform: scale(1.1);
    }

    .custom-select {
      position: relative;
      margin: 15px 0;
    }

    .select-btn {
      width: 100%;
      padding: 12px 15px;
      background: white;
      border: 2px solid #ddd;
      border-radius: 8px;
      cursor: pointer;
      text-align: left;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: all 0.2s;
    }

    .select-btn:hover {
      border-color: #333;
    }

    .select-btn img {
      width: 24px;
      height: 24px;
    }

    .options {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 2px solid #ddd;
      border-radius: 8px;
      margin-top: 5px;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s;
      z-index: 10;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .options.active {
      max-height: 300px;
      overflow-y: auto;
    }

    .options li {
      list-style: none;
      padding: 12px 15px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: background 0.2s;
    }

    .options li:hover {
      background: #f5f5f5;
    }

    .options li img {
      width: 24px;
      height: 24px;
    }

    /* Alert de carrinho */
    .cart-alert {
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 15px;
      font-weight: 500;
      text-align: center;
      display: none;
    }

    @media (max-width: 768px) {
      .cart-item {
        flex-wrap: wrap;
        gap: 10px;
      }
      
      .item-image img {
        width: 60px;
        height: 60px;
      }
      
      .item-quantity,
      .item-total {
        width: 100%;
      }
    }
  </style>

</head>

<body>
  <nav class="navbar navbar-expand-lg px-4">
    <div class="container-fluid justify-content-between align-items-center">

      <!-- Esquerda -->
      <div class="d-flex align-items-center gap-4 me-5">
        <a class="nav-link text-decoration-none" href="#">Maquiagem</a>
        <a class="nav-link text-decoration-none" href="../index.php">Home</a>
      </div>

      <!-- Centro: Logo -->
      <a class="navbar-brand position-absolute start-50 translate-middle-x" href="../index.php">
        G <span>✦</span> W
      </a>

      <!-- Direita -->
      <div class="d-flex align-items-center gap-3 ms-5">
        <!-- Busca -->
        <form class="d-flex" role="search">
          <input class="form-control form-control-sm px-3" type="search" placeholder="Buscar..." aria-label="Buscar">
        </form>

        <!-- Ícones -->
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
      <!-- Caixa de produtos -->
      <div class="products" id="cart-items">
        <p class="empty">Seu carrinho está vazio</p>
        <img src="./imgs/cart.png" alt="Carrinho vazio" class="empty-img">
      </div>

      <!-- Caixa do resumo -->
      <div class="resume">
        <h3>Resumo da compra</h3>

        <div class="resume-total">
          <p>Total: <span class="total-cart">R$ 0,00</span></p>
        </div>

        <!-- Seção de pagamento -->
        <div class="resume-payment">
          <label for="pagamento">Escolha a forma de pagamento</label>
          <div class="custom-select">
            <button type="button" class="select-btn">Selecione a forma de pagamento</button>
            <ul class="options">
              <li data-value="credito">
                <img src="./imgs/sem-contato.png" alt="Crédito"> Cartão de crédito
              </li>
              <li data-value="debito">
                <img src="./imgs/sem-contato.png" alt="Débito"> Cartão de débito
              </li>
              <li data-value="pix">
                <img src="./imgs/pix.png" alt="Pix"> Pix
              </li>
              <li data-value="boleto">
                <img src="./imgs/recibo.png" alt="Boleto"> Boleto
              </li>
            </ul>
          </div>
        </div>

        <!-- Botão para finalizar -->
        <div class="resume-action">
          <button id="go-to-checkout">Finalizar Compra</button>
        </div>
      </div>
    </div>
  </main>

  <!-- APENAS O SCRIPT.JS - Remove todo JavaScript inline duplicado -->
  <script src="script.js"></script>

</body>

</html>