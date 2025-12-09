// finalizar.js - Gerencia a finalização da compra
(function() {
  document.addEventListener('DOMContentLoaded', init);

  function init() {
    // Buscar dados do carrinho e pagamento
    const cart = JSON.parse(localStorage.getItem('carrinho') || localStorage.getItem('cart') || '[]');
    const urlParams = new URLSearchParams(window.location.search);
    const paymentMethod = urlParams.get('payment') || localStorage.getItem('paymentMethod') || '';

    // Se carrinho vazio, redireciona
    if (!cart || cart.length === 0) {
      alert('Seu carrinho está vazio!');
      window.location.href = 'carrinho.php';
      return;
    }

    // Renderizar resumo do pedido e forma de pagamento
    renderOrderSummary(cart, paymentMethod);
    renderPaymentMethod(paymentMethod);

    // Buscar CEP
    const cepInput = document.getElementById('ship-cep');
    if (cepInput) {
      cepInput.addEventListener('blur', buscarCEP);
    }

    // Submeter formulário
    const form = document.getElementById('shipping-form');
    if (form) {
      form.addEventListener('submit', finalizarCompra);
    }
  }

  // Formatar moeda BRL
  function formatBRL(value) {
    const n = Number(value) || 0;
    return n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Renderizar resumo do pedido
  function renderOrderSummary(cart, paymentMethod) {
    const paymentSection = document.getElementById('payment-section');
    if (!paymentSection) return;

    let subtotal = 0;
    let itemsHTML = '';

    cart.forEach(item => {
      const preco = Number(item.preco || item.price || 0);
      const qtd = Number(item.quantidade || item.qty || 1);
      const itemTotal = preco * qtd;
      subtotal += itemTotal;

      const nome = item.nome || item.name || 'Produto';
      itemsHTML += `
        <div class="order-item">
          <span>${qtd}x ${nome}</span>
          <span>R$ ${formatBRL(itemTotal)}</span>
        </div>
      `;
    });

    const frete = 15.00; // Frete fixo
    const total = subtotal + frete;

    const summaryHTML = `
      <div class="order-summary">
        <h4><i class="bi bi-cart-check"></i> Resumo do Pedido</h4>
        ${itemsHTML}
        <div class="order-item">
          <span>Frete</span>
          <span>R$ ${formatBRL(frete)}</span>
        </div>
        <div class="order-item">
          <span>Total</span>
          <span>R$ ${formatBRL(total)}</span>
        </div>
      </div>
    `;

    paymentSection.innerHTML = summaryHTML;
  }

  // Renderizar informações de pagamento
  function renderPaymentMethod(method) {
    const paymentSection = document.getElementById('payment-section');
    if (!paymentSection) return;

    let paymentHTML = '';

    switch(method) {
      case 'credito':
      case 'debito':
        paymentHTML = `
          <div class="payment-info">
            <h4><i class="bi bi-credit-card"></i> Dados do Cartão</h4>
            <div class="card-form">
              <label>Número do Cartão:</label>
              <input type="text" id="card-number" placeholder="0000 0000 0000 0000" maxlength="19" required>
              
              <label>Nome no Cartão:</label>
              <input type="text" id="card-name" placeholder="NOME COMPLETO" required>
              
              <div style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                  <label>Validade:</label>
                  <input type="text" id="card-expiry" placeholder="MM/AA" maxlength="5" required>
                </div>
                <div style="flex: 1;">
                  <label>CVV:</label>
                  <input type="text" id="card-cvv" placeholder="123" maxlength="4" required>
                </div>
              </div>

              ${method === 'credito' ? `
                <label>Parcelas:</label>
                <select id="card-installments">
                  <option value="1">1x sem juros</option>
                  <option value="2">2x sem juros</option>
                  <option value="3">3x sem juros</option>
                  <option value="4">4x sem juros</option>
                  <option value="5">5x sem juros</option>
                  <option value="6">6x sem juros</option>
                </select>
              ` : ''}
            </div>
          </div>
        `;
        break;

      case 'pix':
        const pixCode = gerarCodigoPix();
        paymentHTML = `
          <div class="payment-info">
            <h4><i class="bi bi-qr-code"></i> Pagamento via PIX</h4>
            <div class="pix-info">
              <p>Escaneie o QR Code abaixo ou copie o código PIX:</p>
              <div class="pix-qrcode">
                <i class="bi bi-qr-code" style="font-size: 100px; color: #667eea;"></i>
              </div>
              <div class="pix-code">${pixCode}</div>
              <button type="button" class="btn btn-sm btn-secondary" onclick="copiarPix('${pixCode}')">
                <i class="bi bi-clipboard"></i> Copiar Código PIX
              </button>
              <p class="mt-3 text-muted">
                <small>Após confirmar as informações de envio, você terá 30 minutos para realizar o pagamento.</small>
              </p>
            </div>
          </div>
        `;
        break;

      case 'boleto':
        const codigoBoleto = gerarCodigoBoleto();
        paymentHTML = `
          <div class="payment-info">
            <h4><i class="bi bi-receipt"></i> Pagamento via Boleto</h4>
            <div class="boleto-info">
              <p>O boleto será gerado após a confirmação do pedido.</p>
              <div class="boleto-barcode">${codigoBoleto}</div>
              <button type="button" class="btn btn-sm btn-secondary" onclick="copiarBoleto('${codigoBoleto}')">
                <i class="bi bi-clipboard"></i> Copiar Código de Barras
              </button>
              <p class="mt-3 text-muted">
                <small>Vencimento: 3 dias após a confirmação. Após o pagamento, o pedido será processado em até 2 dias úteis.</small>
              </p>
            </div>
          </div>
        `;
        break;

      default:
        paymentHTML = `
          <div class="payment-info">
            <p class="text-danger">Método de pagamento não selecionado.</p>
            <a href="carrinho.php" class="btn btn-primary">Voltar ao Carrinho</a>
          </div>
        `;
    }

    paymentSection.innerHTML += paymentHTML;

    // Adicionar máscaras de input se for cartão
    if (method === 'credito' || method === 'debito') {
      aplicarMascarasCartao();
    }
  }

  // Aplicar máscaras nos campos de cartão
  function aplicarMascarasCartao() {
    const cardNumber = document.getElementById('card-number');
    const cardExpiry = document.getElementById('card-expiry');
    const cardCVV = document.getElementById('card-cvv');

    if (cardNumber) {
      cardNumber.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
      });
    }

    if (cardExpiry) {
      cardExpiry.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
          value = value.slice(0, 2) + '/' + value.slice(2, 4);
        }
        e.target.value = value;
      });
    }

    if (cardCVV) {
      cardCVV.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
      });
    }
  }

  // Buscar CEP via API
  function buscarCEP(e) {
    const cep = e.target.value.replace(/\D/g, '');
    
    if (cep.length !== 8) return;

    fetch(`https://viacep.com.br/ws/${cep}/json/`)
      .then(response => response.json())
      .then(data => {
        if (data.erro) {
          alert('CEP não encontrado!');
          return;
        }

        document.getElementById('ship-street').value = data.logradouro || '';
        document.getElementById('ship-neighborhood').value = data.bairro || '';
        document.getElementById('ship-city').value = data.localidade || '';
        document.getElementById('ship-state').value = data.uf || '';
      })
      .catch(error => {
        console.error('Erro ao buscar CEP:', error);
      });
  }

  // Finalizar compra
  function finalizarCompra(e) {
    e.preventDefault();

    // Coletar dados do formulário
    const dadosEnvio = {
      nome: document.getElementById('ship-name').value,
      telefone: document.getElementById('ship-phone').value,
      cep: document.getElementById('ship-cep').value,
      rua: document.getElementById('ship-street').value,
      numero: document.getElementById('ship-number').value,
      complemento: document.getElementById('ship-complement').value,
      bairro: document.getElementById('ship-neighborhood').value,
      cidade: document.getElementById('ship-city').value,
      estado: document.getElementById('ship-state').value
    };

    // Validar dados de pagamento se for cartão
    const paymentMethod = new URLSearchParams(window.location.search).get('payment');
    if (paymentMethod === 'credito' || paymentMethod === 'debito') {
      const cardNumber = document.getElementById('card-number')?.value;
      const cardName = document.getElementById('card-name')?.value;
      const cardExpiry = document.getElementById('card-expiry')?.value;
      const cardCVV = document.getElementById('card-cvv')?.value;

      if (!cardNumber || !cardName || !cardExpiry || !cardCVV) {
        alert('Preencha todos os dados do cartão!');
        return;
      }
    }

    // Salvar pedido no localStorage (simulação)
    const cart = JSON.parse(localStorage.getItem('carrinho') || '[]');
    const pedido = {
      id: Date.now(),
      data: new Date().toISOString(),
      itens: cart,
      envio: dadosEnvio,
      pagamento: paymentMethod,
      total: calcularTotal(cart)
    };

    // Salvar pedido
    const pedidos = JSON.parse(localStorage.getItem('pedidos') || '[]');
    pedidos.push(pedido);
    localStorage.setItem('pedidos', JSON.stringify(pedidos));

    // Limpar carrinho
    localStorage.removeItem('carrinho');
    localStorage.removeItem('cart');
    localStorage.removeItem('paymentMethod');

    // Redirecionar para página de sucesso
    window.location.href = 'sucesso.html?pedido=' + pedido.id;
  }

  // Calcular total
  function calcularTotal(cart) {
    const subtotal = cart.reduce((acc, item) => {
      const preco = Number(item.preco || item.price || 0);
      const qtd = Number(item.quantidade || item.qty || 1);
      return acc + (preco * qtd);
    }, 0);
    return subtotal + 15.00; // + frete
  }

  // Gerar código PIX simulado
  function gerarCodigoPix() {
    return '00020126580014br.gov.bcb.pix0136' + 
           Math.random().toString(36).substring(2, 15) + 
           Math.random().toString(36).substring(2, 15) + 
           '5204000053039865802BR5925LOJA GW BEAUTY6014RIO BRANCO62070503***6304';
  }

  // Gerar código de boleto simulado
  function gerarCodigoBoleto() {
    const linha1 = Math.floor(Math.random() * 100000).toString().padStart(5, '0') + '.' +
                   Math.floor(Math.random() * 100000).toString().padStart(5, '0');
    const linha2 = Math.floor(Math.random() * 100000).toString().padStart(5, '0') + '.' +
                   Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
    const linha3 = Math.floor(Math.random() * 100000).toString().padStart(5, '0') + '.' +
                   Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
    const linha4 = Math.floor(Math.random() * 10);
    const linha5 = Math.floor(Math.random() * 10000000000000).toString().padStart(14, '0');
    
    return `${linha1} ${linha2} ${linha3} ${linha4} ${linha5}`;
  }

  // Funções globais para copiar códigos
  window.copiarPix = function(codigo) {
    navigator.clipboard.writeText(codigo).then(() => {
      alert('Código PIX copiado!');
    });
  };

  window.copiarBoleto = function(codigo) {
    navigator.clipboard.writeText(codigo).then(() => {
      alert('Código de barras copiado!');
    });
  };

})();