// script.js - gerencia carrinho, pagamento e checkout
(function () {
  document.addEventListener("DOMContentLoaded", init);

  function init() {
    const productsContainer = document.querySelector(".products") || document.getElementById("cart-items");
    const resumeEl = document.querySelector(".resume");
    let cartTotalEl = document.querySelector(".total-cart") || document.getElementById("cart-total");
    let cartAlert = document.getElementById("cart-alert");
    const checkoutBtn = document.getElementById("checkout-btn") || document.getElementById("go-to-checkout") || document.querySelector(".button");

    // cria alert se não existir
    if (!cartAlert && resumeEl) {
      cartAlert = document.createElement("div");
      cartAlert.id = "cart-alert";
      cartAlert.className = "cart-alert";
      cartAlert.style.cssText = "display:none;padding:12px;border-radius:8px;margin-bottom:15px;font-weight:500;text-align:center;";
      resumeEl.insertBefore(cartAlert, resumeEl.firstChild);
    }

    // garante total element
    if (!cartTotalEl && resumeEl) {
      const p = document.createElement("p");
      p.innerHTML = `Total: <span class="total-cart">R$ 0,00</span>`;
      resumeEl.insertBefore(p, resumeEl.querySelector(".custom-select") || resumeEl.querySelector("button") || resumeEl.firstChild);
      cartTotalEl = document.querySelector(".total-cart");
    }

    // MESCLAR: usar 'carrinho' para compatibilidade com o código anterior
    let cart = JSON.parse(localStorage.getItem("carrinho") || localStorage.getItem("cart") || "[]");

    // pagamento selecionado (puxa do localStorage se existir)
    let selectedPayment = localStorage.getItem("paymentMethod") || null;

    // referências do custom-select (se existir)
    const selectBtn = document.querySelector(".select-btn");
    const customSelect = document.querySelector(".custom-select");
    const optionsList = document.querySelector(".options");
    const optionsItems = document.querySelectorAll(".options li");

    // util: formata BRL
    function formatBRL(value) {
      const n = Number(value) || 0;
      return n.toLocaleString("pt-BR", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // salva cart e payment no localStorage
    function saveState() {
      localStorage.setItem("carrinho", JSON.stringify(cart));
      localStorage.setItem("cart", JSON.stringify(cart)); // backup
      if (selectedPayment) localStorage.setItem("paymentMethod", selectedPayment);
    }

    // mostra mensagem de alerta inline
    let alertTimeout = null;
    function showCartAlert(msg, type = "error") {
      if (!cartAlert) {
        alert(msg);
        return;
      }
      cartAlert.textContent = msg;
      cartAlert.style.display = "block";
      if (type === "error") cartAlert.style.background = "#ff6b6b";
      else cartAlert.style.background = "#4caf50";
      clearTimeout(alertTimeout);
      alertTimeout = setTimeout(() => {
        if (cartAlert) cartAlert.style.display = "none";
      }, 4000);
    }

    function clearCartAlert() {
      if (!cartAlert) return;
      cartAlert.style.display = "none";
      cartAlert.textContent = "";
    }

    // renderiza o carrinho (produtos) - MESCLADO
    function renderCart() {
      if (!productsContainer) return;
      productsContainer.innerHTML = "";

      if (!cart || cart.length === 0) {
        const emptyMessage = document.createElement("p");
        emptyMessage.className = "empty";
        emptyMessage.textContent = "Seu carrinho está vazio";

        const emptyImg = document.createElement("img");
        emptyImg.src = "./imgs/cart.png";
        emptyImg.alt = "Carrinho vazio";
        emptyImg.className = "empty-img";

        productsContainer.appendChild(emptyMessage);
        productsContainer.appendChild(emptyImg);
        updateTotal();
        return;
      }

      cart.forEach((item, index) => {
        // Compatibilidade: aceita tanto 'imagem' quanto 'img', 'preco' quanto 'price', etc
        const itemNome = item.nome || item.name || "Produto";
        const itemPreco = Number(item.preco || item.price || 0);
        const itemImagem = item.imagem || item.img || "https://via.placeholder.com/80";
        const itemQtd = Number(item.quantidade || item.qty || 1);

        const itemDiv = document.createElement("div");
        itemDiv.className = "cart-item";

        // subtotal
        const subtotal = itemPreco * itemQtd;

        itemDiv.innerHTML = `
          <div class="item-image">
            <img src="${itemImagem}" alt="${escapeHtml(itemNome)}">
          </div>
          <div class="item-info">
            <h4>${escapeHtml(itemNome)}</h4>
            <p class="item-price">R$ ${formatBRL(itemPreco)}</p>
          </div>
          <div class="item-quantity">
            <button onclick="diminuirQuantidade(${index})">-</button>
            <span>${itemQtd}</span>
            <button onclick="aumentarQuantidade(${index})">+</button>
          </div>
          <div class="item-total">
            <p>R$ ${formatBRL(subtotal)}</p>
          </div>
          <button class="item-remove" onclick="removerItem(${index})">
            <i class="bi bi-trash"></i>
          </button>
        `;

        productsContainer.appendChild(itemDiv);
      });

      updateTotal();
    }

    // escape simples para evitar HTML injection em nomes
    function escapeHtml(str) {
      return String(str || "").replace(/[&<>"'`=\/]/g, function (s) {
        return ({
          '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
          "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
        })[s];
      });
    }

    // calcula e atualiza total na resume
    function updateTotal() {
      const total = cart.reduce((acc, it) => {
        const preco = Number(it.preco || it.price || 0);
        const qtd = Number(it.quantidade || it.qty || 1);
        return acc + (preco * qtd);
      }, 0);
      
      if (cartTotalEl) {
        cartTotalEl.textContent = `R$ ${formatBRL(total)}`;
      }
      saveState();
    }

    // FUNÇÕES GLOBAIS para compatibilidade
    window.aumentarQuantidade = function(index) {
      if (cart[index]) {
        if (cart[index].quantidade) cart[index].quantidade++;
        else if (cart[index].qty) cart[index].qty++;
        else cart[index].quantidade = 2;
        
        saveState();
        renderCart();
      }
    };

    window.diminuirQuantidade = function(index) {
      if (cart[index]) {
        const qtdAtual = cart[index].quantidade || cart[index].qty || 1;
        
        if (qtdAtual > 1) {
          if (cart[index].quantidade) cart[index].quantidade--;
          else if (cart[index].qty) cart[index].qty--;
          
          saveState();
          renderCart();
        } else {
          window.removerItem(index);
        }
      }
    };

    window.removerItem = function(index) {
      if (confirm('Deseja remover este item do carrinho?')) {
        cart.splice(index, 1);
        saveState();
        renderCart();
        updatePaymentUI(selectedPayment);
      }
    };

    // adiciona item ao carrinho (exposto globalmente)
    window.adicionarAoCarrinho = window.addToCart = function (nomeOuObj, preco, imagem, quantidade = 1) {
      // Aceita objeto ou parâmetros separados
      let itemNovo;
      
      if (typeof nomeOuObj === 'object') {
        itemNovo = nomeOuObj;
      } else {
        itemNovo = {
          id: Date.now(),
          nome: nomeOuObj,
          preco: Number(preco || 0),
          imagem: imagem || "",
          quantidade: Number(quantidade || 1)
        };
      }

      // Verificar se já existe (por nome e preço)
      const itemNome = itemNovo.nome || itemNovo.name;
      const itemPreco = Number(itemNovo.preco || itemNovo.price || 0);
      
      const found = cart.find(it => {
        const nome = it.nome || it.name;
        const preco = Number(it.preco || it.price || 0);
        return nome === itemNome && preco === itemPreco;
      });

      if (found) {
        // Aumenta quantidade
        if (found.quantidade) found.quantidade += Number(itemNovo.quantidade || itemNovo.qty || 1);
        else if (found.qty) found.qty += Number(itemNovo.quantidade || itemNovo.qty || 1);
        else found.quantidade = Number(itemNovo.quantidade || itemNovo.qty || 1);
      } else {
        cart.push(itemNovo);
      }

      saveState();
      renderCart();
      updatePaymentUI(selectedPayment);
      showCartAlert("Produto adicionado ao carrinho.", "ok");
    };

    // seleção de pagamento
    function updatePaymentUI(value) {
      selectedPayment = value || null;
      if (selectBtn && optionsList) {
        optionsItems.forEach(li => li.classList.remove("selected"));
        const match = Array.from(optionsItems).find(li => (li.dataset.value || li.getAttribute("data-value")) === value);
        if (match) {
          match.classList.add("selected");
          const img = match.querySelector("img") ? match.querySelector("img").cloneNode(true) : null;
          selectBtn.innerHTML = "";
          if (img) {
            img.style.width = "24px";
            img.style.height = "24px";
            img.style.objectFit = "contain";
            selectBtn.appendChild(img);
          }
          const txt = match.textContent.trim();
          selectBtn.appendChild(document.createTextNode(" " + txt));
        } else {
          selectBtn.textContent = "Selecione a forma de pagamento";
        }
      }
      saveState();
    }

    // listeners no custom-select
    if (selectBtn && optionsList) {
      selectBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        optionsList.classList.toggle("active");
        customSelect?.classList.toggle("open");
      });

      optionsItems.forEach(li => {
        li.addEventListener("click", () => {
          const value = li.dataset.value || li.getAttribute("data-value");
          updatePaymentUI(value);
          optionsList.classList.remove("active");
          customSelect?.classList.remove("open");
        });
      });

      document.addEventListener("click", (e) => {
        if (!e.target.closest(".custom-select")) {
          optionsList?.classList.remove("active");
          customSelect?.classList.remove("open");
        }
      });
    }

    // botão checkout
    if (checkoutBtn) {
      checkoutBtn.addEventListener("click", (e) => {
        if (e) e.preventDefault && e.preventDefault();
        clearCartAlert();

        if (!cart || cart.length === 0) {
          showCartAlert("Adicione itens no carrinho antes de continuar.");
          return;
        }

        if (!selectedPayment) {
          showCartAlert("Selecione a forma de pagamento antes de continuar.");
          return;
        }

        saveState();
        
        // Redirecionar para página de finalização
        window.location.href = "finalizar.php?payment=" + encodeURIComponent(selectedPayment);
      });
    }

    // inicia UI
    if (selectedPayment) updatePaymentUI(selectedPayment);
    renderCart();

    // Expor funções globalmente para debug
    window.carregarCarrinho = renderCart;
    window.atualizarTotal = updateTotal;
    window._cartDebug = { cart, updateTotal, updatePaymentUI, renderCart };
  }
})();