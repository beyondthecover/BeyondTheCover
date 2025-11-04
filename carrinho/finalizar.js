document.addEventListener("DOMContentLoaded", () => {
  const cart = JSON.parse(localStorage.getItem("cart") || "[]");
  const paymentMethod = localStorage.getItem("paymentMethod");
  const paymentSection = document.getElementById("payment-section");
  const shippingPanel = document.getElementById("shipping-panel");
  const shippingForm = document.getElementById("shipping-form");
  const finalizeBtn = document.getElementById("finalize-btn");

  function formatBRL(value) {
    const n = Number(value) || 0;
    return n.toLocaleString("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Se alguém abriu esta página sem ter itens/pgto, bloqueia e mostra aviso.
  if (!cart || cart.length === 0) {
    shippingPanel.innerHTML = `<p class="empty">Seu carrinho está vazio. Volte ao carrinho para adicionar produtos.</p>`;
    if (finalizeBtn) finalizeBtn.disabled = true;
    paymentSection.innerHTML = `<p class="empty">Nenhum pedido para processar.</p>`;
    return;
  }

  if (!paymentMethod) {
    paymentSection.innerHTML = `<p class="empty">Nenhuma forma de pagamento selecionada. Volte ao carrinho e escolha uma forma de pagamento.</p>`;
    if (finalizeBtn) finalizeBtn.disabled = true;
  }

  // monta resumo do pedido (sempre visível na direita)
  function buildOrderSummary() {
    let html = `<div class="order-summary"><h4>Resumo do pedido</h4>`;
    cart.forEach(it => {
      html += `<div style="display:flex;justify-content:space-between;margin:8px 0;">
                 <div>${it.name} ${it.qty ? 'x' + it.qty : ''}</div>
                 <div>R$ ${formatBRL((Number(it.price)||0) * (it.qty||1))}</div>
               </div>`;
    });
    const total = cart.reduce((acc, it) => acc + (Number(it.price)||0) * (it.qty||1), 0);
    html += `<hr><div style="display:flex;justify-content:space-between;font-weight:bold;"> <div>Total</div> <div>R$ ${formatBRL(total)}</div></div></div>`;
    return html;
  }

  // Se pagamento for cartão: mostra campos do cartão; caso contrário mostra imagem + texto
  function renderPaymentSection() {
    const summary = buildOrderSummary();

    if (paymentMethod === "credito" || paymentMethod === "debito") {
      paymentSection.innerHTML = `
        ${summary}
        <div class="card-form" style="margin-top:16px;">
          <h4>Dados do Cartão</h4>
          <label>Número do Cartão</label>
          <input type="text" id="card-number" maxlength="19" placeholder="1234 5678 9012 3456" required>

          <label>Nome no Cartão</label>
          <input type="text" id="card-name" required>

          <div style="display:flex;gap:8px;">
            <div style="flex:1;">
              <label>Validade</label>
              <input type="text" id="card-exp" placeholder="MM/AA" required>
            </div>
            <div style="width:100px;">
              <label>CVV</label>
              <input type="text" id="card-cvv" maxlength="4" required>
            </div>
          </div>
        </div>
      `;
    } else {
      paymentSection.innerHTML = `
        ${summary}
        <div style="margin-top:16px;">
          <img src="./imgs/checkout.png" alt="Finalize sua compra" style="max-width:200px;">
          <p>Revise seus dados e clique em "Finalizar Compra".</p>
        </div>
      `;
    }
  }

  renderPaymentSection();

  // Validação e finalização
  shippingForm.addEventListener("submit", (e) => {
    e.preventDefault();

    if (!shippingForm.checkValidity()) {
      alert("Preencha todos os campos de entrega corretamente.");
      return;
    }

    // Se for cartão: validações simples
    if (paymentMethod === "credito" || paymentMethod === "debito") {
      const cardNumber = document.getElementById("card-number");
      const cardName = document.getElementById("card-name");
      const cardExp = document.getElementById("card-exp");
      const cardCvv = document.getElementById("card-cvv");

      if (!cardNumber || !cardName || !cardExp || !cardCvv) {
        alert("Preencha os dados do cartão.");
        return;
      }

      // validações simples (apenas checks básicos)
      if (cardNumber.value.replace(/\s/g, "").length < 13) {
        alert("Número do cartão inválido.");
        return;
      }
      if (!/^\d{2}\/?\d{2}$/.test(cardExp.value)) {
        // aceitável: MM/AA ou MMAA
        // não é validação completa, apenas básica
        // permite também MM/AA
        // continue se parecer ok
      }
      if (cardCvv.value.length < 3) {
        alert("CVV inválido.");
        return;
      }
    }

    // Simula finalização
    alert("Compra finalizada com sucesso! Obrigado.");
    localStorage.removeItem("cart");
    localStorage.removeItem("paymentMethod");

    // redireciona para a página inicial (ajuste se for outra)
    window.location.href = "index.html";
  });
});